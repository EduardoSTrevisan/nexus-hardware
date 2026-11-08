<?php
declare(strict_types=1);

/** Loja acadêmica. Modo padrão: JSON com flock. Aceita PostgreSQL e MySQL via DB_DSN. */
final class LojaStore {
    private static ?PDO $pdo = null;
    private static function dsn(): string { return (string)(getenv('DB_DSN') ?: ''); }
    private static function con(): PDO {
        if (self::$pdo) return self::$pdo;
        if (self::dsn() === '') throw new RuntimeException('Banco de dados não configurado.');
        self::$pdo = new PDO(self::dsn(), (string)(getenv('DB_USER') ?: ''), (string)(getenv('DB_PASSWORD') ?: ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return self::$pdo;
    }
    private static function isPgsql(): bool { return str_starts_with(self::dsn(), 'pgsql:'); }
    private static function dataPath(): string { return dirname(__DIR__) . '/storage/loja.json'; }
    private static function arquivo(callable $callback, bool $mutacao = false): mixed {
        $path = self::dataPath();
        if (!is_dir(dirname($path))) throw new RuntimeException('Pasta storage ausente.');
        $f = fopen($path, 'c+');
        if (!$f) throw new RuntimeException('Não foi possível abrir o arquivo de dados.');
        try {
            if (!flock($f, LOCK_EX)) throw new RuntimeException('Erro ao bloquear o arquivo.');
            rewind($f);
            $raw = stream_get_contents($f);
            if ($raw === '' || $raw === false) {
                $produtos = json_decode((string)file_get_contents(dirname(__DIR__) . '/seed/produtos.json'), true, 512, JSON_THROW_ON_ERROR);
                $dados = ['produtos' => $produtos, 'pedidos' => [], 'sequencia' => 0];
                $mutacao = true;
            } else {
                $dados = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            }
            $resultado = $callback($dados);
            if ($mutacao) {
                rewind($f);
                if (!ftruncate($f, 0)) throw new RuntimeException('Não foi possível gravar o arquivo.');
                fwrite($f, json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
                fflush($f);
            }
            flock($f, LOCK_UN);
            return $resultado;
        } finally { fclose($f); }
    }
    private static function adaptar(array $row): array {
        if (self::dsn() === '') return $row;
        return [
            'id' => (int)$row['id'], 'nome' => $row['nome'], 'categoria' => $row['categoria'],
            'tipo' => $row['tipo'], 'preco' => (int)$row['preco_centavos'] / 100,
            'estoque' => (int)$row['estoque'], 'imagem' => $row['imagem'],
            'destaque' => in_array(strtolower((string)$row['destaque']), ['1','t','true'], true), 'selo' => $row['selo'],
            'descricao' => $row['descricao'],
            'especificacoes' => json_decode($row['especificacoes'], true, 512, JSON_THROW_ON_ERROR),
        ];
    }
    public static function produtos(): array {
        if (self::dsn() === '') return self::arquivo(fn($d) => $d['produtos']);
        $rows = self::con()->query('SELECT * FROM produtos ORDER BY id')->fetchAll();
        return array_map([self::class, 'adaptar'], $rows);
    }
    public static function produto(int $id): ?array {
        if ($id <= 0) return null;
        if (self::dsn() === '') return self::arquivo(function($d) use ($id) {
            foreach ($d['produtos'] as $produto) if ($produto['id'] === $id) return $produto;
            return null;
        });
        $stmt = self::con()->prepare('SELECT * FROM produtos WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::adaptar($row) : null;
    }
    /** Frete apenas ilustrativo: não representa preço de transportadora. */
    public static function calcularFrete(string $cep, int $subtotalCentavos): array {
        $digitos = preg_replace('/\D/', '', $cep);
        if (!preg_match('/^\d{8}$/', $digitos)) throw new InvalidArgumentException('CEP deve conter 8 números.');
        // Exemplo didático: SP 01-19, demais CEPs outras regiões; grátis acima de R$ 3 mil.
        $centavos = $subtotalCentavos >= 300000 ? 0 : ((int)substr($digitos, 0, 2) <= 19 ? 2490 : 3990);
        return ['cep'=>$digitos, 'valor'=>$centavos/100, 'prazo'=>'3 a 8 dias úteis (simulação)', 'simulado'=>true];
    }
    public static function pedido(array $cliente, array $itens): array {
        $nome = trim((string)($cliente['nome'] ?? ''));
        $email = trim((string)($cliente['email'] ?? ''));
        if ($nome === '' || strlen($nome) > 240 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 160) {
            throw new InvalidArgumentException('Informe nome e e-mail válidos.');
        }
        $cep = trim((string)($cliente['cep'] ?? ''));
        if ($cep !== '' && !preg_match('/^\d{5}-?\d{3}$/', $cep)) throw new InvalidArgumentException('CEP inválido.');
        if (count($itens) < 1 || count($itens) > 25) throw new InvalidArgumentException('Informe de 1 a 25 produtos no pedido.');
        $quantidades = [];
        foreach ($itens as $item) {
            if (!is_array($item)) throw new InvalidArgumentException('Item inválido.');
            $id = filter_var($item['produto_id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
            $qtd = filter_var($item['quantidade'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>50]]);
            if (!$id || !$qtd) throw new InvalidArgumentException('Produto ou quantidade inválida.');
            $quantidades[(int)$id] = ($quantidades[(int)$id] ?? 0) + (int)$qtd;
            if ($quantidades[(int)$id] > 50) throw new InvalidArgumentException('Quantidade máxima: 50 por produto.');
        }
        ksort($quantidades);
        if (self::dsn() !== '') return self::pedidoBanco($nome, $email, $quantidades, $cep);
        return self::arquivo(function (&$dados) use ($nome, $email, $quantidades, $cep) {
            $precos = []; $totalCentavos = 0;
            foreach ($quantidades as $id => $qtd) {
                $produto = null;
                foreach ($dados['produtos'] as $p) if ($p['id'] === $id) { $produto = $p; break; }
                if (!$produto) throw new InvalidArgumentException('Produto ' . $id . ' não encontrado.');
                if ($produto['estoque'] < $qtd) throw new InvalidArgumentException('Estoque insuficiente para ' . $produto['nome'] . '.');
                $precos[$id] = (int)round($produto['preco'] * 100);
                $totalCentavos += $precos[$id] * $qtd;
            }
            foreach ($dados['produtos'] as &$p) if (isset($quantidades[$p['id']])) $p['estoque'] -= $quantidades[$p['id']];
            unset($p);
            $frete = $cep === '' ? 0 : (int)round(self::calcularFrete($cep, $totalCentavos)['valor'] * 100);
            $numero = ++$dados['sequencia'];
            $codigo = 'NX-' . str_pad((string)$numero, 6, '0', STR_PAD_LEFT);
            $dados['pedidos'][] = ['numero' => $codigo, 'cliente' => ['nome'=>$nome, 'email'=>$email], 'itens'=>$quantidades, 'total_centavos'=>$totalCentavos+$frete, 'frete_centavos'=>$frete, 'criado_em'=>gmdate('c')];
            return ['sucesso' => true, 'pedido' => $codigo, 'subtotal' => $totalCentavos / 100, 'frete' => $frete / 100, 'total' => ($totalCentavos + $frete) / 100];
        }, true);
    }
    private static function pedidoBanco(string $nome, string $email, array $quantidades, string $cep): array {
        $db = self::con(); $db->beginTransaction();
        try {
            $precos = []; $total = 0;
            $buscar = $db->prepare('SELECT nome, estoque, preco_centavos FROM produtos WHERE id = ? FOR UPDATE');
            foreach ($quantidades as $id => $qtd) {
                $buscar->execute([$id]); $p = $buscar->fetch();
                if (!$p) throw new InvalidArgumentException('Produto ' . $id . ' não encontrado.');
                if ((int)$p['estoque'] < $qtd) throw new InvalidArgumentException('Estoque insuficiente para ' . $p['nome'] . '.');
                $precos[$id] = (int)$p['preco_centavos'];
                $total += $precos[$id] * $qtd;
            }
            $frete = $cep === '' ? 0 : (int)round(self::calcularFrete($cep, $total)['valor'] * 100);
            // PostgreSQL exige RETURNING id; MySQL permite lastInsertId().
            $sql = 'INSERT INTO pedidos (cliente_nome, cliente_email, cep, frete_centavos, total_centavos, criado_em) VALUES (?, ?, ?, ?, ?, NOW())';
            if (self::isPgsql()) $sql .= ' RETURNING id';
            $inserir = $db->prepare($sql);
            $inserir->execute([$nome, $email, $cep, $frete, $total+$frete]);
            $pedidoId = self::isPgsql() ? (int)$inserir->fetchColumn() : (int)$db->lastInsertId();
            if ($pedidoId < 1) throw new RuntimeException('Não foi possível identificar o pedido.');
            $itemSql = $db->prepare('INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_centavos) VALUES (?, ?, ?, ?)');
            $estoqueSql = $db->prepare('UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?');
            foreach ($quantidades as $id => $qtd) {
                $estoqueSql->execute([$qtd, $id, $qtd]);
                if ($estoqueSql->rowCount() !== 1) throw new InvalidArgumentException('Estoque esgotado durante a compra.');
                $itemSql->execute([$pedidoId, $id, $qtd, $precos[$id]]);
            }
            $db->commit();
            return ['sucesso' => true, 'pedido' => 'NX-' . str_pad((string)$pedidoId, 6, '0', STR_PAD_LEFT), 'subtotal' => $total / 100, 'frete' => $frete / 100, 'total' => ($total+$frete) / 100];
        } catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); throw $e; }
    }
    public static function reset(): int {
        $seed = json_decode((string)file_get_contents(dirname(__DIR__) . '/seed/produtos.json'), true, 512, JSON_THROW_ON_ERROR);
        if (self::dsn() === '') return self::arquivo(function (&$dados) use ($seed) {
            $dados['produtos'] = $seed;
            return count($seed);
        }, true);
        $db = self::con(); $db->beginTransaction();
        try {
            $stmt = $db->prepare('UPDATE produtos SET estoque = ? WHERE id = ?');
            foreach ($seed as $p) $stmt->execute([$p['estoque'], $p['id']]);
            $db->commit(); return count($seed);
        } catch (Throwable $e) { $db->rollBack(); throw $e; }
    }
}

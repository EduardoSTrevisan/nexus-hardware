/*
  Esta camada é a única parte que precisará ser conectada ao PHP.
  O restante do frontend continua igual.

  Endpoints esperados (quando CONFIG.modo = 'php'):
  GET  /api/produtos.php
  GET  /api/produto.php?id=1
  POST /api/finalizar_pedido.php
*/
const API = {
  chaveDemo: 'nexus_produtos_demo_v1',

  async produtos() {
    if (CONFIG.modo === 'demo') {
      if (!localStorage.getItem(this.chaveDemo)) this.reporEstoque();
      return JSON.parse(localStorage.getItem(this.chaveDemo));
    }
    return this.requisitar('/produtos.php');
  },

  async produto(id) {
    if (CONFIG.modo === 'demo') {
      const produtos = await this.produtos();
      return produtos.find(p => p.id === Number(id)) || null;
    }
    return this.requisitar('/produto.php?id=' + encodeURIComponent(id));
  },

  async calcularFrete(cep, subtotal) {
    if (CONFIG.modo === 'php') {
      return this.requisitar('/frete.php?cep=' + encodeURIComponent(cep) + '&subtotal_centavos=' + Math.round(subtotal * 100));
    }
    const digitos = String(cep).replace(/\D/g, '');
    if (!/^\d{8}$/.test(digitos)) throw new Error('CEP deve conter 8 números.');
    return { cep: digitos, valor: subtotal >= 3000 ? 0 : Number(digitos.slice(0, 2)) <= 19 ? 24.9 : 39.9, prazo: '3 a 8 dias úteis (simulação)', simulado: true };
  },

  async finalizarPedido(pedido) {
    if (CONFIG.modo !== 'demo') {
      return this.requisitar('/finalizar_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(pedido)
      });
    }

    const produtos = await this.produtos();
    let total = 0;
    for (const item of pedido.itens) {
      const produto = produtos.find(p => p.id === Number(item.produto_id));
      if (!produto) throw new Error('Um produto não foi encontrado.');
      if (!Number.isInteger(item.quantidade) || item.quantidade < 1) throw new Error('Quantidade inválida.');
      if (produto.estoque < item.quantidade) throw new Error('Estoque insuficiente para ' + produto.nome + '.');
      total += produto.preco * item.quantidade;
    }
    for (const item of pedido.itens) {
      const produto = produtos.find(p => p.id === Number(item.produto_id));
      produto.estoque -= item.quantidade;
    }
    localStorage.setItem(this.chaveDemo, JSON.stringify(produtos));
    const frete = pedido.cliente.cep ? (await this.calcularFrete(pedido.cliente.cep, total)).valor : 0;
    const numero = 'NX-' + Date.now().toString().slice(-8);
    const resposta = { sucesso: true, pedido: numero, subtotal: Math.round(total * 100) / 100, frete, total: Math.round((total + frete) * 100) / 100 };
    const historico = JSON.parse(localStorage.getItem('nexus_pedidos_demo_v1') || '[]');
    historico.push({ ...resposta, cliente: pedido.cliente, itens: pedido.itens, criado_em: new Date().toISOString() });
    localStorage.setItem('nexus_pedidos_demo_v1', JSON.stringify(historico));
    return resposta;
  },

  reporEstoque() {
    if (CONFIG.modo !== 'demo') return;
    localStorage.setItem(this.chaveDemo, JSON.stringify(PRODUTOS_DEMO));
  },

  async requisitar(caminho, opcoes = {}) {
    let resposta;
    try {
      resposta = await fetch(CONFIG.apiBase + caminho, opcoes);
    } catch (erro) {
      throw new Error('Não foi possível conectar à API PHP. Verifique a URL e se o servidor está ligado.');
    }
    let dados;
    try {
      dados = await resposta.json();
    } catch (erro) {
      throw new Error('A API não retornou um JSON válido.');
    }
    if (!resposta.ok || dados?.sucesso === false) {
      throw new Error(dados?.erro || dados?.mensagem || 'Ocorreu um erro na API.');
    }
    return dados;
  }
};

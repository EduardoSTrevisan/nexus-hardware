let produtoAtual = null;
let quantidadeSelecionada = 1;

async function iniciarProduto() {
  const id = new URLSearchParams(location.search).get('id');
  const area = document.getElementById('conteudo-produto');
  if (!id || !/^\d+$/.test(id)) return mostrarErro('Produto não encontrado.');
  try {
    produtoAtual = await API.produto(Number(id));
    if (!produtoAtual) return mostrarErro('Produto não encontrado.');
    document.title = produtoAtual.nome + ' | NEXUS Hardware';
    document.getElementById('nome-breadcrumb').textContent = produtoAtual.nome;
    const p = produtoAtual;
    area.innerHTML = `<div class="detail-art"><img src="${escapar(p.imagem)}" alt="Ilustração de ${escapar(p.nome)}"></div>
      <div class="detail-info">
        <div class="detail-category">${escapar(p.categoria)} <span>›</span> ${escapar(p.tipo)}</div>
        <h1>${escapar(p.nome)}</h1>
        <p class="detail-description">${escapar(p.descricao)}</p>
        <p class="stock detail-stock ${p.estoque ? '' : 'out'}"><span class="stock-dot"></span>${p.estoque ? `${p.estoque} unidades disponíveis` : 'Produto esgotado'}</p>
        <div class="detail-price">${moeda(p.preco)}<small>Valor ilustrativo para o projeto</small></div>
        <div class="detail-controls">
          <div class="qty-control" aria-label="Quantidade"><button id="menos" type="button" aria-label="Diminuir quantidade">−</button><span id="qtd">1</span><button id="mais" type="button" aria-label="Aumentar quantidade">+</button></div>
          <button id="adicionar" class="btn btn-primary" ${p.estoque ? '' : 'disabled'}>Adicionar ao carrinho <span>↗</span></button>
        </div>
        <button id="comprar" class="btn btn-outline btn-full" ${p.estoque ? '' : 'disabled'}>Comprar agora</button>
        <div class="detail-benefits"><span>✓ Compra simulada</span><span>✓ Estoque atualizado após o pedido</span></div>
      </div>`;
    document.getElementById('especificacoes').innerHTML = p.especificacoes.map(s => `<li>${escapar(s)}</li>`).join('');
    document.getElementById('menos').addEventListener('click', () => alterarQtd(-1));
    document.getElementById('mais').addEventListener('click', () => alterarQtd(1));
    document.getElementById('adicionar').addEventListener('click', () => adicionarCarrinho(p, quantidadeSelecionada));
    document.getElementById('comprar').addEventListener('click', () => {
      if (adicionarCarrinho(p, quantidadeSelecionada)) location.href = 'carrinho.html';
    });
  } catch (erro) { mostrarErro(erro.message); }
}
function alterarQtd(diferenca) {
  quantidadeSelecionada = Math.min(Math.max(1, quantidadeSelecionada + diferenca), produtoAtual.estoque);
  document.getElementById('qtd').textContent = quantidadeSelecionada;
}
function mostrarErro(mensagem) {
  document.getElementById('conteudo-produto').innerHTML = `<div class="empty-state"><h2>${escapar(mensagem)}</h2><a class="btn btn-primary" href="index.html#produtos">Voltar à loja</a></div>`;
  document.getElementById('sec-especificacoes').hidden = true;
}
document.addEventListener('DOMContentLoaded', iniciarProduto);

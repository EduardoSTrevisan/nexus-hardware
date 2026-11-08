/* Funções compartilhadas pelas três páginas. JavaScript puro. */
const CHAVE_CARRINHO = 'nexus_carrinho_v1';

function moeda(valor) {
  return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function carrinho() {
  return JSON.parse(localStorage.getItem(CHAVE_CARRINHO) || '[]');
}

function salvarCarrinho(itens) {
  localStorage.setItem(CHAVE_CARRINHO, JSON.stringify(itens));
  atualizarContador();
}

function atualizarContador() {
  const qtd = carrinho().reduce((total, item) => total + item.quantidade, 0);
  document.querySelectorAll('[data-contador-carrinho]').forEach(el => {
    el.textContent = qtd;
    el.setAttribute('aria-label', qtd + ' itens no carrinho');
  });
}

function adicionarCarrinho(produto, quantidade = 1) {
  if (!Number.isInteger(quantidade) || quantidade < 1) return aviso('Quantidade inválida.');
  const itens = carrinho();
  const existente = itens.find(item => item.produto_id === produto.id);
  const atual = existente ? existente.quantidade : 0;
  if (produto.estoque < atual + quantidade) {
    aviso('Não há estoque suficiente para essa quantidade.', 'erro');
    return false;
  }
  if (existente) existente.quantidade += quantidade;
  else itens.push({ produto_id: produto.id, quantidade });
  salvarCarrinho(itens);
  aviso('Produto adicionado ao carrinho!');
  return true;
}

function aviso(mensagem, tipo = 'ok') {
  const el = document.getElementById('toast');
  if (!el) return;
  el.textContent = mensagem;
  el.className = 'toast mostrar' + (tipo === 'erro' ? ' toast-erro' : '');
  clearTimeout(window.tempoToast);
  window.tempoToast = setTimeout(() => { el.className = 'toast'; }, 3500);
}

function escapar(texto) {
  return String(texto).replace(/[&<>"']/g, letra => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[letra]);
}

function linkProduto(id) {
  return CONFIG.modo === 'php' && CONFIG.seoProductPages ? 'p.php?id=' + encodeURIComponent(id) : 'produto.html?id=' + encodeURIComponent(id);
}

function cardProduto(p) {
  return `<article class="product-card">
    <a href="${linkProduto(p.id)}" class="product-art" aria-label="Ver ${escapar(p.nome)}">
      ${p.selo && p.estoque ? `<span class="card-tag">${escapar(p.selo)}</span>` : ''}
      <img src="${escapar(p.imagem)}" alt="Ilustração de ${escapar(p.nome)}" loading="lazy" />
    </a>
    <div class="product-content">
      <span class="eyebrow">${escapar(p.tipo)}</span>
      <a class="product-title" href="${linkProduto(p.id)}">${escapar(p.nome)}</a>
      <p class="stock ${p.estoque ? '' : 'out'}"><span class="stock-dot"></span>${p.estoque ? `${p.estoque} em estoque` : 'Esgotado'}</p>
      <div class="product-bottom">
        <strong class="product-price">${moeda(p.preco)}</strong>
        <button class="icon-add" aria-label="Adicionar ${escapar(p.nome)} ao carrinho" title="Adicionar ao carrinho" data-adicionar="${p.id}" ${p.estoque ? '' : 'disabled'}>
          ${p.estoque ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>' : '<span>—</span>'}
        </button>
      </div>
    </div>
  </article>`;
}

document.addEventListener('DOMContentLoaded', () => {
  atualizarContador();
  document.querySelectorAll('[data-ano]').forEach(el => el.textContent = new Date().getFullYear());
  const atual = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('[data-link-nav]').forEach(link => {
    if (link.getAttribute('href').split('#')[0] === atual) link.classList.add('ativo');
  });
});

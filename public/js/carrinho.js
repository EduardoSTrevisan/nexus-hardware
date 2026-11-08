let produtosCarrinho = [];
let freteAtual = null;
let subtotalAtual = 0;

async function iniciarCarrinho() {
  try {
    produtosCarrinho = await API.produtos();
    if (!Array.isArray(produtosCarrinho)) throw new Error('A API deve retornar uma lista de produtos.');
    renderizarCarrinho();
  } catch (erro) {
    document.getElementById('lista-carrinho').innerHTML = `<div class="empty-state"><h3>Não foi possível carregar o carrinho</h3><p>${escapar(erro.message)}</p></div>`;
    aviso(erro.message, 'erro');
  }
}

function itensDetalhados() {
  return carrinho().map(item => ({ ...item, produto: produtosCarrinho.find(p => p.id === item.produto_id) }))
    .filter(item => item.produto);
}

function renderizarCarrinho() {
  const itens = itensDetalhados();
  const lista = document.getElementById('lista-carrinho');
  const conteudo = document.getElementById('layout-carrinho');
  document.getElementById('sucesso-pedido').hidden = true;
  conteudo.hidden = false;
  if (!itens.length) {
    lista.innerHTML = `<div class="empty-state empty-cart"><div class="empty-cart-icon">◫</div><h2>Seu carrinho está vazio</h2><p>Que tal escolher as peças para seu próximo setup?</p><a class="btn btn-primary" href="index.html#produtos">Ver produtos <span>↗</span></a></div>`;
    document.getElementById('resumo-pedido').hidden = true;
    document.getElementById('form-pedido').hidden = true;
    return;
  }
  document.getElementById('resumo-pedido').hidden = false;
  document.getElementById('form-pedido').hidden = false;
  lista.innerHTML = itens.map(({ produto: p, quantidade }) => `<div class="cart-line">
      <a class="cart-image" href="${linkProduto(p.id)}"><img src="${escapar(p.imagem)}" alt="${escapar(p.nome)}"></a>
      <div class="cart-item-info"><span class="eyebrow">${escapar(p.tipo)}</span><a href="${linkProduto(p.id)}" class="cart-item-name">${escapar(p.nome)}</a><p>${moeda(p.preco)} por unidade</p><button class="remove-link" data-remover="${p.id}">Remover</button></div>
      <div class="cart-right"><div class="qty-control qty-small"><button data-quantidade="${p.id}" data-delta="-1" aria-label="Diminuir quantidade">−</button><span>${quantidade}</span><button data-quantidade="${p.id}" data-delta="1" aria-label="Aumentar quantidade">+</button></div><strong>${moeda(p.preco * quantidade)}</strong></div>
    </div>`).join('');
  const subtotal = itens.reduce((total, item) => total + item.produto.preco * item.quantidade, 0);
  subtotalAtual = subtotal;
  // Quantidade alterada invalida o cálculo de frete anterior.
  freteAtual = null;
  document.getElementById('resumo-frete').textContent = 'Não calculada';
  document.getElementById('prazo-frete').textContent = '';
  const totalItens = itens.reduce((total, item) => total + item.quantidade, 0);
  document.getElementById('resumo-itens').textContent = totalItens;
  document.getElementById('resumo-subtotal').textContent = moeda(subtotal);
  document.getElementById('resumo-total').textContent = moeda(subtotal);
  document.getElementById('qtd-carrinho-titulo').textContent = totalItens + (totalItens === 1 ? ' item' : ' itens');
}

function mudarQuantidade(id, delta) {
  const itens = carrinho();
  const item = itens.find(i => i.produto_id === id);
  const p = produtosCarrinho.find(p => p.id === id);
  if (!item || !p) return;
  const novo = item.quantidade + delta;
  if (novo > p.estoque) return aviso('Estoque máximo disponível: ' + p.estoque + '.', 'erro');
  if (novo <= 0) return removerItem(id);
  item.quantidade = novo;
  salvarCarrinho(itens);
  renderizarCarrinho();
}

function removerItem(id) {
  salvarCarrinho(carrinho().filter(item => item.produto_id !== id));
  renderizarCarrinho();
  aviso('Produto removido do carrinho.');
}

async function finalizar(evento) {
  evento.preventDefault();
  const botao = document.getElementById('botao-finalizar');
  const itens = itensDetalhados();
  if (!itens.length) return aviso('Adicione produtos ao carrinho primeiro.', 'erro');
  const divergente = itens.find(item => item.quantidade > item.produto.estoque);
  if (divergente) return aviso('Estoque insuficiente de ' + divergente.produto.nome + '. Ajuste a quantidade.', 'erro');
  const nome = document.getElementById('nome-cliente').value.trim();
  const email = document.getElementById('email-cliente').value.trim();
  if (!nome || !email) return aviso('Preencha nome e e-mail.', 'erro');
  const cep = document.getElementById('cep-cliente').value.trim();
  const pedido = { cliente: { nome, email, cep }, itens: itens.map(i => ({ produto_id: i.produto_id, quantidade: i.quantidade })) };
  botao.disabled = true;
  botao.textContent = 'Finalizando...';
  try {
    const resposta = await API.finalizarPedido(pedido);
    if (!resposta || resposta.sucesso !== true || !resposta.pedido) throw new Error('A API não confirmou o pedido corretamente.');
    salvarCarrinho([]);
    document.getElementById('layout-carrinho').hidden = true;
    const sucesso = document.getElementById('sucesso-pedido');
    sucesso.hidden = false;
    sucesso.innerHTML = `<div class="success-mark">✓</div><p class="eyebrow">PEDIDO CONFIRMADO</p><h2>Seu pedido foi registrado!</h2><p>Obrigado, ${escapar(nome)}. Esta é uma finalização de compra para fins acadêmicos, sem cobrança real.</p><div class="order-number"><span>Número do pedido</span><strong>${escapar(resposta.pedido)}</strong></div><div class="order-number"><span>Total</span><strong>${moeda(resposta.total)}</strong></div><a class="btn btn-primary" href="index.html#produtos">Continuar comprando ↗</a>`;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (erro) {
    aviso(erro.message, 'erro');
    try { produtosCarrinho = await API.produtos(); renderizarCarrinho(); } catch (_) { /* já existe a mensagem do erro */ }
  } finally {
    botao.disabled = false;
    botao.textContent = 'Confirmar pedido de demonstração ↗';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  iniciarCarrinho();
  document.getElementById('lista-carrinho').addEventListener('click', e => {
    const alterar = e.target.closest('[data-quantidade]');
    if (alterar) return mudarQuantidade(Number(alterar.dataset.quantidade), Number(alterar.dataset.delta));
    const remover = e.target.closest('[data-remover]');
    if (remover) removerItem(Number(remover.dataset.remover));
  });
  document.getElementById('form-pedido').addEventListener('submit', finalizar);
  document.getElementById('btn-calcular-frete').addEventListener('click', async () => {
    const botao = document.getElementById('btn-calcular-frete');
    botao.disabled = true;
    try {
      const cep = document.getElementById('cep-cliente').value.trim();
      freteAtual = await API.calcularFrete(cep, subtotalAtual);
      document.getElementById('resumo-frete').textContent = moeda(freteAtual.valor) + ' (simulado)';
      document.getElementById('resumo-total').textContent = moeda(subtotalAtual + freteAtual.valor);
      document.getElementById('prazo-frete').textContent = freteAtual.prazo + '. Sem entrega real.';
    } catch (erro) { aviso(erro.message, 'erro'); }
    finally { botao.disabled = false; }
  });
  document.getElementById('cep-cliente').addEventListener('input', () => {
    freteAtual = null;
    document.getElementById('resumo-frete').textContent = 'Não calculada';
    document.getElementById('resumo-total').textContent = moeda(subtotalAtual);
    document.getElementById('prazo-frete').textContent = '';
  });
});

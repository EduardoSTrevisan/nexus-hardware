let todosProdutos = [];
let categoriaSelecionada = 'Todos';
let termoBusca = '';
let ordenacao = 'padrao';

async function iniciarVitrine() {
  const area = document.getElementById('grade-produtos');
  try {
    todosProdutos = await API.produtos();
    if (!Array.isArray(todosProdutos)) throw new Error('A API deve retornar uma lista de produtos.');
    montarCategorias();
    renderizarProdutos();
    const destaques = todosProdutos.filter(p => p.destaque && p.estoque > 0).slice(0, 4);
    document.getElementById('grade-destaques').innerHTML = destaques.map(cardProduto).join('');
  } catch (erro) {
    area.innerHTML = `<div class="empty-state"><h3>Não foi possível carregar os produtos</h3><p>${escapar(erro.message)}</p></div>`;
    document.getElementById('grade-destaques').innerHTML = '';
    aviso(erro.message, 'erro');
  }
  document.getElementById('demo-footer').hidden = CONFIG.modo !== 'demo';
}

function montarCategorias() {
  const categorias = ['Todos', ...new Set(todosProdutos.map(p => p.categoria))];
  const area = document.getElementById('categorias');
  area.innerHTML = categorias.map(cat => `<button type="button" class="category-btn ${cat === categoriaSelecionada ? 'selected' : ''}" data-categoria="${escapar(cat)}">${escapar(cat)}</button>`).join('');
}

function renderizarProdutos() {
  let resultado = todosProdutos.filter(p =>
    (categoriaSelecionada === 'Todos' || p.categoria === categoriaSelecionada) &&
    (p.nome + ' ' + p.tipo + ' ' + p.categoria).toLocaleLowerCase('pt-BR').includes(termoBusca)
  );
  if (ordenacao === 'menor') resultado.sort((a, b) => a.preco - b.preco);
  if (ordenacao === 'maior') resultado.sort((a, b) => b.preco - a.preco);
  if (ordenacao === 'nome') resultado.sort((a, b) => a.nome.localeCompare(b.nome, 'pt-BR'));
  document.getElementById('quantidade-resultados').textContent = resultado.length + (resultado.length === 1 ? ' produto encontrado' : ' produtos encontrados');
  document.getElementById('grade-produtos').innerHTML = resultado.length
    ? resultado.map(cardProduto).join('')
    : '<div class="empty-state"><h3>Nenhum produto encontrado</h3><p>Tente outra busca ou selecione uma categoria diferente.</p></div>';
}

document.addEventListener('DOMContentLoaded', () => {
  iniciarVitrine();
  document.getElementById('busca').addEventListener('input', e => {
    termoBusca = e.target.value.toLocaleLowerCase('pt-BR').trim();
    renderizarProdutos();
  });
  document.getElementById('ordenar').addEventListener('change', e => {
    ordenacao = e.target.value;
    renderizarProdutos();
  });
  document.getElementById('categorias').addEventListener('click', e => {
    const botao = e.target.closest('[data-categoria]');
    if (!botao) return;
    categoriaSelecionada = botao.dataset.categoria;
    montarCategorias();
    renderizarProdutos();
  });
  document.addEventListener('click', e => {
    const botao = e.target.closest('[data-adicionar]');
    if (!botao) return;
    const produto = todosProdutos.find(p => p.id === Number(botao.dataset.adicionar));
    if (produto) adicionarCarrinho(produto);
  });
  document.getElementById('reset-demo').addEventListener('click', async () => {
    if (!confirm('Restaurar os estoques dos produtos de demonstração?')) return;
    API.reporEstoque();
    salvarCarrinho([]);
    await iniciarVitrine();
    aviso('Estoques de demonstração restaurados.');
  });
});

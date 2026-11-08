/* Dados fictícios para testar o frontend antes de criar o backend PHP. */
const PRODUTOS_DEMO = [
  {
    id: 1, nome: 'Placa de Vídeo Phantom 12GB', categoria: 'Componentes', tipo: 'Placas de vídeo',
    preco: 2899.90, estoque: 8, imagem: 'assets/gpu.svg', destaque: true, selo: 'Mais procurado',
    descricao: 'Performance para jogos, estudos e criação de conteúdo. Uma opção para montar um computador de alto desempenho.',
    especificacoes: ['Memória de vídeo: 12 GB', 'Interface: PCI Express', 'Resfriamento: 3 ventoinhas', 'Saídas de vídeo: HDMI e DisplayPort']
  },
  {
    id: 2, nome: 'Processador Hexa 6 Núcleos', categoria: 'Componentes', tipo: 'Processadores',
    preco: 1099.90, estoque: 12, imagem: 'assets/cpu.svg', destaque: true, selo: 'Destaque',
    descricao: 'Processador com seis núcleos, ideal para multitarefas, produtividade e seu próximo setup.',
    especificacoes: ['6 núcleos e 12 threads', 'Frequência base: 3,8 GHz', 'Arquitetura de 64 bits', 'Cooler não incluso']
  },
  {
    id: 3, nome: 'Memória RAM Pulse RGB 16GB', categoria: 'Componentes', tipo: 'Memórias',
    preco: 279.90, estoque: 20, imagem: 'assets/ram.svg', destaque: true, selo: 'Bom preço',
    descricao: 'Módulo de memória para dar mais agilidade ao computador, com acabamento moderno e iluminação RGB.',
    especificacoes: ['Capacidade: 16 GB', 'Padrão: DDR4', 'Frequência: 3200 MHz', 'Iluminação: RGB']
  },
  {
    id: 4, nome: 'SSD NVMe Flash 1TB', categoria: 'Componentes', tipo: 'Armazenamento',
    preco: 429.90, estoque: 15, imagem: 'assets/ssd.svg', destaque: false, selo: '',
    descricao: 'Armazenamento rápido para inicializar o sistema, instalar programas e guardar arquivos.',
    especificacoes: ['Capacidade: 1 TB', 'Formato: M.2 2280', 'Interface: NVMe PCIe', 'Uso: desktop e notebooks compatíveis']
  },
  {
    id: 5, nome: 'Placa-Mãe Matrix B550M', categoria: 'Componentes', tipo: 'Placas-mãe',
    preco: 699.90, estoque: 7, imagem: 'assets/motherboard.svg', destaque: false, selo: '',
    descricao: 'Base para a montagem do computador, com slots de expansão e conectividade para o seu setup.',
    especificacoes: ['Formato: Micro ATX', 'Slots para memória DDR4', 'Slot M.2 para SSD', 'Conexões USB e rede']
  },
  {
    id: 6, nome: 'Fonte Modular Power 650W', categoria: 'Componentes', tipo: 'Fontes',
    preco: 439.90, estoque: 9, imagem: 'assets/psu.svg', destaque: false, selo: '',
    descricao: 'Fonte de alimentação para setups de médio porte, com cabeamento organizado.',
    especificacoes: ['Potência: 650 W', 'Cabeamento: modular', 'Ventoinha: 120 mm', 'Proteções elétricas integradas']
  },
  {
    id: 7, nome: 'Teclado Mecânico Spectrum', categoria: 'Periféricos', tipo: 'Teclados',
    preco: 259.90, estoque: 10, imagem: 'assets/keyboard.svg', destaque: true, selo: 'Para seu setup',
    descricao: 'Teclado mecânico com visual minimalista, iluminação RGB e teclas confortáveis.',
    especificacoes: ['Conexão: USB', 'Layout: ABNT2', 'Iluminação: RGB', 'Tipo: mecânico']
  },
  {
    id: 8, nome: 'Mouse Gamer Vector 12000 DPI', categoria: 'Periféricos', tipo: 'Mouses',
    preco: 149.90, estoque: 0, imagem: 'assets/mouse.svg', destaque: false, selo: '',
    descricao: 'Mouse ergonômico, com botões laterais e controle de sensibilidade.',
    especificacoes: ['Sensor: óptico', 'Até 12.000 DPI', 'Conexão: USB', 'Botões laterais programáveis']
  },
  {
    id: 9, nome: 'Gabinete Aero Mid Tower', categoria: 'Gabinetes', tipo: 'Gabinetes',
    preco: 389.90, estoque: 6, imagem: 'assets/case.svg', destaque: false, selo: '',
    descricao: 'Gabinete com painel lateral transparente e espaço para uma montagem organizada.',
    especificacoes: ['Formato: Mid Tower', 'Lateral: vidro temperado', 'Suporte a placas ATX', 'Frontal com boa ventilação']
  },
  {
    id: 10, nome: 'Monitor Vision 27” QHD', categoria: 'Monitores', tipo: 'Monitores',
    preco: 1499.90, estoque: 5, imagem: 'assets/monitor.svg', destaque: true, selo: 'Alta definição',
    descricao: 'Monitor de 27 polegadas com ótima área de trabalho e imagem nítida para jogos ou produtividade.',
    especificacoes: ['Tela: 27 polegadas', 'Resolução: QHD', 'Taxa de atualização: 165 Hz', 'Entradas: HDMI e DisplayPort']
  }
];

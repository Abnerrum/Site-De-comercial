CREATE DATABASE IF NOT EXISTS loja_virtual CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loja_virtual;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT,
    nome VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    descricao_curta VARCHAR(255),
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    preco_promocional DECIMAL(10,2) DEFAULT NULL,
    estoque INT DEFAULT 0,
    imagem VARCHAR(255) DEFAULT 'sem-imagem.jpg',
    destaque TINYINT(1) DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    cpf VARCHAR(14),
    endereco TEXT,
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('admin','gerente') DEFAULT 'admin',
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    status ENUM('pendente','pago','enviado','entregue','cancelado') DEFAULT 'pendente',
    total DECIMAL(10,2) NOT NULL,
    frete DECIMAL(10,2) DEFAULT 0,
    forma_pagamento VARCHAR(50),
    endereco_entrega TEXT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

INSERT INTO categorias (nome,slug,descricao) VALUES
('Eletronicos','eletronicos','Smartphones, notebooks e acessorios'),
('Roupas','roupas','Moda masculina, feminina e infantil'),
('Casa e Decoracao','casa-decoracao','Itens para sua casa'),
('Esportes','esportes','Equipamentos e acessorios esportivos'),
('Livros','livros','Livros fisicos e digitais');

INSERT INTO produtos (categoria_id,nome,slug,descricao_curta,descricao,preco,preco_promocional,estoque,imagem,destaque) VALUES
(1,'Smartphone Galaxy Pro','smartphone-galaxy-pro','Celular de ultima geracao com camera 108MP','O Smartphone Galaxy Pro vem equipado com processador octa-core, 12GB de RAM, 256GB de armazenamento e camera principal de 108MP. Tela AMOLED de 6.7 polegadas com taxa de atualizacao de 120Hz.',3999.90,3499.90,15,'produto1.jpg',1),
(1,'Notebook Ultra Slim','notebook-ultra-slim','Notebook leve e potente para trabalho','Notebook com processador Intel Core i7 de 12 geracao, 16GB de RAM, SSD de 512GB NVMe. Tela Full HD de 15.6 polegadas com bordas ultrafinas.',5499.90,NULL,8,'produto2.jpg',1),
(1,'Fone de Ouvido Bluetooth','fone-bluetooth-premium','Som de alta qualidade sem fios','Fone de ouvido com cancelamento de ruido ativo, bateria de 30 horas, conexao Bluetooth 5.3 e drivers de 40mm.',499.90,349.90,30,'produto3.jpg',1),
(2,'Camiseta Premium Algodao','camiseta-premium','100% algodao egipcio','Camiseta confeccionada em algodao egipcio de alta gramatura. Corte moderno, costuras reforcadas e acabamento premium.',129.90,89.90,50,'produto4.jpg',0),
(2,'Jaqueta Jeans Classica','jaqueta-jeans-classica','Estilo atemporal e versatil','Jaqueta jeans com lavagem media, botoes de metal, bolsos frontais e ajuste regular.',299.90,249.90,20,'produto5.jpg',0),
(3,'Luminaria LED Moderna','luminaria-led-moderna','Iluminacao ambiente ajustavel','Luminaria de mesa com design minimalista, controle de intensidade de luz, temperatura de cor ajustavel e braco articulado.',189.90,NULL,25,'produto6.jpg',1),
(3,'Conjunto de Panelas','conjunto-panelas-5pcs','5 pecas em aluminio reforcado','Conjunto com 5 panelas em aluminio reforcado com revestimento antiaderente ceramico.',459.90,379.90,12,'produto7.jpg',0),
(4,'Bicicleta Mountain Bike','bike-mountain-pro','Aro 29 com suspensao dianteira','Bicicleta com quadro em aluminio, aro 29, 21 marchas Shimano, freios a disco hidraulicos.',1899.90,1599.90,5,'produto8.jpg',1),
(4,'Caneleiras de Futebol','caneleiras-futebol-pro','Protecao maxima em campo','Caneleiras com estrutura em polipropileno de alta resistencia e espuma interna.',79.90,59.90,40,'produto9.jpg',0),
(5,'Livro: O Poder do Habito','livro-poder-do-habito','Best-seller de Charles Duhigg','Uma obra fundamental para entender como os habitos funcionam e como podemos transforma-los.',59.90,44.90,100,'produto10.jpg',0);

INSERT INTO administradores (nome,email,senha,nivel) VALUES
('Administrador','admin@loja.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin');

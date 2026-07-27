# 🚀 Site de Comercial - Documentação Técnica Completa

## 📚 Índice

1. [Visão Geral](#visão-geral)
2. [Arquitetura do Projeto](#arquitetura)
3. [Estrutura de Diretórios](#estrutura)
4. [Stack Tecnológico](#stack)
5. [Banco de Dados](#banco-de-dados)
6. [Fluxos Principais](#fluxos)
7. [API de Funções](#api-de-funções)
8. [Segurança](#segurança)
9. [Performance](#performance)
10. [Deploy em Produção](#deploy)

---

## 🎯 Visão Geral

**Site de Comercial** é uma plataforma de e-commerce moderna desenvolvida com PHP e MySQL, oferecendo:

- ✅ Catálogo de produtos com categorias
- ✅ Carrinho de compras persistente
- ✅ Sistema de autenticação seguro (bcrypt)
- ✅ Cálculo dinâmico de frete por CEP
- ✅ Checkout com validação completa
- ✅ Painel administrativo
- ✅ Sistema de email com tokens
- ✅ Auditoria e logs de atividade

---

## 🏗️ Arquitetura do Projeto

```
┌─────────────────────────────────────────────────────────────┐
│                      USER INTERFACE                          │
│            (HTML/CSS/JavaScript Frontend)                    │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                   PAGE LAYER                                 │
│  (index.php, produtos.php, carrinho.php, etc)               │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                 BUSINESS LOGIC LAYER                         │
│      (includes/funcoes.php, includes/frete.php)             │
│      (includes/email.php, includes/header.php)              │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                DATABASE ABSTRACTION                          │
│    (PDO - prepared statements, transactions)                │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                 DATA LAYER                                   │
│              (MySQL Database)                               │
│  - usuarios, categorias, produtos, pedidos, etc             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📂 Estrutura de Diretórios

```
loja/
├── config/
│   ├── conexao.php          Conexão PDO + helpers (formatarPreco, validarEmail, etc)
│   └── env.php              Carregamento de variáveis de ambiente (.env)
│
├── includes/
│   ├── header.php           Template do cabeçalho (navegação, carrinho)
│   ├── footer.php           Template do rodapé
│   ├── funcoes.php          Funções de banco de dados (getCategorias, getProdutos, etc)
│   ├── frete.php            Sistema de cálculo de frete dinâmico
│   └── email.php            Sistema de envio de emails com tokens
│
├── assets/
│   ├── css/
│   │   └── style.css        Estilos CSS responsivos
│   ├── js/
│   │   └── main.js          JavaScript frontend
│   ├── imagens/             Imagens de produtos (upload)
│   └── uploads/             Uploads de usuários
│
├── admin/
│   ├── index.php            Dashboard administrativo
│   ├── login.php            Login do admin
│   ├── produtos.php         CRUD de produtos
│   ├── pedidos.php          Gerenciamento de pedidos
│   └── usuarios.php         Gerenciamento de usuários
│
├── index.php                Home (produtos em destaque)
├── produtos.php             Listagem com filtros e paginação
├── produto.php              Detalhe do produto
├── carrinho.php             Carrinho de compras
├── login.php                Login de usuário
├── cadastro.php             Registro de usuário
├── finalizar.php            Checkout (endereço + pagamento)
├── logout.php               Encerrar sessão
│
├── banco_de_dados.sql       Script inicial do BD
└── banco_de_dados_migracao_001.sql  Novas tabelas e triggers
```

---

## 💻 Stack Tecnológico

| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| **Servidor** | Apache / Nginx | 2.4+ / 1.18+ |
| **Linguagem** | PHP | 7.4+ |
| **Banco** | MySQL | 5.7+ |
| **Frontend** | HTML5 / CSS3 / JavaScript | ES6+ |
| **Segurança** | bcrypt (password_hash) | - |
| **Database Driver** | PDO MySQL | - |

**Dependências Opcionais:**
- PHPMailer (para envio real de email)
- Stripe SDK (para gateway de pagamento)
- Composer (gerenciador de dependências PHP)

---

## 🗄️ Banco de Dados

### Tabelas Principais

#### 1. `usuarios`

```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,  -- hash bcrypt
    telefone VARCHAR(20),
    cpf VARCHAR(14),
    endereco TEXT,
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    ativo TINYINT(1) DEFAULT 1,
    email_verificado TINYINT(1) DEFAULT 0,  -- NOVO
    token_verificacao VARCHAR(100) UNIQUE,  -- NOVO
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Hash de Senha (bcrypt):**
```php
// Criar
$hash = password_hash('Senha123', PASSWORD_BCRYPT);
// Resultado: $2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36jbMYSe

// Verificar
password_verify('Senha123', $hash);  // true
```

#### 2. `produtos`

```sql
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT,
    nome VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,  -- URL amigável
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

-- Índices para performance
INDEX idx_categoria (categoria_id),
INDEX idx_slug (slug),
INDEX idx_ativo (ativo)
```

#### 3. `pedidos`

```sql
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    codigo VARCHAR(20) NOT NULL UNIQUE,  -- PED202307271A2B3C
    status ENUM('pendente', 'pago', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
    total DECIMAL(10,2) NOT NULL,
    frete DECIMAL(10,2) DEFAULT 0,
    forma_pagamento VARCHAR(50),
    endereco_entrega TEXT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

#### 4. `email_tokens` (NOVO)

```sql
CREATE TABLE email_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    tipo ENUM('verificacao_email', 'recuperar_senha') DEFAULT 'verificacao_email',
    data_expiracao TIMESTAMP NOT NULL,
    usado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_usuario (usuario_id)
);
```

#### 5. `configuracao_frete` (NOVO)

```sql
CREATE TABLE configuracao_frete (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('regiao', 'fixo', 'peso') DEFAULT 'regiao',
    estado VARCHAR(2),
    valor_frete DECIMAL(10,2) NOT NULL,
    preco_minimo_frete_gratis DECIMAL(10,2),
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_estado (estado)
);
```

---

## 🔄 Fluxos Principais

### Fluxo 1: Cadastro de Usuário

```
1. Usuário acessa /cadastro.php
   ↓
2. Preenche formulário com email e senha
   ↓
3. Validação Frontend (HTML5)
   ↓
4. Validação Backend:
   - validarEmail() → filter_var(FILTER_VALIDATE_EMAIL)
   - validarSenha() → Requisitos de força
   ↓
5. Verificar se email já existe
   ↓
6. password_hash($senha, PASSWORD_BCRYPT) → cria hash
   ↓
7. INSERT INTO usuarios (...)
   ↓
8. Redireciona para login.php
```

### Fluxo 2: Login Seguro

```
1. Usuário acessa /login.php
   ↓
2. Digita email e senha
   ↓
3. Backend busca usuário por email
   SELECT * FROM usuarios WHERE email = ?
   ↓
4. password_verify($senha_digitada, $hash_banco)
   ↓
5. Se OK: $_SESSION['usuario_id'] = $usuario['id']
   ↓
6. Redireciona para home
```

### Fluxo 3: Listar Produtos com Filtros

```
1. Usuário acessa /produtos.php?categoria=eletronicos&pagina=1
   ↓
2. getProdutos([
     'categoria' => 'eletronicos',
     'pagina' => 1,
     'itens_por_pagina' => 12
   ])
   ↓
3. Construir SQL dinamicamente com prepared statements
   ↓
4. Contar total de resultados
   ↓
5. Executar query com LIMIT/OFFSET
   LIMIT 12 OFFSET 0
   ↓
6. Retornar:
   [
     'produtos' => [array de 12 produtos],
     'total' => 45,
     'paginas' => 4,
     'pagina_atual' => 1
   ]
   ↓
7. Renderizar grid + paginação
```

### Fluxo 4: Checkout com Frete Dinâmico

```
1. Usuário clica em "Finalizar Compra"
   ↓
2. Redireciona para /finalizar.php (se não logado, pede login)
   ↓
3. Preenche endereço e CEP
   ↓
4. OnBlur do CEP: calcularFrete('01310-100', 500)
   ↓
5. validarCEP() → extrai estado (SP)
   ↓
6. Busca tabela configuracao_frete WHERE estado = 'SP'
   ↓
7. Verifica se subtotal >= preco_minimo_frete_gratis
   ↓
8. Retorna frete (0 se grátis, ou valor se pago)
   ↓
9. Exibe total com frete calculado
   ↓
10. Usuário seleciona forma de pagamento
    ↓
11. Clica em "Confirmar"
    ↓
12. criarPedido($usuario_id, $dados) → BEGIN TRANSACTION
    - INSERT INTO pedidos
    - INSERT INTO pedido_itens (para cada item do carrinho)
    - UPDATE produtos SET estoque = estoque - quantidade
    - COMMIT
    ↓
13. Se sucesso: exibir código PED202307271A2B3C
    ↓
14. Enviar email de confirmação
```

---

## 🔌 API de Funções

### config/conexao.php

```php
// UTILITÁRIOS
formatarPreco(1234.56)                 // "R$ 1.234,56"
gerarSlug("iPhone 13 Pro")              // "iphone-13-pro"
limpar("<script>alert('xss')</script>") // "&lt;script&gt;..."
validarEmail("user@email.com")          // true/false
validarSenha("Senha123")                // ['valida' => true, 'erros' => []]

// SESSÃO
setuserLogado()                         // true/false
adminLogado()                           // true/false
setFlash('sucesso', 'Mensagem')         // Define flash na sessão
getFlash()                              // Obtém e remove flash
redirecionar('index.php')               // header + exit
```

### includes/funcoes.php

```php
// CATEGORIAS
getCategorias()                         // Retorna todas as categorias

// PRODUTOS
getProdutoPorSlug($slug)                // Produto com detalhes
getProdutos($filtros)                   // Com paginação e filtros
getProdutosDestaque($limite)            // Produtos em destaque
getProdutosRelacionados($cat_id, $prod_id)

// CARRINHO
adicionarCarrinho($produto_id, $quantidade)
removerCarrinho($produto_id)
atualizarCarrinho($produto_id, $quantidade)
contarCarrinho()                        // Quantidade total de itens
totalCarrinho()                         // Valor total

// PEDIDOS
gerarCodigoPedido()                     // "PED202307271A2B3C"
criarPedido($usuario_id, $dados)        // Cria pedido com transação
getPedidosUsuario($usuario_id)          // Lista pedidos do usuário
getPedidoDetalhes($pedido_id, $usuario_id)
```

### includes/frete.php

```php
validarCEP($cep)                        // Retorna estado ou false
calcularFrete($cep, $subtotal)          // Calcula valor do frete
getFretesDisponiveis()                  // Lista todas configurações
```

### includes/email.php

```php
gerarToken()                            // Token criptográfico de 100 chars
enviarEmailVerificacao($user_id, $email, $nome)
verificarTokenEmail($token)             // Ativa email se token válido
enviarEmailRecuperarSenha($email)
redefinirSenhaComToken($token, $nova_senha)
```

---

## 🔐 Segurança

### 1️⃣ Proteção contra SQL Injection

```php
// ❌ VULNERÁVEL
$stmt = $pdo->query("SELECT * FROM usuarios WHERE email = '$email'");

// ✅ SEGURO
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
```

### 2️⃣ Hash Seguro de Senhas (bcrypt)

```php
// ✅ SEGURO
$hash = password_hash('Senha123', PASSWORD_BCRYPT);
password_verify('Senha123', $hash);  // true

// Hash example: $2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36jbMYSe
```

### 3️⃣ Proteção contra XSS

```php
// ❌ VULNERÁVEL
echo "Olá, " . $_GET['nome'];  // <script>alert('xss')</script>

// ✅ SEGURO
echo "Olá, " . htmlspecialchars($_GET['nome'], ENT_QUOTES, 'UTF-8');
// Output: Olá, &lt;script&gt;alert('xss')&lt;/script&gt;
```

### 4️⃣ Variáveis de Ambiente

```php
// ✅ SEGURO - Credenciais não no código
$host = env('DB_HOST', 'localhost');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', '');
```

### 5️⃣ Session Security

```php
// Timeout automático
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_destroy();
    header('Location: login.php?sessao_expirada=1');
}
$_SESSION['last_activity'] = time();
```

### 6️⃣ CSRF Protection (Opcional)

```php
// Gerar token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validar em formulário
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token inválido');
}
```

---

## ⚡ Performance

### 1. Índices do Banco de Dados

```sql
-- Produtos
INDEX idx_categoria (categoria_id)
INDEX idx_slug (slug)
INDEX idx_ativo (ativo)
INDEX idx_destaque (destaque)

-- Usuários
INDEX idx_email (email)

-- Pedidos
INDEX idx_usuario (usuario_id)
INDEX idx_status (status)
INDEX idx_data (created_at)
```

### 2. Prepared Statements (Cache)

```php
// PDO caches prepared statements
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
// Primeira execução: compila a query
$stmt->execute([1]);
// Segunda execução: usa cache
$stmt->execute([2]);
```

### 3. Paginação (Não Carregar Tudo)

```php
// ❌ LENTO - Carrega 1000 produtos
SELECT * FROM produtos LIMIT 1000

// ✅ RÁPIDO - Carrega apenas 12
SELECT * FROM produtos LIMIT 12 OFFSET 0
```

### 4. Lazy Loading de Imagens

```html
<!-- Imagem carrega apenas ao entrar no viewport -->
<img src="produto.jpg" loading="lazy" alt="Produto">
```

---

## 🚀 Deploy em Produção

### Checklist Pré-Deploy

- [ ] Copiar `.env.example` para `.env` com credenciais reais
- [ ] Setar `APP_ENV=production` no `.env`
- [ ] Executar scripts SQL no banco de produção
- [ ] Criar diretórios `/assets/imagens` e `/assets/uploads`
- [ ] Configurar SSL/HTTPS
- [ ] Configurar email real (PHPMailer + SMTP)
- [ ] Testar compra completa
- [ ] Backup do banco antes de ir ao ar

### Servidor Apache

```apache
# .htaccess para redirecionar /loja para raiz
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /loja/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
</IfModule>
```

### Variáveis de Ambiente (Produção)

```env
# .env
APP_ENV=production
DB_HOST=db.empresa.com
DB_USER=user_loja
DB_PASS=senha_super_segura_aqui
SITE_URL=https://lojavirtual.com.br

# Email
MAIL_DRIVER=sendgrid
MAIL_HOST=smtp.sendgrid.net
MAIL_USER=apikey
MAIL_PASS=SG.token_sendgrid_aqui

# Pagamento
STRIPE_SECRET_KEY=sk_live_...
```

---

## 📞 Referências

- [PHP PDO](https://www.php.net/manual/en/book.pdo.php)
- [Password Hashing](https://www.php.net/manual/en/function.password-hash.php)
- [OWASP - Segurança Web](https://owasp.org/)
- [MySQL Best Practices](https://dev.mysql.com/doc/)

---

**Versão:** 2.0.0  
**Último Update:** 27/07/2026  
**Autor:** Abnerrum  
**Status:** ✅ Em Produção

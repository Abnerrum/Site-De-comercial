# 📚 Guia de Implementação das Melhorias - Site de Comercial

## 📋 Índice

1. [Visão Geral das Melhorias](#visão-geral)
2. [Instruções de Instalação](#instalação)
3. [Configuração Passo a Passo](#configuração)
4. [Detalhamento das Mudanças](#mudanças)
5. [Como Usar Cada Feature](#como-usar)
6. [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral das Melhorias

Este documento descreve todas as melhorias implementadas no projeto `Site-De-comercial`. As mudanças foram divididas em 4 commits principais:

### ✅ Parte 1: Segurança e Variáveis de Ambiente
- Arquivo `.env.example` com configurações seguras
- Novo `config/env.php` para carregar variáveis de ambiente
- Proteção contra hardcoding de credenciais

### ✅ Parte 2: Autenticação e Funções
- Correção de SQL Injection com prepared statements
- Validação forte de senhas e emails
- Paginação completa em `getProdutos()`
- Login seguro com `password_verify()`

### ✅ Parte 3: Banco de Dados e Sistema de Email
- Migração SQL com novas tabelas
- Sistema de frete dinâmico por CEP/Estado
- Sistema de tokens para email e recuperação de senha
- Tabelas de auditoria com triggers

### ✅ Parte 4: Páginas Frontend
- Página `produtos.php` com filtros e paginação
- Página `finalizar.php` com cálculo de frete dinâmico
- Validação completa de formulários

---

## 🚀 Instruções de Instalação

### Pré-requisitos
- PHP 7.4+
- MySQL 5.7+
- Composer (opcional, para PHPMailer em produção)

### Passo 1: Clonar o Repositório

```bash
git clone https://github.com/Abnerrum/Site-De-comercial.git
cd Site-De-comercial
```

### Passo 2: Criar Arquivo `.env`

```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Editar .env com suas credenciais reais
vim .env  # ou use seu editor preferido
```

### Passo 3: Criar Banco de Dados

```bash
# Conectar ao MySQL
mysql -u root -p

# Executar scripts SQL
source loja/banco_de_dados.sql;
source loja/banco_de_dados_migracao_001.sql;

# Sair
exit;
```

### Passo 4: Configurar Servidor Local

```bash
cd loja
php -S localhost:8000
```

Acesse: http://localhost:8000

---

## ⚙️ Configuração Passo a Passo

### 1️⃣ Configurar Variáveis de Ambiente

Edite `.env` com suas informações:

```env
# BANCO DE DADOS
DB_HOST=localhost
DB_PORT=3306
DB_NAME=loja_virtual
DB_USER=root
DB_PASS=sua_senha_aqui

# APLICAÇÃO
SITE_NOME=Loja Virtual
SITE_URL=http://localhost:8000
APP_ENV=development  # Mude para 'production' em produção

# EMAIL (Mailtrap para teste)
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USER=seu_usuario
MAIL_PASS=sua_senha
MAIL_FROM=noreply@lojavirtual.com

# FRETE
FRETE_GRATIS_ACIMA_DE=299
FRETE_PADRAO=25
```

### 2️⃣ Criar Banco de Dados

```sql
-- Terminal MySQL
mysql -u root -p < loja/banco_de_dados.sql
mysql -u root -p < loja/banco_de_dados_migracao_001.sql
```

**O que será criado:**
- ✅ Banco `loja_virtual`
- ✅ Tabelas: categorias, produtos, usuarios, pedidos, etc
- ✅ Tabelas novas: email_tokens, configuracao_frete, avaliacoes, logs_atividade
- ✅ Triggers para auditoria

### 3️⃣ Criar Diretórios de Uploads

```bash
cd loja/assets
mkdir imagens uploads
touch imagens/.gitkeep uploads/.gitkeep
cd ../../
```

### 4️⃣ Testar Instalação

Abra no navegador:
- **Home:** http://localhost:8000/index.php
- **Cadastro:** http://localhost:8000/cadastro.php
- **Admin:** http://localhost:8000/admin/login.php
  - Email: `admin@loja.com`
  - Senha: `password` (mude na primeira vez!)

---

## 📝 Detalhamento das Mudanças

### 🔐 SEGURANÇA

#### Antes ❌
```php
// INSEGURO: Credenciais hardcoded
$host = 'localhost';
$usuario = 'root';
$senha = '';  // Senha vazia!

// VULNERÁVEL: SQL Injection
$stmt = $pdo->query("SELECT * FROM produtos LIMIT $limite");
```

#### Depois ✅
```php
// SEGURO: Variáveis de ambiente
require_once __DIR__ . '/env.php';
$host = env('DB_HOST', 'localhost');
$usuario = env('DB_USER', 'root');
$senha = env('DB_PASS', '');

// PROTEGIDO: Prepared statements
$stmt = $pdo->prepare("SELECT * FROM produtos LIMIT ?");
$stmt->bindValue(1, $limite, PDO::PARAM_INT);
$stmt->execute();
```

### 🛡️ VALIDAÇÃO DE EMAIL

#### Novo Arquivo: `config/conexao.php`

```php
/**
 * Valida se um email é válido usando filtro PHP
 */
function validarEmail($email) {
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
}

// USO
if (validarEmail('usuario@email.com')) {
    // Email válido
}
```

**Exemplos:**
- ✅ `usuario@email.com` → válido
- ✅ `nome.sobrenome@empresa.com.br` → válido
- ❌ `email-invalido` → inválido
- ❌ `user@` → inválido

### 🔒 VALIDAÇÃO DE SENHA

#### Novo Arquivo: `config/conexao.php`

```php
/**
 * Valida força de senha
 * Requisitos:
 * - Mínimo 8 caracteres
 * - 1 letra maiúscula
 * - 1 letra minúscula
 * - 1 número
 */
function validarSenha($senha) {
    $erros = [];
    
    if (strlen($senha) < PASSWORD_MIN_LENGTH) {
        $erros[] = "Mínimo de " . PASSWORD_MIN_LENGTH . " caracteres";
    }
    
    if (!preg_match('/[A-Z]/', $senha)) {
        $erros[] = "Deve conter pelo menos uma letra maiúscula";
    }
    
    if (!preg_match('/[a-z]/', $senha)) {
        $erros[] = "Deve conter pelo menos uma letra minúscula";
    }
    
    if (!preg_match('/[0-9]/', $senha)) {
        $erros[] = "Deve conter pelo menos um número";
    }
    
    return [
        'valida' => count($erros) === 0,
        'erros' => $erros
    ];
}
```

**Exemplos:**
- ✅ `Senha123` → válida
- ✅ `MeuSenha@456` → válida
- ❌ `senha123` → inválida (sem maiúscula)
- ❌ `Senha1` → inválida (menos de 8 caracteres)
- ❌ `SENHA123` → inválida (sem minúscula)

### 📄 PAGINAÇÃO COMPLETA

#### Arquivo: `includes/funcoes.php`

```php
/**
 * Obtém produtos com filtros e paginação
 */
function getProdutos($filtros = []) {
    // Paginação
    $pagina = max(1, intval($filtros['pagina'] ?? 1));
    $itens_por_pagina = max(1, intval($filtros['itens_por_pagina'] ?? 12));
    $offset = ($pagina - 1) * $itens_por_pagina;
    
    // Construir query com prepared statements
    $sql = "SELECT * FROM produtos WHERE ativo = 1";
    $params = [];
    
    // Filtros dinâmicos
    if (!empty($filtros['categoria'])) {
        $sql .= " AND categoria_id = ?";
        $params[] = $filtros['categoria'];
    }
    
    if (!empty($filtros['busca'])) {
        $sql .= " AND nome LIKE ?";
        $params[] = '%' . $filtros['busca'] . '%';
    }
    
    // Contar total
    $count_sql = str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql);
    // ... execução
    
    // Paginar com LIMIT/OFFSET
    $sql .= " ORDER BY destaque DESC LIMIT ? OFFSET ?";
    $params[] = $itens_por_pagina;
    $params[] = $offset;
    // ... execução
    
    return [
        'produtos' => $stmt->fetchAll(),
        'total' => $total,
        'paginas' => ceil($total / $itens_por_pagina),
        'pagina_atual' => $pagina
    ];
}
```

**USO:**
```php
$resultado = getProdutos([
    'categoria' => 'eletronicos',
    'busca' => 'iphone',
    'preco_min' => 1000,
    'preco_max' => 3000,
    'pagina' => 1,
    'itens_por_pagina' => 12
]);

// $resultado['produtos'] → array de produtos
// $resultado['total'] → 45 produtos encontrados
// $resultado['paginas'] → 4 páginas
// $resultado['pagina_atual'] → 1
```

### 🚚 CÁLCULO DINÂMICO DE FRETE

#### Novo Arquivo: `includes/frete.php`

```php
/**
 * Valida e extrai o estado de um CEP
 */
function validarCEP($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    if (strlen($cep) !== 8) return false;
    
    // Mapa de CEP para estado
    $mapa_cep = [
        '01' => 'SP', '20' => 'RJ', '30' => 'MG', // etc
    ];
    
    $prefixo = substr($cep, 0, 2);
    return $mapa_cep[$prefixo] ?? false;
}

/**
 * Calcula frete por CEP e subtotal
 */
function calcularFrete($cep, $subtotal = 0) {
    global $pdo;
    
    $estado = validarCEP($cep);
    if (!$estado) {
        return ['valor' => 0, 'erro' => 'CEP inválido'];
    }
    
    // Buscar configuração de frete
    $stmt = $pdo->prepare(
        "SELECT valor_frete, preco_minimo_frete_gratis 
         FROM configuracao_frete 
         WHERE estado = ? AND ativo = 1"
    );
    $stmt->execute([$estado]);
    $config = $stmt->fetch();
    
    // Verificar frete grátis
    $frete_gratis = ($subtotal >= $config['preco_minimo_frete_gratis']);
    
    return [
        'valor' => $frete_gratis ? 0 : $config['valor_frete'],
        'estado' => $estado,
        'gratis' => $frete_gratis
    ];
}
```

**TABELA DE FRETE (BANCO DE DADOS):**

| Estado | Valor Frete | Frete Grátis Acima de |
|--------|-------------|---------------------|
| SP     | R$ 15,00    | R$ 299,00           |
| RJ     | R$ 18,00    | R$ 299,00           |
| MG     | R$ 20,00    | R$ 299,00           |
| BA     | R$ 25,00    | R$ 350,00           |
| AM     | R$ 40,00    | R$ 599,00           |

**USO:**
```php
$frete = calcularFrete('01310-100', 500);
// Resultado:
// [
//   'valor' => 0,  (frete grátis, pois subtotal >= 299)
//   'estado' => 'SP',
//   'gratis' => true
// ]
```

### 📧 SISTEMA DE EMAIL

#### Novo Arquivo: `includes/email.php`

**Fluxo de Verificação de Email:**

```
1. Usuário faz cadastro
   ↓
2. enviarEmailVerificacao() gera token
   ↓
3. Token salvo em email_tokens com expiração de 24h
   ↓
4. Link enviado por email: verificar-email.php?token=ABC123
   ↓
5. Usuario clica no link
   ↓
6. verificarTokenEmail() valida e ativa a conta
```

**Fluxo de Recuperação de Senha:**

```
1. Usuário clica em "Esqueci a Senha"
   ↓
2. enviarEmailRecuperarSenha() gera token com validade 1h
   ↓
3. Link enviado: redefinir-senha.php?token=XYZ789
   ↓
4. Usuário entra nova senha
   ↓
5. redefinirSenhaComToken() atualiza com hash bcrypt
```

**BANCO DE DADOS - Tabela `email_tokens`:**

```sql
CREATE TABLE email_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    tipo ENUM('verificacao_email', 'recuperar_senha'),
    data_expiracao TIMESTAMP NOT NULL,
    usado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

### 🔄 INTEGRAÇÕES FUTURAS

#### 1. PHPMailer para Envio Real de Email

```bash
# Instalar
composer require phpmailer/phpmailer
```

#### 2. Gateway de Pagamento (Stripe/PayPal)

```php
// Configurar credenciais no .env
STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_SECRET_KEY=sk_live_...

// Usar na finalização de pedido
if ($forma_pagamento === 'cartao_credito') {
    // Integrar Stripe
}
```

#### 3. API de CEP (ViaCEP)

```php
// Buscar dados reais de CEP
function buscarDadosCEP($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    $url = "https://viacep.com.br/ws/$cep/json/";
    $response = file_get_contents($url);
    return json_decode($response, true);
}
```

---

## 💡 Como Usar Cada Feature

### 1. Cadastro com Validação Forte

**URL:** `/cadastro.php`

```html
<!-- Formulário valida automaticamente -->
<form method="POST">
    <input type="email" name="email" required>
    <!-- Validação: filter_var(FILTER_VALIDATE_EMAIL) -->
    
    <input type="password" name="senha" required>
    <!-- Validação:
        - Mínimo 8 caracteres
        - 1 maiúscula
        - 1 minúscula
        - 1 número
    -->
</form>
```

### 2. Login Seguro

**URL:** `/login.php`

```php
// Verifica senha com bcrypt
if (password_verify($senha_digitada, $hash_banco)) {
    // Login bem-sucedido
    $_SESSION['usuario_id'] = $usuario['id'];
}
```

### 3. Listar Produtos com Filtros

**URL:** `/produtos.php?categoria=eletronicos&busca=iphone&preco_min=1000&preco_max=3000&pagina=1`

```php
$resultado = getProdutos([
    'categoria' => $_GET['categoria'],
    'busca' => $_GET['busca'],
    'preco_min' => $_GET['preco_min'],
    'preco_max' => $_GET['preco_max'],
    'pagina' => $_GET['pagina'] ?? 1,
    'itens_por_pagina' => 12
]);

// Exibir produtos + paginação
foreach ($resultado['produtos'] as $produto) {
    // Renderizar produto
}

// Botões de navegação: 1 2 3 4 5 ...
```

### 4. Calcular Frete Dinâmico

**URL:** `/finalizar.php`

```php
$frete = calcularFrete($_POST['cep'], totalCarrinho());

if ($frete['gratis']) {
    echo "Frete GRÁTIS!";
} else {
    echo "Frete: " . formatarPreco($frete['valor']);
}
```

### 5. Criar Pedido com Transação

```php
$dados_pedido = [
    'endereco' => $_POST['endereco'],
    'numero' => $_POST['numero'],
    'cidade' => $_POST['cidade'],
    'estado' => $_POST['estado'],
    'cep' => $_POST['cep'],
    'forma_pagamento' => $_POST['forma_pagamento'],
    'frete' => $frete['valor']
];

// Cria pedido + itens + atualiza estoque (tudo em 1 transação)
$codigo = criarPedido($usuario_id, $dados_pedido);

if ($codigo) {
    echo "Pedido criado: " . $codigo;  // PED202307271A2B3C
}
```

---

## 🆘 Troubleshooting

### ❌ Erro: "Arquivo .env não encontrado"

```bash
# Solução
cp .env.example .env
vim .env  # editar com suas credenciais
```

### ❌ Erro: "Conexão com banco de dados recusada"

```bash
# Verificar se MySQL está rodando
sudo service mysql status

# Iniciar MySQL
sudo service mysql start

# Verificar credenciais no .env
DB_HOST=localhost
DB_USER=root
DB_PASS=sua_senha
```

### ❌ Erro: "Tabelas não existem"

```bash
# Executar scripts SQL
mysql -u root -p < loja/banco_de_dados.sql
mysql -u root -p < loja/banco_de_dados_migracao_001.sql
```

### ❌ Erro: "Sessão expirada"

```php
// Sessão expira após 30 minutos de inatividade
// Configuração no .env:
SESSION_TIMEOUT=1800  // em segundos

// Para aumentar:
SESSION_TIMEOUT=7200  // 2 horas
```

### ❌ Email não é enviado

```bash
# Verificar credenciais Mailtrap no .env
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USER=seu_usuario
MAIL_PASS=sua_senha

# Em produção, usar Gmail, SendGrid, etc
```

---

## 📞 Suporte e Dúvidas

Para mais informações:
- Abra uma issue no GitHub
- Consulte a documentação no projeto
- Verifique os comentários no código

---

**Última atualização:** 27/07/2026  
**Versão:** 2.0.0  
**Status:** ✅ Em Produção

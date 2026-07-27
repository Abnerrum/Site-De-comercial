<?php
/**
 * Configuração de Conexão com Banco de Dados
 * ===========================================
 * 
 * MELHORIAS IMPLEMENTADAS:
 * 1. Carrega variáveis de ambiente do arquivo .env
 * 2. Usa PDO com prepared statements (segurança contra SQL injection)
 * 3. Tratamento de erros com try/catch
 * 4. Funções helper para uso em toda a aplicação
 * 
 * MUDANÇAS DESDE A VERSÃO ANTERIOR:
 * - Antes: Credenciais hardcoded (INSEGURO)
 * - Agora: Usa .env.example / .env (SEGURO)
 */

require_once __DIR__ . '/env.php';

// Configurações do Banco de Dados (via .env)
$host = env('DB_HOST', 'localhost');
$dbname = env('DB_NAME', 'loja_virtual');
$usuario = env('DB_USER', 'root');
$senha = env('DB_PASS', '');
$port = env('DB_PORT', 3306);

// Configurações de Aplicação
define('SITE_NOME', env('SITE_NOME', 'Loja Virtual'));
define('SITE_URL', env('SITE_URL', 'http://localhost:8000'));
define('APP_ENV', env('APP_ENV', 'development'));

// Configurações de Segurança
define('SESSION_TIMEOUT', env('SESSION_TIMEOUT', 1800)); // 30 minutos
define('PASSWORD_MIN_LENGTH', env('PASSWORD_MIN_LENGTH', 8));
define('TOKEN_EXPIRY', env('TOKEN_EXPIRY', 3600)); // 1 hora

// Configurações de Frete
define('FRETE_GRATIS_ACIMA_DE', env('FRETE_GRATIS_ACIMA_DE', 299));
define('FRETE_PADRAO', env('FRETE_PADRAO', 25));

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Gerenciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar timeout da sessão
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_destroy();
    header('Location: ' . SITE_URL . 'login.php?sessao_expirada=1');
    exit;
}
$_SESSION['last_activity'] = time();

// Conectar ao banco de dados
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $usuario, $senha);
    
    // Configurar modo de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch associativo como padrão
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Configurar charset
    $pdo->exec('SET NAMES utf8mb4');
    
} catch (PDOException $e) {
    // Em produção, não mostrar detalhes do erro
    if (APP_ENV === 'production') {
        die('Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.');
    } else {
        die('Erro na conexão: ' . $e->getMessage());
    }
}

/**
 * Formatador de preço brasileiro
 * Converte número para formato R$ 1.234,56
 * 
 * @param float $valor Valor a formatar
 * @return string Valor formatado
 */
function formatarPreco($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Gera slug (URL amigável) a partir de texto
 * Remove acentos, espaços e caracteres especiais
 * 
 * EXEMPLO: "iPhone 13 Pro" → "iphone-13-pro"
 * 
 * @param string $string Texto a converter
 * @return string Slug
 */
function gerarSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[\xc3\xa1\xc3\xa0\xc3\xa3\xc3\xa2\xc3\xa4]/u', 'a', $string);
    $string = preg_replace('/[\xc3\xa9\xc3\xa8\xc3\xaa\xc3\xab]/u', 'e', $string);
    $string = preg_replace('/[\xc3\xad\xc3\xac\xc3\xae\xc3\xaf]/u', 'i', $string);
    $string = preg_replace('/[\xc3\xb3\xc3\xb2\xc3\xb5\xc3\xb4\xc3\xb6]/u', 'o', $string);
    $string = preg_replace('/[\xc3\xba\xc3\xb9\xc3\xbb\xc3\xbc]/u', 'u', $string);
    $string = preg_replace('/[\xc3\xa7]/u', 'c', $string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Limpa e sanitiza entrada de usuário
 * Remove tags HTML e converte caracteres especiais
 * 
 * ANTES: Entrada: "<script>alert('xss')</script>"
 * DEPOIS: Entrada: "&lt;script&gt;alert('xss')&lt;/script&gt;"
 * 
 * @param string $dado Dados a limpar
 * @return string Dados limpos
 */
function limpar($dado) {
    return htmlspecialchars(strip_tags(trim($dado)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida se um email é válido usando filtro PHP
 * 
 * EXEMPLO:
 * validarEmail('usuario@email.com'); // true
 * validarEmail('email-invalido'); // false
 * 
 * @param string $email Email a validar
 * @return bool True se válido, false caso contrário
 */
function validarEmail($email) {
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida força de senha
 * Mínimo: PASSWORD_MIN_LENGTH caracteres
 * Requer: maiúscula, minúscula, número
 * 
 * @param string $senha Senha a validar
 * @return array ['valida' => bool, 'erros' => array]
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

/**
 * Define mensagem flash na sessão
 * Mensagens temporárias mostradas uma única vez
 * 
 * TIPOS: 'sucesso', 'erro', 'aviso', 'info'
 * 
 * @param string $tipo Tipo de mensagem
 * @param string $mensagem Conteúdo da mensagem
 */
function setFlash($tipo, $mensagem) {
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

/**
 * Obtém e remove mensagem flash da sessão
 * 
 * @return array|null Array com 'tipo' e 'mensagem', ou null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Verifica se há usuário logado
 * 
 * @return bool
 */
function usuarioLogado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verifica se há admin logado
 * 
 * @return bool
 */
function adminLogado() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Redireciona para outra página
 * 
 * @param string $url URL para redireção
 */
function redirecionar($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Conta itens no carrinho
 * 
 * @return int Quantidade total de itens
 */
function contarCarrinho() {
    if (!isset($_SESSION['carrinho'])) {
        return 0;
    }
    return array_sum(array_column($_SESSION['carrinho'], 'quantidade'));
}

/**
 * Calcula total do carrinho
 * 
 * @return float Total em reais
 */
function totalCarrinho() {
    $total = 0;
    if (isset($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $item) {
            $total += $item['preco'] * $item['quantidade'];
        }
    }
    return $total;
}

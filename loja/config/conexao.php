<?php
$host = 'localhost';
$dbname = 'loja_virtual';
$usuario = 'root';
$senha = '';

date_default_timezone_set('America/Sao_Paulo');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexao: " . $e->getMessage());
}

define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/');
define('SITE_NOME', 'Loja Virtual');

function formatarPreco($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

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

function limpar($dado) {
    return htmlspecialchars(strip_tags(trim($dado)), ENT_QUOTES, 'UTF-8');
}

function setFlash($tipo, $mensagem) {
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function usuarioLogado() {
    return isset($_SESSION['usuario_id']);
}

function adminLogado() {
    return isset($_SESSION['admin_id']);
}

function redirecionar($url) {
    header("Location: $url");
    exit;
}

function contarCarrinho() {
    if (!isset($_SESSION['carrinho'])) return 0;
    return array_sum(array_column($_SESSION['carrinho'], 'quantidade'));
}

function totalCarrinho() {
    $total = 0;
    if (isset($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $item) {
            $total += $item['preco'] * $item['quantidade'];
        }
    }
    return $total;
}

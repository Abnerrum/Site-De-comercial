<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/funcoes.php';

$categorias = getCategorias();
$qtd_carrinho = contarCarrinho();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' - ' : ''; ?><?php echo SITE_NOME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="top-bar">
        <p>Frete gratis em compras acima de R$ 299,00 | Cupom PRIMEIRA10 - 10% OFF na primeira compra</p>
    </div>
    <header class="main-header">
        <div class="container header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-store"></i>
                <span><?php echo SITE_NOME; ?></span>
            </a>
            <form action="produtos.php" method="GET" class="search-form">
                <input type="text" name="busca" placeholder="Buscar produtos..." value="<?php echo isset($_GET['busca']) ? limpar($_GET['busca']) : ''; ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            <div class="header-actions">
                <?php if (usuarioLogado()): ?>
                    <div class="user-menu">
                        <a href="#" class="action-btn">
                            <i class="fas fa-user"></i>
                            <span>Ola, <?php echo isset($_SESSION['usuario_nome']) ? explode(' ', $_SESSION['usuario_nome'])[0] : 'Cliente'; ?></span>
                        </a>
                        <div class="dropdown">
                            <a href="finalizar.php"><i class="fas fa-box"></i> Meus Pedidos</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="action-btn">
                        <i class="fas fa-user"></i>
                        <span>Entrar</span>
                    </a>
                <?php endif; ?>
                <a href="carrinho.php" class="action-btn cart-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Carrinho</span>
                    <?php if ($qtd_carrinho > 0): ?>
                        <span class="cart-badge"><?php echo $qtd_carrinho; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>
    <nav class="main-nav">
        <div class="container">
            <ul class="nav-menu">
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Inicio</a></li>
                <li><a href="produtos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'produtos.php' ? 'active' : ''; ?>"><i class="fas fa-th-large"></i> Todos os Produtos</a></li>
                <?php foreach ($categorias as $cat): ?>
                    <li><a href="produtos.php?categoria=<?php echo $cat['slug']; ?>"><?php echo $cat['nome']; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
    <?php if ($flash): ?>
        <div class="flash-message flash-<?php echo $flash['tipo']; ?>">
            <div class="container">
                <i class="fas fa-<?php echo $flash['tipo'] == 'sucesso' ? 'check-circle' : ($flash['tipo'] == 'erro' ? 'times-circle' : 'info-circle'); ?>"></i>
                <?php echo $flash['mensagem']; ?>
            </div>
        </div>
    <?php endif; ?>
    <main class="main-content">

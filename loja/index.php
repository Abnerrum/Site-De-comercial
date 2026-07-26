<?php
$titulo_pagina = 'Inicio';
require_once 'includes/header.php';
$produtos_destaque = getProdutosDestaque(8);
?>
<section class="hero-banner">
    <div class="hero-content">
        <h1>As Melhores Ofertas da Semana</h1>
        <p>Ate 30% de desconto em produtos selecionados. Aproveite!</p>
        <a href="produtos.php" class="btn btn-primary btn-lg">Ver Ofertas <i class="fas fa-arrow-right"></i></a>
    </div>
</section>
<section class="categorias-section">
    <div class="container">
        <h2 class="section-title">Compre por Categoria</h2>
        <div class="categorias-grid">
            <?php foreach ($categorias as $cat): ?>
                <a href="produtos.php?categoria=<?php echo $cat['slug']; ?>" class="categoria-card">
                    <div class="categoria-icon">
                        <i class="fas fa-<?php
                            $icons = ['eletronicos'=>'laptop','roupas'=>'tshirt','casa-decoracao'=>'couch','esportes'=>'futbol','livros'=>'book'];
                            echo $icons[$cat['slug']] ?? 'tag';
                        ?>"></i>
                    </div>
                    <h3><?php echo $cat['nome']; ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="produtos-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Produtos em Destaque</h2>
            <a href="produtos.php" class="ver-todos">Ver todos <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="produtos-grid">
            <?php foreach ($produtos_destaque as $prod): ?>
                <?php echo renderProdutoCard($prod); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="promo-banner">
    <div class="container">
        <div class="promo-content">
            <div class="promo-text">
                <span class="promo-tag">NOVIDADE</span>
                <h2>Smartphone Galaxy Pro</h2>
                <p>Camera 108MP, 12GB RAM, 256GB. A tecnologia do futuro nas suas maos.</p>
                <div class="promo-preco">
                    <span class="preco-antigo">R$ 3.999,90</span>
                    <span class="preco-atual">R$ 3.499,90</span>
                </div>
                <a href="produto.php?slug=smartphone-galaxy-pro" class="btn btn-light btn-lg">Comprar Agora</a>
            </div>
        </div>
    </div>
</section>
<section class="vantagens-section">
    <div class="container">
        <div class="vantagens-grid">
            <div class="vantagem-item"><i class="fas fa-truck"></i><h3>Frete Gratis</h3><p>Em compras acima de R$ 299</p></div>
            <div class="vantagem-item"><i class="fas fa-shield-alt"></i><h3>Compra Segura</h3><p>Ambiente 100% protegido</p></div>
            <div class="vantagem-item"><i class="fas fa-undo"></i><h3>Troca Facil</h3><p>7 dias para trocar</p></div>
            <div class="vantagem-item"><i class="fas fa-headset"></i><h3>Suporte 24h</h3><p>Atendimento especializado</p></div>
        </div>
    </div>
</section>
<?php
function renderProdutoCard($prod) {
    $preco_final = $prod['preco_promocional'] ?: $prod['preco'];
    $tem_promo = $prod['preco_promocional'] > 0;
    $desconto = $tem_promo ? round((1 - $prod['preco_promocional'] / $prod['preco']) * 100) : 0;
    $html = '<div class="produto-card">';
    $html .= '<a href="produto.php?slug=' . $prod['slug'] . '" class="produto-link">';
    $html .= '<div class="produto-imagem">';
    $html .= '<img src="assets/imagens/' . $prod['imagem'] . '" alt="' . htmlspecialchars($prod['nome']) . '" onerror="this.src=\'assets/imagens/sem-imagem.jpg\'">';
    if ($tem_promo) $html .= '<span class="badge-promo">-' . $desconto . '%</span>';
    $html .= '</div>';
    $html .= '<div class="produto-info">';
    $html .= '<span class="produto-categoria">' . ($prod['categoria_nome'] ?? 'Geral') . '</span>';
    $html .= '<h3 class="produto-nome">' . htmlspecialchars($prod['nome']) . '</h3>';
    $html .= '<div class="produto-preco">';
    if ($tem_promo) $html .= '<span class="preco-antigo">' . formatarPreco($prod['preco']) . '</span>';
    $html .= '<span class="preco-atual">' . formatarPreco($preco_final) . '</span>';
    $html .= '</div>';
    $html .= '<div class="produto-estoque">';
    if ($prod['estoque'] > 0) {
        $html .= '<span class="disponivel"><i class="fas fa-check-circle"></i> Em estoque</span>';
    } else {
        $html .= '<span class="indisponivel"><i class="fas fa-times-circle"></i> Indisponivel</span>';
    }
    $html .= '</div></div></a>';
    $html .= '<form action="carrinho.php" method="POST" class="produto-acao">';
    $html .= '<input type="hidden" name="acao" value="adicionar">';
    $html .= '<input type="hidden" name="produto_id" value="' . $prod['id'] . '">';
    $html .= '<button type="submit" class="btn btn-primary btn-full" ' . ($prod['estoque'] <= 0 ? 'disabled' : '') . '>';
    $html .= '<i class="fas fa-shopping-cart"></i> ' . ($prod['estoque'] > 0 ? 'Adicionar' : 'Indisponivel');
    $html .= '</button></form></div>';
    return $html;
}
require_once 'includes/footer.php';
?>
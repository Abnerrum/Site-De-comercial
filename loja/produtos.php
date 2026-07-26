<?php
require_once 'includes/header.php';
$filtros = [
    'categoria' => $_GET['categoria'] ?? '',
    'busca' => $_GET['busca'] ?? '',
    'preco_min' => $_GET['preco_min'] ?? '',
    'preco_max' => $_GET['preco_max'] ?? ''
];
$produtos = getProdutos($filtros);
if (!empty($filtros['busca'])) {
    $titulo_pagina = 'Resultados para "' . limpar($filtros['busca']) . '"';
} elseif (!empty($filtros['categoria'])) {
    $cat_nome = '';
    foreach ($categorias as $c) { if ($c['slug'] == $filtros['categoria']) { $cat_nome = $c['nome']; break; } }
    $titulo_pagina = $cat_nome ?: 'Produtos';
} else {
    $titulo_pagina = 'Todos os Produtos';
}
?>
<section class="produtos-page">
    <div class="container">
        <div class="page-header">
            <h1><?php echo $titulo_pagina; ?></h1>
            <p><?php echo count($produtos); ?> produto(s) encontrado(s)</p>
        </div>
        <div class="produtos-layout">
            <aside class="filtros-sidebar">
                <div class="filtro-box">
                    <h3><i class="fas fa-filter"></i> Filtros</h3>
                    <form action="produtos.php" method="GET">
                        <?php if ($filtros['categoria']): ?><input type="hidden" name="categoria" value="<?php echo $filtros['categoria']; ?>"><?php endif; ?>
                        <?php if ($filtros['busca']): ?><input type="hidden" name="busca" value="<?php echo limpar($filtros['busca']); ?>"><?php endif; ?>
                        <div class="filtro-grupo">
                            <label>Preco Minimo</label>
                            <input type="number" name="preco_min" placeholder="R$ 0,00" value="<?php echo $filtros['preco_min']; ?>">
                        </div>
                        <div class="filtro-grupo">
                            <label>Preco Maximo</label>
                            <input type="number" name="preco_max" placeholder="R$ 0,00" value="<?php echo $filtros['preco_max']; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-full">Aplicar Filtros</button>
                        <a href="produtos.php" class="btn btn-outline btn-full">Limpar Filtros</a>
                    </form>
                </div>
                <div class="filtro-box">
                    <h3>Categorias</h3>
                    <ul class="categoria-lista">
                        <li><a href="produtos.php" class="<?php echo empty($filtros['categoria']) ? 'active' : ''; ?>">Todas</a></li>
                        <?php foreach ($categorias as $cat): ?>
                            <li><a href="produtos.php?categoria=<?php echo $cat['slug']; ?>" class="<?php echo $filtros['categoria'] == $cat['slug'] ? 'active' : ''; ?>"><?php echo $cat['nome']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
            <div class="produtos-resultado">
                <?php if (count($produtos) > 0): ?>
                    <div class="produtos-grid">
                        <?php foreach ($produtos as $prod): ?>
                            <?php echo renderProdutoCard($prod); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="sem-resultados">
                        <i class="fas fa-search"></i>
                        <h2>Nenhum produto encontrado</h2>
                        <p>Tente ajustar seus filtros ou buscar por outro termo.</p>
                        <a href="produtos.php" class="btn btn-primary">Ver todos os produtos</a>
                    </div>
                <?php endif; ?>
            </div>
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
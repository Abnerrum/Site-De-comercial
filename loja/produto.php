<?php
require_once 'includes/header.php';
$slug = $_GET['slug'] ?? '';
$produto = getProdutoPorSlug($slug);
if (!$produto) {
    setFlash('erro', 'Produto nao encontrado.');
    redirecionar('produtos.php');
}
$titulo_pagina = $produto['nome'];
$preco_final = $produto['preco_promocional'] ?: $produto['preco'];
$tem_promo = $produto['preco_promocional'] > 0;
$desconto = $tem_promo ? round((1 - $produto['preco_promocional'] / $produto['preco']) * 100) : 0;
$relacionados = getProdutosRelacionados($produto['categoria_id'], $produto['id'], 4);
?>
<section class="produto-detalhe">
    <div class="container">
        <nav class="breadcrumb">
            <a href="index.php">Inicio</a>
            <span>/</span>
            <a href="produtos.php">Produtos</a>
            <span>/</span>
            <span><?php echo htmlspecialchars($produto['nome']); ?></span>
        </nav>
        <div class="produto-layout">
            <div class="produto-galeria">
                <div class="imagem-principal">
                    <img src="assets/imagens/<?php echo $produto['imagem']; ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" onerror="this.src='assets/imagens/sem-imagem.jpg'">
                    <?php if ($tem_promo): ?><span class="badge-promo grande">-<?php echo $desconto; ?>% OFF</span><?php endif; ?>
                </div>
            </div>
            <div class="produto-info-detalhe">
                <span class="categoria-tag"><?php echo $produto['categoria_nome'] ?? 'Geral'; ?></span>
                <h1><?php echo htmlspecialchars($produto['nome']); ?></h1>
                <p class="descricao-curta"><?php echo htmlspecialchars($produto['descricao_curta']); ?></p>
                <div class="produto-avaliacao">
                    <div class="estrelas">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <span>4.5 (128 avaliacoes)</span>
                </div>
                <div class="produto-preco-box">
                    <?php if ($tem_promo): ?><span class="preco-antigo grande"><?php echo formatarPreco($produto['preco']); ?></span><?php endif; ?>
                    <span class="preco-atual grande"><?php echo formatarPreco($preco_final); ?></span>
                    <?php if ($tem_promo): ?><span class="economia">Economize <?php echo formatarPreco($produto['preco'] - $produto['preco_promocional']); ?></span><?php endif; ?>
                </div>
                <div class="produto-estoque-info">
                    <?php if ($produto['estoque'] > 10): ?>
                        <span class="estoque-alto"><i class="fas fa-check-circle"></i> Em estoque (<?php echo $produto['estoque']; ?> unidades)</span>
                    <?php elseif ($produto['estoque'] > 0): ?>
                        <span class="estoque-baixo"><i class="fas fa-exclamation-circle"></i> Apenas <?php echo $produto['estoque']; ?> unidades restantes!</span>
                    <?php else: ?>
                        <span class="estoque-zero"><i class="fas fa-times-circle"></i> Produto indisponivel</span>
                    <?php endif; ?>
                </div>
                <form action="carrinho.php" method="POST" class="produto-compra">
                    <input type="hidden" name="acao" value="adicionar">
                    <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                    <div class="quantidade-box">
                        <label>Quantidade:</label>
                        <div class="quantidade-controle">
                            <button type="button" class="btn-qtd" onclick="alterarQtd(-1)">-</button>
                            <input type="number" name="quantidade" id="qtd" value="1" min="1" max="<?php echo $produto['estoque']; ?>">
                            <button type="button" class="btn-qtd" onclick="alterarQtd(1)">+</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-compra" <?php echo $produto['estoque'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart"></i> <?php echo $produto['estoque'] > 0 ? 'Adicionar ao Carrinho' : 'Indisponivel'; ?>
                    </button>
                </form>
                <div class="produto-beneficios">
                    <div><i class="fas fa-truck"></i> Frete gratis acima de R$ 299</div>
                    <div><i class="fas fa-undo"></i> Troca em ate 7 dias</div>
                    <div><i class="fas fa-shield-alt"></i> Garantia de 12 meses</div>
                </div>
            </div>
        </div>
        <div class="produto-descricao">
            <h2>Descricao do Produto</h2>
            <div class="descricao-conteudo"><?php echo nl2br(htmlspecialchars($produto['descricao'])); ?></div>
        </div>
        <?php if (count($relacionados) > 0): ?>
        <div class="produtos-relacionados">
            <h2>Produtos Relacionados</h2>
            <div class="produtos-grid">
                <?php foreach ($relacionados as $prod): ?>
                    <?php echo renderProdutoCard($prod); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<script>
function alterarQtd(delta) {
    const input = document.getElementById('qtd');
    let val = parseInt(input.value) + delta;
    const max = parseInt(input.max);
    if (val < 1) val = 1;
    if (val > max) val = max;
    input.value = val;
}
</script>
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
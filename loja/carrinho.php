<?php
require_once 'includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $produto_id = intval($_POST['produto_id'] ?? 0);
    switch ($acao) {
        case 'adicionar':
            $quantidade = max(1, intval($_POST['quantidade'] ?? 1));
            if (adicionarCarrinho($produto_id, $quantidade)) {
                setFlash('sucesso', 'Produto adicionado ao carrinho!');
            } else {
                setFlash('erro', 'Produto nao encontrado ou indisponivel.');
            }
            redirecionar('carrinho.php');
            break;
        case 'remover':
            removerCarrinho($produto_id);
            setFlash('sucesso', 'Produto removido do carrinho.');
            redirecionar('carrinho.php');
            break;
        case 'atualizar':
            $quantidade = max(0, intval($_POST['quantidade'] ?? 1));
            atualizarCarrinho($produto_id, $quantidade);
            redirecionar('carrinho.php');
            break;
        case 'limpar':
            unset($_SESSION['carrinho']);
            setFlash('sucesso', 'Carrinho esvaziado.');
            redirecionar('carrinho.php');
            break;
    }
}
$titulo_pagina = 'Carrinho de Compras';
$carrinho = $_SESSION['carrinho'] ?? [];
$subtotal = totalCarrinho();
$frete = $subtotal >= 299 ? 0 : 25.00;
$total = $subtotal + $frete;
?>
<section class="carrinho-page">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Carrinho de Compras</h1>
        <?php if (count($carrinho) > 0): ?>
            <div class="carrinho-layout">
                <div class="carrinho-itens">
                    <div class="carrinho-header">
                        <span>Produto</span><span>Preco</span><span>Qtd</span><span>Subtotal</span><span></span>
                    </div>
                    <?php foreach ($carrinho as $item):
                        $item_subtotal = $item['preco'] * $item['quantidade'];
                    ?>
                    <div class="carrinho-item">
                        <div class="item-produto">
                            <img src="assets/imagens/<?php echo $item['imagem']; ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>" onerror="this.src='assets/imagens/sem-imagem.jpg'">
                            <div class="item-info"><h3><?php echo htmlspecialchars($item['nome']); ?></h3></div>
                        </div>
                        <div class="item-preco"><?php echo formatarPreco($item['preco']); ?></div>
                        <div class="item-qtd">
                            <form action="carrinho.php" method="POST" class="qtd-form">
                                <input type="hidden" name="acao" value="atualizar">
                                <input type="hidden" name="produto_id" value="<?php echo $item['id']; ?>">
                                <button type="button" class="btn-qtd" onclick="this.parentElement.querySelector('input[type=number]').stepDown(); this.parentElement.submit();">-</button>
                                <input type="number" name="quantidade" value="<?php echo $item['quantidade']; ?>" min="1" onchange="this.form.submit()">
                                <button type="button" class="btn-qtd" onclick="this.parentElement.querySelector('input[type=number]').stepUp(); this.parentElement.submit();">+</button>
                            </form>
                        </div>
                        <div class="item-subtotal"><?php echo formatarPreco($item_subtotal); ?></div>
                        <div class="item-remover">
                            <form action="carrinho.php" method="POST">
                                <input type="hidden" name="acao" value="remover">
                                <input type="hidden" name="produto_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-remover" title="Remover"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="carrinho-acoes">
                        <a href="produtos.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Continuar Comprando</a>
                        <form action="carrinho.php" method="POST" style="display:inline;">
                            <input type="hidden" name="acao" value="limpar">
                            <button type="submit" class="btn btn-danger-outline"><i class="fas fa-trash"></i> Esvaziar Carrinho</button>
                        </form>
                    </div>
                </div>
                <div class="carrinho-resumo">
                    <h3>Resumo do Pedido</h3>
                    <div class="resumo-linha"><span>Subtotal</span><span><?php echo formatarPreco($subtotal); ?></span></div>
                    <div class="resumo-linha"><span>Frete</span><span class="<?php echo $frete == 0 ? 'frete-gratis' : ''; ?>"><?php echo $frete == 0 ? 'GRATIS' : formatarPreco($frete); ?></span></div>
                    <?php if ($frete > 0): ?><div class="frete-info"><i class="fas fa-info-circle"></i> Frete gratis em compras acima de R$ 299,00</div><?php endif; ?>
                    <div class="resumo-linha total"><span>Total</span><span><?php echo formatarPreco($total); ?></span></div>
                    <a href="finalizar.php" class="btn btn-primary btn-lg btn-full"><i class="fas fa-check-circle"></i> Finalizar Compra</a>
                    <div class="pagamento-seguro"><i class="fas fa-lock"></i> Pagamento 100% seguro</div>
                </div>
            </div>
        <?php else: ?>
            <div class="carrinho-vazio">
                <i class="fas fa-shopping-cart"></i>
                <h2>Seu carrinho esta vazio</h2>
                <p>Que tal dar uma olhada em nossos produtos?</p>
                <a href="produtos.php" class="btn btn-primary btn-lg">Ver Produtos</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
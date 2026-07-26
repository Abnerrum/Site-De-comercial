<?php
require_once 'includes/header.php';
if (empty($_SESSION['carrinho'])) {
    setFlash('erro', 'Seu carrinho esta vazio.');
    redirecionar('produtos.php');
}
if (!usuarioLogado()) {
    $_SESSION['redirect_after_login'] = 'finalizar.php';
    setFlash('info', 'Faca login para finalizar sua compra.');
    redirecionar('login.php');
}
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();
$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'endereco' => limpar($_POST['endereco'] ?? ''),
        'numero' => limpar($_POST['numero'] ?? ''),
        'complemento' => limpar($_POST['complemento'] ?? ''),
        'bairro' => limpar($_POST['bairro'] ?? ''),
        'cidade' => limpar($_POST['cidade'] ?? ''),
        'estado' => limpar($_POST['estado'] ?? ''),
        'cep' => limpar($_POST['cep'] ?? ''),
        'forma_pagamento' => limpar($_POST['forma_pagamento'] ?? ''),
        'observacoes' => limpar($_POST['observacoes'] ?? ''),
        'frete' => totalCarrinho() >= 299 ? 0 : 25.00
    ];
    if (empty($dados['endereco'])) $erros[] = 'Endereco e obrigatorio';
    if (empty($dados['numero'])) $erros[] = 'Numero e obrigatorio';
    if (empty($dados['bairro'])) $erros[] = 'Bairro e obrigatorio';
    if (empty($dados['cidade'])) $erros[] = 'Cidade e obrigatoria';
    if (empty($dados['estado'])) $erros[] = 'Estado e obrigatorio';
    if (empty($dados['cep'])) $erros[] = 'CEP e obrigatorio';
    if (empty($dados['forma_pagamento'])) $erros[] = 'Selecione uma forma de pagamento';
    if (empty($erros)) {
        $codigo = criarPedido($_SESSION['usuario_id'], $dados);
        setFlash('sucesso', 'Pedido ' . $codigo . ' realizado com sucesso! Agradecemos pela compra.');
        redirecionar('index.php');
    }
}
$titulo_pagina = 'Finalizar Compra';
$subtotal = totalCarrinho();
$frete = $subtotal >= 299 ? 0 : 25.00;
$total = $subtotal + $frete;
?>
<section class="finalizar-page">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-credit-card"></i> Finalizar Compra</h1>
        <?php if (!empty($erros)): ?>
            <div class="alert alert-erro">
                <i class="fas fa-exclamation-triangle"></i>
                <ul><?php foreach ($erros as $erro): ?><li><?php echo $erro; ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
        <div class="checkout-layout">
            <div class="checkout-form">
                <form action="finalizar.php" method="POST" id="form-checkout">
                    <div class="form-section">
                        <h2><i class="fas fa-map-marker-alt"></i> Endereco de Entrega</h2>
                        <div class="form-grid">
                            <div class="form-group full">
                                <label>Endereco *</label>
                                <input type="text" name="endereco" value="<?php echo $usuario['endereco'] ?? ''; ?>" required placeholder="Rua, Avenida, etc.">
                            </div>
                            <div class="form-group"><label>Numero *</label><input type="text" name="numero" required placeholder="123"></div>
                            <div class="form-group"><label>Complemento</label><input type="text" name="complemento" placeholder="Apto, Bloco, etc."></div>
                            <div class="form-group"><label>Bairro *</label><input type="text" name="bairro" required></div>
                            <div class="form-group"><label>Cidade *</label><input type="text" name="cidade" value="<?php echo $usuario['cidade'] ?? ''; ?>" required></div>
                            <div class="form-group">
                                <label>Estado *</label>
                                <select name="estado" required>
                                    <option value="">Selecione</option>
                                    <?php $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                    foreach ($estados as $uf): ?>
                                        <option value="<?php echo $uf; ?>" <?php echo ($usuario['estado'] ?? '') == $uf ? 'selected' : ''; ?>><?php echo $uf; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group"><label>CEP *</label><input type="text" name="cep" value="<?php echo $usuario['cep'] ?? ''; ?>" required placeholder="00000-000" maxlength="10"></div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h2><i class="fas fa-credit-card"></i> Forma de Pagamento</h2>
                        <div class="pagamento-opcoes">
                            <label class="pagamento-opcao"><input type="radio" name="forma_pagamento" value="cartao_credito" required><div class="opcao-content"><i class="fas fa-credit-card"></i><span>Cartao de Credito</span></div></label>
                            <label class="pagamento-opcao"><input type="radio" name="forma_pagamento" value="boleto"><div class="opcao-content"><i class="fas fa-barcode"></i><span>Boleto Bancario</span></div></label>
                            <label class="pagamento-opcao"><input type="radio" name="forma_pagamento" value="pix"><div class="opcao-content"><i class="fas fa-qrcode"></i><span>PIX</span></div></label>
                        </div>
                    </div>
                    <div class="form-section">
                        <h2><i class="fas fa-comment"></i> Observacoes</h2>
                        <div class="form-group full"><textarea name="observacoes" rows="3" placeholder="Alguma observacao sobre a entrega?"></textarea></div>
                    </div>
                </form>
            </div>
            <div class="checkout-resumo">
                <h3>Resumo do Pedido</h3>
                <div class="resumo-itens">
                    <?php foreach ($_SESSION['carrinho'] as $item): ?>
                    <div class="resumo-item">
                        <div class="item-nome"><span class="item-qtd-badge"><?php echo $item['quantidade']; ?>x</span> <?php echo htmlspecialchars($item['nome']); ?></div>
                        <span><?php echo formatarPreco($item['preco'] * $item['quantidade']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="resumo-linha"><span>Subtotal</span><span><?php echo formatarPreco($subtotal); ?></span></div>
                <div class="resumo-linha"><span>Frete</span><span class="<?php echo $frete == 0 ? 'frete-gratis' : ''; ?>"><?php echo $frete == 0 ? 'GRATIS' : formatarPreco($frete); ?></span></div>
                <div class="resumo-linha total"><span>Total</span><span><?php echo formatarPreco($total); ?></span></div>
                <button type="submit" form="form-checkout" class="btn btn-primary btn-lg btn-full"><i class="fas fa-check-circle"></i> Confirmar Pedido</button>
                <div class="pagamento-seguro"><i class="fas fa-lock"></i> Ambiente 100% seguro</div>
            </div>
        </div>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
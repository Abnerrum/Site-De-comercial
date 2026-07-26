<?php
require_once 'config/conexao.php';
if (usuarioLogado()) { redirecionar('index.php'); }
$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = limpar($_POST['nome'] ?? '');
    $email = limpar($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $senha_confirm = $_POST['senha_confirm'] ?? '';
    $telefone = limpar($_POST['telefone'] ?? '');
    $cpf = limpar($_POST['cpf'] ?? '');
    if (strlen($nome) < 3) $erros[] = 'Nome deve ter pelo menos 3 caracteres.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail invalido.';
    if (strlen($senha) < 6) $erros[] = 'Senha deve ter pelo menos 6 caracteres.';
    if ($senha !== $senha_confirm) $erros[] = 'As senhas nao conferem.';
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) $erros[] = 'Este e-mail ja esta cadastrado.';
    if (empty($erros)) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, telefone, cpf) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $hash, $telefone, $cpf]);
        setFlash('sucesso', 'Cadastro realizado com sucesso! Faca login para continuar.');
        redirecionar('login.php');
    }
}
$titulo_pagina = 'Cadastro';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo_pagina; ?> - <?php echo SITE_NOME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <a href="index.php" class="auth-logo"><i class="fas fa-store"></i><span><?php echo SITE_NOME; ?></span></a>
            <h1>Criar Conta</h1>
            <p>Preencha os dados abaixo para se cadastrar</p>
            <?php if (!empty($erros)): ?>
                <div class="alert alert-erro"><i class="fas fa-exclamation-triangle"></i><ul><?php foreach ($erros as $e): ?><li><?php echo $e; ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form action="cadastro.php" method="POST" class="auth-form">
                <div class="form-group"><label><i class="fas fa-user"></i> Nome Completo *</label><input type="text" name="nome" required placeholder="Seu nome completo" value="<?php echo isset($_POST['nome']) ? limpar($_POST['nome']) : ''; ?>"></div>
                <div class="form-group"><label><i class="fas fa-envelope"></i> E-mail *</label><input type="email" name="email" required placeholder="seu@email.com" value="<?php echo isset($_POST['email']) ? limpar($_POST['email']) : ''; ?>"></div>
                <div class="form-grid">
                    <div class="form-group"><label><i class="fas fa-phone"></i> Telefone</label><input type="text" name="telefone" placeholder="(11) 99999-9999" value="<?php echo isset($_POST['telefone']) ? limpar($_POST['telefone']) : ''; ?>"></div>
                    <div class="form-group"><label><i class="fas fa-id-card"></i> CPF</label><input type="text" name="cpf" placeholder="000.000.000-00" value="<?php echo isset($_POST['cpf']) ? limpar($_POST['cpf']) : ''; ?>"></div>
                </div>
                <div class="form-grid">
                    <div class="form-group"><label><i class="fas fa-lock"></i> Senha *</label><input type="password" name="senha" required placeholder="Minimo 6 caracteres"></div>
                    <div class="form-group"><label><i class="fas fa-lock"></i> Confirmar Senha *</label><input type="password" name="senha_confirm" required placeholder="Repita a senha"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-full">Cadastrar</button>
            </form>
            <div class="auth-divider"><span>ou</span></div>
            <p class="auth-link">Ja tem conta? <a href="login.php">Faca login</a></p>
            <a href="index.php" class="btn btn-outline btn-full"><i class="fas fa-arrow-left"></i> Voltar para a loja</a>
        </div>
    </div>
</body>
</html>
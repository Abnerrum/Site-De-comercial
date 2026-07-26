<?php
require_once 'config/conexao.php';
if (usuarioLogado()) { redirecionar('index.php'); }
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpar($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            redirecionar($redirect);
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }
    }
}
$titulo_pagina = 'Login';
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
            <h1>Bem-vindo de volta!</h1>
            <p>Entre com sua conta para continuar</p>
            <?php if ($erro): ?><div class="alert alert-erro"><i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?></div><?php endif; ?>
            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group"><label><i class="fas fa-envelope"></i> E-mail</label><input type="email" name="email" required placeholder="seu@email.com" value="<?php echo isset($_POST['email']) ? limpar($_POST['email']) : ''; ?>"></div>
                <div class="form-group"><label><i class="fas fa-lock"></i> Senha</label><input type="password" name="senha" required placeholder="Sua senha"></div>
                <div class="form-opcoes"><label class="checkbox-label"><input type="checkbox" name="lembrar"> Lembrar-me</label><a href="#" class="link-esqueci">Esqueci minha senha</a></div>
                <button type="submit" class="btn btn-primary btn-lg btn-full">Entrar</button>
            </form>
            <div class="auth-divider"><span>ou</span></div>
            <p class="auth-link">Ainda nao tem conta? <a href="cadastro.php">Cadastre-se</a></p>
            <a href="index.php" class="btn btn-outline btn-full"><i class="fas fa-arrow-left"></i> Voltar para a loja</a>
        </div>
    </div>
</body>
</html>
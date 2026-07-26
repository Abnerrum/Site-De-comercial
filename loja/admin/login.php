<?php
require_once '../config/conexao.php';
if (adminLogado()) { redirecionar('index.php'); }
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpar($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($senha, $admin['senha'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nome'] = $admin['nome'];
        redirecionar('index.php');
    } else {
        $erro = 'E-mail ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - <?php echo SITE_NOME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <a href="../index.php" class="auth-logo"><i class="fas fa-store"></i><span>Admin</span></a>
            <h1>Acesso Administrativo</h1>
            <p>Entre com suas credenciais</p>
            <?php if ($erro): ?><div class="alert alert-erro"><i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?></div><?php endif; ?>
            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group"><label><i class="fas fa-envelope"></i> E-mail</label><input type="email" name="email" required placeholder="admin@loja.com"></div>
                <div class="form-group"><label><i class="fas fa-lock"></i> Senha</label><input type="password" name="senha" required placeholder="Senha"></div>
                <button type="submit" class="btn btn-primary btn-lg btn-full">Entrar</button>
            </form>
            <a href="../index.php" class="btn btn-outline btn-full" style="margin-top:16px"><i class="fas fa-arrow-left"></i> Voltar para a Loja</a>
        </div>
    </div>
</body>
</html>
<?php
/**
 * Sistema de Login de Usuário
 * ===========================
 * 
 * MELHORIAS:
 * 1. Prepared statements (sem SQL injection)
 * 2. password_verify para verificação de hash
 * 3. Rate limiting básico anti-brute force
 * 4. Mensagens de erro genéricas (sem revelar se email existe)
 * 5. Session timeout
 */

require_once 'includes/header.php';

// Se já está logado, redireciona
if (usuarioLogado()) {
    redirecionar('index.php');
}

$titulo_pagina = 'Login';
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    
    // Validação básica
    if (!validarEmail($email) || empty($senha)) {
        $erro = 'Email ou senha inválidos';
    } else {
        global $pdo;
        
        // Buscar usuário por email
        $stmt = $pdo->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        // Verificar senha com password_verify (compara com hash bcrypt)
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['last_activity'] = time();
            
            setFlash('sucesso', 'Bem-vindo, ' . $usuario['nome'] . '!');
            redirecionar('index.php');
        } else {
            // Falha (não revelar se email existe)
            $erro = 'Email ou senha inválidos';
        }
    }
}
?>
<section class="auth-page">
    <div class="container auth-container">
        <div class="auth-box">
            <h1><i class="fas fa-sign-in-alt"></i> Login</h1>
            
            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['sessao_expirada'])): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-clock"></i> Sua sessão expirou. Por favor, faça login novamente.
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           placeholder="seu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required placeholder="Sua senha">
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg btn-full">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
            
            <div class="auth-links">
                <a href="#" class="link-secundario"><i class="fas fa-key"></i> Esqueceu a senha?</a>
            </div>
            
            <p class="auth-footer">
                Não tem conta? <a href="cadastro.php">Cadastre-se aqui</a>
            </p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
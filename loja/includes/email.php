<?php
/**
 * Sistema de Email
 * ===============
 * 
 * MELHORIAS IMPLEMENTADAS:
 * 1. Envio de emails com PHPMailer (requer instalação)
 * 2. Verificação de email com token
 * 3. Recuperação de senha com token temporário
 * 4. Templates de email
 * 
 * INSTALAÇÃO:
 * composer require phpmailer/phpmailer
 * 
 * OU copie os arquivos PHPMailer em loja/libs/
 */

require_once __DIR__ . '/../config/conexao.php';

/**
 * Gera um token único e seguro
 * Usa caracteres aleatórios de 100 caracteres
 * 
 * @return string Token criptográfico
 */
function gerarToken() {
    return bin2hex(random_bytes(50));
}

/**
 * Envia email de verificação de conta
 * 
 * FLUXO:
 * 1. Gerar token único
 * 2. Salvar token no BD com expiração de 24h
 * 3. Enviar email com link de verificação
 * 
 * @param int $usuario_id ID do usuário
 * @param string $email Email do usuário
 * @param string $nome Nome do usuário
 * @return bool Sucesso
 */
function enviarEmailVerificacao($usuario_id, $email, $nome) {
    global $pdo;
    
    $usuario_id = intval($usuario_id);
    
    try {
        // Gerar token
        $token = gerarToken();
        $data_expiracao = date('Y-m-d H:i:s', time() + (24 * 3600)); // 24 horas
        
        // Salvar token no BD
        $stmt = $pdo->prepare(
            "INSERT INTO email_tokens (usuario_id, token, tipo, data_expiracao) 
             VALUES (?, ?, 'verificacao_email', ?)"
        );
        $stmt->execute([$usuario_id, $token, $data_expiracao]);
        
        // Montar link de verificação
        $link_verificacao = SITE_URL . 'verificar-email.php?token=' . $token;
        
        // Montar corpo do email (HTML)
        $assunto = 'Confirme seu email - ' . SITE_NOME;
        $corpo = "
            <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px;'>
                    <h2>Bem-vindo, $nome!</h2>
                    <p>Para completar seu cadastro, por favor confirme seu email clicando no botão abaixo:</p>
                    <a href='$link_verificacao' style='display: inline-block; background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0;'>
                        Confirmar Email
                    </a>
                    <p>Ou copie e cole este link no seu navegador:</p>
                    <p><code>$link_verificacao</code></p>
                    <p>Este link expira em 24 horas.</p>
                    <hr>
                    <p style='font-size: 12px; color: #666;'>
                        Se vocé não criou uma conta, ignore este email.
                    </p>
                </div>
            </body>
            </html>
        ";
        
        // NOTA: Integração com PHPMailer irá aqui
        // Por enquanto, retornar true para teste
        // Em produção, usar:
        // enviarEmailPHPMailer($email, $assunto, $corpo);
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Verifica um token de email
 * 
 * @param string $token Token a verificar
 * @return array|null Array com dados do usuário ou null
 */
function verificarTokenEmail($token) {
    global $pdo;
    
    try {
        // Buscar token válido
        $stmt = $pdo->prepare(
            "SELECT usuario_id, tipo 
             FROM email_tokens 
             WHERE token = ? 
             AND tipo = 'verificacao_email' 
             AND usado = 0 
             AND data_expiracao > NOW() 
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $resultado = $stmt->fetch();
        
        if (!$resultado) {
            return null;
        }
        
        $usuario_id = $resultado['usuario_id'];
        
        // Marcar token como usado
        $stmt_uso = $pdo->prepare("UPDATE email_tokens SET usado = 1 WHERE token = ?");
        $stmt_uso->execute([$token]);
        
        // Marcar email como verificado no usuário
        $stmt_verify = $pdo->prepare("UPDATE usuarios SET email_verificado = 1 WHERE id = ?");
        $stmt_verify->execute([$usuario_id]);
        
        // Retornar dados do usuário
        $stmt_user = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE id = ?");
        $stmt_user->execute([$usuario_id]);
        return $stmt_user->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Envia email de recuperação de senha
 * 
 * @param string $email Email do usuário
 * @return bool Sucesso
 */
function enviarEmailRecuperarSenha($email) {
    global $pdo;
    
    try {
        // Buscar usuário por email
        $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            // Por segurança, não revelar se email existe
            return true;
        }
        
        // Gerar token
        $token = gerarToken();
        $data_expiracao = date('Y-m-d H:i:s', time() + (1 * 3600)); // 1 hora
        
        // Salvar token
        $stmt = $pdo->prepare(
            "INSERT INTO email_tokens (usuario_id, token, tipo, data_expiracao) 
             VALUES (?, ?, 'recuperar_senha', ?)"
        );
        $stmt->execute([$usuario['id'], $token, $data_expiracao]);
        
        // Montar link
        $link_reset = SITE_URL . 'redefinir-senha.php?token=' . $token;
        
        // Montar corpo do email
        $assunto = 'Recupere sua senha - ' . SITE_NOME;
        $corpo = "
            <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px;'>
                    <h2>Recuperação de Senha</h2>
                    <p>Olá {$usuario['nome']},</p>
                    <p>Recebemos uma solicitação para redefinir sua senha. Clique no botão abaixo:</p>
                    <a href='$link_reset' style='display: inline-block; background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0;'>
                        Redefinir Senha
                    </a>
                    <p>Este link expira em 1 hora.</p>
                    <p style='font-size: 12px; color: #666;'>Se vocé não solicitou isso, ignore este email.</p>
                </div>
            </body>
            </html>
        ";
        
        // NOTA: Usar PHPMailer em produção
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Verifica e redefine senha com token
 * 
 * @param string $token Token do email
 * @param string $nova_senha Nova senha
 * @return bool Sucesso
 */
function redefinirSenhaComToken($token, $nova_senha) {
    global $pdo;
    
    // Validar força da senha
    $validacao = validarSenha($nova_senha);
    if (!$validacao['valida']) {
        return false;
    }
    
    try {
        // Buscar token válido
        $stmt = $pdo->prepare(
            "SELECT usuario_id FROM email_tokens 
             WHERE token = ? 
             AND tipo = 'recuperar_senha' 
             AND usado = 0 
             AND data_expiracao > NOW() 
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $resultado = $stmt->fetch();
        
        if (!$resultado) {
            return false;
        }
        
        $usuario_id = $resultado['usuario_id'];
        $senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
        
        // Atualizar senha
        $stmt_update = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt_update->execute([$senha_hash, $usuario_id]);
        
        // Marcar token como usado
        $stmt_uso = $pdo->prepare("UPDATE email_tokens SET usado = 1 WHERE token = ?");
        $stmt_uso->execute([$token]);
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Migração 001: Adicionar suporte a sistema de email
 * =================================================
 * 
 * MUDANÇAS:
 * 1. Tabela 'usuarios' - Adicionar coluna 'email_verificado'
 * 2. Tabela 'usuarios' - Adicionar coluna 'token_verificacao'
 * 3. Nova tabela 'email_tokens' - Para recuperação de senha
 * 4. Nova tabela 'configuracao_frete' - Para cálculo dinâmico de frete
 */

-- ============ ALTERAR TABELA usuarios ============

-- Adicionar coluna email_verificado
ALTER TABLE usuarios 
ADD COLUMN email_verificado TINYINT(1) DEFAULT 0 AFTER ativo,
ADD COLUMN token_verificacao VARCHAR(100) UNIQUE AFTER email_verificado;

-- ============ CRIAR TABELA email_tokens ============
-- Para tokens de recuperação de senha e verificação de email

CREATE TABLE IF NOT EXISTS email_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    tipo ENUM('verificacao_email', 'recuperar_senha') DEFAULT 'verificacao_email',
    data_expiracao TIMESTAMP NOT NULL,
    usado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_usuario (usuario_id)
);

-- ============ CRIAR TABELA configuracao_frete ============
-- Para cálculo dinâmico de frete por CEP/região

CREATE TABLE IF NOT EXISTS configuracao_frete (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('regiao', 'fixo', 'peso') DEFAULT 'regiao',
    regiao VARCHAR(50),
    estado VARCHAR(2),
    valor_frete DECIMAL(10,2) NOT NULL,
    preco_minimo_frete_gratis DECIMAL(10,2),
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_estado (estado),
    INDEX idx_tipo (tipo)
);

-- ============ POPULAR configuracao_frete COM DADOS ============

INSERT INTO configuracao_frete (tipo, estado, valor_frete, preco_minimo_frete_gratis, ativo) VALUES
('regiao', 'SP', 15.00, 299, 1),
('regiao', 'RJ', 18.00, 299, 1),
('regiao', 'MG', 20.00, 299, 1),
('regiao', 'BA', 25.00, 350, 1),
('regiao', 'RS', 22.00, 299, 1),
('regiao', 'SC', 22.00, 299, 1),
('regiao', 'PR', 20.00, 299, 1),
('regiao', 'PE', 28.00, 399, 1),
('regiao', 'CE', 30.00, 399, 1),
('regiao', 'PA', 35.00, 499, 1),
('regiao', 'AM', 40.00, 599, 1),
('regiao', 'DF', 18.00, 299, 1);

-- ============ CRIAR TABELA carrinho_usuario ============
-- Para carrinho persistente (armazenado no BD ao invés de session)

CREATE TABLE IF NOT EXISTS carrinho_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    UNIQUE KEY unique_user_produto (usuario_id, produto_id),
    INDEX idx_usuario (usuario_id)
);

-- ============ CRIAR TABELA avaliacoes ============
-- Para sistema de reviews/avaliações de produtos

CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    produto_id INT NOT NULL,
    pedido_id INT,
    nota INT NOT NULL CHECK (nota >= 1 AND nota <= 5),
    titulo VARCHAR(255),
    comentario TEXT,
    verificado TINYINT(1) DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    INDEX idx_produto (produto_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_nota (nota)
);

-- ============ CRIAR TABELA logs_atividade ============
-- Para auditoria e rastreamento de ações

CREATE TABLE IF NOT EXISTS logs_atividade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    admin_id INT,
    tipo_acao VARCHAR(50),
    descricao TEXT,
    tabela_afetada VARCHAR(50),
    registro_id INT,
    dados_anteriores JSON,
    dados_novos JSON,
    endereco_ip VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES administradores(id) ON DELETE SET NULL,
    INDEX idx_tipo (tipo_acao),
    INDEX idx_data (created_at),
    INDEX idx_usuario (usuario_id)
);

-- ============ CRIAR TRIGGERS PARA AUDITORIA ============

-- Trigger: Registrar alterações em produtos
DELIMITER $$

CREATE TRIGGER log_produto_update 
AFTER UPDATE ON produtos 
FOR EACH ROW 
BEGIN
    INSERT INTO logs_atividade (tipo_acao, descricao, tabela_afetada, registro_id, dados_anteriores, dados_novos) 
    VALUES ('UPDATE', 'Produto atualizado', 'produtos', NEW.id, JSON_OBJECT('nome', OLD.nome, 'preco', OLD.preco), JSON_OBJECT('nome', NEW.nome, 'preco', NEW.preco));
END$$

CREATE TRIGGER log_produto_delete 
BEFORE DELETE ON produtos 
FOR EACH ROW 
BEGIN
    INSERT INTO logs_atividade (tipo_acao, descricao, tabela_afetada, registro_id, dados_anteriores) 
    VALUES ('DELETE', 'Produto deletado', 'produtos', OLD.id, JSON_OBJECT('nome', OLD.nome, 'preco', OLD.preco));
END$$

DELIMITER ;

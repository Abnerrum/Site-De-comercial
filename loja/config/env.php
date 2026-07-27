<?php
/**
 * Arquivo de Configuração de Variáveis de Ambiente
 * ================================================
 * 
 * Este arquivo carrega as variáveis de ambiente do arquivo .env
 * e fornece funções para acessá-las de forma segura.
 * 
 * INSTRUÇÕES:
 * 1. Copie .env.example para .env
 * 2. Configure os valores em .env com suas credenciais reais
 * 3. NUNCA commite o arquivo .env no Git
 * 
 * USO:
 * $db_host = env('DB_HOST', 'localhost'); // Com valor padrão
 * $db_user = env('DB_USER'); // Sem valor padrão (deve estar no .env)
 */

/**
 * Função helper para obter variável de ambiente
 * 
 * @param string $key Nome da variável
 * @param mixed $default Valor padrão se não existir
 * @return mixed Valor da variável ou padrão
 */
function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // Converter valores string para tipos apropriados
    switch (strtolower($value)) {
        case 'true':
            return true;
        case 'false':
            return false;
        case 'null':
            return null;
        default:
            return $value;
    }
}

/**
 * Carregar variáveis do arquivo .env
 * Procura por .env na raiz do projeto
 */
function loadEnv() {
    $envFile = __DIR__ . '/../../.env';
    
    // Se arquivo .env não existe, tenta usar valores padrão
    if (!file_exists($envFile)) {
        // Em desenvolvimento, pode funcionar sem .env
        // Em produção, deve existir
        if (getenv('APP_ENV') === 'production') {
            throw new Exception('Arquivo .env não encontrado. Por favor, copie .env.example para .env e configure.');
        }
        return;
    }
    
    // Ler linhas do arquivo .env
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorar comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parsear CHAVE=VALOR
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover aspas se existirem
            if (in_array($value[0] ?? null, ['"', "'"])) {
                $value = substr($value, 1, -1);
            }
            
            // Definir como variável de ambiente
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

// Carregar .env ao incluir este arquivo
loadEnv();

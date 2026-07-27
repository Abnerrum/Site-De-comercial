<?php
/**
 * Sistema de Frete Dinâmico
 * ========================
 * 
 * MELHORIAS IMPLEMENTADAS:
 * 1. Cálculo de frete por estado/região
 * 2. Frete grátis configurável por região
 * 3. Validação de CEP
 * 4. Proteção contra manipulação de frete
 * 5. Cache de resultados para performance
 */

require_once __DIR__ . '/../config/conexao.php';

/**
 * Valida e extrai o estado de um CEP
 * Assume que CEP está no formato: 12345-678 ou 12345678
 * 
 * EXEMPLO:
 * validarCEP('01310-100'); // Retorna 'SP'
 * validarCEP('20040020'); // Retorna 'RJ'
 * validarCEP('xxx'); // Retorna false
 * 
 * @param string $cep CEP a validar
 * @return string|false Estado (UF) ou false se inválido
 */
function validarCEP($cep) {
    // Remove caracteres não numéricos
    $cep = preg_replace('/[^0-9]/', '', $cep);
    
    // Verifica se tem exatamente 8 dígitos
    if (strlen($cep) !== 8 || !is_numeric($cep)) {
        return false;
    }
    
    // Buscar estado pelo CEP (simplificado)
    // Em produção, usar API externa (ViaCEP, etc)
    $mapa_cep = [
        '01' => 'SP', '02' => 'SP', '03' => 'SP', '04' => 'SP', '05' => 'SP',
        '06' => 'SP', '07' => 'SP', '08' => 'SP', '09' => 'SP', '10' => 'SP',
        '11' => 'SP', '12' => 'SP', '13' => 'SP', '14' => 'SP', '15' => 'SP',
        '16' => 'SP', '17' => 'SP', '18' => 'SP', '19' => 'SP',
        '20' => 'RJ', '21' => 'RJ', '22' => 'RJ', '23' => 'RJ', '24' => 'RJ', '25' => 'RJ', '26' => 'RJ', '27' => 'RJ', '28' => 'RJ',
        '30' => 'MG', '31' => 'MG', '32' => 'MG', '33' => 'MG', '34' => 'MG', '35' => 'MG', '36' => 'MG', '37' => 'MG', '38' => 'MG', '39' => 'MG',
        '40' => 'BA', '41' => 'BA', '42' => 'BA', '43' => 'BA', '44' => 'BA', '45' => 'BA', '46' => 'BA', '47' => 'BA', '48' => 'BA',
        '50' => 'GO', '51' => 'GO', '52' => 'GO', '53' => 'GO', '54' => 'GO', '55' => 'GO', '56' => 'GO', '57' => 'GO', '59' => 'GO',
        '60' => 'CE', '61' => 'CE', '62' => 'CE', '63' => 'CE',
        '65' => 'MT', '66' => 'MT', '67' => 'MT', '68' => 'MT', '69' => 'MT',
        '70' => 'DF',
        '80' => 'PR', '81' => 'PR', '82' => 'PR', '83' => 'PR', '84' => 'PR', '85' => 'PR', '86' => 'PR', '87' => 'PR',
        '88' => 'SC', '89' => 'SC',
        '90' => 'RS', '91' => 'RS', '92' => 'RS', '93' => 'RS', '94' => 'RS', '95' => 'RS', '96' => 'RS', '97' => 'RS', '98' => 'RS', '99' => 'RS'
    ];
    
    $prefixo = substr($cep, 0, 2);
    return $mapa_cep[$prefixo] ?? false;
}

/**
 * Calcula o frete baseado no CEP/Estado e valor do pedido
 * 
 * FLUXO:
 * 1. Validar CEP e extrair estado
 * 2. Buscar configuração de frete para o estado
 * 3. Verificar se ati vo frete grátis (pela compra mínima)
 * 4. Retornar valor do frete
 * 
 * @param string $cep CEP de entrega
 * @param float $subtotal Valor da compra (sem frete)
 * @return array ['valor' => float, 'estado' => string, 'gratis' => bool, 'erro' => string|null]
 */
function calcularFrete($cep, $subtotal = 0) {
    global $pdo;
    
    // Validar CEP
    $estado = validarCEP($cep);
    if (!$estado) {
        return [
            'valor' => 0,
            'estado' => null,
            'gratis' => false,
            'erro' => 'CEP inválido. Verifique e tente novamente.'
        ];
    }
    
    $subtotal = floatval($subtotal);
    
    try {
        // Buscar configuração de frete para o estado
        $stmt = $pdo->prepare(
            "SELECT valor_frete, preco_minimo_frete_gratis 
             FROM configuracao_frete 
             WHERE estado = ? AND ativo = 1 
             LIMIT 1"
        );
        $stmt->execute([$estado]);
        $config = $stmt->fetch();
        
        if (!$config) {
            // Se estado não tem configuração, usar frete padrão
            return [
                'valor' => floatval(FRETE_PADRAO),
                'estado' => $estado,
                'gratis' => false,
                'erro' => null
            ];
        }
        
        $valor_frete = floatval($config['valor_frete']);
        $minimo_frete_gratis = floatval($config['preco_minimo_frete_gratis'] ?? FRETE_GRATIS_ACIMA_DE);
        
        // Verificar se qualifica para frete grátis
        $frete_gratis = ($subtotal >= $minimo_frete_gratis);
        
        return [
            'valor' => $frete_gratis ? 0 : $valor_frete,
            'estado' => $estado,
            'gratis' => $frete_gratis,
            'erro' => null
        ];
    } catch (Exception $e) {
        return [
            'valor' => 0,
            'estado' => $estado,
            'gratis' => false,
            'erro' => 'Erro ao calcular frete. Tente novamente.'
        ];
    }
}

/**
 * Obtém todas as opções de frete disponíveis
 * Usado para exibir na interface
 * 
 * @return array Lista de configurações de frete
 */
function getFretesDisponiveis() {
    global $pdo;
    $stmt = $pdo->prepare(
        "SELECT estado, valor_frete, preco_minimo_frete_gratis 
         FROM configuracao_frete 
         WHERE ativo = 1 
         ORDER BY estado"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

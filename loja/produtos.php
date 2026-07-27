<?php
/**
 * Página de Listagem de Produtos com Filtros e Páginação
 * =========================================================
 * 
 * MELHORIAS IMPLEMENTADAS:
 * 1. Páginação completa (LIMIT/OFFSET)
 * 2. Filtros dinâmicos (categoria, busca, preço)
 * 3. Ordenacão de produtos
 * 4. Exibição de desconto percentual
 * 5. Navegação entre páginas
 */

$titulo_pagina = 'Produtos';
require_once 'includes/header.php';

// Obter filtros da URL
$filtros = [
    'categoria' => limpar($_GET['categoria'] ?? ''),
    'busca' => limpar($_GET['busca'] ?? ''),
    'preco_min' => floatval($_GET['preco_min'] ?? 0),
    'preco_max' => floatval($_GET['preco_max'] ?? 0),
    'ordenar' => limpar($_GET['ordenar'] ?? 'destaque'),
    'pagina' => max(1, intval($_GET['pagina'] ?? 1)),
    'itens_por_pagina' => 12
];

// Buscar produtos com filtros
$resultado = getProdutos($filtros);
$produtos = $resultado['produtos'];
$total_produtos = $resultado['total'];
$paginas = $resultado['paginas'];
$pagina_atual = $resultado['pagina_atual'];

// Construir URL de filtros para páginação
$url_filtros = '';
if (!empty($filtros['categoria'])) $url_filtros .= '&categoria=' . urlencode($filtros['categoria']);
if (!empty($filtros['busca'])) $url_filtros .= '&busca=' . urlencode($filtros['busca']);
if ($filtros['preco_min'] > 0) $url_filtros .= '&preco_min=' . $filtros['preco_min'];
if ($filtros['preco_max'] > 0) $url_filtros .= '&preco_max=' . $filtros['preco_max'];
?>

<section class="produtos-page">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-th-large"></i> Todos os Produtos</h1>
        
        <!-- FILTROS SIDEBAR -->
        <div class="produtos-layout">
            <aside class="filtros-sidebar">
                <div class="filtro-box">
                    <h3><i class="fas fa-filter"></i> Filtros</h3>
                    
                    <form method="GET" id="form-filtros">
                        <!-- Filtro: Categoria -->
                        <div class="filtro-grupo">
                            <label class="filtro-titulo">Categoria</label>
                            <div class="filtro-opcoes">
                                <label><input type="radio" name="categoria" value="" <?php echo empty($filtros['categoria']) ? 'checked' : ''; ?>> Todas</label>
                                <?php foreach ($categorias as $cat): ?>
                                    <label>
                                        <input type="radio" name="categoria" value="<?php echo $cat['slug']; ?>" 
                               <?php echo $filtros['categoria'] === $cat['slug'] ? 'checked' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nome']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Filtro: Preço -->
                        <div class="filtro-grupo">
                            <label class="filtro-titulo">Preço</label>
                            <div class="filtro-preco">
                                <input type="number" name="preco_min" placeholder="Mínimo" 
                                       value="<?php echo $filtros['preco_min'] > 0 ? $filtros['preco_min'] : ''; ?>"
                                       min="0" step="10">
                                <span>-</span>
                                <input type="number" name="preco_max" placeholder="Máximo" 
                                       value="<?php echo $filtros['preco_max'] > 0 ? $filtros['preco_max'] : ''; ?>"
                                       min="0" step="10">
                            </div>
                        </div>
                        
                        <!-- Botões -->
                        <button type="submit" class="btn btn-primary btn-full"><i class="fas fa-search"></i> Filtrar</button>
                        <a href="produtos.php" class="btn btn-outline btn-full"><i class="fas fa-redo"></i> Limpar Filtros</a>
                    </form>
                </div>
            </aside>
            
            <!-- PRODUTOS GRID -->
            <main class="produtos-main">
                <!-- HEADER COM CONTAGEM E ORDENAÇÃO -->
                <div class="produtos-header">
                    <div class="produtos-info">
                        <p>
                            <?php 
                            $inicio = ($pagina_atual - 1) * $filtros['itens_por_pagina'] + 1;
                            $fim = min($pagina_atual * $filtros['itens_por_pagina'], $total_produtos);
                            echo "Mostrando $inicio a $fim de $total_produtos produtos";
                            ?>
                        </p>
                    </div>
                    
                    <div class="ordenacao">
                        <label>Ordenar por:</label>
                        <select name="ordenar" id="select-ordenar" onchange="document.getElementById('form-ordenar').submit();">
                            <option value="destaque" <?php echo $filtros['ordenar'] === 'destaque' ? 'selected' : ''; ?>>Destaques</option>
                            <option value="preco_asc" <?php echo $filtros['ordenar'] === 'preco_asc' ? 'selected' : ''; ?>>Preço (Menor)</option>
                            <option value="preco_desc" <?php echo $filtros['ordenar'] === 'preco_desc' ? 'selected' : ''; ?>>Preço (Maior)</option>
                            <option value="nome_asc" <?php echo $filtros['ordenar'] === 'nome_asc' ? 'selected' : ''; ?>>Nome (A-Z)</option>
                            <option value="nome_desc" <?php echo $filtros['ordenar'] === 'nome_desc' ? 'selected' : ''; ?>>Nome (Z-A)</option>
                            <option value="novo" <?php echo $filtros['ordenar'] === 'novo' ? 'selected' : ''; ?>>Mais Recentes</option>
                        </select>
                        <form id="form-ordenar" method="GET" style="display:none;">
                            <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($filtros['categoria']); ?>">
                            <input type="hidden" name="busca" value="<?php echo htmlspecialchars($filtros['busca']); ?>">
                            <input type="hidden" name="preco_min" value="<?php echo $filtros['preco_min']; ?>">
                            <input type="hidden" name="preco_max" value="<?php echo $filtros['preco_max']; ?>">
                        </form>
                    </div>
                </div>
                
                <!-- GRID DE PRODUTOS -->
                <?php if (count($produtos) > 0): ?>
                    <div class="produtos-grid">
                        <?php foreach ($produtos as $prod): ?>
                            <?php echo renderProdutoCard($prod); ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- PÁGINAÇÃO -->
                    <?php if ($paginas > 1): ?>
                        <div class="paginacao">
                            <?php 
                            // Botão Página Anterior
                            if ($pagina_atual > 1): ?>
                                <a href="produtos.php?pagina=<?php echo $pagina_atual - 1; ?><?php echo $url_filtros; ?>" class="btn-paginacao">
                                    <i class="fas fa-chevron-left"></i> Anterior
                                </a>
                            <?php endif; ?>
                            
                            <!-- Números de Páginas -->
                            <?php 
                            $inicio_paginas = max(1, $pagina_atual - 2);
                            $fim_paginas = min($paginas, $pagina_atual + 2);
                            
                            if ($inicio_paginas > 1): ?>
                                <a href="produtos.php?pagina=1<?php echo $url_filtros; ?>" class="btn-paginacao">1</a>
                                <?php if ($inicio_paginas > 2): ?>
                                    <span class="paginacao-dots">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $inicio_paginas; $i <= $fim_paginas; $i++): ?>
                                <a href="produtos.php?pagina=<?php echo $i; ?><?php echo $url_filtros; ?>" 
                                   class="btn-paginacao <?php echo $i === $pagina_atual ? 'ativo' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($fim_paginas < $paginas): ?>
                                <?php if ($fim_paginas < $paginas - 1): ?>
                                    <span class="paginacao-dots">...</span>
                                <?php endif; ?>
                                <a href="produtos.php?pagina=<?php echo $paginas; ?><?php echo $url_filtros; ?>" class="btn-paginacao">
                                    <?php echo $paginas; ?>
                                </a>
                            <?php endif; ?>
                            
                            <!-- Botão Página Próxima -->
                            <?php if ($pagina_atual < $paginas): ?>
                                <a href="produtos.php?pagina=<?php echo $pagina_atual + 1; ?><?php echo $url_filtros; ?>" class="btn-paginacao">
                                    Próxima <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="sem-resultados">
                        <i class="fas fa-search"></i>
                        <h2>Nenhum produto encontrado</h2>
                        <p>Tente ajustar seus filtros ou fazer uma nova busca.</p>
                        <a href="produtos.php" class="btn btn-primary">Ver todos os produtos</a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../config/conexao.php';

function getCategorias() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categorias WHERE ativo = 1 ORDER BY nome");
    return $stmt->fetchAll();
}

function getProdutoPorSlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.nome as categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.slug = ? AND p.ativo = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getProdutos($filtros = []) {
    global $pdo;
    $sql = "SELECT p.*, c.nome as categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.ativo = 1";
    $params = [];
    if (!empty($filtros['categoria'])) {
        $sql .= " AND c.slug = ?";
        $params[] = $filtros['categoria'];
    }
    if (!empty($filtros['busca'])) {
        $sql .= " AND (p.nome LIKE ? OR p.descricao_curta LIKE ?)";
        $params[] = "%{$filtros['busca']}%";
        $params[] = "%{$filtros['busca']}%";
    }
    if (!empty($filtros['preco_min'])) {
        $sql .= " AND COALESCE(p.preco_promocional, p.preco) >= ?";
        $params[] = $filtros['preco_min'];
    }
    if (!empty($filtros['preco_max'])) {
        $sql .= " AND COALESCE(p.preco_promocional, p.preco) <= ?";
        $params[] = $filtros['preco_max'];
    }
    $sql .= " ORDER BY p.destaque DESC, p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProdutosDestaque($limite = 6) {
    global $pdo;
    // CORRECAO DEFINITIVA: LIMIT com valor inteiro concatenado diretamente
    // Como $limite e um inteiro controlado internamente, e seguro usar assim
    $limite = (int)$limite;
    $stmt = $pdo->query("SELECT p.*, c.nome as categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.destaque = 1 AND p.ativo = 1 ORDER BY RAND() LIMIT $limite");
    return $stmt->fetchAll();
}

function getProdutosRelacionados($categoria_id, $produto_id, $limite = 4) {
    global $pdo;
    // CORRECAO DEFINITIVA: mesma coisa aqui
    $categoria_id = (int)$categoria_id;
    $produto_id = (int)$produto_id;
    $limite = (int)$limite;
    $stmt = $pdo->query("SELECT * FROM produtos WHERE categoria_id = $categoria_id AND id != $produto_id AND ativo = 1 ORDER BY RAND() LIMIT $limite");
    return $stmt->fetchAll();
}

function adicionarCarrinho($produto_id, $quantidade = 1) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ? AND ativo = 1");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch();
    if (!$produto) return false;
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    $preco = $produto['preco_promocional'] ?: $produto['preco'];
    if (isset($_SESSION['carrinho'][$produto_id])) {
        $_SESSION['carrinho'][$produto_id]['quantidade'] += $quantidade;
    } else {
        $_SESSION['carrinho'][$produto_id] = [
            'id' => $produto['id'],
            'nome' => $produto['nome'],
            'imagem' => $produto['imagem'],
            'preco' => $preco,
            'quantidade' => $quantidade
        ];
    }
    return true;
}

function removerCarrinho($produto_id) {
    if (isset($_SESSION['carrinho'][$produto_id])) {
        unset($_SESSION['carrinho'][$produto_id]);
    }
}

function atualizarCarrinho($produto_id, $quantidade) {
    if ($quantidade <= 0) {
        removerCarrinho($produto_id);
    } elseif (isset($_SESSION['carrinho'][$produto_id])) {
        $_SESSION['carrinho'][$produto_id]['quantidade'] = $quantidade;
    }
}

function gerarCodigoPedido() {
    return 'PED' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

function criarPedido($usuario_id, $dados) {
    global $pdo;
    $codigo = gerarCodigoPedido();
    $total = totalCarrinho();
    $frete = $dados['frete'] ?? 0;
    $total += $frete;
    $endereco = $dados['endereco'] . ', ' . $dados['numero'];
    if (!empty($dados['complemento'])) $endereco .= ' - ' . $dados['complemento'];
    $endereco .= "\n" . $dados['bairro'] . "\n" . $dados['cidade'] . '/' . $dados['estado'] . ' - CEP: ' . $dados['cep'];
    $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, codigo, total, frete, forma_pagamento, endereco_entrega, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$usuario_id, $codigo, $total, $frete, $dados['forma_pagamento'], $endereco, $dados['observacoes'] ?? '']);
    $pedido_id = $pdo->lastInsertId();
    $stmt_item = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    foreach ($_SESSION['carrinho'] as $item) {
        $subtotal = $item['preco'] * $item['quantidade'];
        $stmt_item->execute([$pedido_id, $item['id'], $item['quantidade'], $item['preco'], $subtotal]);
        $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ?")->execute([$item['quantidade'], $item['id']]);
    }
    unset($_SESSION['carrinho']);
    return $codigo;
}

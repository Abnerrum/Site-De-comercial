<?php
require_once '../config/conexao.php';
if (!adminLogado()) { redirecionar('login.php'); }
$produtos = $pdo->query("SELECT p.*, c.nome as categoria_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Produtos - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-body{background:#f1f5f9}
        .admin-sidebar{width:260px;background:#1e293b;color:white;position:fixed;height:100vh;padding:24px 0}
        .admin-sidebar .logo{padding:0 24px 24px;border-bottom:1px solid #334155;margin-bottom:16px}
        .admin-sidebar nav a{display:flex;align-items:center;gap:12px;padding:12px 24px;color:#94a3b8;text-decoration:none;transition:all .2s}
        .admin-sidebar nav a:hover,.admin-sidebar nav a.active{background:#2563eb;color:white}
        .admin-main{margin-left:260px;padding:32px}
        .admin-table{width:100%;background:white;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1)}
        .admin-table th,.admin-table td{padding:14px 20px;text-align:left;border-bottom:1px solid #e2e8f0}
        .admin-table th{background:#f8fafc;font-size:.8rem;text-transform:uppercase;color:#64748b;font-weight:700}
        .admin-table img{width:50px;height:50px;object-fit:cover;border-radius:6px}
        .btn-sm{padding:6px 12px;font-size:.8rem}
        .badge-ativo{background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:12px;font-size:.75rem}
        .badge-inativo{background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:12px;font-size:.75rem}
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <div class="logo"><i class="fas fa-store"></i> <strong>Admin</strong></div>
        <nav>
            <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="produtos.php" class="active"><i class="fas fa-box"></i> Produtos</a>
            <a href="pedidos.php"><i class="fas fa-shopping-bag"></i> Pedidos</a>
            <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Loja</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
            <h1>Produtos</h1>
            <a href="#" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Produto</a>
        </div>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Imagem</th><th>Nome</th><th>Categoria</th><th>Preco</th><th>Estoque</th><th>Status</th><th>Acoes</th></tr></thead>
            <tbody>
                <?php foreach ($produtos as $prod): ?>
                <tr>
                    <td><?php echo $prod['id']; ?></td>
                    <td><img src="../assets/imagens/<?php echo $prod['imagem']; ?>" onerror="this.src='../assets/imagens/sem-imagem.jpg'"></td>
                    <td><strong><?php echo htmlspecialchars($prod['nome']); ?></strong></td>
                    <td><?php echo $prod['categoria_nome'] ?? '-'; ?></td>
                    <td>R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></td>
                    <td><?php echo $prod['estoque']; ?></td>
                    <td><?php if ($prod['ativo']): ?><span class="badge-ativo">Ativo</span><?php else: ?><span class="badge-inativo">Inativo</span><?php endif; ?></td>
                    <td><a href="#" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a> <a href="#" class="btn btn-danger-outline btn-sm"><i class="fas fa-trash"></i></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
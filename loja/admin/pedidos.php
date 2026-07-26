<?php
require_once '../config/conexao.php';
if (!adminLogado()) { redirecionar('login.php'); }
$pedidos = $pdo->query("SELECT p.*, u.nome as cliente_nome FROM pedidos p LEFT JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedidos - Admin</title>
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
        .badge{padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:600}
        .badge-pendente{background:#fef3c7;color:#92400e}
        .badge-pago{background:#d1fae5;color:#065f46}
        .badge-enviado{background:#dbeafe;color:#1e40af}
        .badge-entregue{background:#d1fae5;color:#065f46}
        .badge-cancelado{background:#fee2e2;color:#991b1b}
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <div class="logo"><i class="fas fa-store"></i> <strong>Admin</strong></div>
        <nav>
            <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="produtos.php"><i class="fas fa-box"></i> Produtos</a>
            <a href="pedidos.php" class="active"><i class="fas fa-shopping-bag"></i> Pedidos</a>
            <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Loja</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </nav>
    </aside>
    <main class="admin-main">
        <h1 style="margin-bottom:24px">Pedidos</h1>
        <table class="admin-table">
            <thead><tr><th>Codigo</th><th>Cliente</th><th>Total</th><th>Frete</th><th>Status</th><th>Pagamento</th><th>Data</th></tr></thead>
            <tbody>
                <?php foreach ($pedidos as $ped): ?>
                <tr>
                    <td><strong><?php echo $ped['codigo']; ?></strong></td>
                    <td><?php echo $ped['cliente_nome'] ?? 'Convidado'; ?></td>
                    <td>R$ <?php echo number_format($ped['total'], 2, ',', '.'); ?></td>
                    <td>R$ <?php echo number_format($ped['frete'], 2, ',', '.'); ?></td>
                    <td><span class="badge badge-<?php echo $ped['status']; ?>"><?php echo ucfirst($ped['status']); ?></span></td>
                    <td><?php echo ucfirst(str_replace('_', ' ', $ped['forma_pagamento'])); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($ped['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
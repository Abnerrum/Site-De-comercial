<?php
require_once 'config/conexao.php';
session_destroy();
setFlash('sucesso', 'Voce saiu da sua conta.');
redirecionar('index.php');
?>
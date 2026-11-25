<?php

require_once "src/conexao-bd.php";
require_once "src/auth.php";

start_auth_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit;
}

ensure_auth_table($pdo);
$totalUsuarios = (int) $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
if ($totalUsuarios === 0) {
    header("Location: setup-admin.php");
    exit;
}

$emailInput = $_POST['email'] ?? '';
$email = is_string($emailInput)
    ? filter_var(trim($emailInput), FILTER_VALIDATE_EMAIL)
    : false;
$password = $_POST['password'] ?? '';

if ($email === false || !is_string($password) || $password === '') {
    header("Location: login.html?erro=1");
    exit;
}

$statement = $pdo->prepare("SELECT id, senha_hash FROM usuarios WHERE email = ?");
$statement->execute([$email]);
$usuario = $statement->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !password_verify($password, $usuario['senha_hash'])) {
    header("Location: login.html?erro=1");
    exit;
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $usuario['id'];
$_SESSION['usuario_email'] = $email;

header("Location: admin.php");
exit;

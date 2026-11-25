<?php

require_once "src/conexao-bd.php";
require_once "src/auth.php";

start_auth_session();

ensure_auth_table($pdo);

$totalUsuarios = (int) $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
if ($totalUsuarios > 0) {
    header("Location: login.html");
    exit;
}

if (empty($_SESSION['setup_csrf'])) {
    $_SESSION['setup_csrf'] = bin2hex(random_bytes(32));
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = $_POST['email'] ?? '';
    $email = is_string($emailInput)
        ? filter_var(trim($emailInput), FILTER_VALIDATE_EMAIL)
        : false;
    $senha = $_POST['senha'] ?? '';
    $confirmacao = $_POST['confirmacao'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['setup_csrf'], $token)) {
        $erro = 'A sessão expirou. Atualize a página e tente novamente.';
    } elseif ($email === false) {
        $erro = 'Informe um e-mail válido.';
    } elseif (!is_string($senha) || strlen($senha) < 10) {
        $erro = 'A senha deve ter pelo menos 10 caracteres.';
    } elseif (!is_string($confirmacao) || !hash_equals($senha, $confirmacao)) {
        $erro = 'As senhas não conferem.';
    } else {
        $lock = $pdo->query("SELECT GET_LOCK('borcelle_admin_setup', 5)")->fetchColumn();

        if ((int) $lock !== 1) {
            $erro = 'Não foi possível iniciar a configuração. Tente novamente.';
        } else {
            $jaConfigurado = false;
            $usuarioId = 0;

            try {
                $totalUsuarios = (int) $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

                if ($totalUsuarios > 0) {
                    $jaConfigurado = true;
                } else {
                    $statement = $pdo->prepare(
                        "INSERT INTO usuarios (email, senha_hash) VALUES (?, ?)"
                    );
                    $statement->execute([
                        $email,
                        password_hash($senha, PASSWORD_DEFAULT),
                    ]);
                    $usuarioId = (int) $pdo->lastInsertId();
                }
            } finally {
                $pdo->query("SELECT RELEASE_LOCK('borcelle_admin_setup')");
            }

            if ($jaConfigurado) {
                header("Location: login.html");
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuarioId;
            $_SESSION['usuario_email'] = $email;
            unset($_SESSION['setup_csrf']);

            header("Location: admin.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/admin.css">
  <link rel="stylesheet" href="css/form.css">
  <link rel="stylesheet" href="css/login.css">
  <link rel="icon" href="img/icone-grao.png" type="image/x-icon">
  <title>Configurar administrador - Grão Digital</title>
</head>
<body>
<main>
  <section class="container-admin-banner">
    <img src="img/logo-grao.png" class="logo-admin" alt="Grão Digital">
    <h1>Primeiro acesso</h1>
    <img class="ornaments" src="img/ornaments-coffee.png" alt="">
  </section>
  <section class="container-form">
    <form method="post" action="setup-admin.php">
      <?php if ($erro !== ''): ?>
        <p class="login-feedback" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
      <p class="setup-instructions">Crie a conta que terá acesso à administração.</p>
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" autocomplete="username" required>
      <label for="senha">Senha (mínimo de 10 caracteres)</label>
      <input type="password" id="senha" name="senha" autocomplete="new-password" minlength="10" required>
      <label for="confirmacao">Confirme a senha</label>
      <input type="password" id="confirmacao" name="confirmacao" autocomplete="new-password" minlength="10" required>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['setup_csrf'], ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="botao-cadastrar">Criar administrador</button>
      <a class="login-setup-link" href="login.html">Voltar ao login</a>
    </form>
  </section>
</main>
</body>
</html>

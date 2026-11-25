<?php
require_once "src/auth.php";
require_auth();

require "src/conexao-bd.php";
require "src/Modelo/Produto.php";
require "src/Repositorio/produtoRepositorio.php";


$produtoRepositorio = new ProdutoRepositorio($pdo);
$produtos = $produtoRepositorio->buscarTodos();
?>


<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/admin.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="icon" href="img/icone-grao.png" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
  <title>Borcelle Cafeteria - Admin</title>
</head>
<body>
<main>
  <section class="container-admin-banner">
    <img src="img/logo-grao.png" class="logo-admin" alt="Grão Digital">
    <h1>Admistração Aurora</h1>
    <img class= "ornaments" src="img/ornaments-coffee.png" alt="ornaments">
  </section>
  <h2>Lista de Produtos</h2>

  <section class="container-table">
    <form action="logout.php" method="post">
      <button type="submit" class="botao-cadastrar">Sair</button>
    </form>
    <table>
      <thead>
        <tr>
          <th>Produto</th>
          <th>Tipo</th>
          <th>Descricão</th>
          <th>Valor</th>
          <th colspan="2">Ação</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($produtos as $produto): ?>
      <tr>
        <td><?php echo $produto->getNome(); ?></td>
        <td><?php echo $produto->getTipo(); ?></td>
        <td><?php echo $produto->getDescricao(); ?></td>
        <td>R$ <?php echo $produto->getPrecoFormatado(); ?></td> 
        <td><a class="botao-editar" href="editar-produto.php?id=<?= $produto->getId() ?>">Editar</a></td>
        <td>
          <form action="excluir-produto.php" method="post">
            <input type="hidden" name="id" value="<?= $produto->getId()?>"> 
            <input type="submit" class="botao-excluir" value="Excluir">
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
        </tbody>
      </tbody>
    </table>
  <a class="botao-cadastrar" href="cadastrar-produto.php">Cadastrar produto</a>
  <form action="#" method="post">
    <input type="submit" class="botao-cadastrar" value="Baixar Relatório"/>
  </form>
  </section>
</main>
</body>
</html>
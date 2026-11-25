<?php

require_once "src/auth.php";
require_auth();

require "src/conexao-bd.php";
require "src/Modelo/Produto.php";
require "src/Repositorio/produtoRepositorio.php";

$produtoRepositorio = new ProdutoRepositorio($pdo); 
$produtoRepositorio -> deletar($_POST['id']);

header("Location: admin.php");
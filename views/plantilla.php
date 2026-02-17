<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="utf-8">
<title>Sistema de Correos</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark">

  <div class="container-fluid">

    <span class="navbar-brand">
      📧 Sistema de Correos
    </span>

    <span class="text-white">
      <?= $_SESSION["usuario"] ?>
    </span>

  </div>

</nav>

<!-- CONTENIDO -->
<div class="container mt-4">

<?php

/* ===========================
   ROUTING SIMPLE
=========================== */

$pagina = $_GET["pagina"] ?? "correos";

switch($pagina){

  case "correos":
    include "views/correos.php";
  break;

  default:
    echo "<h4>Página no encontrada</h4>";
  break;

}

?>

</div>

<!-- FOOTER -->
<footer class="text-center mt-5 text-muted">

<hr>

</footer>

</body>
</html>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="utf-8">
<title>Sistema de Correos</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    overflow-x: hidden;
}
.sidebar {
    height: 100vh;
    background: #1f2d3d;
    color: white;
}
.sidebar a {
    color: #cfd8dc;
    text-decoration: none;
    display: block;
    padding: 12px 20px;
}
.sidebar a:hover,
.sidebar a.active {
    background: #34495e;
    color: #fff;
}
.content-area {
    padding: 25px;
}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

    <!-- SIDEBAR -->
    <div class="col-md-2 sidebar p-0">

        <div class="p-3 border-bottom">
            <h5 class="text-white">Sistema SC</h5>
            <small><?= $_SESSION["usuario"] ?? "Usuario" ?></small>
        </div>

        <?php $pagina = $_GET["pagina"] ?? "correos"; ?>

        <a href="?pagina=correos" 
           class="<?= $pagina == 'correos' ? 'active' : '' ?>">
            📥 Correos
        </a>

        <a href="?pagina=cotizaciones" 
           class="<?= $pagina == 'cotizaciones' ? 'active' : '' ?>">
            📑 Cotizaciones
        </a>

        <a href="?pagina=reportes" 
           class="<?= $pagina == 'reportes' ? 'active' : '' ?>">
            📊 Reportes
        </a>

        <a href="?pagina=configuracion" 
           class="<?= $pagina == 'configuracion' ? 'active' : '' ?>">
            ⚙ Configuración
        </a>

    </div>

    <!-- CONTENIDO -->
    <div class="col-md-10 content-area bg-light">

        <?php

        switch($pagina){

            case "correos":
                include "views/correos.php";
            break;

            case "cotizaciones":
                include "views/cotizaciones.php";
            break;

            case "cotizaciones_detalle":
                include "views/cotizacion_detalle.php";
            break;

            case "reportes":
                echo "<h4>Reportes próximamente...</h4>";
            break;

            case "configuracion":
                echo "<h4>Configuración próximamente...</h4>";
            break;

            case "generar_pdf":
    require_once "views/generar_pdf.php";
    break;


            default:
                echo "<h4>Página no encontrada</h4>";
            break;
        }

        ?>

    </div>

</div>
</div>

</body>
</html>

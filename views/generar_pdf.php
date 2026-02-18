<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../models/Conexion.php";
require_once __DIR__ . "/../models/CotizacionesModelo.php";
require_once __DIR__ . "/../controllers/CotizacionesControlador.php";


use Dompdf\Dompdf;
use Dompdf\Options;

if(!isset($_GET["id"])){
    die("Cotización no encontrada");
}

$id = (int)$_GET["id"];

$cotizacion = CotizacionesControlador::ctrObtenerCotizacion($id);
$items = CotizacionesControlador::ctrObtenerItems($id);

if(!$cotizacion){
    die("Cotización no encontrada");
}

/* ================================
   CONFIGURAR DOMPDF
================================ */

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

/* ================================
   HTML DEL PDF
================================ */

ob_start();
?>

<style>
body {
    font-family: Arial, sans-serif;
    font-size: 12px;
}
h2 {
    text-align: center;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table, th, td {
    border: 1px solid #000;
}
th, td {
    padding: 6px;
}
.total {
    text-align: right;
    font-size: 14px;
    font-weight: bold;
}
.header {
    margin-bottom: 20px;
}
</style>

<div class="header">
    <h2>COTIZACIÓN</h2>

    <strong>Folio:</strong> <?= $cotizacion["folio"] ?><br>
    <strong>Cliente:</strong> <?= $cotizacion["cliente_email"] ?><br>
    <strong>Asunto:</strong> <?= $cotizacion["asunto"] ?><br>
    <strong>Fecha:</strong> <?= date("d/m/Y", strtotime($cotizacion["fecha_creacion"])) ?><br>
</div>

<table>
    <thead>
        <tr>
            <th>Descripción</th>
            <th width="60">Cant.</th>
            <th width="80">P. Unit</th>
            <th width="80">Subtotal</th>
        </tr>
    </thead>
    <tbody>

    <?php 
    $total = 0;
    foreach($items as $item): 
        $total += $item["subtotal"];
    ?>
        <tr>
            <td><?= $item["descripcion"] ?></td>
            <td align="center"><?= $item["cantidad"] ?></td>
            <td align="right">$ <?= number_format($item["precio_unitario"],2) ?></td>
            <td align="right">$ <?= number_format($item["subtotal"],2) ?></td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

<br>

<div class="total">
    Total: $ <?= number_format($total,2) ?>
</div>

<?php

$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* ================================
   DESCARGAR PDF
================================ */

$dompdf->stream("Cotizacion_".$cotizacion["folio"].".pdf", [
    "Attachment" => false // true = descarga, false = abrir en navegador
]);

exit;

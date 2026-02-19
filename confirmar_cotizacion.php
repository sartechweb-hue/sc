<?php
require_once "controllers/CotizacionesControlador.php";
require_once "models/CotizacionesModelo.php";
require_once "models/conexion.php";

$folio = $_GET["folio"] ?? null;
$accion = $_GET["accion"] ?? null;

if(!$folio || !$accion){
    die("Solicitud inválida.");
}

$accion = strtolower($accion);

if($accion === "aceptar"){
    $nuevoEstado = "aceptada";
}elseif($accion === "rechazar"){
    $nuevoEstado = "rechazada";
}else{
    die("Acción inválida.");
}

$resultado = CotizacionesControlador::ctrActualizarDesdeRespuesta([
    "folio" => $folio,
    "estado" => $nuevoEstado
]);

if(isset($resultado["ok"])){

    echo "
    <h2>Gracias</h2>
    <p>Su respuesta fue registrada correctamente.</p>
    ";

}else{
    echo "Error: ".$resultado["error"];
}

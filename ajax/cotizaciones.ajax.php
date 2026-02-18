<?php

// 🔴 ELIMINA CUALQUIER SALIDA PREVIA
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

require_once "../models/Conexion.php";
require_once "../models/CotizacionesModelo.php";
require_once "../controllers/CotizacionesControlador.php";

define("API_TOKEN", "SC_TOKEN_2026_SECRETO");

/* ===========================
   VALIDAR MÉTODO
=========================== */

if($_SERVER["REQUEST_METHOD"] !== "POST"){
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

/* ===========================
   VALIDAR TOKEN
=========================== */

$token = $_SERVER['HTTP_X_API_TOKEN'] ?? '';

if($token !== API_TOKEN){
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

/* ===========================
   LEER INPUT
=========================== */

$raw = file_get_contents("php://input");
$input = json_decode($raw, true);

if(json_last_error() !== JSON_ERROR_NONE){
    http_response_code(400);
    echo json_encode(["error" => "JSON inválido"]);
    exit;
}

/* ===========================
   PROCESAR ACCIÓN
=========================== */

if(isset($input["accion"]) && $input["accion"] === "crear_desde_correo"){

    $respuesta = CotizacionesControlador::ctrCrearDesdeCorreo($input);

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(["error" => "Acción inválida"]);
exit;

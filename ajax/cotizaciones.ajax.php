<?php

if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

require_once "../models/Conexion.php";
require_once "../models/CotizacionesModelo.php";
require_once "../controllers/CotizacionesControlador.php";

define("API_TOKEN", "SC_TOKEN_2026_SECRETO");

/* =========================================================
   1️ SI VIENE JSON (API EXTERNA - n8n)
========================================================= */

$raw = file_get_contents("php://input");
$input = json_decode($raw, true);

if($_SERVER["REQUEST_METHOD"] === "POST" && $input){

    $token = $_SERVER['HTTP_X_API_TOKEN'] ?? '';

    if($token !== API_TOKEN){
        echo json_encode(["error" => "No autorizado"]);
        exit;
    }

    if(isset($input["accion"]) && $input["accion"] === "crear_desde_correo"){
        $respuesta = CotizacionesControlador::ctrCrearDesdeCorreo($input);
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if(isset($input["accion"]) && $input["accion"] === "enviar_cotizacion"){
        $respuesta = CotizacionesControlador::ctrEnviarCotizacion($input["id"]);
        echo json_encode($respuesta);
        exit;
    }

    echo json_encode(["error" => "Acción inválida"]);
    exit;
}

/* =========================================================
   2️ PANEL INTERNO
========================================================= */

if($_SERVER["REQUEST_METHOD"] === "POST"){

    // 🔹 Guardar items
    if(isset($_POST["cotizacion_id"])){

        $respuesta = CotizacionesControlador::ctrGuardarItems($_POST);
        echo json_encode($respuesta);
        exit;
    }

    // 🔹 Enviar cotización
    if(isset($_POST["accion"]) && $_POST["accion"] === "enviar"){

        $id = (int)$_POST["id"];

        $respuesta = CotizacionesControlador::ctrEnviarCotizacion($id);
        echo json_encode($respuesta);
        exit;
    }
}

echo json_encode(["error" => "Solicitud inválida"]);
exit;



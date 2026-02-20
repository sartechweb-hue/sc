<?php
session_start();

require_once "../models/CorreosModelo.php";
require_once "../controllers/CorreosControlador.php";

header('Content-Type: application/json; charset=utf-8');

$respuesta = CorreosControlador::ctrEnviarCorreo();

echo json_encode($respuesta);
<?php

session_start();

require_once "../models/CorreosModelo.php";
require_once "../controllers/CorreosControlador.php";

$respuesta = CorreosControlador::ctrEnviarCorreo();

echo json_encode($respuesta);

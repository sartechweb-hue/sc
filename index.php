<?php

session_start();

/* ===========================
   CONFIGURACIÓN GENERAL
=========================== */

define("BASE_PATH", dirname(__FILE__));
define("BASE_URL", "http://localhost/correo_system/");

/* ===========================
   AUTOLOGIN DEMO (QUÍTALO EN PROD)
=========================== */

if(!isset($_SESSION["id_usuario"])){

  $_SESSION["id_usuario"] = 1;
  $_SESSION["usuario"]   = "admin";
  $_SESSION["perfil"]    = "Administrador";

}

/* ===========================
   INCLUDES
=========================== */

require_once "models/CorreosModelo.php";
require_once "controllers/CorreosControlador.php";

/* ===========================
   CARGAR PLANTILLA
=========================== */

include "views/plantilla.php";

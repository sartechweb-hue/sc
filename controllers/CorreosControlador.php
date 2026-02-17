<?php

class CorreosControlador {

/* ===========================
   Enviar correo
=========================== */
static public function ctrEnviarCorreo(){

  if(!isset($_POST["to"])) return;

  /* ===========================
     VALIDACIONES
  =========================== */

  $from    = trim($_POST["from"] ?? '');
  $to      = trim($_POST["to"] ?? '');
  $asunto  = trim($_POST["asunto"] ?? '');
  $mensaje = trim($_POST["mensaje"] ?? '');

  // Validar correos
  if(
    !filter_var($to, FILTER_VALIDATE_EMAIL) ||
    !filter_var($from, FILTER_VALIDATE_EMAIL)
  ){
    return ["error"=>"Correo inválido"];
  }

  // Validar campos
  if(empty($asunto) || empty($mensaje)){
    return ["error"=>"Campos incompletos"];
  }

  // Validar sesión
  if(!isset($_SESSION["id_usuario"])){
    return ["error"=>"Sesión inválida"];
  }


  /* ===========================
     PREPARAR DATOS
  =========================== */

  $datos = [

    "usuario" => (int) $_SESSION["id_usuario"],
    "from"    => $from,
    "to"      => $to,
    "asunto"  => htmlspecialchars($asunto),
    "mensaje" => htmlspecialchars($mensaje)

  ];


  /* ===========================
     GUARDAR BD
  =========================== */

  try{

    $resp = CorreosModelo::mdlGuardarCorreo($datos);

    if($resp != "ok"){
      return ["error"=>"Error al guardar"];
    }

  }catch(Exception $e){

    return ["error"=>"DB: ".$e->getMessage()];

  }


  /* ===========================
     ENVIAR N8N
  =========================== */

  $respuesta = self::enviarAN8N($datos);

  if(!$respuesta){
    return ["error"=>"No responde n8n"];
  }


  return ["ok"=>"Correo enviado"];

}



/* ===========================
   Historial
=========================== */
static public function ctrHistorial(){

  return CorreosModelo::mdlHistorial();

}



/* ===========================
   Obtener remitentes
=========================== */
static public function ctrObtenerRemitentes(){

  return CorreosModelo::mdlObtenerRemitentes();

}



/* ===========================
   Obtener contactos
=========================== */
static public function ctrObtenerContactos(){

  return CorreosModelo::mdlObtenerContactos();

}



/* ===========================
   Webhook n8n
=========================== */
static private function enviarAN8N($data){

  $url = "http://localhost:5678/webhook/enviar-correo";

  $ch = curl_init($url);

  curl_setopt_array($ch,[

    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($data),
    CURLOPT_TIMEOUT        => 15

  ]);

  $response = curl_exec($ch);

  if(curl_errno($ch)){
    curl_close($ch);
    return false;
  }

  curl_close($ch);

  return json_decode($response,true);

}


}

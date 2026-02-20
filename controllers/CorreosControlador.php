<?php

class CorreosControlador {

  /* ===========================
     Enviar correo
  =========================== */
  static public function ctrEnviarCorreo(){

    if(!isset($_POST["to"])) {
      return ["error" => "Solicitud inválida"];
    }

    // Validar sesión
    if(!isset($_SESSION["id_usuario"])){
      return ["error" => "Sesión inválida"];
    }

    $from    = trim($_POST["from"] ?? '');
    $to      = trim($_POST["to"] ?? '');
    $asunto  = trim($_POST["asunto"] ?? '');
    $mensaje = trim($_POST["mensaje"] ?? '');

    // Validar correos
    if(!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($from, FILTER_VALIDATE_EMAIL)){
      return ["error" => "Correo inválido"];
    }

    // Validar campos
    if($asunto === '' || $mensaje === ''){
      return ["error" => "Campos incompletos"];
    }

    /* ==================================
       OBTENER smtp_key EN BACKEND (SEGURO)
    ================================== */
    $smtp_key = CorreosModelo::mdlSmtpKeyPorEmail($from);

    if(!$smtp_key){
      return ["error" => "Remitente inválido"];
    }

    /* ===========================
       Preparar datos
    =========================== */
    $datos = [
      "usuario"  => (int)$_SESSION["id_usuario"],
      "from"     => $from,
      "to"       => $to,
      "asunto"   => $asunto,
      "mensaje"  => $mensaje,
      "smtp_key" => $smtp_key
    ];

    /* ===========================
       Guardar en BD como pendiente
    =========================== */
    $idCorreo = CorreosModelo::mdlGuardarCorreo($datos);

    if(!$idCorreo){
      return ["error" => "Error al guardar"];
    }

    /* ===========================
       Enviar a n8n
    =========================== */
    $respN8N = self::enviarAN8N($datos);

    if(!$respN8N){
      CorreosModelo::mdlActualizarEstadoPorId($idCorreo, "error");
      return ["error" => "No responde n8n"];
    }

    // Si n8n responde y (opcional) trae ok
    CorreosModelo::mdlActualizarEstadoPorId($idCorreo, "enviado");

    return ["ok" => "Correo enviado"];
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
      CURLOPT_POSTFIELDS     => json_encode($data, JSON_UNESCAPED_UNICODE),
      CURLOPT_TIMEOUT        => 15
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if(curl_errno($ch) || $httpCode !== 200){
      curl_close($ch);
      return false;
    }

    curl_close($ch);

    $decoded = json_decode($response, true);

    // Si n8n no devuelve JSON válido igual lo consideramos respuesta OK
    return $decoded ?? ["raw" => $response];
  }
}
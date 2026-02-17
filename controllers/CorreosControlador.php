<?php

class CorreosControlador {

  /* ===========================
     Enviar correo
  =========================== */
  static public function ctrEnviarCorreo(){

    if(!isset($_POST["to"])) return;

    /* ==== VALIDACIONES ==== */

    if(
      !filter_var($_POST["to"], FILTER_VALIDATE_EMAIL) ||
      empty($_POST["from"]) ||
      empty($_POST["asunto"]) ||
      empty($_POST["mensaje"])
    ){
      return ["error"=>"Datos inválidos"];
    }

    $datos = [

      "usuario" => $_SESSION["id_usuario"],
      "from"    => $_POST["from"],
      "to"      => $_POST["to"],
      "asunto"  => $_POST["asunto"],
      "mensaje" => $_POST["mensaje"]

    ];

    /* ==== GUARDAR ==== */

    $resp = CorreosModelo::mdlGuardarCorreo($datos);

    if($resp!="ok") return ["error"=>"DB"];

    /* ==== ENVIAR A N8N ==== */

    self::enviarAN8N($datos);

    return ["ok"=>"enviado"];
  }



  /* ===========================
     Webhook n8n
  =========================== */
  static private function enviarAN8N($data){

    $url = "http://localhost:5678/webhook/enviar-correo";

    $ch = curl_init($url);

    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type: application/json']);
    curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));

    curl_exec($ch);
    curl_close($ch);

  }


  /* ===========================
     Historial
  =========================== */
  static public function ctrHistorial(){

    return CorreosModelo::mdlHistorial();

  }

}

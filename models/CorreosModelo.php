<?php

require_once "conexion.php";

class CorreosModelo {

  /* ===========================
     Guardar correo
  =========================== */
  static public function mdlGuardarCorreo($datos){

    $stmt = Conexion::conectar()->prepare("
      INSERT INTO correos_enviados
      (usuario_id,remitente,destinatario,asunto,mensaje)
      VALUES
      (:usuario,:from,:to,:asunto,:mensaje)
    ");

    $stmt->bindParam(":usuario",$datos["usuario"],PDO::PARAM_INT);
    $stmt->bindParam(":from",$datos["from"],PDO::PARAM_STR);
    $stmt->bindParam(":to",$datos["to"],PDO::PARAM_STR);
    $stmt->bindParam(":asunto",$datos["asunto"],PDO::PARAM_STR);
    $stmt->bindParam(":mensaje",$datos["mensaje"],PDO::PARAM_STR);

    return $stmt->execute() ? "ok" : "error";
  }


  /* ===========================
     Historial
  =========================== */
  static public function mdlHistorial(){

    $stmt = Conexion::conectar()->prepare("
      SELECT * FROM correos_enviados
      ORDER BY fecha DESC
    ");

    $stmt->execute();

    return $stmt->fetchAll();
  }

}

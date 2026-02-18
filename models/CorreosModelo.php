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

  /* ===========================
   Obtener destinatarios
=========================== */
static public function mdlObtenerContactos(){

  $stmt = Conexion::conectar()->prepare("
    SELECT id,nombre,email
    FROM contactos_email
    WHERE activo = 1
    ORDER BY nombre
  ");

  $stmt->execute();

  return $stmt->fetchAll();
}

/* ===========================
   Obtener remitentes
=========================== */
static public function mdlObtenerRemitentes(){

  $stmt = Conexion::conectar()->prepare("
    SELECT id,nombre,email,smtp_key
    FROM correos_remitentes
    WHERE activo = 1
    ORDER BY nombre
  ");

  $stmt->execute();

  return $stmt->fetchAll();
}

/* ===========================
   Actualizar estado
=========================== */
static public function mdlActualizarEstado($destino,$estado){

  $stmt = Conexion::conectar()->prepare("
    UPDATE correos_enviados
    SET estado = :estado
    WHERE destinatario = :to
    ORDER BY id DESC
    LIMIT 1
  ");

  $stmt->bindParam(":estado",$estado,PDO::PARAM_STR);
  $stmt->bindParam(":to",$destino,PDO::PARAM_STR);

  return $stmt->execute();
}




}

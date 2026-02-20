<?php

require_once "conexion.php";

class CorreosModelo {

  /* ===========================
     Guardar correo (pendiente) y devolver ID
  =========================== */
  static public function mdlGuardarCorreo($datos){

    $pdo = Conexion::conectar();

    $stmt = $pdo->prepare("
      INSERT INTO correos_enviados
      (usuario_id, remitente, destinatario, asunto, mensaje, estado)
      VALUES
      (:usuario, :from, :to, :asunto, :mensaje, 'pendiente')
    ");

    $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_INT);
    $stmt->bindParam(":from",    $datos["from"],    PDO::PARAM_STR);
    $stmt->bindParam(":to",      $datos["to"],      PDO::PARAM_STR);
    $stmt->bindParam(":asunto",  $datos["asunto"],  PDO::PARAM_STR);
    $stmt->bindParam(":mensaje", $datos["mensaje"], PDO::PARAM_STR);

    if($stmt->execute()){
      return (int)$pdo->lastInsertId();
    }

    return false;
  }

  /* ===========================
     Historial
  =========================== */
  static public function mdlHistorial(){

    $stmt = Conexion::conectar()->prepare("
      SELECT *
      FROM correos_enviados
      ORDER BY fecha DESC
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /* ===========================
     Obtener contactos
  =========================== */
  static public function mdlObtenerContactos(){

    $stmt = Conexion::conectar()->prepare("
      SELECT id, nombre, email
      FROM contactos_email
      WHERE activo = 1
      ORDER BY nombre
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /* ===========================
     Obtener remitentes
  =========================== */
  static public function mdlObtenerRemitentes(){

    $stmt = Conexion::conectar()->prepare("
      SELECT id, nombre, email, smtp_key
      FROM correos_remitentes
      WHERE activo = 1
      ORDER BY nombre
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /* ===========================
     Obtener smtp_key por email (seguro)
  =========================== */
  static public function mdlSmtpKeyPorEmail($email){

    $stmt = Conexion::conectar()->prepare("
      SELECT smtp_key
      FROM correos_remitentes
      WHERE email = :email AND activo = 1
      LIMIT 1
    ");

    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row["smtp_key"] ?? null;
  }

  /* ===========================
     Actualizar estado por ID (mejor que por destinatario)
  =========================== */
  static public function mdlActualizarEstadoPorId($idCorreo, $estado){

    $stmt = Conexion::conectar()->prepare("
      UPDATE correos_enviados
      SET estado = :estado
      WHERE id = :id
      LIMIT 1
    ");

    $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
    $stmt->bindParam(":id", $idCorreo, PDO::PARAM_INT);

    return $stmt->execute();
  }
}
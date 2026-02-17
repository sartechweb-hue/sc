<?php

class Conexion {

  /* ===========================
     CONEXIÓN PDO
  =========================== */
  static public function conectar(){

    try {

      /* ===== CONFIGURACIÓN ===== */

      $host = "localhost";
      $db   = "sc_central";   // ← Tu base real
      $user = "root";
      $pass = "";

      /* ========================= */

      $link = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => false
        ]
      );

      return $link;

    } catch (PDOException $e) {

      die("❌ Error de conexión: " . $e->getMessage());

    }

  }

}

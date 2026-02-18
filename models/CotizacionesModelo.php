<?php

class CotizacionesModelo {

static public function mdlCrear($datos){

    try {

        $db = Conexion::conectar(); // 👈 AQUÍ ESTÁ LA CLAVE

        $stmt = $db->prepare("
            INSERT INTO cotizaciones (
                folio,
                cliente_email,
                asunto,
                mensaje,
                estado,
                fecha_creacion,
                fecha_actualizacion
            ) VALUES (
                :folio,
                :cliente_email,
                :asunto,
                :mensaje,
                'solicitada',
                NOW(),
                NOW()
            )
        ");

        $stmt->bindParam(":folio", $datos["folio"], PDO::PARAM_STR);
        $stmt->bindParam(":cliente_email", $datos["cliente_email"], PDO::PARAM_STR);
        $stmt->bindParam(":asunto", $datos["asunto"], PDO::PARAM_STR);
        $stmt->bindParam(":mensaje", $datos["mensaje"], PDO::PARAM_STR);

        if($stmt->execute()){
            return $db->lastInsertId(); 
        }

        return false;

    } catch(Exception $e){
        return false;
    }
}



  /* ===========================
   LISTAR COTIZACIONES
=========================== */
static public function mdlListar($tabla){

    $stmt = Conexion::conectar()->prepare("
        SELECT *
        FROM $tabla
        ORDER BY id DESC
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}
// Verificar si ya existe una cotización con el mismo message_id para evitar duplicados
static public function mdlExisteMessageId($message_id){

    $stmt = Conexion::conectar()->prepare("
        SELECT id 
        FROM cotizaciones 
        WHERE message_id = :message_id
        LIMIT 1
    ");

    $stmt->bindParam(":message_id", $message_id, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetch() ? true : false;
}

// Agregar un item a una cotización
static public function mdlAgregarItem($datos){

    $stmt = Conexion::conectar()->prepare("
        INSERT INTO cotizaciones_detalle (
            cotizacion_id,
            descripcion,
            cantidad,
            precio_unitario,
            subtotal,
            texto_detectado
        ) VALUES (
            :cotizacion_id,
            :descripcion,
            :cantidad,
            :precio_unitario,
            :subtotal,
            :texto_detectado
        )
    ");

    $stmt->bindParam(":cotizacion_id", $datos["cotizacion_id"]);
    $stmt->bindParam(":descripcion", $datos["descripcion"]);
    $stmt->bindParam(":cantidad", $datos["cantidad"]);
    $stmt->bindParam(":precio_unitario", $datos["precio_unitario"]);
    $stmt->bindParam(":subtotal", $datos["subtotal"]);
    $stmt->bindParam(":texto_detectado", $datos["texto_detectado"]);

    return $stmt->execute();
}



// Obtener cotización por ID
static public function mdlObtenerCotizacion($id){

    $stmt = Conexion::conectar()->prepare("
        SELECT * 
        FROM cotizaciones 
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Obtener items de una cotización
static public function mdlObtenerItems($cotizacion_id){

    $stmt = Conexion::conectar()->prepare("
        SELECT *
        FROM cotizaciones_detalle   
        WHERE cotizacion_id = :cotizacion_id
    ");

    $stmt->bindParam(":cotizacion_id", $cotizacion_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Obtener catálogo de servicios
static public function mdlObtenerCatalogo(){

    $stmt = Conexion::conectar()->prepare("
        SELECT * FROM catalogo_servicios
        WHERE activo = 1
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




}

<?php

class CotizacionesControlador {

static public function ctrCrearDesdeCorreo($data){

    /* ===========================
       VALIDACIONES OBLIGATORIAS
    =========================== */

    if(
        empty($data["from"]) ||
        empty($data["subject"]) ||
        empty($data["body"]) ||
        empty($data["message_id"])
    ){
        return [
            "error" => "Datos incompletos"
        ];
    }

    $message_id = trim($data["message_id"]);

    /* ===========================
       VALIDAR DUPLICADO
    =========================== */

    if(CotizacionesModelo::mdlExisteMessageId($message_id)){
        return [
            "error" => "Correo ya procesado"
        ];
    }

    /* ===========================
       GENERAR FOLIO ÚNICO
    =========================== */

    $folio = "COT-" . date("YmdHis") . "-" . random_int(100,999);

    /* ===========================
       PREPARAR DATOS
    =========================== */

    $datos = [
        "folio"         => $folio,
        "cliente_email" => trim($data["from"]),
        "asunto"        => trim($data["subject"]),
        "mensaje"       => trim($data["body"]),
        "message_id"    => $message_id,
        "smtp_key"      => $data["smtp_key"] ?? "smtp_default"
    ];

    /* ===========================
       GUARDAR EN BD
    =========================== */

    $cotizacion_id = CotizacionesModelo::mdlCrear($datos);

    if(!$cotizacion_id){
        return [
            "error" => "No se pudo guardar"
        ];
    }

    /* ===========================
       GENERAR ITEMS AUTOMÁTICOS
    =========================== */

    try{
        self::ctrGenerarItemsDesdeTexto($cotizacion_id, $data["body"]);
    }catch(Exception $e){
        // No rompemos el flujo si falla la detección
    }

    /* ===========================
       RESPUESTA FINAL
    =========================== */

    return [
        "ok" => true,
        "folio" => $folio,
        "cotizacion_id" => $cotizacion_id,
        "smtp_key" => $datos["smtp_key"]
    ];
}


  /* ===========================
   LISTAR COTIZACIONES
=========================== */
static public function ctrListar(){

    $tabla = "cotizaciones";

    return CotizacionesModelo::mdlListar($tabla);

}

// Generar items desde texto del correo
static public function ctrGenerarItemsDesdeTexto($cotizacion_id, $mensaje){

    $catalogo = CotizacionesModelo::mdlObtenerCatalogo();

    // 🔥 Normalizar mensaje
    $mensaje = strip_tags($mensaje);
    $mensaje = html_entity_decode($mensaje);
    $mensaje = str_replace(["\r"], "\n", $mensaje);
    $mensaje_normalizado = strtolower($mensaje);

    $serviciosDetectados = [];
    $lineas = preg_split('/\n+/', $mensaje_normalizado);

    foreach($lineas as $linea){

        $linea = trim($linea);

        if(strlen($linea) < 3){
            continue;
        }

        $encontrado = false;

     foreach($catalogo as $servicio){

    $palabra = self::normalizarTexto($servicio["palabra_clave"]);
    $linea_normalizada = self::normalizarTexto($linea);

    if(str_contains($linea_normalizada, $palabra)){

        // Evitar duplicados
        if(in_array($servicio["id"], $serviciosDetectados)){
            continue;
        }

        $precio = (float)$servicio["precio_base"];

        CotizacionesModelo::mdlAgregarItem([
            "cotizacion_id" => (int)$cotizacion_id,
            "descripcion" => $servicio["descripcion"],
            "cantidad" => 1,
            "precio_unitario" => $precio,
            "subtotal" => $precio,
            "texto_detectado" => $linea // texto real original
        ]);

        $serviciosDetectados[] = $servicio["id"];
        $encontrado = true;
        break;
    }
}

        //  Si no se encontró en catálogo
        if(!$encontrado){

            CotizacionesModelo::mdlAgregarItem([
                "cotizacion_id" => $cotizacion_id,
                "descripcion" => "Servicio no identificado (revisar)",
                "cantidad" => 1,
                "precio_unitario" => 0,
                "subtotal" => 0,
                "texto_detectado" => $linea
            ]);
        }
    }
}



static public function ctrObtenerCotizacion($id){
    return CotizacionesModelo::mdlObtenerCotizacion($id);
}

static public function ctrObtenerItems($id){
    return CotizacionesModelo::mdlObtenerItems($id);
}


// Función auxiliar para normalizar texto (opcional, no se usa actualmente)
private static function normalizarTexto($texto){

    $texto = strtolower($texto);
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
    $texto = preg_replace('/[^a-z0-9\s]/', ' ', $texto);
    $texto = preg_replace('/\s+/', ' ', $texto);

    return trim($texto);
}

// Catálogo de servicios (puede ser dinámico o estático)
private static function obtenerCatalogo(){

    return [

        [
            "palabras" => ["pagina web", "sitio web", "web corporativa"],
            "descripcion" => "Desarrollo de Página Web Corporativa",
            "precio" => 6000
        ],

        [
            "palabras" => ["correo corporativo", "correos corporativos", "correo empresarial", "correos empresariales"],
            "descripcion" => "Configuración de Correos Corporativos",
            "precio" => 250
        ],

        [
            "palabras" => ["crm", "sistema web", "plataforma web"],
            "descripcion" => "Desarrollo de Sistema Web / CRM",
            "precio" => 15000
        ],

        [
            "palabras" => ["mantenimiento", "soporte mensual"],
            "descripcion" => "Servicio de Mantenimiento Mensual",
            "precio" => 1200
        ]

    ];
}


static public function ctrGuardarItems($data){

    $cotizacion_id = (int)$data["cotizacion_id"];

    if(!$cotizacion_id){
        return ["error" => true];
    }

    // 🔒 Validar estado
    $cotizacion = CotizacionesModelo::mdlObtenerCotizacion($cotizacion_id);

    if(!$cotizacion || in_array($cotizacion["estado"], ["enviada","cerrada"])){
        return ["error" => "Cotización bloqueada"];
    }

    $idsExistentes = CotizacionesModelo::mdlObtenerIdsItems($cotizacion_id);

    $idsFormulario = $data["item_id"];
    $descripciones = $data["descripcion"];
    $cantidades = $data["cantidad"];
    $precios = $data["precio"];

    $idsProcesados = [];

    for($i=0; $i<count($descripciones); $i++){

        $idItem = (int)$idsFormulario[$i];
        $descripcion = trim($descripciones[$i]);
        $cantidad = (float)$cantidades[$i];
        $precio = (float)$precios[$i];
        $subtotal = $cantidad * $precio;

        // Si ya existe → UPDATE
        if($idItem > 0){

            CotizacionesModelo::mdlActualizarItem([
                "id" => $idItem,
                "descripcion" => $descripcion,
                "cantidad" => $cantidad,
                "precio_unitario" => $precio,
                "subtotal" => $subtotal
            ]);

            $idsProcesados[] = $idItem;

        }else{

            // Nuevo item → INSERT
            CotizacionesModelo::mdlAgregarItem([
                "cotizacion_id" => $cotizacion_id,
                "descripcion" => $descripcion,
                "cantidad" => $cantidad,
                "precio_unitario" => $precio,
                "subtotal" => $subtotal,
                "texto_detectado" => null
            ]);
        }
    }

    // 🔥 Eliminar los que ya no están en el formulario
    foreach($idsExistentes as $idBD){

        if(!in_array($idBD, $idsProcesados)){
            CotizacionesModelo::mdlEliminarItemPorId($idBD);
        }
    }

// Cambiar estado a enviada al guardar
CotizacionesModelo::mdlCambiarEstado($cotizacion_id, "enviada");

return ["ok" => true];
}


/* ===============================
   ENVIAR COTIZACIÓN POR CORREO
================================ */

static public function ctrEnviarCotizacion($id){

    $id = (int)$id;

    if(!$id){
        return ["error" => "ID inválido"];
    }

    $cotizacion = CotizacionesModelo::mdlObtenerCotizacion($id);

    if(!$cotizacion){
        return ["error" => "Cotización no encontrada"];
    }

    if(in_array($cotizacion["estado"], ["enviada","cerrada"])){
        return ["error" => "Ya fue enviada o cerrada"];
    }

    $resultado = CotizacionesModelo::mdlCambiarEstado($id, "enviada");

    if(isset($resultado["ok"])){
        return ["ok" => true];
    }

    return ["error" => "No se pudo actualizar estado"];
}



static public function ctrCerrarCotizacion($id){

    return CotizacionesModelo::mdlCambiarEstado($id, "cerrada");
}



}

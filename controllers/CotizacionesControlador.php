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

    /* ===========================
       VALIDAR DUPLICADO
    =========================== */

    $existe = CotizacionesModelo::mdlExisteMessageId(trim($data["message_id"]));

    if($existe){
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
        "message_id"    => trim($data["message_id"]),
        "smtp_key"      => $data["smtp_key"] ?? "smtp_default"
    ];

    /* ===========================
       GUARDAR EN BD
    =========================== */

$cotizacion_id = CotizacionesModelo::mdlCrear($datos);

if($cotizacion_id){

    //  Generar items automáticos
    self::ctrGenerarItemsDesdeTexto($cotizacion_id, $data["body"]);

    return [
        "ok" => true,
        "folio" => $folio,
        "smtp_key" => $datos["smtp_key"]
    ];
}

    return [
        "error" => "No se pudo guardar"
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

    $itemsDetectados = [];

    $mensaje = strtolower($mensaje);

    if(str_contains($mensaje, "pagina web")){
        $itemsDetectados[] = [
            "descripcion" => "Desarrollo de página web corporativa",
            "cantidad" => 1,
            "precio_unitario" => 6000
        ];
    }

    if(str_contains($mensaje, "correo")){
        $itemsDetectados[] = [
            "descripcion" => "Configuración de correos corporativos",
            "cantidad" => 5,
            "precio_unitario" => 250
        ];
    }

    foreach($itemsDetectados as $item){

        $subtotal = $item["cantidad"] * $item["precio_unitario"];

        CotizacionesModelo::mdlAgregarItem([
            "cotizacion_id" => $cotizacion_id,
            "descripcion" => $item["descripcion"],
            "cantidad" => $item["cantidad"],
            "precio_unitario" => $item["precio_unitario"],
            "subtotal" => $subtotal
        ]);
    }

}


static public function ctrObtenerCotizacion($id){
    return CotizacionesModelo::mdlObtenerCotizacion($id);
}

static public function ctrObtenerItems($id){
    return CotizacionesModelo::mdlObtenerItems($id);
}


}

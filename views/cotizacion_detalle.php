<?php

if(!isset($_GET["id"])){
    echo "<h4>Cotización no encontrada</h4>";
    return;
}

$id = (int) $_GET["id"];

$cotizacion = CotizacionesControlador::ctrObtenerCotizacion($id);

if(!$cotizacion){
    echo "<h4>Cotización no encontrada</h4>";
    return;
}

$items = CotizacionesControlador::ctrObtenerItems($id);
$estado = $cotizacion["estado"];
$pdfGenerado = !empty($cotizacion["pdf_path"]);

$editable = ($estado === "solicitada");
$mostrarGenerarPDF = ($estado === "guardada" && !$pdfGenerado);

/* Mostrar PDF siempre que exista */
$mostrarVerPDF = $pdfGenerado;

/* Enviar solo si está guardada y tiene PDF */
$mostrarEnviar = ($estado === "guardada" && $pdfGenerado);


$finalizada = in_array($estado, ["enviada","cerrada"]);



?>

<div class="container mt-4">

<h4>Detalle Cotización</h4>

<?php if($cotizacion["estado"] === "enviada"): ?>
<div class="alert alert-info">
    Cotización enviada — Documento bloqueado
</div>
<?php endif; ?>

<?php if($cotizacion["estado"] === "cerrada"): ?>
<div class="alert alert-success">
    Cotización cerrada — Proceso finalizado
</div>
<?php endif; ?>



<div class="card mb-3">
<div class="card-body">

<strong>Folio:</strong> <?= $cotizacion["folio"] ?><br>
<strong>Cliente:</strong> <?= $cotizacion["cliente_email"] ?><br>
<strong>Asunto:</strong> <?= $cotizacion["asunto"] ?><br>
<strong>Estado:</strong> <?= $cotizacion["estado"] ?><br>
<strong>Fecha:</strong> <?= $cotizacion["fecha_creacion"] ?><br>

</div>
</div>

<form id="formItems">

<input type="hidden" name="cotizacion_id" value="<?= $id ?>">

<table class="table table-bordered" id="tablaItems">

<thead class="table-dark">
<tr>
<th>Descripción</th>
<th width="100">Cantidad</th>
<th width="150">Precio Unit.</th>
<th width="150">Subtotal</th>
<?php if($editable): ?>
<th width="50"></th>
<?php endif; ?>
</tr>
</thead>

<tbody>

<?php foreach($items as $item): ?>

<tr>

<input type="hidden" name="item_id[]" value="<?= $item["id"] ?>">

<td>

    <input type="text"
           name="descripcion[]"
           class="form-control"
           value="<?= $item["descripcion"] ?>"
         <?= !$editable ? 'readonly' : '' ?>>

    <?php if(!empty($item["texto_detectado"])): ?>
        <small class="text-muted">
            Texto original: <?= $item["texto_detectado"] ?>
        </small>
    <?php endif; ?>
</td>

<td>
<input type="number"
       name="cantidad[]"
       class="form-control cantidad"
       value="<?= $item["cantidad"] ?>"
       min="1"
       <?= !$editable ? 'readonly' : '' ?>
>
</td>

<td>
<input type="number"
       step="0.01"
       name="precio[]"
       class="form-control precio"
       value="<?= $item["precio_unitario"] ?>"
       <?= !$editable ? 'readonly' : '' ?>>
</td>

<td class="subtotal text-end">
<?= number_format($item["subtotal"],2) ?>
</td>

<?php if($editable): ?>
<td>
<button type="button" class="btn btn-danger btn-sm eliminarFila">X</button>
</td>
<?php endif; ?>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php if($editable): ?>
<button type="button" class="btn btn-secondary mb-3" id="agregarItem">
Agregar Item
</button>
<?php endif; ?>

<h5 class="text-end">
Total: $ <span id="totalGeneral">0.00</span>
</h5>

<hr>

<?php if($editable): ?>
<button type="button" class="btn btn-success" id="guardarItems">
Guardar
</button>
<?php endif; ?>


<?php if($mostrarGenerarPDF): ?>
<a href="views/generar_pdf.php?id=<?= $id ?>" class="btn btn-primary">
Generar PDF
</a>
<?php endif; ?>

<?php if($mostrarVerPDF): ?>
<a href="<?= $cotizacion["pdf_path"] ?>"
   target="_blank"
   class="btn btn-outline-primary">
Ver PDF generado
</a>
<?php endif; ?>


<?php if($mostrarEnviar): ?>
<button type="button"
class="btn btn-dark"
id="enviarCotizacion"
data-id="<?= $id ?>">
Enviar Cotización
</button>
<?php endif; ?>


</form>

</div>

<script>

let editable = <?= $editable ? 'true' : 'false' ?>;

/* ============================
   Calcular totales
============================ */

function recalcular(){

    let total = 0;

    document.querySelectorAll("#tablaItems tbody tr").forEach(fila=>{

        let cantidad = parseFloat(fila.querySelector(".cantidad")?.value) || 0;
        let precio = parseFloat(fila.querySelector(".precio")?.value) || 0;

        let subtotal = cantidad * precio;

        fila.querySelector(".subtotal").innerText =
            subtotal.toFixed(2);

        total += subtotal;

    });

    document.getElementById("totalGeneral").innerText =
        total.toFixed(2);
}

if(editable){

document.addEventListener("input",function(e){

    if(e.target.classList.contains("cantidad") ||
       e.target.classList.contains("precio")){
        recalcular();
    }

});

/* ============================
   Agregar fila
============================ */

document.getElementById("agregarItem")?.addEventListener("click",()=>{

 let fila = `
<tr>
<input type="hidden" name="item_id[]" value="">
<td><input type="text" name="descripcion[]" class="form-control"></td>
<td><input type="number" name="cantidad[]" class="form-control cantidad" value="1"></td>
<td><input type="number" step="0.01" name="precio[]" class="form-control precio" value="0"></td>
<td class="subtotal text-end">0.00</td>
<td><button type="button" class="btn btn-danger btn-sm eliminarFila">X</button></td>
</tr>
`;

    document.querySelector("#tablaItems tbody")
    .insertAdjacentHTML("beforeend", fila);

});

/* ============================
   Eliminar fila
============================ */

document.addEventListener("click",function(e){

    if(e.target.classList.contains("eliminarFila")){
        e.target.closest("tr").remove();
        recalcular();
    }

});

/* ============================
   Guardar items
============================ */

document.getElementById("guardarItems")?.addEventListener("click",()=>{

    fetch("ajax/cotizaciones.ajax.php",{
        method:"POST",
        body:new FormData(document.getElementById("formItems"))
    })
    .then(r=>r.json())
    .then(r=>{
        if(r.ok){
            alert("Guardado correctamente");
            location.reload();
        }else{
            alert("Error al guardar");
        }
    });

});

}

recalcular();

</script>


<script>
document.getElementById("enviarCotizacion")?.addEventListener("click",function(){

    let id = this.dataset.id;

    let formData = new FormData();
    formData.append("accion", "enviar");
    formData.append("id", id);

    fetch("ajax/cotizaciones.ajax.php",{
        method:"POST",
        body: formData
    })
    .then(r=>r.json())
    .then(r=>{
        if(r.ok){
            alert("Cotización enviada correctamente");
            location.reload();
        }else{
            alert(r.error || "Error al enviar");
        }
    });

});

</script>

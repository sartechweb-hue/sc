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
$bloqueada = in_array($cotizacion["estado"], ["enviada","cerrada"]);

?>

<div class="container mt-4">

<h4>Detalle Cotización</h4>

<?php if($bloqueada): ?>
<div class="alert alert-success">
    Cotización <?= ucfirst($cotizacion["estado"]) ?> — Documento bloqueado
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
<?php if(!$bloqueada): ?>
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
           <?= $bloqueada ? 'readonly' : '' ?>>

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
       <?= $bloqueada ? 'readonly' : '' ?>>
</td>

<td>
<input type="number"
       step="0.01"
       name="precio[]"
       class="form-control precio"
       value="<?= $item["precio_unitario"] ?>"
       <?= $bloqueada ? 'readonly' : '' ?>>
</td>

<td class="subtotal text-end">
<?= number_format($item["subtotal"],2) ?>
</td>

<?php if(!$bloqueada): ?>
<td>
<button type="button" class="btn btn-danger btn-sm eliminarFila">X</button>
</td>
<?php endif; ?>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php if(!$bloqueada): ?>
<button type="button" class="btn btn-secondary mb-3" id="agregarItem">
Agregar Item
</button>
<?php endif; ?>

<h5 class="text-end">
Total: $ <span id="totalGeneral">0.00</span>
</h5>

<hr>

<?php if(!$bloqueada): ?>
<button type="button" class="btn btn-success" id="guardarItems">
Guardar
</button>
<?php endif; ?>

<a href="views/generar_pdf.php?id=<?= $id ?>" class="btn btn-primary">
Generar PDF
</a>


<?php if(!$bloqueada): ?>
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

let bloqueada = <?= $bloqueada ? 'true' : 'false' ?>;

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

if(!bloqueada){

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

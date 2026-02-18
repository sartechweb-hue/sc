<?php

if(!isset($_GET["id"])){
    echo "<h4>Cotización no encontrada</h4>";
    return;
}

$id = (int) $_GET["id"];

$cotizacion = CotizacionesControlador::ctrObtenerCotizacion($id);
$items = CotizacionesControlador::ctrObtenerItems($id);

if(!$cotizacion){
    echo "<h4>Cotización no encontrada</h4>";
    return;
}

?>

<div class="container mt-4">

<h4>Detalle Cotización</h4>

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
<th width="50"></th>
</tr>
</thead>

<tbody>

<?php foreach($items as $item): ?>

<tr>
<td>
<input type="text" name="descripcion[]" class="form-control"
value="<?= $item["descripcion"] ?>">
</td>

<td>
<input type="number" name="cantidad[]" class="form-control cantidad"
value="<?= $item["cantidad"] ?>" min="1">
</td>

<td>
<input type="number" step="0.01" name="precio[]" class="form-control precio"
value="<?= $item["precio_unitario"] ?>">
</td>

<td class="subtotal text-end">
<?= number_format($item["subtotal"],2) ?>
</td>

<td>
<button type="button" class="btn btn-danger btn-sm eliminarFila">X</button>
</td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

<button type="button" class="btn btn-secondary mb-3" id="agregarItem">
Agregar Item
</button>

<h5 class="text-end">
Total: $ <span id="totalGeneral">0.00</span>
</h5>

<hr>

<button type="button" class="btn btn-success" id="guardarItems">
Guardar
</button>

<a href="index.php?pagina=generar_pdf&id=<?= $id ?>"
class="btn btn-primary">
Generar PDF
</a>

<button type="button"
class="btn btn-dark"
id="enviarCotizacion"
data-id="<?= $id ?>">
Enviar Cotización
</button>

</form>

</div>



<script>

/* ============================
   Calcular totales
============================ */

function recalcular(){

    let total = 0;

    document.querySelectorAll("#tablaItems tbody tr").forEach(fila=>{

        let cantidad = parseFloat(fila.querySelector(".cantidad").value) || 0;
        let precio = parseFloat(fila.querySelector(".precio").value) || 0;

        let subtotal = cantidad * precio;

        fila.querySelector(".subtotal").innerText =
            subtotal.toFixed(2);

        total += subtotal;

    });

    document.getElementById("totalGeneral").innerText =
        total.toFixed(2);
}

document.addEventListener("input",function(e){

    if(e.target.classList.contains("cantidad") ||
       e.target.classList.contains("precio")){
        recalcular();
    }

});


/* ============================
   Agregar fila
============================ */

document.getElementById("agregarItem")
.addEventListener("click",()=>{

    let fila = `
    <tr>
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

document.getElementById("guardarItems")
.addEventListener("click",()=>{

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


/* ============================
   Enviar Cotización
============================ */

document.getElementById("enviarCotizacion")
.addEventListener("click",function(){

    let id = this.dataset.id;

    fetch("ajax/cotizaciones.ajax.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body:JSON.stringify({
            accion:"enviar_cotizacion",
            id:id
        })
    })
    .then(r=>r.json())
    .then(r=>{
        if(r.ok){
            alert("Cotización enviada");
            location.reload();
        }else{
            alert("Error al enviar");
        }
    });

});


recalcular();

</script>

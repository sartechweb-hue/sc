<?php
$historial = CorreosControlador::ctrHistorial();
?>

<div class="container mt-4">

<h4>Enviar Correo</h4>

<form id="formCorreo">

<!-- REMITENTE -->
<div class="mb-2">
<label>Remitente</label>
<select class="form-control" name="from" required>
  <option value="">Seleccione</option>
  <option value="ventas@empresa.com">Ventas</option>
  <option value="info@empresa.com">Info</option>
  <option value="soporte@empresa.com">Soporte</option>
</select>
</div>

<!-- DESTINO -->
<div class="mb-2">
<label>Destinatario</label>
<input type="email" name="to" class="form-control" required>
</div>

<!-- MOTIVO -->
<div class="mb-2">
<label>Motivo</label>
<input type="text" name="asunto" class="form-control" required>
</div>

<!-- MENSAJE -->
<div class="mb-2">
<label>Mensaje</label>
<textarea name="mensaje" class="form-control" rows="5" required></textarea>
</div>

<button class="btn btn-primary">Enviar</button>

</form>

<hr>

<h5>Historial</h5>

<table class="table table-sm">

<tr>
<th>Fecha</th>
<th>De</th>
<th>Para</th>
<th>Asunto</th>
<th>Estado</th>
</tr>

<?php foreach($historial as $h): ?>

<tr>
<td><?= $h["fecha"] ?></td>
<td><?= $h["remitente"] ?></td>
<td><?= $h["destinatario"] ?></td>
<td><?= $h["asunto"] ?></td>
<td><?= $h["estado"] ?></td>
</tr>

<?php endforeach; ?>

</table>

</div>


<script>
document.getElementById("formCorreo").addEventListener("submit",e=>{

  e.preventDefault();

  fetch("ajax/correos.ajax.php",{
    method:"POST",
    body:new FormData(e.target)
  })
  .then(r=>r.json())
  .then(r=>{

    if(r.ok){
      alert("Correo enviado");
      location.reload();
    }else{
      alert("Error");
    }

  });

});
</script>

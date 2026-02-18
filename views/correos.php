<?php
$historial = CorreosControlador::ctrHistorial();
?>

<div class="container mt-4">

<h4>Enviar Correo</h4>

<form id="formCorreo">

<!-- REMITENTE -->
<div class="mb-2">
<label>Remitente</label>

<select name="from" class="form-control" required>

<option value="">Seleccione</option>

<?php
$remitentes = CorreosControlador::ctrObtenerRemitentes();

foreach($remitentes as $r):
?>

<option 
  value="<?= $r["email"] ?>" 
  data-smtp="<?= $r["smtp_key"] ?>"
>
  <?= $r["nombre"] ?> (<?= $r["email"] ?>)
</option>


<?php endforeach; ?>

</select>
<input type="hidden" name="smtp_key" id="smtp_key">

</div>


<!-- DESTINATARIO -->
<div class="mb-2">
<label>Destinatario</label>

<select name="to" class="form-control" required>

<option value="">Seleccione</option>

<?php
$contactos = CorreosControlador::ctrObtenerContactos();

foreach($contactos as $c):

?>

<option value="<?= $c["email"] ?>">
  <?= $c["nombre"] ?> (<?= $c["email"] ?>)
</option>

<?php endforeach; ?>

</select>
</div>
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
document.querySelector('[name="from"]').addEventListener('change', function(){

  const selected = this.options[this.selectedIndex];
  const smtp = selected.getAttribute('data-smtp');

  document.getElementById('smtp_key').value = smtp;

});
</script>



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

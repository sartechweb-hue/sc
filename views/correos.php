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
          <option value="<?= htmlspecialchars($r["email"]) ?>">
            <?= htmlspecialchars($r["nombre"]) ?> (<?= htmlspecialchars($r["email"]) ?>)
          </option>
        <?php endforeach; ?>
      </select>
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
          <option value="<?= htmlspecialchars($c["email"]) ?>">
            <?= htmlspecialchars($c["nombre"]) ?> (<?= htmlspecialchars($c["email"]) ?>)
          </option>
        <?php endforeach; ?>
      </select>
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
        <td><?= htmlspecialchars($h["fecha"]) ?></td>
        <td><?= htmlspecialchars($h["remitente"]) ?></td>
        <td><?= htmlspecialchars($h["destinatario"]) ?></td>
        <td><?= htmlspecialchars($h["asunto"]) ?></td>
        <td><?= htmlspecialchars($h["estado"]) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

</div>

<script>
document.getElementById("formCorreo").addEventListener("submit", function(e){
  e.preventDefault();

  fetch("ajax/correos.ajax.php",{
    method:"POST",
    body:new FormData(this)
  })
  .then(r=>r.json())
  .then(r=>{
    if(r.ok){
      alert(r.ok);
      location.reload();
    }else{
      alert("Error: " + (r.error || "Desconocido"));
    }
  })
  .catch(()=>{
    alert("Error de red");
  });
});
</script>
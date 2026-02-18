<div class="container mt-4">

  <h3>Solicitudes de Cotización</h3>

  <table class="table table-bordered table-striped">

    <thead>
      <tr>
        <th>Folio</th>
        <th>Cliente</th>
        <th>Asunto</th>
        <th>Estado</th>
        <th>Fecha</th>
        <th>Acciones</th>
      </tr>
    </thead>

    <tbody>

      <?php

        $cotizaciones = CotizacionesControlador::ctrListar();

        foreach($cotizaciones as $c):

      ?>

        <tr>
          <td><?= $c["folio"] ?></td>
          <td><?= $c["cliente_email"] ?></td>
          <td><?= $c["asunto"] ?></td>
          <td>
            <span class="badge bg-warning">
              <?= $c["estado"] ?>
            </span>
          </td>
          <td><?= $c["fecha_creacion"] ?></td>
          <td>
<a href="?pagina=cotizaciones_detalle&id=<?= $c["id"] ?>" 
   class="btn btn-sm btn-outline-primary">
    Ver
</a>


          </td>
        </tr>

      <?php endforeach; ?>

    </tbody>

  </table>

</div>

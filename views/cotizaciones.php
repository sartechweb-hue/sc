<div class="container mt-4">

  <h3>Solicitudes de Cotización</h3>

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
          <?php
          $color = "secondary";

          switch($c["estado"]){
              case "solicitada": $color="secondary"; break;
              case "guardada": $color="warning"; break;
              case "enviada": $color="info"; break;
              case "aceptada": $color="success"; break;
              case "rechazada": $color="danger"; break;
              case "facturada": $color="primary"; break;
              case "pagada": $color="dark"; break;
              case "entregada": $color="success"; break;
              case "cerrada": $color="success"; break;
          }
          ?>
          <span class="badge bg-<?= $color ?>">
            <?= ucfirst(str_replace("_"," ",$c["estado"])) ?>
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


</div>

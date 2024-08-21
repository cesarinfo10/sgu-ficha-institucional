<h4>Tasa de egreso total por duración de carrera, por sede, nivel de formación, jornada, modalidad, área del conocimiento y sexo</h4>
<!--<button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalBenPregrado">Agregar +</button>-->
<br />
<table class="table table-bordered">
  <thead>
    <tr>
      <th class="tituloTabla">Nombre del programa, proyecto o iniciativa</th>
      <th class="tituloTabla">Año de inicio</th>
      <th class="tituloTabla">Línea, tipo o área de desarrollo</th>
      <th class="tituloTabla">Resultados o contribución</th>
      <th class="tituloTabla">Descripción de grupos de interés</th>
      <th class="tituloTabla">N° de participantes</th>
    </tr>

  </thead>
  <tbody>
    <?php
    for ($i = 1; $i <= 30; $i++) {
      echo '<tr>';
      echo '<td>' . $i . '.</td>';
      echo '<td></td>';
      echo '<td></td>';
      echo '<td></td>';
      echo '<td></td>';
      echo '<td></td>';
      echo '</tr>';
    }
    ?>
  </tbody>
</table>

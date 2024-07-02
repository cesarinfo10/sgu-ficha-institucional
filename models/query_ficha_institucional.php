<?php
include('conexion.php');
//echo 'Hola';
/*=============================================
LLAMAR A TODOS LOS PAISES
=============================================*/
if (isset($_GET['getCarrera'])){
    $dbconn = db_connect();
  
    $query = "SELECT cn.nombre,COUNT(c.id) from carreras c
                left join carreras_niveles cn on c.id_nivel= cn.id
                where c.admision and c.activa and c.regimen in ('PRE', 'PRE-D')
                GROUP BY cn.nombre";

    $result = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());
     
    $queryAC = "SELECT area_conocimiento,COUNT(id) from carreras
                where admision and activa and regimen in ('PRE', 'PRE-D')
                GROUP BY area_conocimiento";

    $resultAC = pg_query($dbconn, $queryAC) or die('La consulta fallo: ' . pg_last_error());
    //return $result;
      
    echo '<table class="table table-bordered">
    <thead>
      <tr>
        <th></th>
        <th class="tituloTabla">Proceso anterior [1]</th>
        <th class="tituloTabla">Proceso actual (año actual)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-left tituloTabla">N° de sedes</td>
        <td></td>
        <td></td>
      </tr>
       <tr>
      <td></td>
          <td class="text-center tituloTabla">N° de carreras de pregrado</td>
          <td></td>
        </tr>';
    while ($row = pg_fetch_row($result)) {
      echo '<tr>
            <td>'.$row[0].'</td>
            <td>'.$row[1].'</td>
            <td>'.$row[2].'</td>
            </tr>';
    }
// Áreas del conocimiento [3]
echo ' <tr>
    <td></td>
        <td class="text-center tituloTabla">Áreas del conocimiento</td>
        <td></td>
      </tr>';
    while ($rowAC = pg_fetch_row($resultAC)) {
      echo '<tr>
            <td>'.$rowAC[0].'</td>
            <td>'.$rowAC[1].'</td>
            <td>'.$rowAC[2].'</td>
            </tr>';
    }
    echo '</table>';
}


<?php
include ('conexion.php');
/*==========================================================================================
                                  DOTACION DE PERSONAL
==========================================================================================*/

/*=============================================
LLAMAR A Dotación de personal, por sede
=============================================*/
if (isset($_GET['getSede'])) {
  $dbconn = db_connect();

  $query = "SELECT * FROM public.sede_ficha";

  $result = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());


  echo '<label for="sede">Sede:</label>
    <select class="shadow-lg p-1 bg-white form-control" id="tipoBeneficio" name="tipoBeneficio" style="visibility: visible;">
      <option value="0" selected>Seleccione Sede</option>';
  while ($row = pg_fetch_row($result)) {
    echo '<option value="' . $row[0] . '">' . $row[1] . '</option>';
  }
  echo '  </select>';
}

/*=============================================
INSERTAR - UPDATE DOTACION DE PERSONAL
=============================================*/
if (isset($_GET['postDtPersonal'])) {

  $dbconn = db_connect();
  $idSede = $_POST['idSede'];
  $sedeAno = $_POST['sedeAno'];
  $porc_mujeres = $_POST['porc_mujeres'];
  $total = $_POST['total'];
  $cftSede = $_POST['cftSede'];
  $ipSede = $_POST['ipSede'];
  $uniSede = $_POST['uniSede'];

  $query = "SELECT idSede FROM public.informacion_sedes WHERE idSede = '" . $idSede . "' AND sedeAno= '" . $sedeAno . "'";
  $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
  $rows = pg_num_rows($result);

  if ($rows == 0) {

    $sql = "INSERT INTO public.informacion_sedes (idSede, sedeAno, porc_mujeres, total, cftSede, ipSede, uniSede)
              VALUES ('" . $idSede . "', '" . $sedeAno . "', '" . $porc_mujeres . "', '" . $total . "', '" . $cftSede . "', 
              '" . $ipSede . "', '" . $uniSede . "')";

    // Ejecutamos la sentencia preparada
    $result = pg_query($dbconn, $sql);

    if ($result) {

      echo 1;
      while ($row = pg_fetch_row($result)) {
        echo $row[0];
      }

    } else {
      echo 2;
    }
    //echo $result ;
    pg_close($dbconn);
  } else {

    $sql = "UPDATE public.informacion_sedes SET idSede = '" . $idSede . "', sedeAno = '" . $sedeAno . "', porc_mujeres = '" . $porc_mujeres . "',
           total = '" . $total . "', cftSede = '" . $cftSede . "', ipSede = '" . $ipSede . "', uniSede = '" . $uniSede . "'
          WHERE idSede = '" . $idSede . "' AND sedeAno= '" . $sedeAno . "'";


    // Ejecutamos la sentencia preparada
    $result = pg_query($dbconn, $sql);

    if ($result) {

      echo 3;
    } else {
      echo 2;
    }
  }
}

/*=============================================
LLAMAR A TODAS LAS DOTACION DE PERSONAL
=============================================*/
if (isset($_GET['getDPersonal'])) {
  $dbconn = db_connect();

  $query = "SELECT informacion_sedes.idsede, sede_ficha.nomsede, sede_ficha.estado, informacion_sedes.cftSede, 
      informacion_sedes.ipSede, informacion_sedes.uniSede
            FROM informacion_sedes
            INNER JOIN sede_ficha
            ON informacion_sedes.idsede = sede_ficha.id
            GROUP BY informacion_sedes.idsede, sede_ficha.nomsede, sede_ficha.estado,
            informacion_sedes.cftSede, informacion_sedes.ipSede, informacion_sedes.uniSede
            ORDER BY informacion_sedes.idsede";

  $result = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());
  $result2 = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());
  $rowIdsede = pg_fetch_row($result);


  
  $queryIS = "SELECT *FROM public.informacion_sedes WHERE idSede = '" . $rowIdsede[0] . "' ORDER BY sedeAno";
  $resultIS = pg_query($dbconn, $queryIS) or die('La consulta fallo: ' . pg_last_error());
  $resultIS2 = pg_query($dbconn, $queryIS) or die('La consulta fallo: ' . pg_last_error());
  $resultIS3 = pg_query($dbconn, $queryIS) or die('La consulta fallo: ' . pg_last_error());
  //return $result;
    
  echo '<table class="table table-bordered">
  <thead>
    <tr>
      <th></th>';
      while ($rowIS = pg_fetch_row($resultIS)) {
      echo '<th class="tituloTabla" colspan="2">'.$rowIS[2].'</th>';
      }
     echo' <th class="tituloTabla" colspan="3">Instituciones del conglomerado</th>
    </tr>
    <tr>
    <tr>
      <th class="tituloTabla">Sede</th>';
      while ($rowIS = pg_fetch_row($resultIS2)) {
      echo '<th class="tituloTabla">Total</th>
            <th class="tituloTabla">Mujer (%)</th>';
      }
      echo'<th class="tituloTabla">CFT</th>
      <th class="tituloTabla">IP</th>
      <th class="tituloTabla">Universidad</th>
    </tr>
  </thead>
      </tr>
        <tbody>';
  while ($row = pg_fetch_row($result2)) {
   // var_dump($row);
    echo '<tr>
      <td>'.$row[1].'</td>';
      while ($rowIS = pg_fetch_row($resultIS3)) {
      echo '<td>'.$rowIS[4].'</td>
            <td>'.$rowIS[3].' %</td>';
      }
      echo '<td>'.(($row[3] == 't') ? 'Sí' : 'No').'</td>
            <td>'.(($row[4] == 't') ? 'Sí' : 'No').'</td>
            <td>'.(($row[5] == 't') ? 'Sí' : 'No').'</td>
      </tr>';
  }

  echo ' </tbody>
  </table>';
}
/*==========================================================================================
                                  FIN DOTACION DE PERSONAL
==========================================================================================*/

/*==========================================================================================
                                  EDIFICIO PROPIO
==========================================================================================*/

/*=============================================
INSERTAR - UPDATE EDIFICIO PROPIO
=============================================*/
if (isset($_GET['postEPropio'])) {

  $dbconn = db_connect();
  
  $idSede = $_POST['idSede'];
  $direccion = $_POST['direccion'];
  $mtEp = $_POST['mtEp'];
  $anoadquisision = $_POST['anoAdquisision'];

  $cftSede = $_POST['cftSede'];
  $ipSede = $_POST['ipSede'];
  $uniSede = $_POST['uniSede'];

  $query = "SELECT idSede FROM public.edi_propio_sedes WHERE idSede = '" . $idSede . "'";
  $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
  $rows = pg_num_rows($result);

  if ($rows == 0) {

    $sql = "INSERT INTO public.edi_propio_sedes (idSede, direccion, mtEp, anoadquisision, cftSede, ipSede, uniSede)
              VALUES ('" . $idSede . "', '" . $direccion . "', '" . $mtEp . "', '" . $anoadquisision . "', '" . $cftSede . "', 
              '" . $ipSede . "', '" . $uniSede . "')";

    // Ejecutamos la sentencia preparada
    $result = pg_query($dbconn, $sql);

    if ($result) {

      echo 1;
      while ($row = pg_fetch_row($result)) {
        echo $row[0];
      }

    } else {
      echo 2;
    }
    //echo $result ;
    pg_close($dbconn);
  } else {

    $sql = "UPDATE public.edi_propio_sedes SET idSede = '" . $idSede . "', direccion = '" . $direccion . "', mtEp = '" . $mtEp . "',
           anoadquisision = '" . $anoadquisision . "', cftSede = '" . $cftSede . "', ipSede = '" . $ipSede . "', uniSede = '" . $uniSede . "'
           WHERE idSede = '" . $idSede . "'";


    // Ejecutamos la sentencia preparada
    $result = pg_query($dbconn, $sql);

    if ($result) {

      echo 3;
    } else {
      echo 2;
    }
  }
}
/*=============================================
LLAMAR A TODOS LOS EDIFICIO PROPIO
=============================================*/
if (isset($_GET['getEPropio'])) {
  $dbconn = db_connect();

  $query = "SELECT idSede, (SELECT nomSede FROM sede_ficha WHERE id= idSede) AS nomSede, direccion, 
            mtEp, anoadquisision, cftSede, ipSede, uniSede FROM edi_propio_sedes";
  $result = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());

  //return $result;    
  echo '<table class="table table-bordered">
  <thead>
    <tr>
      <th colspan="4"></th>
      <th class="tituloTabla" colspan="3">Instituciones del conglomerado</th>
    </tr>
    <tr>
    <tr>
      <th class="tituloTabla">Sede</th>
      <th class="tituloTabla">Dirección</th>
      <th class="tituloTabla">Metros cuadrados totales</th>
      <th class="tituloTabla">Año adquisición</th>
      <th class="tituloTabla">CFT</th>
      <th class="tituloTabla">IP</th>
      <th class="tituloTabla">Universidad</th>
    </tr>
  </thead>
  <tbody>';
  while ($row = pg_fetch_row($result)) {
    echo '<tr>
      <td>'.$row[1].'</td>
      <td>'.$row[2].'</td>
      <td>'.$row[3].'</td>
      <td>'.$row[4].'</td>
      <td>'.(($row[5] == 't') ? 'Sí' : 'No').'</td>
      <td>'.(($row[6] == 't') ? 'Sí' : 'No').'</td>
      <td>'.(($row[7] == 't') ? 'Sí' : 'No').'</td>
    </tr>';
  }
  echo '</tbody>
</table>';
}
/*==========================================================================================
                                  FIN EDIFICIO PROPIO
==========================================================================================*/

/*==========================================================================================
                                  EDIFICIO ARRENDADO
==========================================================================================*/

/*=============================================
INSERTAR - UPDATE EDIFICIO ARRENDADO
=============================================*/
if (isset($_GET['postEArrendado'])) {

  $dbconn = db_connect();
  
  $idSede = $_POST['idSede'];
  $propEA = $_POST['propEA'];
  $fecIniEA = $_POST['fecIniEA'];
  $plazoEA = $_POST['plazoEA'];
  $arriendoEA = $_POST['arriendoEA'];
  $metrosCuaEA = $_POST['metrosCuaEA'];

  $arriendoCFT = $_POST['cftSede'];
  $arriendoIP = $_POST['ipSede'];
  $arriendoUni = $_POST['uniSede'];

  $query = "SELECT idSede FROM public.edi_arendado_sedes WHERE idSede = '" . $idSede . "'";
  $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
  $rows = pg_num_rows($result);

  if ($rows == 0) {

    $sql = "INSERT INTO public.edi_arendado_sedes (idSede, propEA, fecIniEA, plazoEA, arriendoEA, metrosCuaEA, arriendoCFT, arriendoIP, arriendoUni)
              VALUES ('" . $idSede . "', '" . $propEA . "', '" . $fecIniEA . "', '" . $plazoEA . "', '" . $arriendoEA . "',
               '" . $metrosCuaEA . "', '" . $arriendoCFT . "', '" . $arriendoIP . "', '" . $arriendoUni . "')";

    // Ejecutamos la sentencia preparada
    $result = pg_query($dbconn, $sql);

    if ($result) {

      echo 1;
      while ($row = pg_fetch_row($result)) {
        echo $row[0];
      }

    } else {
      echo 2;
    }
    //echo $result ;
    pg_close($dbconn);
  } else {

    $sql = "UPDATE public.edi_arendado_sedes SET idSede = '" . $idSede . "', propEA = '" . $propEA . "', fecIniEA = '" . $fecIniEA . "',
           plazoEA = '" . $plazoEA . "', arriendoEA = '" . $arriendoEA . "', metrosCuaEA = '" . $metrosCuaEA . "', 
           cftSede = '" . $arriendoCFT . "', arriendoIP = '" . $arriendoIP . "', arriendoUni = '" . $arriendoUni . "'
           WHERE idSede = '" . $idSede . "'";


    // Ejecutamos la sentencia preparada
    $result = pg_query($dbconn, $sql);

    if ($result) {

      echo 3;
    } else {
      echo 2;
    }
  }
}
/*=============================================
LLAMAR A TODOS LOS EDIFICIO ARRENDADO
=============================================*/
if (isset($_GET['getEArendado'])) {
  $dbconn = db_connect();

  $query = "SELECT idSede, (SELECT nomSede FROM sede_ficha WHERE id= idSede) AS nomSede, propEA, 
            fecIniEA, plazoEA, arriendoEA, metrosCuaEA, arriendoCFT, arriendoIP, arriendoUni FROM edi_arendado_sedes";
  $result = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());

  //return $result;    
  echo '<table class="table table-bordered">
  <thead>
    <tr>
      <th colspan="6"></th>
      <th class="tituloTabla" colspan="3">Metros cuadrados conglomerado</th>
    </tr>
    <tr>
    <tr>
      <th class="tituloTabla">Sede</th>
      <th class="tituloTabla">Propietario</th>
      <th class="tituloTabla">Fecha inicio contrato</th>
      <th class="tituloTabla">Plazo contrato </th>
      <th class="tituloTabla">Arriendo (CLP$ o UF)</th>
      <th class="tituloTabla">Metros cuadrados totales</th>
      <th class="tituloTabla">CFT</th>
      <th class="tituloTabla">IP</th>
      <th class="tituloTabla">Universidad</th>
    </tr>
  </thead>
  <tbody>';
  while ($row = pg_fetch_row($result)) {
  echo '<tr>
      <td>'.$row[1].'</td>
      <td>'.$row[2].'</td>
      <td>'.$row[3].'</td>
      <td>'.$row[4].'</td>
      <td>'.$row[5].'</td>
      <td>'.$row[6].'</td>
      <td>'.(($row[5] == 't') ? 'Sí' : 'No').'</td>
      <td>'.(($row[6] == 't') ? 'Sí' : 'No').'</td>
      <td>'.(($row[7] == 't') ? 'Sí' : 'No').'</td>
    </tr>';
  }
  echo '</tbody>
</table>';
}
/*==========================================================================================
                                  FIN EDIFICIO PROPIO
==========================================================================================*/
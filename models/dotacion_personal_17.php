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

    echo '<div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="tituloTabla">Sede</th>
                        <th class="tituloTabla">Total</th>
                        <th class="tituloTabla">Mujer (%)</th>
                        <th class="tituloTabla">CFT</th>
                        <th class="tituloTabla">IP</th>
                        <th class="tituloTabla">Universidad</th>
                    </tr>
                </thead>
                <tbody>';

    while ($row = pg_fetch_row($result)) {
        echo '<tr>
                <td>' . $row[1] . '</td>';
        
        $queryIS = "SELECT * FROM public.informacion_sedes WHERE idSede = '" . $row[0] . "' ORDER BY sedeAno";
        $resultIS = pg_query($dbconn, $queryIS) or die('La consulta fallo: ' . pg_last_error());
        $rowIS = pg_fetch_row($resultIS);
        echo '<td>' . ($rowIS[4] ?? 0) . '</td>
              <td>' . ($rowIS[3] ?? 0) . ' %</td>';
        
        echo '<td>' . (($row[3] == 't') ? 'Sí' : 'No') . '</td>
              <td>' . (($row[4] == 't') ? 'Sí' : 'No') . '</td>
              <td>' . (($row[5] == 't') ? 'Sí' : 'No') . '</td>
              </tr>';
    }

    echo '</tbody>
          </table>
          </div>';
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
  $metrosCuaEA = $_POST['metrosCuaEA'];
  $arriendoEA = $_POST['arriendoEA'];

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
           arriendoCFT = '" . $arriendoCFT . "', arriendoIP = '" . $arriendoIP . "', arriendoUni = '" . $arriendoUni . "'
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

/*==========================================================================================
                                  EDIFICIO COMODATO
==========================================================================================*/

/*=============================================
INSERTAR - UPDATE EDIFICIO COMODATO
=============================================*/
if (isset($_GET['postEComodato'])) {

  $dbconn = db_connect();
  
  $idSede = $_POST['idSede'];
  $propEC = $_POST['propEC'];
  $fecIniEC = $_POST['fecIniEC'];
  $plazoEC = $_POST['plazoEC'];
  $metrosCuaEC = $_POST['metrosCuaEC'];

  $comodatoCFT = $_POST['comodatoCFT'];
  $comodatoCIP = $_POST['comodatoCIP'];
  $comodatoUni = $_POST['comodatoUni'];

  $query = "SELECT idSede FROM public.edi_comodato_sedes WHERE idSede = '" . $idSede . "'";
  $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
  $rows = pg_num_rows($result);

  if ($rows == 0) {

    $sql = "INSERT INTO public.edi_comodato_sedes (idSede, propEC, fecIniEC, plazoEC, metrosCuaEC, comodatoCFT, comodatoCIP, comodatoUni)
              VALUES ('" . $idSede . "', '" . $propEC . "', '" . $fecIniEC . "', '" . $plazoEC . "',
               '" . $metrosCuaEC . "', '" . $comodatoCFT . "', '" . $comodatoCIP . "', '" . $comodatoUni . "')";

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

    $sql = "UPDATE public.edi_comodato_sedes SET idSede = '" . $idSede . "', propEC = '" . $propEC . "', fecIniEC = '" . $fecIniEC . "',
           plazoEC = '" . $plazoEC . "',  metrosCuaEC = '" . $metrosCuaEC . "', 
           comodatoCFT = '" . $comodatoCFT . "', comodatoCIP = '" . $comodatoCIP . "', comodatoUni = '" . $comodatoUni . "'
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
LLAMAR A TODOS LOS EDIFICIO COMODATO
=============================================*/
if (isset($_GET['getEComodato'])) {
  $dbconn = db_connect();

  $query = "SELECT idSede, (SELECT nomSede FROM sede_ficha WHERE id= idSede) AS nomSede, propEC, 
            fecIniEC, plazoEC, metrosCuaEC, comodatoCFT, comodatoCIP, comodatoUni FROM edi_comodato_sedes";
  $result = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());

  //return $result;    
  echo '<table class="table table-bordered">
  <thead>
    <tr>
      <th colspan="5"></th>
      <th class="tituloTabla" colspan="3">Metros cuadrados conglomerado</th>
    </tr>
    <tr>
    <tr>
      <th class="tituloTabla">Sede</th>
      <th class="tituloTabla">Propietario</th>
      <th class="tituloTabla">Fecha inicio comodato</th>
      <th class="tituloTabla">Plazo</th>
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
      <td>'.(($row[6] == 't') ? 'Sí' : 'No').'</td>
      <td>'.(($row[7] == 't') ? 'Sí' : 'No').'</td>
      <td>'.(($row[8] == 't') ? 'Sí' : 'No').'</td>
    </tr>';
  }
  echo '</tbody>
</table>';
}
/*==========================================================================================
                                  FIN EDIFICIO COMODATO
==========================================================================================*/

/*==========================================================================================
  Evolución de infraestructura total y de otras instituciones del conglomerado por sede.
==========================================================================================*/

/*=============================================
INSERTAR - UPDATE Evolución infraestructura
=============================================*/
if (isset($_GET['postEvoInfra'])) {

  $dbconn = db_connect();
  
  $descripcion = $_POST['descripcion'];
  $ano = $_POST['ano'];
  $metrosCuaEC = $_POST['metrosCuaEC'];


  $query = "SELECT id FROM public.evolucion_conglomerado_sede WHERE descripcion = '" . $descripcion . "' AND ano= '" . $ano . "'";
  $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
  $rows = pg_num_rows($result);

  if ($rows == 0) {

    $sql = "INSERT INTO public.evolucion_conglomerado_sede (descripcion, ano, metrosCuaEC)
              VALUES ('" . $descripcion . "', '" . $ano . "', '" . $metrosCuaEC . "')";

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

    $sql = "UPDATE public.evolucion_conglomerado_sede SET metrosCuaEC = '" . $metrosCuaEC . "' WHERE descripcion = '" . $descripcion . "' AND ano= '" . $ano . "'";


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
LLAMAR A TODA la Evolución infraestructura
=============================================*/
if (isset($_GET['geEvoInfra'])) {
  $dbconn = db_connect();

  $query = "SELECT descripcion, ano, metrosCuaEC FROM evolucion_conglomerado_sede";
  $result = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());
  $result2 = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());

  //return $result;    
  echo '<table class="table table-bordered">
  <thead>
    <tr>
    <th></th>';
      while ($rowA = pg_fetch_row($result)) {
      echo '<th class="tituloTabla">'.$rowA[1].'</th>';
      }
    echo'</tr>
  </thead>
  <tbody>';
  while ($row = pg_fetch_row($result2)) {
  echo '<tr>
      <td>'.$row[0].'</td>
      <td>'.$row[2].'</td>
    </tr>';
  }
  echo '</tbody>
</table>';
}
/*==========================================================================================
                                  FIN Evolución infraestructura
==========================================================================================*/


/*==========================================================================================
  Indicadores de infraestructura: M2 totales por estudiantes, volúmenes, títulos etc.
==========================================================================================*/

/*=============================================
INSERTAR - UPDATE Indicadores infraestructura
=============================================*/
if (isset($_GET['postIndInfra'])) {

  $dbconn = db_connect();
  
  $descripcion = $_POST['descripcion'];
  $ano = $_POST['ano'];
  
if ($descripcion == 'M2 totales por estudiantes presenciales' || $descripcion == 'M2 totales por estudiantes') {
  $query = "SELECT gestion.mat_sies_pre($ano, '$ano-12-31')";
  $result = pg_query($dbconn, $query) or die('La consulta falló: ' . pg_last_error());
  $row = pg_fetch_assoc($result);

  
  $queryM = "SELECT metrosCuaEC FROM evolucion_conglomerado_sede WHERE ano =  '$ano'";
  $resultM = pg_query($dbconn, $queryM) or die('La consulta fallo: ' . pg_last_error());
  $rowM = pg_fetch_assoc($resultM);

  $resultado =  $rowM['metroscuaec'] / $row['mat_sies_pre'];
  $valorCon =  number_format($resultado, 2);
} else {
  $valorCon = $_POST['valorCon'];
}

  $query = "SELECT id FROM public.infraestructura_conglomerado_sede WHERE descripcion = '" . $descripcion . "' AND ano= '" . $ano . "'";
  $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
  $rows = pg_num_rows($result);

  if ($rows == 0) {

    $sql = "INSERT INTO public.infraestructura_conglomerado_sede (descripcion, ano, valorCon)
              VALUES ('" . $descripcion . "', '" . $ano . "', '" . $valorCon . "')";

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

    $sql = "UPDATE public.infraestructura_conglomerado_sede SET valorCon = '" . $valorCon . "' WHERE descripcion = '" . $descripcion . "' AND ano= '" . $ano . "'";


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
LLAMAR A TODA la Indicadores infraestructura
=============================================*/
if (isset($_GET['getIndInfra'])) {
  $dbconn = db_connect();

  $query = "SELECT descripcion, ano, valorCon FROM infraestructura_conglomerado_sede";
  $result = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());
  $result2 = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());

  //return $result;    
  echo '<table class="table table-bordered">
  <thead>
    <tr>
    <th></th>';
      while ($rowA = pg_fetch_row($result)) {
      echo '<th class="tituloTabla">'.$rowA[1].'</th>';
      }
    echo'</tr>
  </thead>
  <tbody>';
  while ($row = pg_fetch_row($result2)) {
  echo '<tr>
      <td>'.$row[0].'</td>
      <td>'.$row[2].'</td>
    </tr>';
  }
  echo '</tbody>
</table>';
}
/*==========================================================================================
                                  FIN Indicadores infraestructura
==========================================================================================*/

/*=============================================
LLAMAR A TODA la Indicadores infraestructura
=============================================*/
if (isset($_GET['getBenEstudiante'])) {
  $dbconn = db_connect();
  $currentYear = date('Y');
  $startYear = $currentYear - 9; // Mostrar desde 5 años atrás
  $endYear = $currentYear; // Mostrar hasta el año actual

  $years = range($startYear, $endYear);
  if (count($years) > 9) {
      $years = array_slice($years, -9); // Mantener solo los últimos 5 años
  }

  $startYearPlusOne = $startYear + 3;

  echo '<div class="tituloTabla">';
  foreach ($years as $key => $year) {
      $checked = ($year > $startYearPlusOne  && $year != $currentYear ) ? 'checked' : '';
      echo '<input type="checkbox" id="checkbox-' . $year . '" name="years[]" value="' . $year . '" ' . $checked . ' onclick="toggleColumn(' . $year . ')"> ' . $year . '';
  }
  echo '</div>';

  echo '<div class="table-responsive">
          <table class="table table-bordered">
              <thead>
                  <tr>
                      <th></th>';
  foreach ($years as $year) {
      echo '<th class="tituloTabla" colspan="3" id="col-' . $year . '"> ' . $year . '</th>';
  }
  echo '</tr>
        <tr>
        <tr>
          <th></th>';
  foreach ($years as $year) {
      echo '<th class="tituloTabla" id="col-' . $year . '-monto">Montos totales ($)</th>
            <th class="tituloTabla" id="col-' . $year . '-est">Estudiantes (%)</th>
            <th class="tituloTabla" id="col-' . $year . '-muj">Mujer (%)</th>';
  }
  echo '</tr>
        <tr>
          <th class="tituloTabla" colspan="19" style="text-align:left !important">Beneficio internos</th>
        </tr>
        </thead>
        <tbody>
        <tr>
        <td>Beca</td>';

  foreach ($years as $year) {
      $queries = [
          'monto' => "SELECT finanzas.beca_pre($year::int2, '$year-12-31'::date, 'monto'::text)",
          'porc' => "SELECT finanzas.beca_pre($year::int2, '$year-12-31'::date, 'porc'::text)",
          'porc_mujeres' => "SELECT finanzas.beca_pre($year::int2, '$year-12-31'::date, 'porc_mujeres'::text)"
      ];

      $results = [];
      foreach ($queries as $key => $query) {
          $result = pg_query($dbconn, $query);
          if (!$result) {
              die('La consulta fallo: ' . pg_last_error());
          }
          $results[$key] = pg_fetch_assoc($result);
      }

      echo '<td id="col-' . $year . '-monto">$' . number_format($results['monto']['beca_pre'], 0, ',', '.') . '</td>';
      echo '<td id="col-' . $year . '-est">' . ($results['porc']['beca_pre']?? 0 ) . '%</td>';
      echo '<td id="col-' . $year . '-muj">' . ($results['porc_mujeres']['beca_pre']?? 0) . '%</td>';
  }
/*********************Descuento de arancel******************************/
  echo '<tr>
  <td>Descuento de arancel</td>';

foreach ($years as $year) {
$queries = [
    'monto' => "SELECT finanzas.descuentos_arancel_pre($year::int2, '$year-12-31'::date, 'monto'::text)",
    'porc' => "SELECT finanzas.descuentos_arancel_pre($year::int2, '$year-12-31'::date, 'porc'::text)",
    'porc_mujeres' => "SELECT finanzas.descuentos_arancel_pre($year::int2, '$year-12-31'::date, 'porc_mujeres'::text)"
];

$results = [];
foreach ($queries as $key => $query) {
    $result = pg_query($dbconn, $query);
    if (!$result) {
        die('La consulta fallo: ' . pg_last_error());
    }
    $results[$key] = pg_fetch_assoc($result);
}

echo '<td id="col-' . $year . '-monto">$' . number_format($results['monto']['descuentos_arancel_pre'] ?? 0, 0, ',', '.') . '</td>';
echo '<td id="col-' . $year . '-est">' . ($results['porc']['descuentos_arancel_pre'] ?? 0) . '%</td>';
echo '<td id="col-' . $year . '-muj">' . ($results['porc_mujeres']['descuentos_arancel_pre'] ?? 0) . '%</td>';
}

echo '</tr>';
/*********************Créditos internos******************************/
echo '<tr>
<td>Créditos internos</td>';

foreach ($years as $year) {
$queries = [
  'monto' => "SELECT finanzas.ci_pre($year::int2, '$year-12-31'::date, 'monto'::text)",
  'porc' => "SELECT finanzas.ci_pre($year::int2, '$year-12-31'::date, 'porc'::text)",
  'porc_mujeres' => "SELECT finanzas.ci_pre($year::int2, '$year-12-31'::date, 'porc_mujeres'::text)"
];

$results = [];
foreach ($queries as $key => $query) {
  $result = pg_query($dbconn, $query);
  if (!$result) {
      die('La consulta fallo: ' . pg_last_error());
  }
  $results[$key] = pg_fetch_assoc($result);
}

echo '<td id="col-' . $year . '-monto">$' . number_format($results['monto']['ci_pre'] ?? 0, 0, ',', '.') . '</td>';
echo '<td id="col-' . $year . '-est">' . ($results['porc']['ci_pre'] ?? 0) . '%</td>';
echo '<td id="col-' . $year . '-muj">' . ($results['porc_mujeres']['ci_pre'] ?? 0) . '%</td>';
}

echo '</tr>

  </tbody>
  </table>
  </div>';
}
?>

<script>
function toggleColumn(year) {
  var columns = document.querySelectorAll('#col-' + year + ', #col-' + year + '-monto, #col-' + year + '-est, #col-' + year + '-muj');
  columns.forEach(function(column) {
      column.style.display = column.style.display === 'none' ? '' : 'none';
  });
 // updateColspan();
}

function updateColspan() {
    var visibleColumns = document.querySelectorAll('th[id^="col-"]:not([style*="display: none"])').length;
    var beneficioHeader = document.getElementById('beneficio-internos-header');
    beneficioHeader.colSpan = visibleColumns + 1; // +1 for the first empty column
}
// Inicializar la visibilidad de las columnas según los checkboxes
setTimeout(() => {
  var checkboxes = document.querySelectorAll('input[type="checkbox"][name="years[]"]');
  var startYearPlusOne = <?php echo json_encode($startYearPlusOne); ?>;
  var currentYear = <?php echo json_encode($currentYear); ?>;
  checkboxes.forEach(function(checkbox) {
    if (!checkbox.checked || checkbox.value == startYearPlusOne || checkbox.value == currentYear) {
          toggleColumn(checkbox.value);
      }
  });
  
}, 1000);
</script>
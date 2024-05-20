<script>
  function presionaCheck(idCarrera) {
          var carreras = $("#id_carrerasSeleccionados").val();

          idCheckBox = "id_checkbox_"+idCarrera;
          nivelSelected = document.getElementById(idCheckBox);
          if (nivelSelected.checked == true){
            //debo agregarlo
            valorFinal = carreras+','+idCarrera+',';
            valorFinal = valorFinal.replace(',,', ',');
            valorFinal = valorFinal.replace(', ,', ',');
            //valorFinal = valorFinal.replace(' ', '');
            $("#id_carrerasSeleccionados").val(valorFinal);
          } else {
            //debo eliminarlo
            result = carreras.replace(','+idCarrera+',', ',');
            if (result.trim() == ',') {
              result = '';
            }
            //valorFinal = valorFinal.replace(' ', '');
            $("#id_carrerasSeleccionados").val(result);
          }

          

          
  }  

</script>
<?php
function sacaCommaFinal($s) {
  $ss = $s;
  $ult = "";
  $ult = substr($ss,strlen($ss)-1,1); 
  if ($ult == ",") {
    $ss = substr($ss,0,strlen($ss)-1);
  }
  return $ss;
}
//***************************** I N S T I T U C I O N A L  1 ************************/
function obtieneUniversoCarrerasPorConcepto_uno($ano, $ano_fin, $filtroIdCarreras, $regimen, $jornada,  $concepto, $id_area_conocimiento) {
  //CAMBIO vm.ano
  $sql_concepto_1 = "                and (
    select 
    max(vm.niveles/2) max_duracion_ano
    from vista_mallas vm
    where 
    vm.id_carrera = c.id
    --and vm.ano between $ano and $ano_fin	
  ) between 2 and 3
  ";
//CAMBIO vm.ano
  $sql_concepto_2 = "                and (
    select 
    max(vm.niveles/2) max_duracion_ano
    from vista_mallas vm
    where 
    vm.id_carrera = c.id
    --and vm.ano between $ano and $ano_fin	
  ) between 4 and 6
  ";
  //CAMBIO vm.ano
  $sql_concepto_1_2 = "                and ((
      select 
      max(vm.niveles/2) max_duracion_ano
      from vista_mallas vm
      where 
      vm.id_carrera = c.id
      --and vm.ano between $ano and $ano_fin	
    ) between 2 and 3
    or (
      select 
      max(vm.niveles/2) max_duracion_ano
      from vista_mallas vm
      where 
      vm.id_carrera = c.id
      --and vm.ano between $ano and $ano_fin	
    ) between 4 and 6
    )
        ";

  $SQL_totalCarreras = "
  select 
  distinct 
  id_carrera,
  nombre_carrera
    from (
    SELECT    
    distinct
    c.id id_carrera,
    c.nombre nombre_carrera,
    a.cohorte
    FROM      alumnos  AS a
    LEFT JOIN carreras AS c
    ON        c.id=a.carrera_actual
    where  a.cohorte BETWEEN $ano AND       $ano_fin";

    if ($filtroIdCarreras <> "") {
      $SQL_totalCarreras = $SQL_totalCarreras." AND       (c.id in ($filtroIdCarreras))";
    }
    if ($regimen <> "") {
      $SQL_totalCarreras = $SQL_totalCarreras." AND       (c.regimen = '$regimen')";
    }
    if ($id_area_conocimiento <> "") {
      $SQL_totalCarreras = $SQL_totalCarreras." and (c.id_area_conocimiento = '$id_area_conocimiento')";
    }

    if ($jornada <> "") {
      $SQL_totalCarreras = $SQL_totalCarreras." AND       (a.jornada = '$jornada')";
    }

      if ($concepto=="1"){
        $SQL_totalCarreras = $SQL_totalCarreras.$sql_concepto_1;
      }
      if ($concepto=="2"){
        $SQL_totalCarreras = $SQL_totalCarreras.$sql_concepto_2;
        
      }
      if ($concepto=="3"){
        $SQL_totalCarreras = $SQL_totalCarreras.$sql_concepto_1_2;    
      }


    $SQL_totalCarreras = $SQL_totalCarreras.") as a 
  order by 
  nombre_carrera
  ";

//  echo("<br>CARRERAS<br>");
//  echo("<br>concepto = $concepto<br>");
//  echo($SQL_totalCarreras);

  $totalCarrerasConcepto = consulta_sql($SQL_totalCarreras);
  return $totalCarrerasConcepto;
}
function alumnosMatriculados_uno($regimen, $ano, $id_carrera, $jornada) {
$ss = "
select count(*) as cuenta
FROM alumnos AS a
WHERE true  
--AND (a.semestre_cohorte = 1 ) --primer semestre 
AND (a.cohorte = '$ano' ) AND (a.carrera_actual = $id_carrera) 
";
if ($jornada <> "") {
  $ss = $ss." AND       (a.jornada = '$jornada')";
}



    $sqlCuenta     = consulta_sql($ss);
    extract($sqlCuenta[0]);
    return $cuenta;

}
function muestraConceptoInstitucional_uno($nombreConcepto, $ano_egreso, $ano_egreso_fin, $totalCarreras, $regimen, $jornada, $id_area_conocimiento) {
  
  echo("<tr class='filaTituloTabla'>");
  echo("<td class='tituloTabla' style='text-align:left'>$nombreConcepto</td>");
      $HTML_anos = "";
      for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
          //$HTML_anos = $HTML_anos."<td class='tituloTabla'>$x</td>";
          $HTML_anos = $HTML_anos."<td class='tituloTabla'></td>";
      }        
      echo($HTML_anos);
  echo("</tr>");

  for ($z=0;$z<count($totalCarreras);$z++) {
      echo("<tr class='filaTituloTabla'>");              
      $idCarrera = $totalCarreras[$z]['id_carrera'];
      //$nombreCarrera = $totalCarreras[$z]['nombre_carrera']."(".$totalCarreras[$z]['alias_carrera'].")"; 
      $nombreCarrera = $totalCarreras[$z]['nombre_carrera']; 
      //$totalCarreras[0]['alias_carrera']
      echo("<td class='textoTabla'>$nombreCarrera</td>");
      $HTML_anos = "";
    
      for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
        $valorTotal =  alumnosMatriculados_uno($regimen, $x, $idCarrera, $jornada); //cuentaEgresados($x, "", $regimen);
        $valor =  cuentaEgresados_uno($x, $idCarrera, $regimen, $jornada, $id_area_conocimiento);
        if ($valor > 0) {
          if ($valorTotal > 0) {
            //$porcentaje = round($valor/$valorTotal,1)*100;
            $porcentaje = round(($valor/$valorTotal)*100,1);
          } else {
            $porcentaje = 0;  
          }
          
        } else {
          $porcentaje = 0;
        }
        
        
        $HTML_anos = $HTML_anos."<td class='textoTabla' style='text-align:right'>($valor)vs($valorTotal) -> $porcentaje%</td>";
        //$HTML_anos = $HTML_anos."<td class='textoTabla' style='text-align:right'>$valor</td>";
      }        
      echo($HTML_anos);
      
  } 

}
function cuentaEgresadosInstitucional_uno($totalCarreras, $ano_buscar, $regimen, $jornada, $id_area_conocimiento, $concepto) {
  $myCarrera = "";
  for ($z=0;$z<count($totalCarreras);$z++) {
    $idCarrera = $totalCarreras[$z]['id_carrera'];
    $myCarrera = $myCarrera.$idCarrera.",";
  } 

  $myCarrera = sacaCommaFinal($myCarrera);

    $ss = "
    select count(*) as cuenta
    FROM alumnos AS a, carreras c
    WHERE   
    --AND (a.semestre_cohorte = 1 ) 
    (a.cohorte = '$ano_buscar' ) AND (a.carrera_actual in ($myCarrera)) 
    and c.id = a.carrera_actual
  ";
  if ($regimen <> "") {
    $ss = $ss."          and c.regimen = '$regimen'";
  }
  if ($id_area_conocimiento <> "") {
    $ss = $ss." and (c.id_area_conocimiento = '$id_area_conocimiento')";
  }

  if ($myCarrera <> "") {
    $ss = $ss." and c.id in ($myCarrera)";
  }
  if ($jornada <> "") {
    $ss = $ss." AND       (a.jornada = '$jornada')";
  }
//NUEVO
  $valor =  cuentaEgresados_uno_todos($ano_buscar, $myCarrera, $regimen, $jornada, $id_area_conocimiento, $concepto);
  //echo("<br>aqui tamo $ss");


  $sqlCuenta     = consulta_sql($ss);
  extract($sqlCuenta[0]);
  
  $resultado = round(($valor / $cuenta)*100, 1); 
  $salida = "($valor)vs($cuenta) -> $resultado";
  //return $cuenta;
  return $salida;

}
function cuentaEgresados_uno_todos($ano_buscar, $idCarreras, $regimen, $jornada, $id_area_conocimiento, $concepto) {
  $cuenta = 0;
//troya
$sql_concepto_1 = "                and (
  select 
  max(vm.niveles/2) max_duracion_ano
  from vista_mallas vm
  where 
  vm.id_carrera = c.id
  --and vm.ano between $ano and $ano_fin	
) between 2 and 3
";
//CAMBIO vm.ano
$sql_concepto_2 = "                and (
  select 
  max(vm.niveles/2) max_duracion_ano
  from vista_mallas vm
  where 
  vm.id_carrera = c.id
  --and vm.ano between $ano and $ano_fin	
) between 4 and 6
";





$ss = "
  select sum(cuenta) cuenta
  from (
          SELECT    
          count(*) as cuenta
          FROM      alumnos  AS a
          LEFT JOIN carreras AS c
          ON        c.id=a.carrera_actual
          left join al_estados ale
          on ale.id = a.estado          
          where     a.cohorte = $ano_buscar
          and ale.nombre in ('Licenciado', 'Egresado', 'Titulado')
          ";
          if ($regimen <> "") {
            $ss = $ss."          and c.regimen = '$regimen'";
          }
          if ($id_area_conocimiento <> "") {
            $ss = $ss." and (c.id_area_conocimiento = '$id_area_conocimiento')";
          }
      
          if ($id_carrera <> "") {
            $ss = $ss." and c.id in ($idCarreras)";
          }
          if ($jornada <> "") {
            $ss = $ss." AND       (a.jornada = '$jornada')";
          }
          if ($concepto=="1"){
            $ss = $ss.$sql_concepto_1;
          }
          if ($concepto=="2"){
            $ss = $ss.$sql_concepto_2;            
          }
              
  $ss = $ss.") as a     ";
//echo("<br>troya $ss");
  $sqlCuenta     = consulta_sql($ss);
  extract($sqlCuenta[0]);
  return $cuenta;
}
function cuentaEgresados_uno($ano_buscar, $id_carrera, $regimen, $jornada, $id_area_conocimiento) {
  $cuenta = 0;

  
$ss = "
  select sum(cuenta) cuenta
  from (
          SELECT    
          count(*) as cuenta
          FROM      alumnos  AS a
          LEFT JOIN carreras AS c
          ON        c.id=a.carrera_actual
          left join al_estados ale
          on ale.id = a.estado          
          where     a.cohorte = $ano_buscar
          and ale.nombre in ('Licenciado', 'Egresado', 'Titulado')
          ";
          if ($regimen <> "") {
            $ss = $ss."          and c.regimen = '$regimen'";
          }
          if ($id_area_conocimiento <> "") {
            $ss = $ss." and (c.id_area_conocimiento = '$id_area_conocimiento')";
          }
      
          if ($id_carrera <> "") {
            $ss = $ss." and c.id = $id_carrera";
          }
          if ($jornada <> "") {
            $ss = $ss." AND       (a.jornada = '$jornada')";
          }
          
  $ss = $ss.") as a     ";

  $sqlCuenta     = consulta_sql($ss);
  extract($sqlCuenta[0]);
  return $cuenta;
}

//***************************** I N S T I T U C I O N A L  2 ************************/
function obtieneUniversoCarrerasPorConcepto_dos($ano, $ano_fin, $filtroIdCarreras, $regimen, $jornada,  $concepto, $id_area_conocimiento) {
  //CAMBIO vm.ano
  $sql_concepto_1 = "                and (
    select 
    max(vm.niveles/2) max_duracion_ano
    from vista_mallas vm
    where 
    vm.id_carrera = c.id
    --and vm.ano between $ano and $ano_fin	
  ) between 2 and 3
  ";
//CAMBIO vm.ano
  $sql_concepto_2 = "                and (
    select 
    max(vm.niveles/2) max_duracion_ano
    from vista_mallas vm
    where 
    vm.id_carrera = c.id
    --and vm.ano between $ano and $ano_fin	
  ) between 4 and 6
  ";
  //CAMBIO vm.ano
  $sql_concepto_1_2 = "                and ((
      select 
      max(vm.niveles/2) max_duracion_ano
      from vista_mallas vm
      where 
      vm.id_carrera = c.id
      --and vm.ano between $ano and $ano_fin	
    ) between 2 and 3
    or (
      select 
      max(vm.niveles/2) max_duracion_ano
      from vista_mallas vm
      where 
      vm.id_carrera = c.id
      --and vm.ano between $ano and $ano_fin	
    ) between 4 and 6
    )
        ";

  $SQL_totalCarreras = "
  select 
  distinct 
  id_carrera,
  nombre_carrera
    from (
    SELECT    
    distinct
    c.id id_carrera,
    c.nombre nombre_carrera,
    a.cohorte
    FROM      alumnos  AS a
    LEFT JOIN carreras AS c
    ON        c.id=a.carrera_actual
    where  a.cohorte BETWEEN $ano AND       $ano_fin";
    if ($filtroIdCarreras <> "") {
      $SQL_totalCarreras = $SQL_totalCarreras." AND       (c.id in ($filtroIdCarreras))";
    }
    if ($regimen <> "") {
      $SQL_totalCarreras = $SQL_totalCarreras." AND       (c.regimen = '$regimen')";
    }
    if ($id_area_conocimiento <> "") {
      $SQL_totalCarreras = $SQL_totalCarreras." and (c.id_area_conocimiento = '$id_area_conocimiento')";
    }

    if ($jornada <> "") {
      $SQL_totalCarreras = $SQL_totalCarreras." AND       (a.jornada = '$jornada')";
    }

      if ($concepto=="1"){
        $SQL_totalCarreras = $SQL_totalCarreras.$sql_concepto_1;
      }
      if ($concepto=="2"){
        $SQL_totalCarreras = $SQL_totalCarreras.$sql_concepto_2;
        
      }
      if ($concepto=="3"){
        $SQL_totalCarreras = $SQL_totalCarreras.$sql_concepto_1_2;    
      }


    $SQL_totalCarreras = $SQL_totalCarreras.") as a 
  order by 
  nombre_carrera
  ";

  //echo("<br>CARRERAS<br>");
  //echo("<br>concepto = $concepto<br>");
  //echo($SQL_totalCarreras);

  $totalCarrerasConcepto = consulta_sql($SQL_totalCarreras);
  return $totalCarrerasConcepto;
}
function alumnosMatriculados_dos($regimen, $ano, $id_carrera, $jornada) {

$ss = "
select count(*) as cuenta
FROM alumnos AS a
WHERE true  
AND (a.semestre_cohorte = 1 ) --primer semestre 
AND (a.cohorte = '$ano' ) AND (a.carrera_actual = $id_carrera) 
";
if ($jornada <> "") {
  $ss = $ss." AND       (a.jornada = '$jornada')";
}

//echo("<br>$ss<br>");

    $sqlCuenta     = consulta_sql($ss);
    extract($sqlCuenta[0]);
    return $cuenta;

}
function muestraConceptoInstitucional_dos($nombreConcepto, $ano_egreso, $ano_egreso_fin, $totalCarreras, $regimen, $jornada, $id_area_conocimiento) {
  
  echo("<tr class='filaTituloTabla'>");
  echo("<td class='tituloTabla' style='text-align:left'>$nombreConcepto</td>");
      $HTML_anos = "";
      for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
          //$HTML_anos = $HTML_anos."<td class='tituloTabla'>$x</td>";
          $HTML_anos = $HTML_anos."<td class='tituloTabla'></td>";
      }        
      echo($HTML_anos);
  echo("</tr>");

  for ($z=0;$z<count($totalCarreras);$z++) {
      echo("<tr class='filaTituloTabla'>");              
      $idCarrera = $totalCarreras[$z]['id_carrera'];
      //$nombreCarrera = $totalCarreras[$z]['nombre_carrera']."(".$totalCarreras[$z]['alias_carrera'].")"; 
      $nombreCarrera = $totalCarreras[$z]['nombre_carrera']; 
      //$totalCarreras[0]['alias_carrera']
      echo("<td class='textoTabla'>$nombreCarrera</td>");
      $HTML_anos = "";
    
      for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
        $valorTotal =  alumnosMatriculados_dos($regimen, $x, $idCarrera, $jornada); //cuentaEgresados($x, "", $regimen);
        $valor =  cuentaEgresados_dos($x, $idCarrera, $regimen, $jornada, $id_area_conocimiento);
        if ($valor > 0) {
          if ($valorTotal > 0) {
            $porcentaje = round($valor/$valorTotal*100,1);
          } else {
            $porcentaje = 0;  
          }
          
        } else {
          $porcentaje = 0;
        }
        
        
        $HTML_anos = $HTML_anos."<td class='textoTabla' style='text-align:right'>($valor)vs($valorTotal) -> $porcentaje%</td>";
        //$HTML_anos = $HTML_anos."<td class='textoTabla' style='text-align:right'>$valor</td>";
      }        
      echo($HTML_anos);
      
  } 

}
function cuentaEgresadosInstitucional_dos($totalCarreras, $ano_buscar, $regimen, $jornada, $id_area_conocimiento, $concepto) {
  $myCarrera = "";
  for ($z=0;$z<count($totalCarreras);$z++) {
    $idCarrera = $totalCarreras[$z]['id_carrera'];
    $myCarrera = $myCarrera.$idCarrera.",";
  } 

  $myCarrera = sacaCommaFinal($myCarrera);

    $ss = "
    select count(*) as cuenta
    FROM alumnos AS a, carreras c
    WHERE   
     (a.semestre_cohorte = 1 ) 
    and (a.cohorte = '$ano_buscar' ) AND (a.carrera_actual in ($myCarrera)) 
    and c.id = a.carrera_actual
  ";
  if ($regimen <> "") {
    $ss = $ss."          and c.regimen = '$regimen'";
  }
  if ($id_area_conocimiento <> "") {
    $ss = $ss." and (c.id_area_conocimiento = '$id_area_conocimiento')";
  }

  if ($myCarrera <> "") {
    $ss = $ss." and c.id in ($myCarrera)";
  }
  if ($jornada <> "") {
    $ss = $ss." AND       (a.jornada = '$jornada')";
  }

  //NUEVO
  $valor =  cuentaEgresados_dos_todos($ano_buscar, $myCarrera, $regimen, $jornada, $id_area_conocimiento, $concepto);
  //echo("<br>monto = $valor");


  $sqlCuenta     = consulta_sql($ss);
//  echo("<br>aqui tamo $ss");


  extract($sqlCuenta[0]);
  
  $resultado = round(($valor / $cuenta)*100, 1); 
  $salida = "($valor)vs($cuenta) -> $resultado";
  //return $cuenta;
  return $salida;



//  $sqlCuenta     = consulta_sql($ss);
//  extract($sqlCuenta[0]);
//  return $cuenta;

}

function cuentaEgresados_dos($ano_buscar, $id_carrera, $regimen, $jornada, $id_area_conocimiento) {
  $cuenta = 0;

  
$ss = "
  select sum(cuenta) cuenta
  from (
          SELECT    
          count(*) as cuenta
          FROM      alumnos  AS a
          LEFT JOIN carreras AS c
          ON        c.id=a.carrera_actual
          left join al_estados ale
          on ale.id = a.estado          
          where     a.cohorte = $ano_buscar
          and ale.nombre in ('Licenciado', 'Egresado', 'Titulado')
          ";
          if ($regimen <> "") {
            $ss = $ss."          and c.regimen = '$regimen'";
          }
          if ($id_area_conocimiento <> "") {
            $ss = $ss." and (c.id_area_conocimiento = '$id_area_conocimiento')";
          }
      
          if ($id_carrera <> "") {
            $ss = $ss." and c.id = $id_carrera";
          }
          if ($jornada <> "") {
            $ss = $ss." AND       (a.jornada = '$jornada')";
          }
          
  $ss = $ss.") as a     ";

  $sqlCuenta     = consulta_sql($ss);
  extract($sqlCuenta[0]);
  return $cuenta;
}

function cuentaEgresados_dos_todos($ano_buscar, $idCarreras, $regimen, $jornada, $id_area_conocimiento, $concepto) {
  //troya
  $cuenta = 0;
  $sql_concepto_1 = "                and (
    select 
    max(vm.niveles/2) max_duracion_ano
    from vista_mallas vm
    where 
    vm.id_carrera = c.id
    --and vm.ano between $ano and $ano_fin	
  ) between 2 and 3
  ";
  //CAMBIO vm.ano
  $sql_concepto_2 = "                and (
    select 
    max(vm.niveles/2) max_duracion_ano
    from vista_mallas vm
    where 
    vm.id_carrera = c.id
    --and vm.ano between $ano and $ano_fin	
  ) between 4 and 6
  ";
  
  
$ss = "
  select sum(cuenta) cuenta
  from (
          SELECT    
          count(*) as cuenta
          FROM      alumnos  AS a
          LEFT JOIN carreras AS c
          ON        c.id=a.carrera_actual
          left join al_estados ale
          on ale.id = a.estado          
          where     a.cohorte = $ano_buscar
          and ale.nombre in ('Licenciado', 'Egresado', 'Titulado')
          ";
          if ($regimen <> "") {
            $ss = $ss."          and c.regimen = '$regimen'";
          }
          if ($id_area_conocimiento <> "") {
            $ss = $ss." and (c.id_area_conocimiento = '$id_area_conocimiento')";
          }
      
          if ($id_carrera <> "") {
            $ss = $ss." and c.id in ( $idCarreras)";
          }
          if ($jornada <> "") {
            $ss = $ss." AND       (a.jornada = '$jornada')";
          }
          if ($concepto=="1"){
            $ss = $ss.$sql_concepto_1;
          }
          if ($concepto=="2"){
            $ss = $ss.$sql_concepto_2;            
          }
          
  $ss = $ss.") as a     ";

  $sqlCuenta     = consulta_sql($ss);
  extract($sqlCuenta[0]);
  return $cuenta;
}













function carreraHaSidoSeleccionada($id_carrerasSeleccionados, $id_carrera) {
  $bSalida = false;
//  if (strpos($id_carrerasSeleccionados,",0,") > 0) {
//    $bSalida = false;
//  }
//  else 

  try {
    {
      $tBuscar = ",$id_carrera,";
//      if (strpos($id_carrerasSeleccionados,$tBuscar) > 0) {
//        $bSalida = true;
//      }  
  // use of explode
    $str_arr = explode (",", $id_carrerasSeleccionados); 
    //echo("<br>$str_arr");
    foreach ($str_arr as $value) {
//      echo("<br>$value");
      if ($value > 0) {
        if ($value == $id_carrera) {
          $bSalida = true;
          break;
        }
      }
    }

    }
    if ($bSalida) {
      $ss = "TRUE";
    } else {
      $ss = "FALSE";
    }
    //echo("<br>id_carrerasSeleccionados = $ss - listado = $id_carrerasSeleccionados - carrera = $id_carrera");
  } catch (Exception $e) {
  }
  return $bSalida;
}


if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
$modulo_destino = "ver_alumno";

$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { 
  $cant_reg = 30; 
}
$cant_reg = -1; 
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }


$institucional1      = $_REQUEST['institucional1'];
$institucional2      = $_REQUEST['institucional2'];
$ejecutar      = $_REQUEST['ejecutar'];
$id_area_conocimiento      = $_REQUEST['id_area_conocimiento'];
//echo("<br>area_conocimiento= $id_area_conocimiento <br>");
$tipo_carrera = $_REQUEST['tipo_carrera'];
/*
$id_checkbox_concepto_1      = $_REQUEST['id_checkbox_concepto_1'];
$id_checkbox_concepto_2      = $_REQUEST['id_checkbox_concepto_2'];
$id_checkbox_concepto_3      = $_REQUEST['id_checkbox_concepto_3'];
$id_checkbox_concepto_4      = $_REQUEST['id_checkbox_concepto_4'];
$id_checkbox_concepto_5      = $_REQUEST['id_checkbox_concepto_5'];
$id_checkbox_concepto_6      = $_REQUEST['id_checkbox_concepto_6'];
$id_checkbox_concepto_7      = $_REQUEST['id_checkbox_concepto_7']; //TODOS
*/

$id_carrerasSeleccionados      = $_REQUEST['id_carrerasSeleccionados'];

$nombreConcepto1 = "Carrera Pregrado 2 a 3 años";
$nombreConcepto2 = "Carrera Pregrado 4 a 5 años";
$nombreConcepto3 = "Nivel de formación";
$nombreConcepto4 = "Jornada";
$nombreConcepto5 = "Modalidad";
$nombreConcepto6 = "Area del Conocimiento";



/*
if ($id_carrerasSeleccionados) {
  // use of explode
    $str_arr = explode (",", $id_carrerasSeleccionados); 
    //echo("<br>$str_arr");
    foreach ($str_arr as $value) {
      echo("<br>$value");
    }
}
//piriri
*/

//echo("<br>Institucional1 = $institucional1");
//echo("<br>Institucional2 = $institucional2");
//echo("<br>Ejecutar = $ejecutar");
/*
echo("<br>id_checkbox_concepto_1 = $id_checkbox_concepto_1");
echo("<br>id_checkbox_concepto_2 = $id_checkbox_concepto_2");
echo("<br>id_checkbox_concepto_3 = $id_checkbox_concepto_3");
echo("<br>id_checkbox_concepto_4 = $id_checkbox_concepto_4");
echo("<br>id_checkbox_concepto_5 = $id_checkbox_concepto_5");
echo("<br>id_checkbox_concepto_6 = $id_checkbox_concepto_6");
*/
//echo("<br>id_checkbox_concepto_7 = $id_checkbox_concepto_7");
$id_carrs = str_replace(" ","", $id_carrerasSeleccionados);
$id_carrs = str_replace(",,",",", $id_carrs);
//echo("<br>id_carrs = $id_carrs");
$id_carrerasSeleccionados = $id_carrs;
//echo("<br>id_carrerasSeleccionados = $id_carrerasSeleccionados");


$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$id_carrera        = $_REQUEST['id_carrera'];
$jornada           = $_REQUEST['jornada'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$mes_cohorte       = $_REQUEST['mes_cohorte'];
$cohorte           = $_REQUEST['cohorte'];
$ano_egreso        = $_REQUEST['ano_egreso'];
$ano_egreso_fin    = $_REQUEST['ano_egreso_fin'];
$semestre_egreso   = -1; //$_REQUEST['semestre_egreso'];
//$fec_ini_egreso    = $_REQUEST['fec_ini_egreso'];
//$fec_fin_egreso    = $_REQUEST['fec_fin_egreso'];
$moroso_financiero = $_REQUEST['moroso_financiero'];
$admision          = $_REQUEST['admision'];
$regimen           = $_REQUEST['regimen'];
$aprob_ant         = $_REQUEST['aprob_ant'];
$matriculado       = $_REQUEST['matriculado'];

/*
echo("<br>");
echo("1.-texto_buscar : ".$texto_buscar."<br>");
echo("1.-buscar : ".$buscar."<br>");
echo("1.-id_carrera : ".$id_carrera."<br>");
echo("1.-jornada : ".$jornada."<br>");
echo("1.-semestre_cohorte: ".$semestre_cohorte."<br>");
echo("1.-mes_cohorte : ".$mes_cohorte."<br>");
echo("1.-cohorte : ".$cohorte."<br>");
echo("1.-ano_egreso : ".$ano_egreso."<br>");
echo("1.-ano_egreso_fin : ".$ano_egreso_fin."<br>");
echo("1.-semestre_egreso : ".$semestre_egreso."<br>");
echo("1.-fec_ini_egreso : ".$fec_ini_egreso."<br>");
echo("1.-fec_fin_egreso : ".$fec_fin_egreso."<br>");
echo("1.-moroso_financiero : ".$moroso_financiero."<br>");
echo("1.-admision : ".$admision."<br>");
echo("1.-regimen : ".$regimen."<br>");
echo("1.-aprob_ant : ".$aprob_ant."<br>");
echo("1.-matriculado : ".$matriculado."<br>");
*/



if (empty($_REQUEST['matriculado'])) { $matriculado = ""; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }
if (empty($_REQUEST['ano_egreso'])) 
{ 
    $ano_egreso = $ANO; 
    //$semestre_egreso = -1; 
}
if (empty($_REQUEST['ano_egreso_fin'])) 
{ 
    $ano_egreso_fin = $ANO; 
    //$semestre_egreso = -1; 
}
//if (empty($_REQUEST['fec_ini_egreso'])) { $fec_ini_egreso = date("Y")."-01-01"; }
//if (empty($_REQUEST['fec_fin_egreso'])) { $fec_fin_egreso = date("Y-m-d"); }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($_REQUEST['aprob_ant'])) { $aprob_ant = 't'; }
if (empty($cond_base)) { $cond_base = "ae.nombre='Egresado'"; }

/*
echo("<br>");

echo("2.-texto_buscar : ".$texto_buscar."<br>");
echo("2.-buscar : ".$buscar."<br>");
echo("2.-id_carrera : ".$id_carrera."<br>");
echo("2.-jornada : ".$jornada."<br>");
echo("2.-semestre_cohorte: ".$semestre_cohorte."<br>");
echo("2.-mes_cohorte : ".$mes_cohorte."<br>");
echo("2.-cohorte : ".$cohorte."<br>");
echo("2.-ano_egreso : ".$ano_egreso."<br>");
echo("2.-ano_egreso_fin : ".$ano_egreso_fin."<br>");
echo("2.-semestre_egreso : ".$semestre_egreso."<br>");
echo("2.-fec_ini_egreso : ".$fec_ini_egreso."<br>");
echo("2.-fec_fin_egreso : ".$fec_fin_egreso."<br>");
echo("2.-moroso_financiero : ".$moroso_financiero."<br>");
echo("2.-admision : ".$admision."<br>");
echo("2.-regimen : ".$regimen."<br>");
echo("2.-aprob_ant : ".$aprob_ant."<br>");
echo("2.-matriculado : ".$matriculado."<br>");
*/

$sem_ant = $ano_ant = 0;
if ($SEMESTRE == 2)     { $sem_ant = 1; $ano_ant = $ANO; }
elseif ($SEMESTRE <= 1) { $sem_ant = 2; $ano_ant = $ANO - 1; }

$condicion = "WHERE $cond_base  ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion .= " AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		            . " a.rut ~* '$cadena_buscada' OR "
		            . " text(a.id) ~* '$cadena_buscada' "
		            . ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$cohorte = $semestre_cohorte = $estado = $id_carrera = $admision = $matriculado = $regimen = null;
} else {

	if ($cohorte > 0) {
		$condicion .= "AND (cohorte = '$cohorte') ";
	}

	if ($semestre_cohorte > 0) {
		$condicion .= "AND (semestre_cohorte = $semestre_cohorte) ";
	}

	if ($mes_cohorte > 0) {
		$condicion .= "AND (mes_cohorte = $mes_cohorte) ";
	}
	 
	if ($ano_egreso > 0) {
		//$condicion .= "AND (a.ano_egreso = $ano_egreso) ";
    $condicion .= "AND (a.ano_egreso between $ano_egreso and $ano_egreso_fin) ";
		if ($semestre_egreso <> "-1") { $condicion .= "AND (semestre_egreso = $semestre_egreso) "; }
	} elseif ($ano_egreso == "-2") {
		//if ($fec_ini_egreso <> "" && $fec_fin_egreso <> "") {
		//	$condicion .= " AND (a.fecha_egreso between '$fec_ini_egreso'::date AND '$fec_fin_egreso'::date) ";
		//}
	}

	if ($moroso_financiero <> "-1") {
		$condicion .= "AND (moroso_financiero = '$moroso_financiero') ";
	}
	
	if ($id_carrera <> "") {
		$condicion .= "AND (carrera_actual = '$id_carrera') ";
	}

	if ($jornada <> "") {
		$condicion .= "AND (a.jornada = '$jornada') ";
	}

	if ($admision <> "") {
		$condicion .= "AND (a.admision = '$admision') ";
	}

	if ($regimen <> "" && $regimen <> "t") {
		//$condicion .= "AND (c.regimen = '$regimen') ";
	}


  
	if ($matriculado == "t") {
		//$condicion .= "AND (m.id_alumno IS NOT NULL) ";
	} elseif ($matriculado == "f") {
		//$condicion .= "AND (m.id_alumno IS NULL) ";
	}
	

	switch ($aprob_ant) {
		case 1:
			$condicion .= " AND (($SQL_tasa_aprob_ant) = 0) ";
			break;
		case 2:
			$condicion .= " AND (($SQL_tasa_aprob_ant) BETWEEN 1 AND 39.9) ";
			break;
		case 3:
			$condicion .= " AND (($SQL_tasa_aprob_ant) BETWEEN 40 AND 100) ";
			break;
	}
}

if (!empty($ids_carreras) && empty($id_carrera)) {
	$condicion .= " AND carrera_actual IN ($ids_carreras) ";
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }


$filtroIdCarreras = substr($id_carrerasSeleccionados,1,strlen($id_carrerasSeleccionados)-2);
/*
if ($filtroIdCarreras=="") {
  $ejecutar = "";
  //echo(msje_js("Debe seleccionar al menos una carrera ."));
  echo("Debe seleccionar al menos una cerrara.");
}
*/


//echo("<br>filtroCerrareas = $filtroIdCarreras<br>");

$puedeSeguir = true;

$puedeSeguir = false;
/*
if ($id_checkbox_concepto_1<>"") {
  $puedeSeguir = true;
}
if (!$puedeSeguir) {
  if ($id_checkbox_concepto_2<>"") {
    $puedeSeguir = true;
  }
}
if (!$puedeSeguir) {
  if ($id_checkbox_concepto_3<>"") {
    $puedeSeguir = true;
  }
}
if (!$puedeSeguir) {
  if ($id_checkbox_concepto_4<>"") {
    $puedeSeguir = true;
  }
}
if (!$puedeSeguir) {
  if ($id_checkbox_concepto_5<>"") {
    $puedeSeguir = true;
  }
}
if (!$puedeSeguir) {
  if ($id_checkbox_concepto_6<>"") {
    $puedeSeguir = true;
  }
}
*/
if ($tipo_carrera <> "") {
  $puedeSeguir = true;
}
if ($puedeSeguir){
//CAMBIO vm.ano
        $sql_concepto_1 = "                and (
          select 
          max(vm.niveles/2) max_duracion_ano
          from vista_mallas vm
          where 
          vm.id_carrera = c.id
          --and vm.ano between $ano_egreso and $ano_egreso_fin	
        ) between 2 and 3
        ";
//CAMBIO vm.ano
        $sql_concepto_2 = "                and (
          select 
          max(vm.niveles/2) max_duracion_ano
          from vista_mallas vm
          where 
          vm.id_carrera = c.id
          --and vm.ano between $ano_egreso and $ano_egreso_fin	
        ) between 4 and 6
        ";
        /*
        $sql_concepto_1_2 = "                and ((
            select 
            max(vm.niveles/2) max_duracion_ano
            from vista_mallas vm
            where 
            vm.id_carrera = c.id
            and vm.ano between $ano_egreso and $ano_egreso_fin	
          ) between 2 and 3
          or (
            select 
            max(vm.niveles/2) max_duracion_ano
            from vista_mallas vm
            where 
            vm.id_carrera = c.id
            and vm.ano between $ano_egreso and $ano_egreso_fin	
          ) between 4 and 5
          )
              ";
              */
/*
        $puedePreguntar = true;
        if ($id_checkbox_concepto_3<>"") {
          $puedePreguntar = false;
        }
        if ($id_checkbox_concepto_4<>"") {
          $puedePreguntar = false;
        }
        if ($id_checkbox_concepto_5<>"") {
          $puedePreguntar = false;
        }
        if ($id_checkbox_concepto_6<>"") {
          $puedePreguntar = false;
        }
        //if ($id_checkbox_concepto_7<>"") {
        //  $puedePreguntar = false;
        //}
  */  
        $SQL_universoCarreras = "
        select 
        distinct 
        id_carrera,
        nombre_carrera
          from (
          SELECT    
          distinct
          c.id id_carrera,
          c.nombre nombre_carrera,
          a.cohorte
          FROM      alumnos  AS a
          LEFT JOIN carreras AS c
          ON        c.id=a.carrera_actual
          where  a.cohorte BETWEEN $ano_egreso AND       $ano_egreso_fin";
          if ($regimen <> "") {
            $SQL_universoCarreras = $SQL_universoCarreras." AND       (c.regimen = '$regimen')";
          }
          if ($id_area_conocimiento <> "") {
            $SQL_universoCarreras = $SQL_universoCarreras." and (c.id_area_conocimiento = '$id_area_conocimiento')";
          }
      
          if ($jornada <> "") {
            $SQL_universoCarreras = $SQL_universoCarreras." AND       (a.jornada = '$jornada')";
          }

          /*
          if ($puedePreguntar) {
            if (($id_checkbox_concepto_1<>"") && ($id_checkbox_concepto_2=="")){
              $SQL_universoCarreras = $SQL_universoCarreras.$sql_concepto_1;
            }
            if (($id_checkbox_concepto_1=="") && ($id_checkbox_concepto_2<>"")){
              $SQL_universoCarreras = $SQL_universoCarreras.$sql_concepto_2;
              
            }
            if (($id_checkbox_concepto_1<>"") && ($id_checkbox_concepto_2<>"")){
              $SQL_universoCarreras = $SQL_universoCarreras.$sql_concepto_1_2;    
            }  
          }
*/
          if ($tipo_carrera == "1") {
            $SQL_universoCarreras = $SQL_universoCarreras.$sql_concepto_1;
          }
          if ($tipo_carrera == "2") {
            $SQL_universoCarreras = $SQL_universoCarreras.$sql_concepto_2;
          }


          $SQL_universoCarreras = $SQL_universoCarreras.") as a 
        order by 
        nombre_carrera
        ";
/*
        $SQL_totalCarreras = "
        select 
        distinct 
        id_carrera,
        nombre_carrera
          from (
          SELECT    
          distinct
          c.id id_carrera,
          c.nombre nombre_carrera,
          a.cohorte
          FROM      alumnos  AS a
          LEFT JOIN carreras AS c
          ON        c.id=a.carrera_actual
          where  a.cohorte BETWEEN $ano_egreso AND       $ano_egreso_fin";
          if ($filtroIdCarreras <> "") {
            $SQL_totalCarreras = $SQL_totalCarreras." AND       (c.id in ($filtroIdCarreras))";
          }
          if ($regimen <> "") {
            $SQL_totalCarreras = $SQL_totalCarreras." AND       (c.regimen = '$regimen')";
          }
          if ($jornada <> "") {
            $SQL_totalCarreras = $SQL_totalCarreras." AND       (a.jornada = '$jornada')";
          }

          if ($puedePreguntar) {
            if (($id_checkbox_concepto_1<>"") && ($id_checkbox_concepto_2=="")){
              $SQL_totalCarreras = $SQL_totalCarreras.$sql_concepto_1;
            }
            if (($id_checkbox_concepto_1=="") && ($id_checkbox_concepto_2<>"")){
              $SQL_totalCarreras = $SQL_totalCarreras.$sql_concepto_2;
              
            }
            if (($id_checkbox_concepto_1<>"") && ($id_checkbox_concepto_2<>"")){
              $SQL_totalCarreras = $SQL_totalCarreras.$sql_concepto_1_2;    
            }
  
          }

          $SQL_totalCarreras = $SQL_totalCarreras.") as a 
        order by 
        nombre_carrera
        ";

        //echo("<br>CARRERAS<br>");
        //echo($SQL_totalCarreras);

        $totalCarreras = consulta_sql($SQL_totalCarreras);
        */

        //echo("<br>CARRERAS<br>");
        //echo($SQL_universoCarreras);

        $universoCarreras = consulta_sql($SQL_universoCarreras);

} else {
  $totalCarreras = null;
  $universoCarreras = null;

}
//echo($SQL_totalCarreras);

//echo("<br>");

/*
$SQL_alumnos = "SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,
                       CASE WHEN estado_tramite IS NOT NULL THEN ae.nombre||'/'||aet.nombre ELSE ae.nombre END AS estado,
                       --CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado
                       '' matriculado
                       ,moroso_financiero,
                       semestre_egreso||'-'||ano_egreso AS periodo_egreso,
                       (ano_egreso-cohorte+1)*2+CASE WHEN semestre_egreso <= semestre_cohorte THEN -1 ELSE 0 END AS duracion
                FROM alumnos AS a
                LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN al_estados AS aet ON aet.id=a.estado_tramite
                --LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
                $condicion
                ORDER BY nombre 
                $limite_reg
                OFFSET $reg_inicio;";
                */
                /*
$SQL_alumnos = "SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
              a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,
              CASE WHEN estado_tramite IS NOT NULL THEN ae.nombre||'/'||aet.nombre ELSE ae.nombre END AS estado,
              '' matriculado
              ,moroso_financiero,
              semestre_egreso||'-'||ano_egreso AS periodo_egreso,
              (ano_egreso-cohorte+1)*2+CASE WHEN semestre_egreso <= semestre_cohorte THEN -1 ELSE 0 END AS duracion
              FROM alumnos AS a
              LEFT JOIN carreras AS c ON c.id=a.carrera_actual
              LEFT JOIN al_estados AS ae ON ae.id=a.estado
              LEFT JOIN al_estados AS aet ON aet.id=a.estado_tramite
              $condicion
              ORDER BY nombre 
              $limite_reg
              OFFSET $reg_inicio;";
*/
//echo($SQL_alumnos);                
//$alumnos = consulta_sql($SQL_alumnos);
/*
$SQL_al_SIES = "SELECT split_part(a.rut,'-',1) AS rut,split_part(a.rut,'-',2) AS dv,
                       translate(upper(split_part(trim(a.apellidos),' ',1)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_pat,
                       translate(upper(split_part(trim(a.apellidos),' ',2)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_mat,
                       translate(upper(a.nombres),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre,
                       upper(a.genero) AS sexo,to_char(a.fec_nac,'DD-MM-YYYY') AS fec_nac,p.nacionalidad,
                       CASE a.jornada WHEN 'D' THEN c.cod_sies_diurno WHEN 'V' THEN c.cod_sies_vespertino END AS cod_carrera_sies,
                       translate(upper(c.nombre_titulo),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre_titulo,
                       translate(upper(c.nombre_grado),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre_grado,
                       1 AS tit_terminal,
                       CASE WHEN a.admision NOT IN (2,20) THEN ((ano_egreso-cohorte)+1)*2+(CASE WHEN semestre_egreso<=semestre_cohorte THEN -1 ELSE 0 END)-($SQL_al_presente) ELSE 0 END AS semestres_susp,
                       CASE WHEN a.admision NOT IN (2,20) THEN a.cohorte ELSE 9999 END AS ano_ing_carrera,a.semestre_cohorte AS sem_ing_carrera,
                       a.cohorte,a.semestre_cohorte,a.ano_egreso,a.semestre_egreso,c.nombre AS nombre_carrera,a.jornada
                FROM alumnos AS a
                LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                LEFT JOIN pais     AS p ON p.localizacion=a.nacionalidad
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN al_estados AS aet ON aet.id=a.estado_tramite
                LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
                $condicion
                ORDER BY c.alias,a.jornada,a.apellidos,a.nombres"; 
                */
//$SQL_tabla_completa_SIES = "COPY ($SQL_al_SIES) to stdout WITH CSV HEADER";

/*
$enlace_nav = "$enlbase=$modulo"
            . "&mes_cohorte=$mes_cohorte"
            . "&semestre_cohorte=$semestre_cohorte"
            . "&cohorte=$cohorte"
            . "&estado=$estado"
            . "&moroso_financiero=$moroso_financiero"
            . "&admision=$admision"            
            . "&matriculado=$matriculado"
            . "&id_carrera=$id_carrera"
            . "&jornada=$jornada"
            . "&regimen=$regimen"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";
*/
/*
if (count($alumnos) > 0) {
	$SQL_total_alumnos =  "SELECT count(a.id) AS total_alumnos 
	                       FROM alumnos AS a 
	                       LEFT JOIN carreras AS c ON c.id=a.carrera_actual 
	                       LEFT JOIN al_estados AS ae ON ae.id=a.estado
	                       LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
	                       $condicion";
	$total_alumnos = consulta_sql($SQL_total_alumnos);
	$tot_reg = $total_alumnos[0]['total_alumnos'];
	
	//$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}
*/

if (count($alumnos) == 1 && $texto_buscar <> "") {
	echo(js("window.location='$enlbase=ver_alumno&id_alumno={$alumnos[0]['id']}&rut={$alumnos[0]['rut']}';"));
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_al_estados = "SELECT id,nombre FROM al_estados WHERE nombre NOT IN ('Moroso') ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

$cohortes = $anos;
/*
$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));
*/
$REGIMENES = consulta_sql("SELECT * FROM regimenes where id in ('PRE','PRE-D','POST-G','POST-GD')");
$AREA_CONOCIMIENTOS = consulta_sql("select id, glosa nombre from area_conocimiento order by glosa");
$TIPOS_CARRERAS = consulta_sql("
select id, nombre from (
  select 1 id, 'Carrera Pregrado 2 a 3 años' nombre
  union
  select 2 id, 'Carrera Pregrado 4 a 5 años' nombre
  ) as A
  order by nombre;
");
/*
$APROB_ANT = array(array("id" => 1, "nombre" => 'Mala (0%)'),
                   array("id" => 2, "nombre" => 'Regular (1% ~ 39%)'),
                   array("id" => 3, "nombre" => 'Buena (40% ~ 100%)'));
*/
                   /*
$SQL_anos_egresos = "SELECT DISTINCT ON (ano_egreso) ano_egreso AS id,ano_egreso AS nombre 
                     FROM alumnos AS a
                     LEFT JOIN al_estados AS ae ON ae.id=a.estado
                     WHERE $cond_base
                     and ano_egreso is not null 
                     ORDER BY id DESC";
*/
/*
$SQL_anos_egresos = "SELECT DISTINCT ON (ano_egreso) ano_egreso AS id,ano_egreso AS nombre 
                     FROM alumnos AS a
                     LEFT JOIN al_estados AS ae ON ae.id=a.estado
                     WHERE 
                         ae.nombre='Egresado'
                     and ano_egreso is not null 
                     ORDER BY id DESC";
*/

/*
$SQL_anos_egresos = "
SELECT distinct ano_egreso AS id,
ano_egreso AS nombre 
FROM alumnos AS a
LEFT JOIN al_estados AS ae ON ae.id=a.estado
WHERE 
 ae.nombre='Egresado'
and ano_egreso is not null 
union
SELECT distinct date_part('year',a.fecha_titulacion) id ,date_part('year',a.fecha_titulacion) AS nombre 
FROM alumnos a, al_estados ae
where --a.fecha_titulacion is not null
ae.id=a.estado
and ae.nombre = 'Titulado'
ORDER BY nombre DESC
";
*/

$SQL_anos_egresos = "
SELECT distinct cohorte AS id,
cohorte AS nombre 
FROM alumnos AS a
WHERE 
 cohorte is not null 
 ORDER BY nombre DESC";
 /*
union
SELECT distinct cohorte id ,cohorte AS nombre 
FROM alumnos a
ORDER BY nombre DESC
";
*/


//echo($SQL_anos_egresos);


$anos_egresos = consulta_sql($SQL_anos_egresos);
$anos_egresos_fin = consulta_sql($SQL_anos_egresos);
//$anos_egresos = array_merge(array(array('id'=>-2,'nombre'=>"Otro")),$anos_egresos);

$id_sesion = "SIES_".$_SESSION['usuario']."_".$modulo."_".session_id();
//$boton_tabla_completa_SIES = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa SIES</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
//file_put_contents($nombre_arch,$SQL_tabla_completa_SIES);

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
<!--        
        <td class="celdaFiltro">
          Cohorte: <br>
<?php //if ($regimen <> "PRE") { ?>          
          <select class="filtro" name="mes_cohorte" onChange="submitform();">
            <option value="0">-- mes --</option>
            <?php //echo(select($meses_fn,$mes_cohorte)); ?>    
          </select>
          -
<?php //} ?>
          <select class="filtro" name="semestre_cohorte" onChange="submitform();">
            <option value="0"></option>
            <?php //echo(select($SEMESTRES_COHORTES,$semestre_cohorte)); ?>    
          </select>
          -
          <select class="filtro" name="cohorte" onChange="submitform();">
            <option value="0">Todas</option>
            <?php //echo(select($cohortes,$cohorte)); ?>    
          </select>
        </td>
-->
        <td class="celdaFiltro">
          Año : <br>
          <select class="filtro" name="ano_egreso" onChange="submitform();">
            <!--<option value="-1">Todos</option>-->
            <?php echo(select($anos_egresos,$ano_egreso)); ?>
          </select>
          <select class="filtro" name="ano_egreso_fin" onChange="submitform();">
            <!--<option value="-1">Todos</option>-->
            <?php echo(select($anos_egresos_fin,$ano_egreso_fin)); ?>
          </select>



          
          <!--
          <?php if ($ano_egreso > 0) { ?>
          <select class="filtro" name="semestre_egreso" onChange="submitform();">
            <option value="-1">- Semestre --</option>
            <?php echo(select($semestres_egreso,$semestre_egreso)); ?>
          </select>
          <?php } ?>
          <?php if ($ano_egreso == -2) { ?>
          <input type="date" placeholder="Fec. ini" name="fec_ini_egreso" value="<?php echo($fec_ini_egreso); ?>" size="10" class="boton" style='font-size: 9pt'>
          <input type="date" placeholder="Fec. fin" name="fec_fin_egreso" value="<?php echo($fec_fin_egreso); ?>" size="10" class="boton" style='font-size: 9pt'>
          <script>document.getElementById("fec_ini_egreso").focus();</script>
          <input type='submit' name='buscar' value='Buscar' style='font-size: 9pt'>
          <?php } ?>
          -->
        </td>
        <!--
        <td class="celdaFiltro">
          Moroso: <br>
          <select class="filtro" name="moroso_financiero" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php //echo(select($sino,$moroso_financiero)); ?>
          </select>
        </td>
          -->
          <!--
        <td class="celdaFiltro">
          Admisión: <br>
          <select class="filtro" name="admision" onChange="submitform();">
            <option value="">Todos</option>
            <?php //echo(select($ADMISION,$admision)); ?>
          </select>
        </td>
          -->
          <!--
        <td class="celdaFiltro">
          Matriculado: <br>
          <select class="filtro" name="matriculado" onChange="submitform();">
            <option value="a">Todos</option>
            <?php //echo(select($sino,$matriculado)); ?>
          </select>
        </td>
          -->
          <!--
        <td class="celdaFiltro">
          Carrera/Programa:<br>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php //echo(select($carreras,$id_carrera)); ?>
          </select>
        </td>
          -->
          
        <td class="celdaFiltro">
          Jornada:<br>
          <select class="filtro" name="jornada" onChange="submitform();">
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
          

        <td class="celdaFiltro">
          Régimen: <br>
          <select class="filtro" name="regimen" onChange="submitform();">
            <!--<option value="t">Todos</option>-->
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Área conocimiento: <br>
          <select class="filtro" name="id_area_conocimiento" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($AREA_CONOCIMIENTOS,$id_area_conocimiento)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Tipo Carreras: <br>
          <select class="filtro" name="tipo_carrera" onChange="submitform();">
            <option value="">Seleccione</option>
            <?php echo(select($TIPOS_CARRERAS,$tipo_carrera)); ?>
          </select>
        </td>

<!--        <td class="celdaFiltro">
          Tasa de Aprobación Anterior: <br>
          <select class="filtro" name="aprob_ant" onChange="submitform();">
            <option value="t">Todos</option>
            <?php //echo(select($APROB_ANT,$aprob_ant)); ?>
          </select>
        </td> -->
      </tr>
    </table>




<!--<input type='hidden' id='id_nivelesSeleccionados' name='id_nivelesSeleccionados'>-->
<!--<input type='hidden' id='maxNiveles' value=$niveles>-->

<br>
<?php
/*
<strong>Seleccione : </strong><br><br>
<?php
  $select_concepto_1 = "";
  $select_concepto_2 = "";
  $select_concepto_3 = "";
  $select_concepto_4 = "";
  $select_concepto_5 = "";
  $select_concepto_6 = "";
  $select_concepto_7 = "";
      if ($id_checkbox_concepto_1 <> "") {
        $select_concepto_1 = "checked";
      }
      if ($id_checkbox_concepto_2 <> "") {
        $select_concepto_2 = "checked";
      }
      if ($id_checkbox_concepto_3 <> "") {
        $select_concepto_3 = "checked";
      }
      if ($id_checkbox_concepto_4 <> "") {
        $select_concepto_4 = "checked";
      }
      if ($id_checkbox_concepto_5 <> "") {
        $select_concepto_5 = "checked";
      }
      if ($id_checkbox_concepto_6 <> "") {
        $select_concepto_6 = "checked";
      }
      if ($id_checkbox_concepto_7 <> "") {
        $select_concepto_7 = "checked";
      }
    

?>
<input type='checkbox' id='id_checkbox_concepto_1' name='id_checkbox_concepto_1' <?php echo($select_concepto_1);?> onclick=submitform()><?php echo($nombreConcepto1); ?><br>
<input type='checkbox' id='id_checkbox_concepto_2' name='id_checkbox_concepto_2' <?php echo($select_concepto_2);?> onclick=submitform()><?php echo($nombreConcepto2); ?><br>
<!--<input type='checkbox' id='id_checkbox_concepto_3' name='id_checkbox_concepto_3' <?php echo($select_concepto_3);?> onclick=submitform()><?php echo($nombreConcepto3); ?><br>-->
<!--<input type='checkbox' id='id_checkbox_concepto_4' name='id_checkbox_concepto_4' <?php echo($select_concepto_4);?> onclick=submitform()><?php echo($nombreConcepto4); ?><br>-->
<!--<input type='checkbox' id='id_checkbox_concepto_5' name='id_checkbox_concepto_5' <?php echo($select_concepto_5);?> onclick=submitform()><?php echo($nombreConcepto5); ?><br>-->
<input type='checkbox' id='id_checkbox_concepto_6' name='id_checkbox_concepto_6' <?php echo($select_concepto_6);?> onclick=submitform()><?php echo($nombreConcepto6); ?><br>
<!--<input type='checkbox' id='id_checkbox_concepto_7' name='id_checkbox_concepto_7' <?php echo($select_concepto_7);?> onclick=submitform()>TODAS <br>-->


<br><br>
*/
?>
<?php //hidden
        echo("<input type='hidden' id='id_carrerasSeleccionados' name='id_carrerasSeleccionados' value='$id_carrerasSeleccionados '>");
      if (count($universoCarreras) > 0) {
        echo("<strong>Seleccione una o más carreras.</strong>");
      } else {
        $id_carrerasSeleccionados = "";
        echo("<strong>No existen carreras para el periodo seleccionado.</strong>");
      }
      
      echo("<br>");
      for ($z=0;$z<count($universoCarreras);$z++) {

        $nombreCarrera = $universoCarreras[$z]['nombre_carrera']; 
        $idCarrera = $universoCarreras[$z]['id_carrera']; 
        //echo("antes de llamar : $id_carrerasSeleccionados");
        $carreraFueSeleccionada = carreraHaSidoSeleccionada($id_carrerasSeleccionados, $idCarrera);
        $seleccionada = "";
        if ($carreraFueSeleccionada) {
          $seleccionada = "checked";
        }
        //echo("<br><input type='checkbox' id='id_checkbox_$idCarrera' name='id_checkbox_$idCarrera' $seleccionada onclick=presionaCheck($idCarrera)>$nombreCarrera");
        echo("<br><input type='checkbox' id='id_checkbox_$idCarrera' $seleccionada onclick=presionaCheck($idCarrera)>$nombreCarrera");
        
        
/*
        echo("<tr class='filaTituloTabla'>");              
        $idCarrera = $totalCarreras[$z]['id_carrera'];
        //$nombreCarrera = $totalCarreras[$z]['nombre_carrera']."(".$totalCarreras[$z]['alias_carrera'].")"; 
        $nombreCarrera = $totalCarreras[$z]['nombre_carrera']; 
        //$totalCarreras[0]['alias_carrera']
        echo("<td class='textoTabla'>$nombreCarrera</td>");
        $HTML_anos = "";
      
        for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
          $valor =  cuentaEgresados($x, $idCarrera, $regimen);
          $HTML_anos = $HTML_anos."<td class='textoTabla' style='text-align:right'>$valor</td>";
        }        
        echo($HTML_anos);
  */      
    } 
    //echo("<br><input type='checkbox' id='id_checkbox_0' name='id_checkbox_0' onclick=presionaCheck(0)>TODAS");
    echo("<br><br>")
?>


    





<?php if (count($universoCarreras) > 0) { ?>
<input type='submit' name='ejecutar' value='Ejecutar' style='font-size: 9pt'>
<?php } ?>
<!--
<input type='submit' name='institucional1' value='Institucional 1' style='font-size: 9pt'>
<input type='submit' name='institucional2' value='Institucional 2' style='font-size: 9pt'>
  -->
    <!--
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Buscar por ID, RUT o nombre:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="60" id="texto_buscar" class='boton'>
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo(" <input type='submit' name='buscar' value='Vaciar'>");
          	};
          ?>          <script>document.getElementById("texto_buscar").focus();</script>
        </td>
        <td class="celdaFiltro">
          Acciones:<br>
          <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=candidatos_egreso&regimen=$regimen"); ?>' class='boton'>Procesar y detectar Candidatos</a>
        </td>
      </tr>
    </table>
          -->
</div>
<!--NUEVA PARTE-->

<!--INSTITUCIONAL -->
<?php
/*
<!--
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
      <tr class='filaTituloTabla'>
        <td class='tituloTabla'>Nro.Egresados por año</td>
        <?php
            $HTML_anos = "";
            for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
                $HTML_anos = $HTML_anos."<td class='tituloTabla'>$x</td>";
            }        
            echo($HTML_anos);
        ?>
      </tr>
      <tr class='filaTituloTabla'>
      <td class='tituloTabla'>Institucional</td>
      <?php
              $HTML_anos = "";
              for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
                  $valor =  cuentaEgresados($x, "", $regimen);
                  $HTML_anos = $HTML_anos."<td class='tituloTabla'>$valor</td>";
              }        
              echo($HTML_anos);
  
      ?>
      </tr>

</table>
            -->
*/          ?>
<!-- FIN INSTITUCIONAL -->
<!-- CARRERAS-->
<?php
if ($ejecutar <> "") {
?>
        <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
              <tr class='filaTituloTabla'>
                <td class='tituloTabla' style='text-align:left'>Tasa Egresos Institucional 1 (todos) </td>
                <?php
                    $HTML_anos = "";
                    for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
                        $HTML_anos = $HTML_anos."<td class='tituloTabla'>$x</td>";
                    }        
                    echo($HTML_anos);
                ?>
              </tr>
              <tr class='filaTituloTabla'>
              <td class='tituloTabla' style='text-align:left'>Institucional</td>
              <?php
                      $HTML_anos = "";
                      if ($tipo_carrera == "1"){
                        $concepto = "1";
                      }
                      if ($tipo_carrera=="2"){
                        $concepto = "2";
                        
                      }

//echo("filtroIdcarreras = ".$filtroIdCarreras);
//if ($filtroIdCarreras<>"") {
//
//}
                      $totalCarreras = obtieneUniversoCarrerasPorConcepto_uno($ano_egreso, $ano_egreso_fin, $filtroIdCarreras, $regimen, $jornada,  $concepto, $id_area_conocimiento);
                      for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
                        //CAMBIO
                          //$valor =  cuentaEgresados($x, "", $regimen);
                          $valor =  cuentaEgresadosInstitucional_uno($totalCarreras, $x, $regimen, $jornada, $id_area_conocimiento, $concepto);
                          //FIN CAMBIO
                          $HTML_anos = $HTML_anos."<td class='textoTabla' style='text-align:right'>$valor</td>";
                      }        
                      echo($HTML_anos);
          
              ?>
              </tr>
        <!---------------------------------------------------------------------------------------------------->
        <?php
          if ($ejecutar <> "") {
            if ($tipo_carrera == "1") {
              $totalCarreras = obtieneUniversoCarrerasPorConcepto_uno($ano_egreso, $ano_egreso_fin, $filtroIdCarreras, $regimen, $jornada,  "1", $id_area_conocimiento);
              muestraConceptoInstitucional_uno($nombreConcepto1, $ano_egreso, $ano_egreso_fin, $totalCarreras, $regimen, $jornada, $id_area_conocimiento);
            }
            if ($tipo_carrera == "2") {
              $totalCarreras = obtieneUniversoCarrerasPorConcepto_uno($ano_egreso, $ano_egreso_fin, $filtroIdCarreras, $regimen, $jornada,  "2", $id_area_conocimiento);
              muestraConceptoInstitucional_uno($nombreConcepto2, $ano_egreso, $ano_egreso_fin, $totalCarreras, $regimen, $jornada, $id_area_conocimiento);
            }
          }     
        ?>      
        </table>
<?php } ?>        
<br>


<?php
if ($ejecutar <> "") {
?>
        <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
              <tr class='filaTituloTabla'>
                <td class='tituloTabla' style='text-align:left'>Tasa Egresos Institucional 2 (1er Sem)</td>
                <?php
                    $HTML_anos = "";
                    for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
                        $HTML_anos = $HTML_anos."<td class='tituloTabla'>$x</td>";
                    }        
                    echo($HTML_anos);
                ?>
              </tr>
              <tr class='filaTituloTabla'>
              <td class='tituloTabla' style='text-align:left'>Institucional</td>
              <?php
                      $HTML_anos = "";
                      if ($tipo_carrera == "1"){
                        $concepto = "1";
                      }
                      if ($tipo_carrera=="2"){
                        $concepto = "2";
                        
                      }

//echo("filtroIdcarreras = ".$filtroIdCarreras);
//if ($filtroIdCarreras<>"") {
//
//}
                      $totalCarreras = obtieneUniversoCarrerasPorConcepto_dos($ano_egreso, $ano_egreso_fin, $filtroIdCarreras, $regimen, $jornada,  $concepto, $id_area_conocimiento);
                      for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
                        //CAMBIO
                          //$valor =  cuentaEgresados($x, "", $regimen);
                          $valor =  cuentaEgresadosInstitucional_dos($totalCarreras, $x, $regimen, $jornada, $id_area_conocimiento, $concepto);
                          //FIN CAMBIO
                          $HTML_anos = $HTML_anos."<td class='textoTabla' style='text-align:right'>$valor</td>";
                      }        
                      echo($HTML_anos);
          
              ?>
              </tr>
        <!---------------------------------------------------------------------------------------------------->
        <?php
          if ($ejecutar <> "") {
            if ($tipo_carrera == "1") {
              $totalCarreras = obtieneUniversoCarrerasPorConcepto_dos($ano_egreso, $ano_egreso_fin, $filtroIdCarreras, $regimen, $jornada,  "1", $id_area_conocimiento);
              muestraConceptoInstitucional_dos($nombreConcepto1, $ano_egreso, $ano_egreso_fin, $totalCarreras, $regimen, $jornada, $id_area_conocimiento);
            }
            if ($tipo_carrera == "2") {
              $totalCarreras = obtieneUniversoCarrerasPorConcepto_dos($ano_egreso, $ano_egreso_fin, $filtroIdCarreras, $regimen, $jornada,  "2", $id_area_conocimiento);
              muestraConceptoInstitucional_dos($nombreConcepto2, $ano_egreso, $ano_egreso_fin, $totalCarreras, $regimen, $jornada, $id_area_conocimiento);
            }
          }     
        ?>      
        </table>
<?php } ?>        


<!--FIN NUEVA PARTE-->
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <!--
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="3">
      Mostrando <b><?php echo($tot_reg); ?></b> alumno(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="5">
      <?php //echo($HTML_paginador); ?>
      <?php //echo($boton_tabla_completa_SIES); ?>
    </td>
  </tr>
          -->


<!--
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Cohorte</td>
    <td class='tituloTabla'>Periodo<br>Egreso</td>
    <td class='tituloTabla'>Duración</td> 
    <td class='tituloTabla'>Mat?</td>
  </tr>
    -->
<?php
/*
	$HTML_alumnos = "";
	if (count($alumnos) > 0) {
		for ($x=0;$x<count($alumnos);$x++) {
			extract($alumnos[$x]);
			
			
			$enl = "$enlbase_sm=editar_alumno_egreso&id_alumno=$id&rut=$rut";
			$enlace = "a class='enlitem' href='$enl' title='Ver ficha de Egreso'";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			if (!empty($duracion)) { $duracion .= " semestres"; }

			$enl_id = "$enlbase_sm=ver_alumno&id_alumno=$id&rut=$rut";			
			$id = "<a href='$enl_id' id='sgu_fancybox' class='enlaces' title='Ver ficha de estudiante'>$id</a>";
			
			$nombre = "<a class='enlaces' href='$enl' title='Ver/Editar ficha de Titulación' id='sgu_fancybox_small'>$nombre</a>";
			
			$HTML_alumnos .= "  <tr class='filaTabla'>\n"
			               . "    <td class='textoTabla'>$id</td>\n"
			               . "    <td class='textoTabla'>$rut</td>\n"
			               . "    <td class='textoTabla'>$nombre</td>\n"
			               . "    <td class='textoTabla'>$carrera</td>\n"
			               . "    <td class='textoTabla'>$cohorte $mes_cohorte</td>\n"
			               . "    <td class='textoTabla'>$periodo_egreso</td>\n"
			               . "    <td class='textoTabla' align='right'>$duracion</td>\n"
			               . "    <td class='textoTabla'>$matriculado</td>\n"
			               . "  </tr>\n";
		}
	} else {
		$HTML_alumnos = "  <tr>"
		              . "    <td class='textoTabla' colspan='8'>"
		              . "      No hay registros para los criterios de búsqueda/selección"
		              . "    </td>\n"
		              . "  </tr>";
	}
	echo($HTML_alumnos);
  */
?>
</table><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <!--<input type="button" name="volver" value="Volver" onClick="window.location='https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/etit_egresos_titulaciones';">-->
      <input type="button" name="volver" value="Volver" onClick="window.location='<?php echo($enlbase); ?>=etit_egresos_titulaciones';">

    </td>
  </tr>
</table><br>
  </form>

<!-- Fin: <?php echo($modulo); ?> -->


<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: true,
		'titleShow'         : false,
		'titlePosition'     : 'inside',
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 1000,
		'height'			: 550,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: true,
		'titleShow'         : false,
		'titlePosition'     : 'inside',
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 600,
		'height'			: 550,
		'maxHeight'			: 550,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});

 

});


</script>

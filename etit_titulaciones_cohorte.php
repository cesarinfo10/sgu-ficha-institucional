<?php
//titulaciones
function cuentaEgresadosAcumulados($ano_buscar, $id_carrera, $regimen) {
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
        WHERE     
               a.cohorte <= $ano_buscar
               and ale.nombre in ('Licenciado', 'Titulado')
               ";

        if ($regimen <> "") {
          $ss = $ss."          and c.regimen = '$regimen'";
        }
$ss = $ss."    ) as a";


//echo("<br>ACUMULADO<br>");
//echo("<br>".$ss."<br>");

  $sqlCuenta     = consulta_sql($ss);
  extract($sqlCuenta[0]);
  return $cuenta;

}
function cuentaEgresados($ano_buscar, $id_carrera, $regimen) {
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
          and ale.nombre in ('Licenciado', 'Titulado')
          ";
          if ($regimen <> "") {
            $ss = $ss."          and c.regimen = '$regimen'";
          }
          
          if ($id_carrera <> "") {
            $ss = $ss." and c.id = $id_carrera";
          }
  $ss = $ss.") as a     ";

//echo("<br>EGRESADO<br>");
//echo("<br>".$ss."<br>");
  
//echo("<br>".$ss."<br>");

  $sqlCuenta     = consulta_sql($ss);
  extract($sqlCuenta[0]);
  return $cuenta;
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
$regimen           = "PRE"; //$_REQUEST['regimen'];
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
//if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($_REQUEST['aprob_ant'])) { $aprob_ant = 't'; }
if (empty($cond_base)) { 
//  $cond_base = "ae.nombre='Egresado'"; 
}

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
  if ($regimen <> "") {
    $SQL_totalCarreras = $SQL_totalCarreras." AND       (c.regimen = '$regimen')";
  }
  $SQL_totalCarreras = $SQL_totalCarreras.") as a 
order by 
nombre_carrera
";

//echo("<br>CARRERAS<br>");
//echo($SQL_totalCarreras);


$totalCarreras = consulta_sql($SQL_totalCarreras);
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
/*$REGIMENES = consulta_sql("SELECT * FROM regimenes");*/
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
          Cohorte de Ingreso: <br>
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
          <!--
        <td class="celdaFiltro">
          Jornada:<br>
          <select class="filtro" name="jornada" onChange="submitform();">
            <option value="">Ambas</option>
            <?php //echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
          -->
          <!--
        <td class="celdaFiltro">
          Régimen: <br>
          <select class="filtro" name="regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php //echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
          -->
<!--        <td class="celdaFiltro">
          Tasa de Aprobación Anterior: <br>
          <select class="filtro" name="aprob_ant" onChange="submitform();">
            <option value="t">Todos</option>
            <?php //echo(select($APROB_ANT,$aprob_ant)); ?>
          </select>
        </td> -->
      </tr>
    </table>
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
<!-- FIN INSTITUCIONAL -->
<!-- CARRERAS-->
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
      <tr class='filaTituloTabla'>
        <td class='tituloTabla' style='text-align:left'>Nro.Egresados por año</td>
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
              for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
                  $valor =  cuentaEgresados($x, "", $regimen);
                  $HTML_anos = $HTML_anos."<td class='textoTabla' style='text-align:right'>$valor</td>";
              }        
              echo($HTML_anos);
  
      ?>
      </tr>
<!---------------------------------------------------------------------------------------------------->
      <tr class='filaTituloTabla'>
        <td class='tituloTabla' style='text-align:left'>Carrera Pregrado.</td>
        <?php
            $HTML_anos = "";
            for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
                //$HTML_anos = $HTML_anos."<td class='tituloTabla'>$x</td>";
                $HTML_anos = $HTML_anos."<td class='tituloTabla'></td>";
            }        
            echo($HTML_anos);
        ?>
      </tr>
      <?php
      
      for ($z=0;$z<count($totalCarreras);$z++) {
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
          
      } 
      ?>
      <!---------------------------------------------------------------------------------------------------->
      <tr class='filaTituloTabla'>
      <td class='tituloTabla' style='text-align:left'>Acumulados</td>
      <?php
              $HTML_anos = "";
              for ($x=$ano_egreso;$x<=$ano_egreso_fin;$x++) {
                  $valor =  cuentaEgresadosAcumulados($x, "", $regimen);
                  $HTML_anos = $HTML_anos."<td class='textoTabla' style='text-align:right'>$valor</td>";
              }        
              echo($HTML_anos);
  
      ?>
      </tr>

</table>
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
      <!--<input type="button" name="volver" value="Volver" onClick="window.location='https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/etit_egresos_titulaciones';"> -->
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

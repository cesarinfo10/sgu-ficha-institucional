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
function obtienePOrcentajeTOTAL_metodologia_COLUMNA($arrTotales, $col) {
	$porcentaje = 0;
	$suma = 0;
	foreach ($arrTotales as $arr) {
		$y++;
		//echo("======>".$arrTotales[$y][$col]);
		//echo("<br>");	
		$suma = $suma + $arrTotales[$y][$col];									
	}

	$porcentaje = $suma / $y;
	//echo("======>porcentaje(".$col.") = ".$suma."/".$y."=".$porcentaje);
	$porcentaje = number_format($porcentaje, 2);
	return $porcentaje;

}
function obtienePorcentajeCalificacionesAlDia($ano, $semestre, $id_carrera, $regimen, $tolerancia, $campo, $fec_ini_asist, $fec_fin_asist) {
	$porcentaje = 0;
//$tolerancia = 7;
	if ($tolerancia == 0) {
		$tolerancia = 0;
	}
	$SS = "
	select (sum(porcentaje_sum_porcentaje) / count(*)) porcentaje_carrera FROM (
		SELECT   vc.id_carrera id_carrera,
				 vc.id         id_curso,
				 (
					sum (
					 case when 
					 (
								SELECT count(*)
								FROM   cargas_academicas c
								WHERE  c.id_curso = vc.id 
					 
					 ) > 0 then 
					 (
						 (
						 SELECT count(*)
						 FROM   cargas_academicas b
						 WHERE  b.id_curso = vc.id
						 and 	b.$campo between '$fec_ini_asist'::date and ('$fec_fin_asist'::date + INTERVAL '$tolerancia day') 
						  )
									   /
					   (
							  SELECT count(*)
							  FROM   cargas_academicas c
							  WHERE  c.id_curso = vc.id 
						) * 100 
					 
					 )
					 else 0
					 end
					 )			  
		   ) as porcentaje_sum_porcentaje
		FROM     vista_cursos vc
		WHERE    vc.ano = $ano
		AND      vc.semestre = $semestre
		AND      vc.id_carrera = $id_carrera
		AND
				 (
						SELECT c.regimen
						FROM   carreras c
						WHERE  c.id = $id_carrera) = '$regimen'
		GROUP BY vc.id_carrera,
				 vc.id ) AS a
";
/*	   


	select 
	(sum(porcentaje_sum_porcentaje)
	/
	count(*)) porcentaje_carrera
	from (	
		select 
		vc.id_carrera id_carrera,
		vc.id id_curso,
		sum(
			(
				select count(*) from cargas_academicas b
				where 
				b.id_curso = vc.id 
				and b.$campo between '$fec_ini_asist'::date and ('$fec_fin_asist'::date + INTERVAL '$tolerancia day') 
			)
			/
			(
				select count(*) from cargas_academicas c where c.id_curso = vc.id 
			)
			* 100) 
			/
			(
				count(*)
			)
			porcentaje_sum_porcentaje
			from vista_cursos vc
			where vc.ano = $ano
			and vc.semestre = $semestre
			and vc.id_carrera = $id_carrera
			and (select c.regimen from carreras c where c.id = $id_carrera) = '$regimen'
			group by vc.id_carrera, vc.id
	) as a	
	";
*/	
//echo("<br>".$SS);
	$ffPorc = consulta_sql($SS);
	$porcentaje = $ffPorc[0]['porcentaje_carrera'];
	return $porcentaje;
}
function obtienePorcentajeMetodologia($arrResultadoMetodologias, $nombreCarreraBuscar, $nombreMetodologiaBuscar) {
	$porcentaje = 0;
	foreach ($arrResultadoMetodologias as $myArr) {
		$nombre_carrera = $myArr->nombreCarrera;
		$nombreMetodologia = $myArr->nombreMetodologia;
		if (($nombre_carrera == $nombreCarreraBuscar) && ($nombreMetodologia == $nombreMetodologiaBuscar)) {
			$porcentaje = $myArr->porcentaje;
			break;
		}		
	}
	return $porcentaje;
}
function obtieneMaxCursosCarrera($arrClaseCarrerasCursos, $nombreCarreraParam) {
	$cuentaCursos = 0;
	foreach ($arrClaseCarrerasCursos as $myArr) {
		$nombre_carrera = $myArr->nombreCarrera;
		$idCurso = $myArr->idCurso;
		//echo("<br>***obtieneMaxCursosCarrera = nombre_carrera = ".$nombre_carrera.", idCurso = ".$idCurso);
		//$idCurso = $myArr->idCurso;
		//$idMetodologia = $myArr->idMetodologia;
		//$porcentaje = $myArr->porcentaje;
		if ($nombre_carrera == $nombreCarreraParam) {
			$cuentaCursos++;
		}		
	}
	//echo("<br>".$nombre_carrera." ... ".$idCurso." ... ".$idMetodologia." ... ".$porcentaje);
	return $cuentaCursos;
}
$arrTotales = array();
class ResultadoMetologias {
	public $nombreCarrera;
	public $nombreMetodologia;
	public $porcentaje;
}
$arrResultadoMetodologias = array();
class ClaseCarreras
{
//	public $idCarrera;
	public $nombreCarrera;
	public $carrera_nombre_largo;

}
$arrClaseCarreras = array();

class ClaseCarrerasCursos
{
		public $nombreCarrera;
		public $idCurso;
	
}
$arrClaseCarrerasCursos = array();

class MetologiasCurso
{
	public $nombre_carrera;
    public $idCurso;
	public $idMetodologia;
	public $porcentaje;
}
$arrMetodologiasCurso = array();

class Metologias
{
    public $id;
    public $nombre;
}
$arrMetodologias = array();

class General
{
	public $id_carrera;
    public $carrera;
	public $carrera_nombre_largo;
    public $asistencia;
	public $av_utiliza;
	public $calif_solemne1;
	public $calif_solemne2;
	public $calif_catedra;
	public $calif_recuperativa;
	public $calif_nota_final;
}
$arrGeneral = array();

class NumeroSesiones
{
    public $carrera;
	public $carrera_nombre_largo;
    public $nroSesiones;
}
$arrNroSesiones = array();

include("validar_modulo.php");

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$ids_carreras = $_SESSION['ids_carreras'];
$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

//$parametro_lista_id_cursos   = $_REQUEST['parametro_lista_id_cursos'];

$texto_buscar    = $_REQUEST['texto_buscar'];
//$tolerancia    = $_REQUEST['tolerancia'];
$buscar          = $_REQUEST['buscar'];
$id_carrera      = $_REQUEST['id_carrera'];
$id_escuela      = $_REQUEST['id_escuela'];
$ano             = $_REQUEST['ano'];
$semestre        = $_REQUEST['semestre'];
$jornada         = $_REQUEST['jornada'];
$regimen         = $_REQUEST['regimen'];
$seccion         = $_REQUEST['seccion'];
$cerrado         = $_REQUEST['cerrado'];
$ayudantia       = $_REQUEST['ayudantia'];
$recep_acta      = $_REQUEST['recep_acta'];
$recep_acta_comp = $_REQUEST['recep_acta_comp'];
$dia             = $_REQUEST['dia'];
$fec_ini_asist   = $_REQUEST['fec_ini_asist'];
$fec_fin_asist   = $_REQUEST['fec_fin_asist'];
$id_asignatura   = $_REQUEST['id_asignatura'];
$id_docente   = $_REQUEST['id_docente'];
//$nombre_docente   = $_REQUEST['nombre_docente'];
$id_tolerancia   = $_REQUEST['id_tolerancia'];
//$id_tipo_grafico_carrera = $_REQUEST['id_tipo_grafico_carrera'];
$titulo  = $_REQUEST['titulo'];
$filtros = $_REQUEST['filtros'];

if ($id_carrera <> "") {
	$id_tipo_grafico_carrera = "TORTA";
} else {
	$id_tipo_grafico_carrera = "BARRA";
}
/*
echo("<br>id_escuela = ".$id_escuela);
echo("<br>id_carrera = ".$id_carrera);
echo("<br>id_asignatura = ".$id_asignatura);
echo("<br>id_docente = ".$id_docente);
echo("<br>id_tolerancia = ".$id_tolerancia);
echo("<br>id_tipo_grafico_carrera = ".$id_tipo_grafico_carrera);
*/
if ($id_escuela == "") {
	$id_carrera = "";
	$id_asignatura = "";
	$id_docengte = "";
}


if ($_REQUEST['ano'] == "") { $ano = $ANO; }
if ($_REQUEST['semestre'] == "") { $semestre = $SEMESTRE; }
//if (empty($_REQUEST['cerrado'])) { $cerrado = "f"; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }

if ($dia > 0 && $buscar == "") { $fec_fin_asist = $fec_ini_asist = date("Y-m-d",strtotime("last Sunday +$dia day")); }

if ($dia == -1 && $buscar == "") { $_REQUEST['fec_ini_asist'] = $_REQUEST['fec_fin_asist'] = ""; }

if ($_REQUEST['fec_ini_asist'] == "") { $fec_ini_asist = ($SEMESTRE == 1) ? date("Y-m-d",$Fec_Ini_Sem1) : date("Y-m-d",$Fec_Ini_Sem2); }
if ($_REQUEST['fec_fin_asist'] == "") { $fec_fin_asist = ($SEMESTRE == 1) ? date("Y-m-d",$Fec_Fin_Sem1) : date("Y-m-d",$Fec_Fin_Sem2); }

$condiciones = "";
$texto_buscar = "";

if ($texto_buscar <> "" &&  $buscar == "Buscar") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar       = explode(" ",$texto_buscar_regexp);
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condiciones   .= " AND (lower(asignatura) ~* '$cadena_buscada' OR "
					   .  "      cod_asignatura ~* '$cadena_buscada' OR "
					   .  "      text(c.id) ~* '$cadena_buscada' OR "
					   .  "      lower(profesor) ~* '$cadena_buscada') ";
	}
	$id_carrera = $ano = $semestre = $jornada = $regimen = null;
} else {
	$texto_buscar = "";
}
if ($id_asignatura <> "") {
	$condiciones   .= " AND cod_asignatura = '$id_asignatura'";
}
if ($id_docente <> "") {
	$condiciones   .= " AND lower(profesor) = '$id_docente'";
}


if (!empty($id_carrera)) { $condiciones .= " AND id_carrera=$id_carrera "; }

if ($ano > 0) {	$condiciones .= " AND c.ano=$ano "; }

if ($semestre > -1) { $condiciones .= " AND c.semestre=$semestre "; }

if ($jornada == 'D') { $condiciones .= " AND c.seccion BETWEEN 1 AND 4 "; }
elseif ($jornada == 'V') { $condiciones .= " AND c.seccion BETWEEN 5 AND 9 "; }

if ($seccion > 0) { $condiciones .= " AND c.seccion=$seccion "; }
//if ($seccion == "") { $condiciones .= " AND c.seccion <> 9 "; }

if ($cerrado == "t") { $condiciones .= " AND c.cerrado "; } 
elseif ($cerrado == "f") {  $condiciones .= " AND NOT c.cerrado "; } 

if ($ayudantia == "t")     { $condiciones .= " AND c.ayudantia "; }      
elseif ($ayudantia == "f") { $condiciones .= " AND NOT c.ayudantia "; }

if ($recep_acta == "t")     { $condiciones .= " AND c.recep_acta "; }      
elseif ($recep_acta == "f") { $condiciones .= " AND NOT c.recep_acta "; }

if ($recep_acta_comp == "t") { $condiciones .= " AND c.recep_acta_comp "; } 
elseif ($recep_acta_comp == "f") { $condiciones .= " AND NOT c.recep_acta_comp "; }  

if ($regimen <> "" && $regimen <> "t") { $condiciones .= " AND (car.regimen = '$regimen') "; }

if ($dia > 0) { $condiciones .= " AND $dia IN (c.dia1,c.dia2,c.dia3) "; }

if ($ids_carreras <> "") { $condiciones .= " AND id_carrera IN ($ids_carreras) "; }

if ($id_escuela <> "") { $condiciones .= " AND car.id_escuela = ".$id_escuela; }

//$SQL_cant_s1    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne1)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
//$SQL_cant_nc    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(nota_catedra)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
//$SQL_cant_s2    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne2)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
//$SQL_cant_nf    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(id_estado)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion)) and id_estado NOT IN (6,10)";
//$SQL_cant_clases = "SELECT count(ap.id) FROM asist_profesores AS ap WHERE ap.id_curso=vista_cursos.id AND asiste='p'";
//$SQL_cursos      = "                      ($SQL_cant_clases)*2 AS cant_horas";
$SQL_cal         = "SELECT CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' WHEN c.seccion=9 THEN 'NC' ELSE 'NO' END AS cal FROM calendarizaciones WHERE id_curso=c.id";
//$SQL_fusiones   = "SELECT char_comma_sum(vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura) AS asig_fusionadas FROM vista_gestion_cursos AS vc LEFT JOIN cursos AS ca USING (id) WHERE ca.id_fusion = c.id";

//$SQL_cant_sesiones = "SELECT count(id) AS cant_sesiones FROM cursos_sesiones WHERE id_curso=c.id";

$SQL_cursos_sesiones = "SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '$fec_ini_asist'::date AND '$fec_fin_asist'::date";
$SQL_asist = "SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN ($SQL_cursos_sesiones) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12)) ";

$SQL_asist_presentes = $SQL_asist . " AND presente";
$SQL_asist_ausentes  = $SQL_asist . " AND NOT presente";

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

/*
$SQL_prog_cursos = "SELECT c.id,cod_asignatura||'-'||c.seccion||' '||asignatura AS asignatura,profesor,rut,u.categorizacion,cant_alumnos_asist(c.id),ga.nombre AS grado_academico,
                           $ANO-(SELECT min(ano) FROM cursos WHERE id_profesor=u.id)+1 AS antiguedad,
                           ($SQL_cant_sesiones) AS cant_sesiones,
                           c.semestre||'-'||c.ano AS periodo,sesion1,sesion2,sesion3,($SQL_cal) AS calendarizado,
                           to_char(c.fec_ini,'TMDay DD-tmMon-YYYY') AS fec_ini,to_char(c.fec_fin,'TMDay DD-tmMon-YYYY') AS fec_fin,c.seccion,
                           ($SQL_fusiones) AS asig_fusionadas,
                           CASE WHEN ayudantia THEN 'Si' ELSE 'No' END AS ayudantia
                    FROM vista_gestion_cursos AS vc
                    LEFT JOIN cursos AS c USING (id)
                    LEFT JOIN carreras AS car ON car.id=vc.id_carrera
                    LEFT JOIN usuarios AS u ON u.id=vc.id_profesor
                    LEFT JOIN grado_acad AS ga ON ga.id=u.grado_academico
                    WHERE id_fusion IS NULL $condiciones 
                    ORDER BY c.ano DESC, c.semestre DESC, vc.carrera, c.seccion, cod_asignatura ";
*/
$SQL_cursos = "SELECT 
                      c.id
					  ,cod_asignatura||'-'||c.seccion AS cod_asignatura
					  , asignatura
					  --,c.semestre||'-'||c.ano AS periodo
					  --,c.id_profesor
                      --,upper(u.apellido) as apellidos
					  --,initcap(u.nombre) AS nombres
					  --,vc.carrera
					  --,sesion1
					  --,sesion2
					  --,sesion3
                      ,cantidad_alumnos(c.id) AS cant_alumnos
					  --,cant_alumnos_asist(c.id) AS al_asist
					  ,c.cerrado,
                      --($SQL_cant_s1) AS s1, 
					  --($SQL_cant_nc) AS nc, 
					  --($SQL_cant_s2) AS s2, 
					  --($SQL_cant_nf) AS nf, 
					  --($SQL_cal) AS cal,
                      to_char(c.fec_ini,'TMDay DD-tmMon-YYYY') AS fec_ini,
					  to_char(c.fec_fin,'TMDay DD-tmMon-YYYY') AS fec_fin,
					  c.seccion,
                      ($SQL_asist_presentes) AS asist_presentes,
					  --($SQL_asist_ausentes) AS asist_ausentes,
cantidad_sesiones_curso(c.id,'$fec_ini_asist','$fec_fin_asist') AS cant_sesiones,
--CASE WHEN cant_alumnos_asist(c.id) > 0 AND cantidad_sesiones_curso(c.id,'$fec_ini_asist','$fec_fin_asist') > 0 AND ($SQL_asist) > 0
--	THEN round((($SQL_asist_presentes)::numeric*100/($SQL_asist))) ELSE 0 
--END AS tasa_presentes,
0 tasa_presentes,
                      --CASE WHEN cant_alumnos_asist(c.id) > 0 AND cantidad_sesiones_curso(c.id,'$fec_ini_asist','$fec_fin_asist') > 0 AND ($SQL_asist) > 0
                      --     THEN round((($SQL_asist_ausentes)::numeric*100/($SQL_asist))) ELSE 0 
                      --END AS tasa_ausentes,
					  --c.cod_google_classroom,
                      --($SQL_fusiones) AS asig_fusionadas,
					  car.id as id_carrera,
					  car.alias nombre_carrera,
					  car.nombre nombre_largo
               FROM vista_gestion_cursos AS vc
               LEFT JOIN cursos AS c USING (id)
               LEFT JOIN carreras AS car ON car.id=vc.id_carrera
               --LEFT JOIN usuarios AS u ON u.id=vc.id_profesor
               WHERE id_fusion IS NULL $condiciones    
			   and cantidad_alumnos(c.id) > 0            
			   ORDER BY car.nombre";
//$SQL_tabla_completa = "COPY ($SQL_cursos) to stdout WITH CSV HEADER";
//$SQL_tabla_completa_pc = "COPY ($SQL_prog_cursos) to stdout WITH CSV HEADER";
//$SQL_cursos .= "$limite_reg OFFSET $reg_inicio";
echo("<!-- $SQL_cursos -->");
$cursos = consulta_sql($SQL_cursos);
//echo($SQL_cursos);
if (count($cursos) > 0) {
	$SQL_cursos2 = "SELECT count(c.id) AS cant_cursos FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id) LEFT JOIN carreras AS car ON car.id=vc.id_carrera LEFT JOIN usuarios AS u ON u.id=vc.id_profesor WHERE id_fusion IS NULL $condiciones;";
	$cursos2 = consulta_sql($SQL_cursos2);
	$tot_reg = $cursos2[0]['cant_cursos'];
	$enlace_nav = "$enlbase=$modulo"
	            . "&id_carrera=$id_carrera"
	            . "&ano=$ano"
	            . "&semestre=$semestre"
	            . "&jornada=$jornada"
	            . "&regimen=$regimen"
	            . "&seccion=$seccion"
	            . "&cerrado=$cerrado"
	            . "&recep_acta=$recep_acta"
	            . "&recep_acta_comp=$recep_acta_comp"
	            . "&dia=$dia"
	            . "&texto_buscar=$texto_buscar"
	            . "&buscar=$buscar"
	            . "&r_inicio";
	//$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

/*
if (count($cursos) == 1 && $texto_buscar <> "") {
	echo(js("window.location='$enlbase=ver_curso&id_curso={$cursos[0]['id']}';"));
}*/
$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }

if ($id_escuela <> "") {
	$sql_cond_escuela = " and id_escuela = ".$id_escuela;
} else {
	$sql_cond_escuela = "";
}

$carreras = consulta_sql("SELECT id,nombre FROM carreras $cond_carreras $sql_cond_escuela ORDER BY nombre;");


$SQL_asignaturas = "SELECT codigo id, concat(codigo,' ',nombre) nombre
                    FROM vista_asignaturas
					where id_carrera = $id_carrera
					and id_escuela = $id_escuela
					";
$asignaturas = consulta_sql($SQL_asignaturas);					
/*
$SQL_docentes = "SELECT distinct profesor id,profesor nombre
                    FROM vista_asignaturas
					where id_carrera = $id_carrera
					and id_escuela = $id_escuela
					and profesor <> ''
					order by profesor
					";
*/

//$SQL_docentes = "select distinct id_profesor as id, profesor from vista_cursos where id_carrera = $id_carrera
//				order by profesor";

if ($jornada == 'D') { 
	$ccJs =  " AND seccion BETWEEN 1 AND 4 "; 
}
elseif ($jornada == 'V') 
{ 
$ccJs = " AND seccion BETWEEN 5 AND 9 "; 
}

$SQL_docentes = "select distinct profesor as id, profesor nombre from vista_cursos 
			where id_carrera = $id_carrera 
			and ano=$ano
			and semestre = $semestre
			$ccJs
			order by profesor";

$docentes = consulta_sql($SQL_docentes);		
//echo("<br>".$SQL_docentes);			


$estado_cursos = array(array("id"=>"f","nombre"=>"Abierto"),
                       array("id"=>"t","nombre"=>"Cerrado"));
                       
$boton_horarios = "";
if ($semestre>0 && $ano>0) {
	$boton_horarios = "<a href='$enlbase=cursos_horarios&semestre=$semestre&ano=$ano&id_carrera=$id_carrera&jornada=$jornada' class='boton'>
	                     Ver filtrado
	                   </a>";
}

$SECCIONES = consulta_sql("SELECT DISTINCT ON (seccion) seccion AS id,seccion AS nombre FROM cursos");

$_SESSION['enlace_volver'] = "$enlbase=$modulo"
                           . "&id_carrera=$id_carrera"
                           . "&ano=$ano"
                           . "&semestre=$semestre"
                           . "&jornada=$jornada"
                           . "&regimen=$regimen"
                           . "&seccion=$seccion"
                           . "&cerrado=$cerrado"
                           . "&recep_acta=$recep_acta"
                           . "&recep_acta_comp=$recep_acta_comp"
                           . "&dia=$dia"
                           . "&texto_buscar=$texto_buscar"
                           . "&buscar=$buscar"
                           . "&r_inicio=$reg_inicio";
                           
$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$boton_tabla_completa_pc = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=pc_$id_sesion');\" class='boton'><small>T. Comp. Prog. Cursos</small></a>";
//$nombre_arch = "sql-fulltables/$id_sesion.sql";
//$nombre_arch_pc = "sql-fulltables/pc_$id_sesion.sql";
//file_put_contents($nombre_arch,$SQL_tabla_completa);
//file_put_contents($nombre_arch_pc,$SQL_tabla_completa_pc);

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<?php if ($titulo<>"no") { ?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<?php } ?>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">


<?php if ($filtros<>"no") { ?>
<div class="texto" style="margin-top: 5px">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Año:<br>
          <select class='filtro' name="ano"  onChange="submitform();">
            <!--<option value="0">Todos</option>-->
            <?php echo(select($anos,$ano)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Semestre:<br>
          <select class='filtro' name="semestre" onChange="submitform();">
            <!--<option value="-1">Todos</option>-->
            <?php echo(select($semestres,$semestre)); ?>
          </select>
        </td>
		
        <td class="celdaFiltro">
          Estado:<br>
          <select class='filtro' name="cerrado" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($estado_cursos,$cerrado)); ?>
          </select>
        </td>

		<!--
        <td class="celdaFiltro">
          Carrera/Programa:<br>
          <select class='filtro' name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php //echo(select($carreras,$id_carrera)); ?>
          </select>
        </td>
		-->
        <td class="celdaFiltro">      
          Jornada:<br>
          <select class='filtro' name="jornada" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
		
        <td class="celdaFiltro">      
          Sección:<br>
          <select class='filtro' name="seccion" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($SECCIONES,$seccion)); ?>
          </select>
        </td>
		
        <td class="celdaFiltro">
          <div align='left'>Régimen:</div>
          <select class="filtro" name="regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Ayudantía:<br>
          <select class='filtro' name="ayudantia" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($sino,$ayudantia)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Rango de fecha para Asistencia Estudiantes:<br>
          Desde:<input type="date" name="fec_ini_asist" value="<?php echo($fec_ini_asist); ?>" class="boton" onBlur="formulario.fec_fin_asist.value=this.value;">
          Hasta:<input type="date" name="fec_fin_asist" value="<?php echo($fec_fin_asist); ?>" class="boton">
        </td>
		<td>
				<input type='submit' name='buscar' value='Buscar' class="boton">          
		</td>
		

</table>
<table>

		<!--
        <td class="celdaFiltro">
          Acta recep.:<br>
          <select class='filtro' name="recep_acta" onChange="submitform();">
            <option value="">Todas</option>
            <?php //echo(select($sino,$recep_acta)); ?>
          </select>
        </td>
			-->
		
		<!--
        <td class="celdaFiltro">      
          Acta Comp. recep.:<br>
          <select class='filtro' name="recep_acta_comp" onChange="submitform();">
            <option value="">Todas</option>
            <?php //echo(select($sino,$recep_acta_comp)); ?>
          </select>
        </td>
		-->
      </tr>
	  <?php
				$escuelas = consulta_sql("select id, nombre from escuelas order by nombre");	  


				if ($id_escuela == "") {
					$disabledAtribute_carrera = "disabled";
					$disabledAtribute_asignatura = "disabled";
					$disabledAtribute_docente = "disabled";
				} else {
					$disabledAtribute_carrera = "";
					$disabledAtribute_asignatura = "disabled";
					$disabledAtribute_docente = "disabled";

				}
				if ($id_carrera == "") {
					$disabledAtribute_asignatura = "disabled";
					$disabledAtribute_docente = "disabled";
				} else {
					$disabledAtribute_asignatura = "";
					$disabledAtribute_docente = "";
				}
/*				
echo("<br>disabledAtribute_carrera=".$disabledAtribute_carrera);
echo("<br>disabledAtribute_asignatura=".$disabledAtribute_asignatura);
echo("<br>disabledAtribute_docente=".$disabledAtribute_docente);
*/


	  ?>
	  <tr>
				<td class="celdaFiltro">
					Escuela:<br>
					<select name="id_escuela" id="id_escuela" onChange="submitform(); ">
                        <option value="">-- Seleccione --</option>
                        <?php echo(select($escuelas,$id_escuela)); ?>
                      </select>					
				</td>		  
				<td class="celdaFiltro">
					Carrera/Programa:<br>
				<select class='filtro' id="id_carrera" name="id_carrera" onChange="submitform();" <?php echo($disabledAtribute_carrera); ?>>
				<!--<select class='filtro' id="id_carrera" name="id_carrera" onChange="javascript:return validarCarrera();" <?php //echo($disabledAtribute_carrera); ?>> -->
					<option value="">Todas</option>
					<?php echo(select($carreras,$id_carrera)); ?>
				</select>
				<input type="hidden" name="id_tipo_grafico_carrera" id="id_tipo_grafico_carrera" value="<?php echo($id_tipo_grafico_carrera); ?>" class="boton">
				</td>
				<td class="celdaFiltro">
					Asignatura:<br>
				<select class='filtro' id="id_asignatura" name="id_asignatura" onChange="submitform();" <?php echo($disabledAtribute_asignatura); ?>>
					<option value="">Todas</option>
					<?php echo(select($asignaturas,$id_asignatura)); ?>
				</select>
				</td>
				<td class="celdaFiltro">
					Docente:<br>
				<select class='filtro' id="id_docente" name="id_docente" onChange="submitform();" <?php echo($disabledAtribute_docente); ?>>
					<option value="">Todos</option>
					<?php echo(select($docentes,$id_docente)); ?>
				</select>
				</td>
				<td class="celdaFiltro">
					Tolerancia (días):<br>
					<select name="id_tolerancia" id="id_tolerancia" onChange="submitform();" >
						<option value="">-- Seleccione --</option>
						<?php
						for ($x=1;$x<=31;$x++) {
							$ss = "";
							if ($id_tolerancia <> "") {
								if ($x==$id_tolerancia) {
									$ss = "selected";
								} else {
									$ss = "";
								}		
							} else {
								if ($x == 7) {
									$ss = "selected";
								}
							}
						echo("<option value='$x' $ss>$x</option>");
						}
						?>
					</select>
				</td>
				<td class="celdaFiltro">      
				Día:<br>
				<select class='filtro' name="dia" onChange="submitform();">
					<option value='-1'>Todos</option>
					<?php echo(select($dias_palabra,$dia)); ?>
				</select>
				</td>

	  </tr>
<?php 
		//--- M E T O D O L O G I A S ----------------------------------------------------------------------------------------------------
		$METODOLOGIAS = consulta_sql("SELECT id,nombre FROM vista_metod_clases order by nombre");
		$MAX_METODOLOGIAS = count($METODOLOGIAS);
		$contadorTotalMetofologias = 0;
		$contadorTotalCarreras = 0;
		$contadorTotalCarrerasCursos = 0;
		for ($x=0; $x<count($METODOLOGIAS); $x++) {
			$arrMetodologias[$x] = new Metologias();
			$arrMetodologias[$x]->id = $METODOLOGIAS[$x]['id'];
			$arrMetodologias[$x]->nombre = $METODOLOGIAS[$x]['nombre'];
		}
		//---  F I N   M E T O D O L O G I A S ----------------------------------------------------------------------------------------------------
?>
<!--
	  <tr>
			<td class="celdaFiltro">
				<div align='left'>Metodologías:</div>

				<div id="checkboxes">
					<ul>
						<?php 
						/*
							$cuenta = 0;
							foreach ($arrMetodologias as $myArr) {	
								$id = $myArr->id;
								$nombre = $myArr->nombre;
								$cuenta++;
								echo("<li><input type='checkbox' id='id_incluir_$cuenta'> $nombre</li>");
							}						
							*/
						?>
					</ul>
				</div>		  
			</td>
			<td>
				<input type='submit' name='buscar' value='Buscar' class="botoncito">          
			</td>
	  </tr>
						-->	  
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
		  <!--
        <td class="celdaFiltro">
          Buscar por Código o nombre de asignatura, N° de Acta o nombre del Profesor:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="45" class='boton' id="texto_buscar">
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 

          //	if ($buscar == "Buscar" && $texto_buscar <> "") {
          //		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          //	}
          ?>
          <script>document.getElementById('texto_buscar').focus();</script>
        </td>
		  -->
		
		<!--
        <td class="celdaFiltro">
          Horarios:<br>
          <?php //echo($boton_horarios); ?>
        </td>
		  -->
      </tr>
    </table>
</div>
<?php } ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="display:none;">
<!--<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" >-->
  <tr bgcolor="#F1F9FF">
	  <!--
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> curso(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php //echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
		  -->
	<!--
    <td class="texto" align="right" colspan="8">
      <?php //echo($HTML_paginador); ?>
      <?php //echo($boton_tabla_completa); ?>
      <?php //echo($boton_tabla_completa_pc); ?>
    </td>
		  -->
    <td colspan="3"></td>
  </tr>
  
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'><span style="color: #7F7F7F">N° de<br>Acta</span></td>
	<td class='tituloTabla'>Carrera</td>
    <!--<td class='tituloTabla'>Asignatura</td>-->
    <!--<td class='tituloTabla'>A.A.<br><small>[A.I.]</small></td>-->
    <!--<td class='tituloTabla'>S.R.</td>-->
    <td class='tituloTabla'>Asist.<br>Est.</td>
	<td class='tituloTabla'>av_utiliza</td>
	<td class='tituloTabla'>nroSesiones</td>
	<!--<td class='tituloTabla'>method</td> -->

    <!--<td class='tituloTabla'>Periodo</td>-->
    <!--<td class='tituloTabla'>Profesor</td>-->
    <!--<td class='tituloTabla'>Classroom</td>-->
    <!--<td class='tituloTabla'>Cal</td>-->
	<!--
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>NC</td>
    <td class='tituloTabla'>S2</td>
    <td class='tituloTabla'>NF</td>
	-->
    <!--<td class='tituloTabla'>Horario<br>{sala}</td>-->
  </tr>
		  
<?php
	if (count($cursos) > 0) {
		//$_verde   = "color: #009900; text-align: center";
		//$_naranjo = "color: #FFA500; text-align: center";
		//$_rojo    = "color: #ff0000; text-align: center";
		$corteControlCarrera = "";
		$corteControlNombreCarreraLargo = "";
		$corteControl_id_carrera = "";
		$contador = 0;
		$acum_tasa_presentes = 0;
		$acum_nroSesiones = 0;
		$acum_av_utiliza = 0;
		$myCorteControl = "";
		$colorB = "green";
		$colorA = "white";
		$colorElegido = "";
		$indiceArreglo = 0;

/*		
		//--- M E T O D O L O G I A S ----------------------------------------------------------------------------------------------------
		$METODOLOGIAS = consulta_sql("SELECT id,nombre FROM vista_metod_clases order by nombre");
		$MAX_METODOLOGIAS = count($METODOLOGIAS);
		$contadorTotalMetofologias = 0;
		$contadorTotalCarreras = 0;
		$contadorTotalCarrerasCursos = 0;
		for ($x=0; $x<count($METODOLOGIAS); $x++) {
			$arrMetodologias[$x] = new Metologias();
			$arrMetodologias[$x]->id = $METODOLOGIAS[$x]['id'];
			$arrMetodologias[$x]->nombre = $METODOLOGIAS[$x]['nombre'];
		}
		//---  F I N   M E T O D O L O G I A S ----------------------------------------------------------------------------------------------------
*/		
		$hizoNoma = false;
		for ($x=0; $x<count($cursos); $x++) {
			extract($cursos[$x]);
			
			//--- M E T O D O L O G I A S ----------------------------------------------------------------------------------------------------
				$sqlSesiones = "
							select id as id_curso_sesion
							FROM cursos_sesiones AS cs
							WHERE id_curso IN (
								SELECT vc.id
								FROM vista_cursos AS vc
								LEFT JOIN cursos AS c USING (id)	
								WHERE id_fusion = $id
								union
								select $id as id	
							);			
				";
				
				$sesionesCurso = consulta_sql($sqlSesiones);
				$listaSesionesCurso = "";
				for ($y=0; $y<count($sesionesCurso); $y++) {
					if ($y>0) {
						$listaSesionesCurso = $listaSesionesCurso.",";
					}
					$listaSesionesCurso = $listaSesionesCurso . $sesionesCurso[$y]['id_curso_sesion'];
				}
				if ($listaSesionesCurso <> "") {
					//$sql_method = "select cuenta, porcentaje, metodologia from ( ";
					$sql_method = "select cuenta, (case when contador > 1 then porcentaje/cuenta else porcentaje end) as porcentaje, metodologia from (
						select sum(cuenta) as cuenta , sum(porcentaje) as porcentaje, metodologia, sum(contador) as contador from ( ";
					for ($z=1; $z<=$MAX_METODOLOGIAS; $z++) {
						if ($z > 1) {
							$sql_method = $sql_method." union ";
						}
						/*
						$sql_method = $sql_method."
						SELECT 
						count(metodologias[$z]) as cuenta, (count(metodologias[$z])::NUMERIC / $MAX_METODOLOGIAS) *100 porcentaje, metodologias[$z] as metodologia
						FROM cursos_sesiones
						WHERE id in ($listaSesionesCurso)
						AND id_curso=$id
						group by metodologias[$z]
						";
						*/

						$sql_method = $sql_method."
						SELECT 
						count(metodologias[$z]) as cuenta, (count(metodologias[$z])::NUMERIC / $MAX_METODOLOGIAS) *100 porcentaje, metodologias[$z] as metodologia, 1 contador
						FROM cursos_sesiones
						WHERE id in ($listaSesionesCurso)
						AND id_curso=$id
						group by metodologias[$z]
						";

					}
					//$sql_method = $sql_method.") as a where cuenta > 0";
					$sql_method = $sql_method.") as a where cuenta > 0
												group by metodologia
												) as b";
					
					$obtieneMethod = consulta_sql($sql_method);
					$strObtieneMethod = "<br>cuenta	porcentaje	metodologia";
					$strObtieneMethod2 = "";
					
					for ($w=0; $w<count($obtieneMethod); $w++) {
						$cuenta = $obtieneMethod[$w]['cuenta'];
						$porcentaje = $obtieneMethod[$w]['porcentaje'];
						$metodologia = $obtieneMethod[$w]['metodologia'];
						$strObtieneMethod2 = "<br>".$strObtieneMethod2.$cuenta."	".$porcentaje."	".$metodologia;

						$arrMetodologiasCurso[$contadorTotalMetofologias] = new MetologiasCurso();
						$arrMetodologiasCurso[$contadorTotalMetofologias]->nombre_carrera = $nombre_carrera;
						$arrMetodologiasCurso[$contadorTotalMetofologias]->idCurso = $id;
						$arrMetodologiasCurso[$contadorTotalMetofologias]->idMetodologia = $metodologia;
						$arrMetodologiasCurso[$contadorTotalMetofologias]->porcentaje = $porcentaje;
						$contadorTotalMetofologias++;
			
					}
					$strObtieneMethod = $strObtieneMethod.$strObtieneMethod2;
				} else {
					$sql_method = "SIN REGISTROS";
					$strObtieneMethod = "";
				}

				//$method = consulta_sql($sql_method);
			//---  F I N   M E T O D O L O G I A S ----------------------------------------------------------------------------------------------------



			if ($x==0) {
				$corteControlCarrera = $nombre_carrera;
				$corteControlNombreCarreraLargo = $nombre_largo;
				$corteControl_id_carrera = $id_carrera;

			}
			//$cant_horas = total_horas_control_asist_2011($id);
			//$est_s1 = $est_nc = $est_s2 = $est_rec = "color: #000000";
			
			/*
			if (strlen($asignatura)>30) { $asignatura = mb_substr($asignatura,0,30)."...";}
			if (strlen($profesor)>20)   { $profesor   = mb_substr($profesor,0,20)."...";}
			*/
			if ($asig_fusionadas <> "") { 
				$asig_fusionadas = explode(",",$asig_fusionadas);
				$asig_fusionadas = "<small>Fusionada con:<br>&nbsp;&nbsp;".implode("<br>&nbsp;&nbsp;",$asig_fusionadas)."</small>";
			}
			/*
			if ($s1==100) { $est_s1 = $_verde; } elseif ($s1==0) { $est_s1 = $_rojo; } else { $est_s1 = $_naranjo; }
			if ($nc==100) { $est_nc = $_verde; } elseif ($nc==0) { $est_nc = $_rojo; } else { $est_nc = $_naranjo; }
			if ($s2==100) { $est_s2 = $_verde; } elseif ($s2==0) { $est_s2 = $_rojo; } else { $est_s2 = $_naranjo; }
			if ($nf==100) { $est_nf = $_verde; } elseif ($nf==0) { $est_nf = $_rojo; } else { $est_nf = $_naranjo; }
			*/
			//if ($seccion == 9) { $sesion1 = "<small>Comienzo: $fec_ini</small>"; $sesion2 = "<small>Término: $fec_fin</small>"; }
			
			$bgcolor_cerrado = "";
			if ($cerrado == "t") { $bgcolor_cerrado = "bgcolor='#C0FFC0'"; }

			//if ($cant_sesiones>0 && $cant_alumnos>0) { //NEW
			if ($cant_sesiones>0 && $cant_alumnos>0) { //NEW	
				$tasa_presentes = round(($asist_presentes/$cant_sesiones)*100 / $cant_alumnos,0);
				//$tasa_ausentes  = round(($asist_ausentes/$cant_sesiones)*100 / $cant_alumnos,0);	
 			} else {
				$tasa_presentes = 0;
				//$tasa_ausentes  = 0;
			 }
			 if ($tasa_presentes == "")  {
				$tasa_presentes = 0;
			 }
			//$asistencia = "<span style='color: green'><b> ✓ </b>$tasa_presentes%</span><br><span style='color: red'><b> ✗ </b>$tasa_ausentes%</span>";
			$asistencia = "<span style='color: $colorElegido'><b> ✓ </b>$tasa_presentes%</span>";

//AUDIOVISUAL UTILIZA
$SQL_1 = "
select count(*) as cuenta
FROM cursos_sesiones AS cs
WHERE id_curso IN (
	SELECT vc.id
	FROM vista_cursos AS vc
	LEFT JOIN cursos AS c USING (id)
	WHERE id_fusion = $id
	union
	select $id as id	
	)
";
$fPending = consulta_sql($SQL_1);	
$existen = $fPending[0]['cuenta'];
//$nroSesiones = $existen;

if ($existen > 0) {
	$SQL_av_utiliza = "
	select 				
	(  
		(select count(*) as cuenta
		FROM cursos_sesiones AS cs
		WHERE id_curso IN (
			SELECT vc.id
			FROM vista_cursos AS vc
			LEFT JOIN cursos AS c USING (id)
			WHERE id_fusion = $id
			union
			select $id as id	
		)
		and av_utiliza
	)
	/
	($existen)*100
	) as cuenta_utiliza_audiovisual
	";
	
	$fAvUtiliza = consulta_sql($SQL_av_utiliza);	
	$av_utiliza = $fAvUtiliza[0]['cuenta_utiliza_audiovisual'];
	if ($av_utiliza=="") {
		$av_utiliza = 0;
	}
	
} else {
	$av_utiliza = 0;
}

			if ($nombre_carrera == $corteControlCarrera) {
				$hizoNoma = false;
				$acum_tasa_presentes = $acum_tasa_presentes + $tasa_presentes;
				$nroSesiones = (number_format(($existen / 18), 2) * 100);
				$acum_nroSesiones = $acum_nroSesiones + $nroSesiones;
				$acum_av_utiliza = $acum_av_utiliza + $av_utiliza;
				$contador++;
				$csm = "<br> contador = ".$contador.", acum_tasa_presentes = ".$acum_tasa_presentes.", acum_nroSesiones = ".$acum_nroSesiones."<br>idCurso=".$id;

				$arrClaseCarrerasCursos[$contadorTotalCarrerasCursos] = new ClaseCarrerasCursos();
				$arrClaseCarrerasCursos[$contadorTotalCarrerasCursos]->nombreCarrera = $corteControlCarrera;				
				$arrClaseCarrerasCursos[$contadorTotalCarrerasCursos]->idCurso = $id;
				$contadorTotalCarrerasCursos++;

			} else {
				//hay corte de contro!
				$hizoNoma = true;
				$arrGeneral[$indiceArreglo] = new General();
				$arrGeneral[$indiceArreglo]->id_carrera = $corteControl_id_carrera;
				$arrGeneral[$indiceArreglo]->carrera = $corteControlCarrera;
				$arrGeneral[$indiceArreglo]->carrera_nombre_largo = $corteControlNombreCarreraLargo;
				$arrGeneral[$indiceArreglo]->asistencia = number_format(($acum_tasa_presentes / $contador), 2);//($acum_tasa_presentes / $contador);
				$arrGeneral[$indiceArreglo]->av_utiliza = number_format(($acum_av_utiliza / $contador), 2);//($acum_tasa_presentes / $contador);


				$arrNroSesiones[$indiceArreglo] = new NumeroSesiones();
				$arrNroSesiones[$indiceArreglo]->carrera = $corteControlCarrera;
				$arrNroSesiones[$indiceArreglo]->carrera_nombre_largo = $corteControlNombreCarreraLargo;
				$arrNroSesiones[$indiceArreglo]->nroSesiones = number_format(($acum_nroSesiones / $contador), 2);//($acum_tasa_presentes / $contador);


				$indiceArreglo++;   				

//111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111
				$arrClaseCarreras[$contadorTotalCarreras] = new ClaseCarreras();
				//$arrClaseCarreras[$contadorTotalCarreras]->idCarrera = $id_carrera;
				$arrClaseCarreras[$contadorTotalCarreras]->nombreCarrera = $corteControlCarrera;
				$arrClaseCarreras[$contadorTotalCarreras]->carrera_nombre_largo = $corteControlNombreCarreraLargo;
				$contadorTotalCarreras++;   				
//111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111

				$myCorteControl = "<br>CORTE DE CONTROL ".$corteControlCarrera.", acum_tasa_presentes = ".$acum_tasa_presentes." aum_av_utiliza = ".$acum_av_utiliza.", acum_nroSesiones = ".$acum_nroSesiones.", contador = ".$contador;
				$corteControlCarrera = $nombre_carrera;
				$corteControlNombreCarreraLargo = $nombre_largo;
				$corteControl_id_carrera = $id_carrera;
				$acum_tasa_presentes = $tasa_presentes; //$acum_tasa_presentes = 0;
				$nroSesiones = (number_format(($existen / 18), 2) * 100);
				$acum_nroSesiones = $nroSesiones;				
				$acum_av_utiliza = $av_utiliza;
				$contador = 1;		


//22222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222



				$arrClaseCarrerasCursos[$contadorTotalCarrerasCursos] = new ClaseCarrerasCursos();
				$arrClaseCarrerasCursos[$contadorTotalCarrerasCursos]->nombreCarrera = $corteControlCarrera;
				$arrClaseCarrerasCursos[$contadorTotalCarrerasCursos]->idCurso = $id;
				$contadorTotalCarrerasCursos++;
//22222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222
				

			}

			//$asistencia = "<a href='$enlbase_sm=cursos_libro_clases&id_curso=$id' id='sgu_fancybox' class='enlaces'>$asistencia</a>";
      //PREGUNTAR POR ESTE TROZO DE CODIGO
			 //NEW
					////$asignatura = "<div>$cod_asignatura </div><div>$asignatura</div><div>$asig_fusionadas</div>";
					//$asignatura = "<div>$cod_asignatura </div><div>$asignatura</div>";
    		//FIN NEW

			//$asignatura = "<a href='$enlbase=ver_curso&id_curso=$id' class='enlaces'>$asignatura</a>";
      //$asignatura = "<a href=javascript:mostraVerCursos('$enlbase=ver_curso&id_curso=$id') class='enlaces'>$asignatura</a>";
      //FIN PREGUNTAR
			

			echo("  <tr class='filaTabla' $bgcolor_cerrado>\n");
			echo("    <td class='textoTabla'><span style='color: #7F7F7F'>$id</span></td>");
      		//$lista_id_cursos = $lista_id_cursos.$id.",";
	  		echo("    <td class='textoTabla'>$nombre_carrera <br> $myCorteControl <br> $csm</td>");
			  $myCorteControl = "";
			  $csm = "";

			//echo("    <td class='textoTabla'>$asignatura</td>");
			//echo("    <td class='textoTabla' style='text-align: center'>$al_asist <br><small>[$cant_alumnos]                                                                    
            //                                                        </small></td>");
			//echo("    <td class='textoTabla' style='text-align: center'>$cant_sesiones</td>");
			echo("    <td class='textoTabla' style='text-align: center'>$asistencia</td>");


			echo("    <td class='textoTabla' style='text-align: center'>$av_utiliza%</td>");
			echo("    <td class='textoTabla' style='text-align: center'>$existen / 18 = $nroSesiones%</td>");
			//echo("    <td class='textoTabla' style='text-align: center'>$strObtieneMethod<br>$sql_method%</td>");
			//echo("    <td class='textoTabla'>$periodo</td>");
			//echo("    <td class='textoTabla'><div>$apellidos </div><div>$nombres</div></td>");
			//echo("    <td class='textoTabla'>$cod_google_classroom</td>");
			//echo("    <td class='textoTabla'><span class='$cal'>$cal</span></td>");
			//echo("    <td class='textoTabla' style='$est_s1'><small>$s1%</small></td>");
			//echo("    <td class='textoTabla' style='$est_nc'><small>$nc%</small></td>");
			//echo("    <td class='textoTabla' style='$est_s2'><small>$s2%</small></td>");
			//echo("    <td class='textoTabla' style='$est_nf'><small>$nf%</small></td>");
			//echo("    <td class='textoTabla'><div>$sesion1 </div><div>$sesion2 </div><div>$sesion3</div></td>");
			echo("  </tr>");
		}
		if ($hizoNoma == false) {
			$arrGeneral[$indiceArreglo] = new General();
			$arrGeneral[$indiceArreglo]->id_carrera = $corteControl_id_carrera;
			$arrGeneral[$indiceArreglo]->carrera = $corteControlCarrera;
			$arrGeneral[$indiceArreglo]->carrera_nombre_largo = $corteControlNombreCarreraLargo;
			$arrGeneral[$indiceArreglo]->asistencia = number_format(($acum_tasa_presentes / $contador), 2);//($acum_tasa_presentes / $contador);
			$arrGeneral[$indiceArreglo]->av_utiliza = number_format(($acum_av_utiliza / $contador), 2);//($acum_tasa_presentes / $contador);


			$arrNroSesiones[$indiceArreglo]->carrera = $corteControlCarrera;
			$arrNroSesiones[$indiceArreglo]->carrera_nombre_largo = $corteControlNombreCarreraLargo;
			$arrNroSesiones[$indiceArreglo]->nroSesiones = number_format(($acum_nroSesiones / $contador), 2);//($acum_tasa_presentes / $contador);


			$arrClaseCarreras[$contadorTotalCarreras] = new ClaseCarreras();
			//$arrClaseCarreras[$contadorTotalCarreras]->idCarrera = $id_carrera;
			$arrClaseCarreras[$contadorTotalCarreras]->nombreCarrera = $corteControlCarrera;
			$arrClaseCarreras[$contadorTotalCarreras]->carrera_nombre_largo = $corteControlNombreCarreraLargo;
			//$arrClaseCarrerasCursos[$contadorTotalCarrerasCursos] = new ClaseCarrerasCursos();
			//$arrClaseCarrerasCursos[$contadorTotalCarrerasCursos]->nombreCarrera = $corteControlCarrera;
			//$arrClaseCarrerasCursos[$contadorTotalCarrerasCursos]->idCurso = $id;


		}

    
	} else {
		echo("<td class='textoTabla' colspan='13' align='center'><br>** No hay registros para los criterios de b&uacute;squeda/selección **<br><br></td>\n");
	}
?>
</table>
<?php
if (count($cursos) == 0) {
	echo(msje_js("No se encontraron registros."));	
} else 
{
?>

			<br>
			<table border=0 class="celdaFiltro">
				<tr bgcolor="#F1F9FF" class='filaTituloTabla'>		
					<td class='textoTabla' colspan=2 style='text-align: center'>Asistencia Estudiantil</td>
				</tr>
				<tr>
					<td>
						<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="celdaFiltro">
							<tr bgcolor="#F1F9FF" class='filaTituloTabla'>
								<td class='textoTabla'><small>Carrera</small></td>
								<td class='textoTabla'><small>Asistencia</small></td>
							</tr>
							<?php
									$promedioGeneral = 0;
									$cuentaCasos = 0;
									$sumaAsistencia = 0;
									$acumulados = "";
									if ($arrGeneral != null) {
										//echo("* * * Datos para ".$host.", token : ".$token."\n");
										foreach ($arrGeneral as $myArr) {
											
											if ($cuentaCasos>0) {
												$acumulados = $acumulados.",";	
											}
											$carrera = $myArr->carrera;
											$nombre_carrera_largo = $myArr->carrera_nombre_largo;
											$asistencia = $myArr->asistencia;
											$sumaAsistencia = $sumaAsistencia+$asistencia;

											echo("<tr>");
											echo("    <td class='textoTabla' >".$nombre_carrera_largo."(".$carrera.")</td>");
											echo("    <td class='textoTabla' align='right'>$asistencia%</td>");
								//			echo("<br>".$carrera."...".$asistencia);
											echo("</tr>");
											$acumulados = $acumulados."['".substr($carrera,0,20)."',".$asistencia."]";
											$cuentaCasos++;
										}
									}
									$promedioGeneral = number_format(($sumaAsistencia / $cuentaCasos), 2);
									$datos_grafico_asistencia = $acumulados;
							?>	
							<tr bgcolor="#F1F9FF" class='filaTituloTabla'>
								<td class='textoTabla'><small>Promedio General</small></td>
								<td class='textoTabla'><small><?php echo($promedioGeneral);?>%</small></td>
							</tr>

						</table>	
					</td>
					<td>
						<div style="display:none;" id="log_div_asistencia"></div>
						<?php
			/*			
						if (count($arrGeneral)>0) {
							echo("<a id='download_link_asistencia' href='/' download>download</a>");
						} 
			*/			
						?>

						<a id="download_link_asistencia" href="/" download>download</a>
					</td>
				</tr>
			</table>
			<table style="display:none;" > 
				<!--<tr><td id="datos_grafico_asistencia" name="datos_grafico_asistencia">['car', 7],['jeep', 2],['taxi', 1]</td></tr>-->
				<tr><td id="datos_grafico_asistencia" name="datos_grafico_asistencia"><?php echo($datos_grafico_asistencia); ?></td></tr>      
				
			</table>





			<br>

			<table border=0  class="celdaFiltro">
				<tr bgcolor="#F1F9FF" class='filaTituloTabla'>		
					<td class='textoTabla' colspan=2 style='text-align: center'>Utilización de Recursos Audiovisuales</td>
				</tr>

				<tr>
					<td>
						<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="celdaFiltro">
							<tr bgcolor="#F1F9FF" class='filaTituloTabla'>
								<td class='textoTabla'><small>Carrera</small></td>
								<td class='textoTabla'><small>Audiovisual</small></td>
							</tr>
							<?php
									$promedioGeneral = 0;
									$cuentaCasos = 0;
									$sumaAv_utiliza = 0;
									$acumulados = "";
									if ($arrGeneral != null) {
										//echo("* * * Datos para ".$host.", token : ".$token."\n");
										foreach ($arrGeneral as $myArr) {
											
											if ($cuentaCasos>0) {
												$acumulados = $acumulados.",";	
											}
											$carrera = $myArr->carrera;
											$carrera_nombre_largo = $myArr->carrera_nombre_largo;

											//$av_utiliza = $myArr->av_utiliza;
											//$sumaAv_utiliza = $sumaAv_utiliza+$av_utiliza;



			$myId_carrera = $myArr->id_carrera;

if ($jornada == 'D') { 
		$ccJ =  " AND seccion BETWEEN 1 AND 4 "; 
}
elseif ($jornada == 'V') 
{ 
	$ccJ = " AND seccion BETWEEN 5 AND 9 "; 
}
if ($regimen <> "") {
	$ss_regimen = " and (select regimen from carreras where id = $myId_carrera) = '$regimen'";
} else {
	$ss_regimen = "";
}
			$SQL_ff = "
				select 
				(
					select count(*) from vista_cursos
					where ano = $ano
					and semestre = $semestre
					and id in (select id_curso from cursos_sesiones where av_utiliza)
					and id_carrera = $myId_carrera
					$ss_regimen
					$ccJ
					
				)::float
				/
				(
					select count(*) from vista_cursos
					where ano = $ano
					and semestre = $semestre
					and id_carrera = $myId_carrera
					$ss_regimen
					$ccJ
				)::float
					* 100 porcentaje_av_utiliza
											";
			//echo("<br>$SQL_ff");
			$ffAvUtiliza = consulta_sql($SQL_ff);	
			$av_utiliza = $ffAvUtiliza[0]['porcentaje_av_utiliza'];
										
			$av_utiliza = number_format(($av_utiliza), 2);

			$sumaAv_utiliza = $sumaAv_utiliza+$av_utiliza;

											echo("<tr>");
											echo("    <td class='textoTabla' >".$carrera_nombre_largo."(".$carrera.")</td>");
											echo("    <td class='textoTabla' align='right'>$av_utiliza%</td>");
								//			echo("<br>".$carrera."...".$asistencia);
											echo("</tr>");
											$acumulados = $acumulados."['".substr($carrera,0,20)."',".$av_utiliza."]";
											$cuentaCasos++;
										}
									}
									$promedioGeneral = number_format(($sumaAv_utiliza / $cuentaCasos), 2);
									$datos_grafico_av_utiliza = $acumulados;
							?>	
							<tr bgcolor="#F1F9FF" class='filaTituloTabla'>
								<td class='textoTabla'><small>Promedio General</small></td>
								<td class='textoTabla'><small><?php echo($promedioGeneral);?>%</small></td>
							</tr>

						</table>	
					</td>
					<td>
						<div style="display:none;"  id="log_div_av_utiliza"></div>
						<?php
						/*
						if (count($arrGeneral)>0) {
							echo("<a id='download_link_av_utiliza' href='/' download>download</a>");
						} 
						*/
						?>

						<a id="download_link_av_utiliza" href="/" download>download</a>

					</td>
				</tr>
			</table>
			<table style="display:none;" > 
				<!--<tr><td id="datos_grafico_asistencia" name="datos_grafico_asistencia">['car', 7],['jeep', 2],['taxi', 1]</td></tr>-->
				<tr><td id="datos_grafico_av_utiliza" name="datos_grafico_av_utiliza"><?php echo($datos_grafico_av_utiliza); ?></td></tr>        
			</table>

			<?php 
			//$lista_id_cursos = sacaCommaFinal($lista_id_cursos);

			echo("<br>");
			//echo("<br>arrMetodologias");
			$datos_grafico_metodologia_cabecera = "['Metodologías'],";
			$cuenta = 0;
			foreach ($arrMetodologias as $myArr) {	
				if ($cuenta > 0) {
					$datos_grafico_metodologia_cabecera = $datos_grafico_metodologia_cabecera.",";
				}

				$id = $myArr->id;
				$nombre = $myArr->nombre;
				$datos_grafico_metodologia_cabecera = $datos_grafico_metodologia_cabecera."['".$nombre."']";
			//	echo("<br>".$id." ... ".$nombre);
				$cuenta++;
			}
			/*
			echo("<br>arrMetodologiasCurso");
			foreach ($arrMetodologiasCurso as $myArr) {
				$nombre_carrera = $myArr->nombre_carrera;
				$idCurso = $myArr->idCurso;
				$idMetodologia = $myArr->idMetodologia;

				$porcentaje = $myArr->porcentaje;
				echo("<br>".$nombre_carrera." ... ".$idCurso." ... ".$idMetodologia." ... ".$porcentaje);
			}
			echo("<br>");
			*/

			//echo("<br>CRRERAS CURSOS : ");
			$indiceArreglo = 0;

			$datos_grafico_metodologia = "";
			$cuenta3 = 0;
			foreach ($arrClaseCarreras as $myArr_1) {
				if ($cuenta3 > 0) {
					$datos_grafico_metodologia = $datos_grafico_metodologia.",";
				}

				//$idCarrera = $myArr->idCarrera;
				$nombreCarrera_buscar = $myArr_1->nombreCarrera;
				$maxCursos = obtieneMaxCursosCarrera($arrClaseCarrerasCursos, $nombreCarrera_buscar);
			//	echo("<br>carrera : ".$nombreCarrera_buscar.". cantidad de cursos = ".$maxCursos);
				
				$datos_grafico_metodologia = $datos_grafico_metodologia."['".$nombreCarrera_buscar."',";
				$cuenta2 = 0;
				foreach ($arrMetodologias as $myArr_2) {	
					if ($cuenta2 > 0) {
						$datos_grafico_metodologia = $datos_grafico_metodologia.",";
					}
					$idMetodologia_1 = $myArr_2->id;
					$nombreMetodologia = $myArr_2->nombre;
					//echo("<br>* * * *".$id." ... ".$nombre);
					//por cada metodologìa las cuento!!
					//cuentaMetodologias por carrera
					$porcMetodologia = 0;
			//		echo("<br>metodologia buscar = ".$idMetodologia_1);;
					foreach ($arrMetodologiasCurso as $myArr_3) {
						$nombre_carrera = $myArr_3->nombre_carrera;
						$idCurso = $myArr_3->idCurso;
						$idMetodologia_2 = $myArr_3->idMetodologia;
						
						$porcentaje = $myArr_3->porcentaje;
						
			//			echo("<br>* * * * busca $idMetodologia_1 = $idMetodologia_2");
						if (($idMetodologia_1 == $idMetodologia_2) && ($nombre_carrera == $nombreCarrera_buscar)) {
			//				echo("<br>* * * * * * * * encuentra $idMetodologia_1 = $idMetodologia_2, porcentaje $porcentaje");
							$porcMetodologia = $porcMetodologia + $porcentaje;
						}
					}
					$resultPorcentaje = number_format(($porcMetodologia / $maxCursos), 2);
			//		echo("<br>RESULT PORCENTAJE ".$nombreMetodologia.", porcentaje = ".$resultPorcentaje);
					$datos_grafico_metodologia = $datos_grafico_metodologia.$resultPorcentaje;
					$arrResultadoMetodologias[$indiceArreglo] = new ResultadoMetologias();
					$arrResultadoMetodologias[$indiceArreglo]->nombreCarrera = $nombreCarrera_buscar;
					$arrResultadoMetodologias[$indiceArreglo]->nombreMetodologia = $nombreMetodologia;
					$arrResultadoMetodologias[$indiceArreglo]->porcentaje = $resultPorcentaje;
					$indiceArreglo++;   				

					$cuenta2++;
				}
				$cuenta3++;
				$datos_grafico_metodologia = $datos_grafico_metodologia."]";
				//$datos_grafico_metodologia_piechart = $datos_grafico_metodologia_piechart + "[";
			}



			?>
			<br>
			<table border=0>
					<table border=0 class="celdaFiltro">
						<tr bgcolor="#F1F9FF" class='filaTituloTabla'>		
							<td class='textoTabla' colspan=2 style='text-align: center'>Metodología Activo Participativas</td>
						</tr>

						<tr>
							<td>
								<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="celdaFiltro">
									<tr bgcolor="#F1F9FF" class='filaTituloTabla'>
										<td class='textoTabla'><small>Carrera</small></td>
										<td class='textoTabla'><small>Cursos</small></td>
										<?php
											foreach ($arrMetodologias as $myArr_2) {
												$idMetodologia_1 = $myArr_2->id;
												$nombreMetodologia = $myArr_2->nombre;	
												echo("<td class='textoTabla'><small>$nombreMetodologia</small></td>");
											}
										?>
									</tr>
									<?php
											if ($arrClaseCarreras != null) {
												//echo("* * * Datos para ".$host.", token : ".$token."\n");
												foreach ($arrClaseCarreras as $myArr_1) {
													$nombreCarrera_buscar = $myArr_1->nombreCarrera;
													$carrera_nombre_largo = $myArr_1->carrera_nombre_largo;
													$maxCursos = obtieneMaxCursosCarrera($arrClaseCarrerasCursos, $nombreCarrera_buscar);
													echo("<tr>");
													echo("    <td class='textoTabla' >".$carrera_nombre_largo."(".$nombreCarrera_buscar.")</td>");
													echo("    <td class='textoTabla' align='right'>$maxCursos</td>");
													$datos_grafico_metodologia_piechart = "";
													$indicePIE = 0;
													foreach ($arrMetodologias as $myArr_2) {
														if ($indicePIE>0) {
															$datos_grafico_metodologia_piechart = $datos_grafico_metodologia_piechart.",";
														}
														$idMetodologia_1 = $myArr_2->id;
														$nombreMetodologia = $myArr_2->nombre;	
														$porcentaje = obtienePorcentajeMetodologia($arrResultadoMetodologias, $nombreCarrera_buscar, $nombreMetodologia);
														echo("<td class='textoTabla' align='right'><small>$porcentaje%</small></td>");
														$datos_grafico_metodologia_piechart = $datos_grafico_metodologia_piechart."['".$nombreMetodologia."',".$porcentaje."]";
														$indicePIE = $indicePIE + 1;
													}

													echo("</tr>");							}
											}
											//$datos_grafico_metodologia = "[...nada aun...]";

//T O T A L E S
$col = 0;
foreach ($arrClaseCarreras as $myArr_1) {
	$col++;
	$nombreCarrera_buscar = $myArr_1->nombreCarrera;
	//$carrera_nombre_largo = $myArr_1->carrera_nombre_largo;
	$row = 0;
	foreach ($arrMetodologias as $myArr_2) {
		$row++;
		//$idMetodologia_1 = $myArr_2->id;
		$nombreMetodologia = $myArr_2->nombre;	
		$porcentaje = obtienePorcentajeMetodologia($arrResultadoMetodologias, $nombreCarrera_buscar, $nombreMetodologia);
		$arrTotales[$col][$row] = $porcentaje;
	}
}
/*
echo("<br>max row = ".$row."<br>");
$y = 0;
$x = 1; //<=$row
$porcentajeTotal = obtienePOrcentajeTOTAL_metodologia_COLUMNA($arrTotales, $x);
echo("<br>porcentaje = ".$porcentajeTotal);
echo("<br>he termimado");
*/
echo("<tr bgcolor='#F1F9FF' class='filaTituloTabla'>");
echo("	<td class='textoTabla'><small>Promedio General</small></td>");
for ($x = 0; $x <= $row; $x++) {
	if ($x>0) {
		$porcentajeTotal = obtienePOrcentajeTOTAL_metodologia_COLUMNA($arrTotales, $x);
		//echo("no casho...".$porcentajeTotal);
		echo("	<td class='textoTabla'  align='right'><small>$porcentajeTotal%</small></td>");
	} else {
		echo("	<td class='textoTabla'><small></small></td>");
	}
}
echo("</tr>");

//F I N  T O T A L E S


									?>	
									<!--
									<tr bgcolor="#F1F9FF" class='filaTituloTabla'>
										<td class='textoTabla'><small>Promedio General</small></td>
										<td class='textoTabla'><small><?php echo($promedioGeneral);?>%</small></td>
									</tr>

										-->
								</table>	
							</td>
							<!--
							<td>
								<div style="display:none;"  id="log_div_metodologia"></div>
								<a id="download_link_metodologia" href="/" download>download</a>

							</td>
										-->
						</tr>
					</table>
			
			<table style="display:none;" > 
<!--
<tr><td id="datos_grafico_metodologia_cabecera" name="datos_grafico_metodologia_cabecera">['Carrera'],['Chorizo'],['Foro de discusión'],['Aprendizaje entre pares']</td></tr>        
<tr><td id="datos_grafico_metodologia" name="datos_grafico_metodologia">['Auditoria',43.2, 4.89,23.23],['Ciencia politica',  12.56, 19.34,	24.45],['Derecho',  89.3,43.4,56.76	],['Electivos de formacion general', 12.23, 67.32,87.45],['Ingenieria Comercial', 15.65, 80.12,	24.56]</td></tr>        
-->	 


				<tr><td id="datos_grafico_metodologia_cabecera" name="datos_grafico_metodologia_cabecera"><?php echo($datos_grafico_metodologia_cabecera); ?></td></tr>
				<tr><td id="datos_grafico_metodologia" name="datos_grafico_metodologia"><?php echo($datos_grafico_metodologia); ?></td></tr>        
				<!--<tr><td id="datos_grafico_metodologia_piechart" name="datos_grafico_metodologia_piechart">['Work',     10],['Eat',      20],['Commute',  30],['Watch TV', 30],['Sleep',    10]</td></tr> -->
				<tr><td id="datos_grafico_metodologia_piechart" name="datos_grafico_metodologia_piechart"><?php echo($datos_grafico_metodologia_piechart); ?></td></tr>

			</table>
			<br>
			<div style="display:none;" id="log_div_metodologia"></div>
						<?php
						/*
						if (count($arrGeneral)>0) {
							echo("<a id='download_link_metodologia' href='/' download>download</a>");
						} 
						*/
						?>

			<a id="download_link_metodologia" href="/" download>download</a>


			<br>
			<br>
			<table border=0 class="celdaFiltro">
				<tr bgcolor="#F1F9FF" class='filaTituloTabla'>		
					<td class='textoTabla' colspan=2 style='text-align: center'>Asistencia Docente</td>
				</tr>
				<tr>
					<td>
						<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="celdaFiltro">
							<tr bgcolor="#F1F9FF" class='filaTituloTabla'>
								<td class='textoTabla'><small>Carrera</small></td>
								<td class='textoTabla'><small>Asistencia</small></td>
							</tr>
							<?php
									$promedioGeneral = 0;
									$cuentaCasos = 0;
									$sumaNroSesiones = 0;
									$acumulados = "";
									if ($arrNroSesiones != null) {
										//echo("* * * Datos para ".$host.", token : ".$token."\n");
										foreach ($arrNroSesiones as $myArr) {
											
											if ($cuentaCasos>0) {
												$acumulados = $acumulados.",";	
											}
											$carrera = $myArr->carrera;
											$carrera_nombre_largo = $myArr->carrera_nombre_largo;
											$nroSesiones = $myArr->nroSesiones;
											$sumaNroSesiones = $sumaNroSesiones+$nroSesiones;
											echo("<tr>");
											echo("    <td class='textoTabla' >".$carrera_nombre_largo."(".$carrera.")</td>");
											echo("    <td class='textoTabla' align='right'>$nroSesiones%</td>");
											echo("</tr>");
											$acumulados = $acumulados."['".substr($carrera,0,20)."',".$nroSesiones."]";
											$cuentaCasos++;
										}
									}
									$promedioGeneral = number_format(($sumaNroSesiones / $cuentaCasos), 2);
									$datos_grafico_nrosesiones = $acumulados;
							?>	
							<tr bgcolor="#F1F9FF" class='filaTituloTabla'>
								<td class='textoTabla'><small>Promedio General</small></td>
								<td class='textoTabla'><small><?php echo($promedioGeneral);?>%</small></td>
							</tr>

						</table>	
					</td>
					<td>
				
						<div style="display:none;" id="log_div_nrosesiones"></div>
						<?php
						/*
						if (count($arrGeneral)>0) {
							echo("<a id='download_link_nrosesiones' href='/' download>download</a>");
						} 
						*/
						?>			
						<a id="download_link_nrosesiones" href="/" download>download</a>
					</td>
				</tr>
			</table>
			<table style="display:none;" >  
				<tr><td id="datos_grafico_nrosesiones" name="datos_grafico_nrosesiones"><?php echo($datos_grafico_nrosesiones); ?></td></tr>      	
			</table>


			<br>
			<br>
			<table border=0 class="celdaFiltro">
				<tr bgcolor="#F1F9FF" class='filaTituloTabla'>		
					<td class='textoTabla' colspan=2 style='text-align: center'>Calificaciones al Día</td>
				</tr>
				<tr>
					<td style="text-align:left;vertical-align:top;padding:0">
						<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="celdaFiltro">
							<tr bgcolor="#F1F9FF" class='filaTituloTabla'>
								<td class='textoTabla'><small>Carrera</small></td>
								<td class='textoTabla'><small>1er Control</small></td>
								<td class='textoTabla'><small>Solemne 1</small></td>
								<td class='textoTabla'><small>Solemne 2</small></td>
								
								<!--<td class='textoTabla'><small>%Recup</small></td>-->
								<td class='textoTabla'><small>Nota Final</small></td>
							</tr>
							<?php
									$promedioGeneral_s1 = 0;
									$promedioGeneral_s2 = 0;
									$promedioGeneral_catedra = 0;
									$promedioGeneral_recuperativa = 0;
									$promedioGeneral_nota_final = 0;
									$cuentaCasos = 0;
									$sumaCalificacionesAldia_solemne1 = 0;
									$sumaCalificacionesAldia_solemne2 = 0;
									$sumaCalificacionesAldia_catedra = 0;
									$sumaCalificacionesAldia_recuperativa = 0;
									$sumaCalificacionesAldia_nota_final = 0;
									$acumulados_solemne1 = "";
									$acumulados_solemne2 = "";
									$acumulados_catedra = "";
									$acumulados_recuperativa = "";
									$acumulados_nota_final = "";
									if ($arrGeneral != null) {
										//echo("* * * Datos para ".$host.", token : ".$token."\n");
										foreach ($arrGeneral as $myArr) {
											if ($cuentaCasos>0) {
												$acumulados_solemne1 = $acumulados_solemne1.",";	
												$acumulados_solemne2 = $acumulados_solemne2.",";	
												$acumulados_catedra = $acumulados_catedra.",";	
												$acumulados_recuperativa = $acumulados_recuperativa.",";	
												$acumulados_nota_final = $acumulados_nota_final.",";	
											}
											$carrera = $myArr->carrera;
											$myId_carrera = $myArr->id_carrera;
											$carrera_nombre_largo = $myArr->carrera_nombre_largo;
//			$calif_solemne1 = $myArr->calif_solemne1;
//			$calif_solemne2 = $myArr->calif_solemne2;
//			$calif_catedra = $myArr->calif_catedra;
//			$calif_recuperativa = $myArr->calif_recuperativa;
//			$calif_nota_final = $myArr->calif_nota_final;

			$calif_solemne1 = 0;
			$calif_solemne2 = 0;
			$calif_catedra = 0;
			//$calif_recuperativa = 0;
			$calif_nota_final = 0;

			
			
			$calif_solemne1 = obtienePorcentajeCalificacionesAlDia($ano, $semestre, $myId_carrera, $regimen, $id_tolerancia,'fecha_solemne1', $fec_ini_asist, $fec_fin_asist);
			$calif_solemne2 = obtienePorcentajeCalificacionesAlDia($ano, $semestre, $myId_carrera, $regimen, $id_tolerancia,'fecha_solemne2', $fec_ini_asist, $fec_fin_asist);
			$calif_catedra = obtienePorcentajeCalificacionesAlDia($ano, $semestre, $myId_carrera, $regimen, $id_tolerancia,'fecha_recuperativa', $fec_ini_asist, $fec_fin_asist);
			//$calif_recuperativa = obtienePorcentajeCalificacionesAlDia($ano, $semestre, $myId_carrera, $regimen, $id_tolerancia,'fecha_nota_final', $fec_ini_asist, $fec_fin_asist);
			$calif_nota_final = obtienePorcentajeCalificacionesAlDia($ano, $semestre, $myId_carrera, $regimen, $id_tolerancia,'fecha_catedra', $fec_ini_asist, $fec_fin_asist);

			$calif_solemne1 = number_format($calif_solemne1, 2);
			$calif_solemne2 = number_format($calif_solemne2, 2);
			$calif_catedra = number_format($calif_catedra, 2);
			//$calif_recuperativa = number_format($calif_recuperativa, 2);
			$calif_nota_final = number_format($calif_nota_final, 2);



											$sumaCalificacionesAldia_solemne1 = $sumaCalificacionesAldia_solemne1+$calif_solemne1;
											$sumaCalificacionesAldia_solemne2 = $sumaCalificacionesAldia_solemne2+$calif_solemne2;
											$sumaCalificacionesAldia_catedra = $sumaCalificacionesAldia_catedra+$calif_catedra;
											$sumaCalificacionesAldia_recuperativa = $sumaCalificacionesAldia_recuperativa+$calif_recuperativa;
											$sumaCalificacionesAldia_nota_final = $sumaCalificacionesAldia_nota_final+$calif_nota_final;
											echo("<tr>");
											echo("    <td class='textoTabla' >".$carrera_nombre_largo."(".$carrera.")</td>");
											echo("    <td class='textoTabla'  align='right'>$calif_catedra%</td>");
											echo("    <td class='textoTabla' align='right'>$calif_solemne1%</td>");
											echo("    <td class='textoTabla' align='right'>$calif_solemne2%</td>");
											
											//echo("    <td class='textoTabla' align='right'>$calif_recuperativa%</td>");
											echo("    <td class='textoTabla' align='right'>$calif_nota_final%</td>");
											echo("</tr>");
											$acumulados_solemne1 = $acumulados_solemne1."['".substr($carrera,0,20)."',".$calif_solemne1."]";
											$acumulados_solemne2 = $acumulados_solemne2."['".substr($carrera,0,20)."',".$calif_solemne2."]";
											$acumulados_catedra = $acumulados_catedra."['".substr($carrera,0,20)."',".$calif_catedra."]";
											//$acumulados_recuperativa = $acumulados_recuperativa."['".substr($carrera,0,20)."',".$calif_recuperativa."]";
											$acumulados_nota_final = $acumulados_nota_final."['".substr($carrera,0,20)."',".$calif_nota_final."]";
											$cuentaCasos++;
										}
									}
									$promedioGeneral_s1 = number_format(($sumaCalificacionesAldia_solemne1 / $cuentaCasos), 2);
									$promedioGeneral_s2 = number_format(($sumaCalificacionesAldia_solemne2 / $cuentaCasos), 2);
									$promedioGeneral_catedra = number_format(($sumaCalificacionesAldia_catedra / $cuentaCasos), 2);
									$promedioGeneral_recuperativa = number_format(($sumaCalificacionesAldia_recuperativa / $cuentaCasos), 2);
									$promedioGeneral_nota_final = number_format(($sumaCalificacionesAldia_nota_final / $cuentaCasos), 2);
									$datos_grafico_calificaciones_al_dia_s1 = $acumulados_solemne1;
									$datos_grafico_calificaciones_al_dia_s2 = $acumulados_solemne2;
									$datos_grafico_calificaciones_al_dia_catedra = $acumulados_catedra;
									//$datos_grafico_calificaciones_al_dia_recuperativa = $acumulados_recuperativa;
									$datos_grafico_calificaciones_al_dia_nota_final = $acumulados_nota_final;

									$datos_grafico_calificaciones_promedio_general = "['1er Control',$promedioGeneral_catedra],
																						['Solemne 1',$promedioGeneral_s1],
																						['Solemne 2',$promedioGeneral_s2],
																						['Nota Final',$promedioGeneral_nota_final]
																					";
							?>	
							<tr bgcolor="#F1F9FF" class='filaTituloTabla'>
								<td class='textoTabla'><small>Promedio General</small></td>
								<td class='textoTabla'><small><?php echo($promedioGeneral_catedra);?>%</small></td>
								<td class='textoTabla'><small><?php echo($promedioGeneral_s1);?>%</small></td>
								<td class='textoTabla'><small><?php echo($promedioGeneral_s2);?>%</small></td>
								
								<!--<td class='textoTabla'><small><?php //echo($promedioGeneral_recuperativa);?>%</small></td>-->
								<td class='textoTabla'><small><?php echo($promedioGeneral_nota_final);?>%</small></td>
							</tr>
							

						</table>	
					</td>
					<td>	
						<table>
							<tr>
								<td>
									<div style="display:none;" id="log_div_calificaciones_promedio_general"></div>
									<a id="download_link_calificaciones_promedio_general" href="/" download>download</a>
								</td>
							</tr>					
<!--
							<tr>
								<td>
									<div style="display:none;" id="log_div_calificaciones_al_dia_catedra"></div>
									<a id="download_link_calificaciones_al_dia_catedra" href="/" download>download</a>

								</td>
							</tr>					
-->
<!--
							<tr>
								<td>
									<div style="display:none;" id="log_div_calificaciones_al_dia_s1"></div>
									<a id="download_link_calificaciones_al_dia_s1" href="/" download>download</a>

								</td>
							</tr>					
-->							
<!--
							<tr>
								<td>
									<div style="display:none;" id="log_div_calificaciones_al_dia_s2"></div>
									<a id="download_link_calificaciones_al_dia_s2" href="/" download>download</a>

								</td>
							</tr>					
-->							
							<!--
							<tr>
								<td>
									<div style="display:none;" id="log_div_calificaciones_al_dia_recuperativa"></div>
									<?php
									?>						

									<a id="download_link_calificaciones_al_dia_recuperativa" href="/" download>download</a>

								</td>
							</tr>					
								-->
<!--								
							<tr>
								<td>
									<div style="display:none;" id="log_div_calificaciones_al_dia_nota_final"></div>
									<a id="download_link_calificaciones_al_dia_nota_final" href="/" download>download</a>

								</td>
							</tr>					
-->
						</table>
					</td>
				</tr>
			</table>
			<table style="display:none;" > 
<!--			<table> -->
			    <tr><td id="datos_grafico_calificaciones_promedio_general" name="datos_grafico_calificaciones_promedio_general"><?php echo($datos_grafico_calificaciones_promedio_general); ?></td></tr>
				<tr><td id="datos_grafico_calificaciones_al_dia_s1" name="datos_grafico_calificaciones_al_dia_s1"><?php echo($datos_grafico_calificaciones_al_dia_s1); ?></td></tr>
<!--<tr><td id="datos_grafico_calificaciones_al_dia_s1" name="datos_grafico_calificaciones_al_dia_s1">['DE ',53.94],['SELLO ',36.80],['IC ',53.67],['LED ',74.00],['LTS ',11.64],['PS ',56.00],['TS ',67.96]</td></tr>-->
<!--<tr><td id="datos_grafico_calificaciones_al_dia_s1" name="datos_grafico_calificaciones_al_dia_s1">['DE ',3.45],['SELLO ',3.45],['IC ',3.45],['LED ',3.45],['LTS ',3.45],['PS ',3.45],['TS ',3.45]</td></tr>-->
				
      
				<tr><td id="datos_grafico_calificaciones_al_dia_s2" name="datos_grafico_calificaciones_al_dia_s2"><?php echo($datos_grafico_calificaciones_al_dia_s2); ?></td></tr>      
<!--<tr><td id="datos_grafico_calificaciones_al_dia_s2" name="datos_grafico_calificaciones_al_dia_s2">['DE ',3.15],['SELLO ',3.45],['IC ',3.45],['LED ',3.45],['LTS ',3.45],['PS ',3.45],['TS ',3.45]</td></tr>				-->
				<tr><td id="datos_grafico_calificaciones_al_dia_catedra" name="datos_grafico_calificaciones_al_dia_catedra"><?php echo($datos_grafico_calificaciones_al_dia_catedra); ?></td></tr>      
<!--<tr><td id="datos_grafico_calificaciones_al_dia_recuperativa" name="datos_grafico_calificaciones_al_dia_recuperativa"><?php //echo($datos_grafico_calificaciones_al_dia_recuperativa); ?></td></tr>      -->
				<tr><td id="datos_grafico_calificaciones_al_dia_nota_final" name="datos_grafico_calificaciones_al_dia_nota_final"><?php echo($datos_grafico_calificaciones_al_dia_nota_final); ?></td></tr>      
			</table>
<?php } ?>


<!--<input type='hidden' name='lista_id_cursos' value='<?php //echo($lista_id_cursos); ?>'; id='lista_id_cursos'>-->

</form>
<!--
<div class="texto">
  A.A.: Alumnos Asistentes (no se cuentan los suspendidos/retirados/abandonados)<br>
  A.I.: Alumnos Inscritos<br>
  S.R.: Sesiones Realizadas (asistencia del docente al curso)<br>
  A.E.: Asistencia Estudiantes (sesiones según rango de fechas)<br>
  Cal.: Calendarización del curso<br>
  S1, NC, S2: Indican estado ingreso de estas calificaciones<br>
  NF: Indica estado de cierre de cursos (cálculo de Notas Finales y Situaciones)
</div>
-->
<!-- Fin: <?php echo($modulo); ?> -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
		var myTipoGrafico = $("#id_tipo_grafico_carrera").val();

		google.charts.load('current', {'packages':['corechart']});
		google.charts.setOnLoadCallback(drawAsistencia);
		google.charts.setOnLoadCallback(drawAudiovisual);
		//alert("myTipoGrafico = "+myTipoGrafico)
		if (myTipoGrafico == 'BARRA') {
			google.charts.setOnLoadCallback(drawMetodologias);		
		} else {
			google.charts.setOnLoadCallback(drawMetodologiasPieChart);		
		}
		
		
		google.charts.setOnLoadCallback(drawNroSesiones);
		google.charts.setOnLoadCallback(drawCalificaciones_promedio_general);

		google.charts.setOnLoadCallback(drawCalificaciones_al_dia_s1);
		google.charts.setOnLoadCallback(drawCalificaciones_al_dia_s2);
		google.charts.setOnLoadCallback(drawCalificaciones_al_dia_catedra);
		//google.charts.setOnLoadCallback(drawCalificaciones_al_dia_recuperativa);
		google.charts.setOnLoadCallback(drawCalificaciones_al_dia_nota_final);

		function drawAsistencia() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Carrera');
			data.addColumn('number', 'Asistencia');	  
			var tdString = document.getElementById('datos_grafico_asistencia').innerHTML;
		
			var arr = eval("[" + tdString + "]");	  
			data.addRows(arr);




			var populationRange = data.getColumnRange(1);

			var logOptions = {
				title: 'Asistencia Estudiantes',
				legend: '',
				width: 800,
				height: 400,
				hAxis: {
					title: '', //'Carreras',
					//textPosition: 'in',
					slantedText: true,
					textStyle : {
						color: "#000",
						fontName: "arial",
						fontSize: 12,
						bold: false,
						italic: false        			
					}
				},
				vAxis: {
					title: '%Asistencia',
					scaleType: 'log',
					ticks: [populationRange.min, 5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100],
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('log_div_asistencia'));

			google.visualization.events.addListener(chart, 'ready', function () {
				download_link_asistencia.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
				document.getElementById("download_link_asistencia").setAttribute("href", chart.getImageURI())
					//document.getElementById("download_link").click();
			});
			chart.draw(data, logOptions);		  

		}		

		function drawAudiovisual() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Carrera');
			data.addColumn('number', 'Audiovisual');	  
			var tdString = document.getElementById('datos_grafico_av_utiliza').innerHTML;
		
			var arr = eval("[" + tdString + "]");	  
			data.addRows(arr);




			var populationRange = data.getColumnRange(1);

			var logOptions = {
				title: 'Recursos Audiovisuales',
				legend: '',
				width: 800,
				height: 400,
				hAxis: {
					title: '', //'Carreras',
					//textPosition: 'in',
					slantedText: true,
					textStyle : {
						color: "#000",
						fontName: "arial",
						fontSize: 12,
						bold: false,
						italic: false        			
					}
				},
				vAxis: {
				title: '%Audiovisual',
				scaleType: 'log',
				ticks: [populationRange.min, 5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100],
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('log_div_av_utiliza'));

			google.visualization.events.addListener(chart, 'ready', function () {
				download_link_av_utiliza.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
				document.getElementById("download_link_av_utiliza").setAttribute("href", chart.getImageURI())
					//document.getElementById("download_link").click();
			});

			chart.draw(data, logOptions);		  

		}		
		function drawMetodologias() {
			var data = new google.visualization.DataTable();
			/////////////////////////////////////////////////////////////////////////////////////////////////////////
				/*
				data.addColumn('string', 'Carrera');
				data.addColumn('number', 'Clase expositiva');
				data.addColumn('number', 'Foro de discusión');
				data.addColumn('number', 'Aprendizaje entre pares');
				*/

				var tdStringCabecera = document.getElementById('datos_grafico_metodologia_cabecera').innerHTML;
				var arrcabecera = eval("[" + tdStringCabecera + "]");	  

				data.addColumn(arrcabecera[0]);

				var numCols = arrcabecera.length;
				for (var i = 1; i < numCols; i++)
					data.addColumn('number', arrcabecera[i]);   
			/////////////////////////////////////////////////////////////////////////////////////////////////////////
				var tdString = document.getElementById('datos_grafico_metodologia').innerHTML;
				
				var arr = eval("[" + tdString + "]");	  
				data.addRows(arr);
				var populationRange = data.getColumnRange(1);

				var logOptions = {
					title: 'Metodología Activo Participativas',
					legend: {textStyle: {fontSize: 10}}, //'',
					width: 1600,
					height: 900,
					hAxis: {
						title: '', //'Carreras',
						//textPosition: 'in',
						slantedText: true,
						textStyle : {
							color: "#000",
							fontName: "arial",
							fontSize: 12,
							bold: false,
							italic: false        			
						}
					},

					isStacked:'percent', //true,
				};

			//	  var logOptions = {title: 'Activo Participantes', isStacked:true};  
					var chart = new google.visualization.ColumnChart(document.getElementById('log_div_metodologia'));

					google.visualization.events.addListener(chart, 'ready', function () {
						download_link_metodologia.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
						document.getElementById("download_link_metodologia").setAttribute("href", chart.getImageURI())
							//document.getElementById("download_link").click();
					});					
					chart.draw(data, logOptions);		 			
		}
		function drawMetodologiasPieChart() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Metodologia');
			data.addColumn('number', 'Porcentaje');	  
			var tdString = document.getElementById('datos_grafico_metodologia_piechart').innerHTML;
		
			var arr = eval("[" + tdString + "]");	  
			data.addRows(arr);




			var populationRange = data.getColumnRange(1);

			var logOptions = {
				title: 'Porcentaje Activo Participantes',
				legend: '',
				width: 1600,
				height: 900,
				hAxis: {
					title: '', //'Carreras',
					//textPosition: 'in',
					slantedText: true,
					textStyle : {
						color: "#000",
						fontName: "arial",
						fontSize: 12,
						bold: false,
						italic: false        			
					}
				},
				vAxis: {
				title: '%Activo Participantge',
				scaleType: 'log',
				ticks: [populationRange.min, 20, 40, 60, 80,100],
				}
			};

			var chart = new google.visualization.PieChart(document.getElementById('log_div_metodologia'));

			google.visualization.events.addListener(chart, 'ready', function () {
				download_link_metodologia.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
				document.getElementById("download_link_metodologia").setAttribute("href", chart.getImageURI())
					//document.getElementById("download_link").click();
			});

			chart.draw(data, logOptions);		  




		}

		function drawNroSesiones() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Carrera');
			data.addColumn('number', 'Asistencia');	  
			var tdString = document.getElementById('datos_grafico_nrosesiones').innerHTML;
		
			var arr = eval("[" + tdString + "]");	  
			data.addRows(arr);




			var populationRange = data.getColumnRange(1);

			var logOptions = {
				title: 'Asistencia Docentes',
				legend: '',
				width: 800,
				height: 400,
				hAxis: {
					title: '', //'Carreras',
					//textPosition: 'in',
					slantedText: true,
					textStyle : {
						color: "#000",
						fontName: "arial",
						fontSize: 12,
						bold: false,
						italic: false        			
					}
				},
				vAxis: {
					title: '%Asistencia',
					scaleType: 'log',
					ticks: [populationRange.min, 5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100],
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('log_div_nrosesiones'));

			google.visualization.events.addListener(chart, 'ready', function () {
				download_link_nrosesiones.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
				document.getElementById("download_link_nrosesiones").setAttribute("href", chart.getImageURI())
					//document.getElementById("download_link").click();
			});
			chart.draw(data, logOptions);		  

		}		
		function drawCalificaciones_promedio_general() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Carrera');
			data.addColumn('number', 'Promedio General');	  
			var tdString = document.getElementById('datos_grafico_calificaciones_promedio_general').innerHTML;
		
			var arr = eval("[" + tdString + "]");	  
			data.addRows(arr);




			var populationRange = data.getColumnRange(1);

			var logOptions = {
				title: 'Calificaciones Promedio General',
				legend: '',
				width: 800,
				height: 400,
				hAxis: {
					title: '', //'Carreras',
					//textPosition: 'in',
					slantedText: true,
					textStyle : {
						color: "#000",
						fontName: "arial",
						fontSize: 12,
						bold: false,
						italic: false        			
					}
				}
				,
				vAxis: {
					title: '%S1',
					scaleType: 'log',
					ticks: [populationRange.min, 5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100],
					format: "percent",
					viewWindow: {
						max:100,
						min:0
					}					
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('log_div_calificaciones_promedio_general'));

			google.visualization.events.addListener(chart, 'ready', function () {
				download_link_calificaciones_promedio_general.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
				document.getElementById("download_link_calificaciones_promedio_general").setAttribute("href", chart.getImageURI())
					//document.getElementById("download_link").click();
			});
			chart.draw(data, logOptions);		  

		}		

		function drawCalificaciones_al_dia_s1() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Carrera');
			data.addColumn('number', 'S1');	  
			var tdString = document.getElementById('datos_grafico_calificaciones_al_dia_s1').innerHTML;
		
			var arr = eval("[" + tdString + "]");	  
			data.addRows(arr);




			var populationRange = data.getColumnRange(1);

			var logOptions = {
				title: 'Calificaciones al dia S1',
				legend: '',
				width: 800,
				height: 400,
				hAxis: {
					title: '', //'Carreras',
					//textPosition: 'in',
					slantedText: true,
					textStyle : {
						color: "#000",
						fontName: "arial",
						fontSize: 12,
						bold: false,
						italic: false        			
					}
				}
				,
				vAxis: {
					title: '%S1',
					scaleType: 'log',
					ticks: [populationRange.min, 5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100],
					format: "percent",
					viewWindow: {
						max:100,
						min:0
					}					
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('log_div_calificaciones_al_dia_s1'));

			google.visualization.events.addListener(chart, 'ready', function () {
				download_link_calificaciones_al_dia_s1.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
				document.getElementById("download_link_calificaciones_al_dia_s1").setAttribute("href", chart.getImageURI())
					//document.getElementById("download_link").click();
			});
			chart.draw(data, logOptions);		  

		}		
		function drawCalificaciones_al_dia_s2() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Carrera');
			data.addColumn('number', 'S2');	  
			var tdString = document.getElementById('datos_grafico_calificaciones_al_dia_s2').innerHTML;
		
			var arr = eval("[" + tdString + "]");	  
			data.addRows(arr);




			var populationRange = data.getColumnRange(1);

			var logOptions = {
				title: 'Calificaciones al dia S2',
				legend: '',
				width: 800,
				height: 400,
				hAxis: {
					title: '', //'Carreras',
					//textPosition: 'in',
					slantedText: true,
					textStyle : {
						color: "#000",
						fontName: "arial",
						fontSize: 12,
						bold: false,
						italic: false        			
					}
				},
				vAxis: {
					title: '%S2',
					scaleType: 'log',
					ticks: [populationRange.min, 5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100],
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('log_div_calificaciones_al_dia_s2'));

			google.visualization.events.addListener(chart, 'ready', function () {
				download_link_calificaciones_al_dia_s2.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
				document.getElementById("download_link_calificaciones_al_dia_s2").setAttribute("href", chart.getImageURI())
					//document.getElementById("download_link").click();
			});
			chart.draw(data, logOptions);		  

		}		
		function drawCalificaciones_al_dia_catedra() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Carrera');
			data.addColumn('number', 'Catedra');	  
			var tdString = document.getElementById('datos_grafico_calificaciones_al_dia_catedra').innerHTML;
		
			var arr = eval("[" + tdString + "]");	  
			data.addRows(arr);




			var populationRange = data.getColumnRange(1);

			var logOptions = {
				title: 'Calificaciones al dia 1er Control',
				legend: '',
				width: 800,
				height: 400,
				hAxis: {
					title: '', //'Carreras',
					//textPosition: 'in',
					slantedText: true,
					textStyle : {
						color: "#000",
						fontName: "arial",
						fontSize: 12,
						bold: false,
						italic: false        			
					}
				},
				vAxis: {
					title: '%1er Control',
					scaleType: 'log',
					ticks: [populationRange.min, 5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100],
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('log_div_calificaciones_al_dia_catedra'));

			google.visualization.events.addListener(chart, 'ready', function () {
				download_link_calificaciones_al_dia_catedra.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
				document.getElementById("download_link_calificaciones_al_dia_catedra").setAttribute("href", chart.getImageURI())
					//document.getElementById("download_link").click();
			});
			chart.draw(data, logOptions);		  

		}		
/*
		function drawCalificaciones_al_dia_recuperativa() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Carrera');
			data.addColumn('number', 'Recuperativa');	  
			var tdString = document.getElementById('datos_grafico_calificaciones_al_dia_recuperativa').innerHTML;
		
			var arr = eval("[" + tdString + "]");	  
			data.addRows(arr);




			var populationRange = data.getColumnRange(1);

			var logOptions = {
				title: 'Calificaciones al dia Recuperativa',
				legend: '',
				width: 800,
				height: 400,
				hAxis: {
					title: '', //'Carreras',
					//textPosition: 'in',
					slantedText: true,
					textStyle : {
						color: "#000",
						fontName: "arial",
						fontSize: 12,
						bold: false,
						italic: false        			
					}
				},
				vAxis: {
					title: '%Recuperativa',
					scaleType: 'log',
					ticks: [populationRange.min, 5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100],
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('log_div_calificaciones_al_dia_recuperativa'));

			google.visualization.events.addListener(chart, 'ready', function () {
				download_link_calificaciones_al_dia_recuperativa.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
				document.getElementById("download_link_calificaciones_al_dia_recuperativa").setAttribute("href", chart.getImageURI())
					//document.getElementById("download_link").click();
			});
			chart.draw(data, logOptions);		  

		}		
*/
		function drawCalificaciones_al_dia_nota_final() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Carrera');
			data.addColumn('number', 'Nota Final');	  
			var tdString = document.getElementById('datos_grafico_calificaciones_al_dia_nota_final').innerHTML;
		
			var arr = eval("[" + tdString + "]");	  
			data.addRows(arr);




			var populationRange = data.getColumnRange(1);

			var logOptions = {
				title: 'Calificaciones al dia Nota Final',
				legend: '',
				width: 800,
				height: 400,
				hAxis: {
					title: '', //'Carreras',
					//textPosition: 'in',
					slantedText: true,
					textStyle : {
						color: "#000",
						fontName: "arial",
						fontSize: 12,
						bold: false,
						italic: false        			
					}
				},
				vAxis: {
					title: '%Nota Final',
					scaleType: 'log',
					ticks: [populationRange.min, 5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100],
				}
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('log_div_calificaciones_al_dia_nota_final'));

			google.visualization.events.addListener(chart, 'ready', function () {
				download_link_calificaciones_al_dia_nota_final.innerHTML = '<img id="chart" src=' + chart.getImageURI() + '>';
				document.getElementById("download_link_calificaciones_al_dia_nota_final").setAttribute("href", chart.getImageURI())
					//document.getElementById("download_link").click();
			});
			chart.draw(data, logOptions);		  

		}		

</script>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 800,
		'maxHeight'			: 700,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 800,
		'height'			: 480,
		'maxHeight'			: 480,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small2").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 800,
		'height'			: 600,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoSize'			: false,
		'fitToView'			: true,
		'autoDimensions'	: false,
		'closeBtn'	        : true,
		'closeClick'	    : false,
		'modal'      	    : true,
		'width'				: 9999,
		'maxHeight'			: 9999,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});

/*
function almacenaVariable(nombreVariable, valor) {
  //RRR
	var i=document.createElement('input');
		i.type='hidden';
		i.name=nombreVariable;
		i.value=valor;
	return i;
}
*/
/*
function enviarValoresEvaluar(parametro_sincronizar, parametro_lista_id_cursos){
  //RRR
		var f = document.createElement('form');
    //alert("debe saltar : " + window.location.href);
		//f.action='?modulo=gestion_cursos';
    f.action=window.location.href;
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("parametro_sincronizar", parametro_sincronizar);
		f.appendChild(i);

		i = almacenaVariable("parametro_lista_id_cursos", parametro_lista_id_cursos);
		f.appendChild(i);



		document.body.appendChild(f);
		f.submit();
}
*/
/*
function enviarValoresEvaluar(tipoGrafico){
		var f = document.createElement('form');
		f.action='?modulo=reporteria_libro_clases';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("id_tipo_grafico_carrera", tipoGrafico);
		f.appendChild(i);

		document.body.appendChild(f);
		f.submit();
}
function validarCarrera() {
		//alert("aqui andamos");
		var myId_carrera = $("select#id_carrera option:checked" ).val();
		//alert(myId_carrera);
		if (myId_carrera == '') {
			tipoGrafico = "BARRA";
			//$("#id_tipo_grafico_carrera").val("BARRA");
		} else {
			tipoGrafico = "TORTA";
			//$("#id_tipo_grafico_carrera").val("TORTA");
		}
		var myTipoGrafico = $("select#var myId_carrera = $("#id_tipo_grafico_carrera").text();
		//alert("aqui salimos");
		enviarValoresEvaluar(tipoGrafico)
		return true;
	}
*/
$(document).ready(function(){
	//alert("ready");
	console.log("READY!");
});  
</script>

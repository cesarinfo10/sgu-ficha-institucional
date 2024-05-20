<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}
/*
class CursoMoodle
{
    public $id;
    public $displayname;
    public $shortname;
    public $categoryid;

}
$arrCursoMoodle = array();
*/
function moodle_obtenerCursos($host, $token) {
        $cmdExec = "php proceso_moodle_obtiene_cursos.php $host $token";
        //echo("<br>".$cmdExec);
        $data = shell_exec($cmdExec);
        //echo("<br> = ");
        //echo($data);
        $decoded_json = json_decode($data, true);
        $indice = 0;
        //echo("<br>Lectura JSON");
        $SS = "select id, nombre from (";
        if ($decoded_json != null) {
            $indice = 0;
            foreach($decoded_json as $key => $value) {
               $indice++;
                if ($indice>1) {
                  $SS .= " union ";
                } 
                $id_curso = $decoded_json[$key]["id_curso"];
                $nombre = $decoded_json[$key]["nombre"];
                //echo("<br>id_curso=".$id_curso);
                //echo("<br>nombre=".$nombre);
                $SS .= " select $id_curso id, concat(concat(cast($id_curso as varchar),' '),'$nombre') nombre ";
            }
        }
        $SS .= ") as a
        order by nombre";
        return $SS;
}
function obtieneListaCourseId($id_moodle) {
        $sql_servicio ="
        select 
        url_servicio, 
        token
        from moodle_servicios
        where 
        id = $id_moodle
        ";
        $fServicio = consulta_sql($sql_servicio);
        $url_servicio = $fServicio[0]['url_servicio'];		 
        $token = $fServicio[0]['token'];		
        //echo("<br>..........seleccionado = ".$id_moodle);
        //echo("<br>..........url_servicio = ".$url_servicio);
        //echo("<br>..........token = ".$token);
        if (strlen($token) <= 3) {
          echo(msje_js("NO SE PUEDE OBTENER ID-CURSOS, DEBE COMPLETAR SERVICIO MOODLE (token)."));
        }
        $querySS = moodle_obtenerCursos($url_servicio, $token);
        /*
        foreach ($arrCursoMoodle as $myArr) {            
                $id = $myArr->id;
                $shortname = $myArr->shortname;
                $displayname = $myArr->displayname;
                echo("<br>...curso..............$id, ".$shortname." ----- ".$displayname);
        }
        */
        return $querySS;
}




//include("validar_modulo.php");
//include("proceso_moodle.php");
$pantalla = $_REQUEST['pantalla'];
$nombre_asignatura = $_REQUEST['nombre_asignatura'];
$nombre_malla= $_REQUEST['nombre_malla'];
$nombre_carrera= $_REQUEST['nombre_carrera'];
$id_curso = $_REQUEST['id_curso'];
$cod_classroom  = $_REQUEST['cod_classroom'];
$token    = $_REQUEST['token'];
$volver   = $_REQUEST['volver'];
$id_moodle   = $_REQUEST['id_moodle'];
$id_course_moodle = $_REQUEST['id_course_moodle'];

$id_course_id = $_REQUEST['id_course_id'];


$guardar = $_REQUEST['guardar'];
if ($id_curso == "" || $token == "") {
	echo(js("location.href='principal.php?modulo=portada';"));
	exit;
}
$sql_moodle ="
select 
id, 
nombre
from moodle_servicios
where 
 length(token)>3
 and length(url_servicio)>20
order by nombre;
";

$fmoodle = consulta_sql($sql_moodle);
/*

echo("<br>1.-pantalla = ".$pantalla);
echo("<br>2.-nombre_asignatura = ".$nombre_asignatura);
echo("<br>3.-nombre_malla = ".$nombre_malla);
echo("<br>4.-nombre_carrera = ".$nombre_carrera);
echo("<br>5.-id_curso = ".$id_curso);
echo("<br>6.-cod_classroom = ".$cod_classroom);
echo("<br>7.-token = ".$token);
echo("<br>8.-volver = ".$volver);
echo("<br>9.-id_moodle = ".$id_moodle);
*/
/*
$sql_Coursemoodle ="
select course_id, nombre from (
  select 1 course_id, 'SISTEMAS' nombre
  union
  select 2 course_id, 'SISTEMAS II' nombre
  ) as a
  order by nombre
  ";

$fCoursemoodle = consulta_sql($sql_Coursemoodle);
*/

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre,
                     vc.ano,vc.profesor,vc.id_profesor,vc.carrera,cant_alumnos_asist(vc.id),
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,c.cupo,c.cant_notas_parciales,token AS cod,
                     CASE WHEN c.cerrado THEN 'Cerrado' ELSE 'Abierto' END AS estado,coalesce(c.cupo,0) AS cupo,
                     coalesce(to_char(c.fec_ini,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_ini,coalesce(to_char(c.fec_fin,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_fin,
                     coalesce(to_char(c.fec_sol1,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol1,coalesce(to_char(c.fec_sol2,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol2,
                     coalesce(to_char(c.fec_sol_recup,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol_recup,
                     c.cod_google_classroom,
                     c.id_moddle_servicio as my_id_moodle, 
                     c.course_id_moodle as my_id_course_id
              FROM vista_cursos AS vc
              LEFT JOIN cursos AS c USING(id)
              LEFT JOIN vista_cursos_cod_barras AS vccb USING(id)
              WHERE vc.id=$id_curso;";

$curso = consulta_sql($SQL_curso);

if (count($curso) > 0) {
	
	extract($curso[0]);


  //if ($guardar<>"" || $pantalla=="1") {
  //echo("<br>pantalla = ".$pantalla);
  if ($pantalla=="1") {  
//    echo("<br>HE ENTRADO...");
    $id_moodle = $curso[0]['my_id_moodle'];
    $id_course_id = $curso[0]['my_id_course_id'];
    $id_course_moodle = $id_course_id;
    //$id_curso = $curso[0]['vc.id'];
    //$id_profesor = $curso[0]['vc.id_profesor'];
    $pantalla = "";
    //echo("<br>BD....id_moodle = ".$id_moodle);
    //echo("<br>BD....id_course_id = ".$id_course_id);

    

  }                
  if ($id_moodle <> "") {
    $querySS = obtieneListaCourseId($id_moodle);
    $fCoursemoodle = consulta_sql($querySS);
    
  }
  //echo("pantalla = ".$pantalla.", id_moodle=".$id_moodle."...id_course_id=".$id_course_id);

	if ($token <> md5($id_curso.$id_profesor)) { 
		echo(msje_js("Error de consistencia. No se puede continuar"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}

	$SQL_cursos_fusion = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,c.id_prog_asig 
	                      FROM vista_cursos AS vc
	                      LEFT JOIN cursos AS c USING (id)
	                      WHERE id_fusion = $id_curso";
	$cursos_fusion     = consulta_sql($SQL_cursos_fusion);
	$HTML_fusionadas = "";
	$ids_cursos = $ids_pa = "";
	for ($x=0;$x<count($cursos_fusion);$x++) {
		$HTML_fusionadas .= "<small><br>&nbsp;<big><b>↳</b></big>{$cursos_fusion[$x]['asignatura']}</small>";
		$ids_cursos      .= "{$cursos_fusion[$x]['id']},";
		$ids_pa          .= "{$cursos_fusion[$x]['id_prog_asig']},";
	}
	
	$ids_cursos .= $id_curso;
	$ids_pa     .= $id_prog_asig;
	
	$SQL_mallas = "SELECT char_comma_sum(alias_carrera||ano::text) AS anos FROM vista_mallas WHERE id IN (SELECT id_malla FROM detalle_mallas WHERE id_prog_asig IN ($ids_pa))";
	$mallas = consulta_sql($SQL_mallas);
	$mallas = $mallas[0]['anos'];
}
if ($_REQUEST['guardar']) {
	//$SQL_update = "UPDATE cursos SET id_moddle_servicio=$id_moodle, course_id_moodle=$id_course_id
  $SQL_update = "UPDATE cursos SET id_moddle_servicio=$id_moodle, course_id_moodle=$id_course_moodle
   WHERE id=$id_curso OR id_fusion=$id_curso;";

   //echo($SQL_update);
   
	if (consulta_dml($SQL_update) > 0) {
		echo(msje_js('Se ha guardado el código Moodle ingresado.'));
		if ($volver <> "") {
			$volver = base64_decode($volver);
			echo(js("location.href='$volver';"));
		} else {
			echo(js("parent.jQuery.fancybox.close();"));
		}
		exit;
	}
  
}
/*
if ($_REQUEST['guardar'] == "Guardar" && $cod_classroom <> "" && $token == md5($id_curso.$id_profesor)) {
	$SQL_curso_update = "UPDATE cursos SET cod_google_classroom='$cod_classroom' WHERE id=$id_curso OR id_fusion=$id_curso;";
	if (consulta_dml($SQL_curso_update) > 0) {
		echo(msje_js('Se ha guardado el código de Google Classroom ingresado.'));
		if ($volver <> "") {
			$volver = base64_decode($volver);
			echo(js("location.href='$volver';"));
		} else {
			echo(js("parent.jQuery.fancybox.close();"));
		}
		exit;
	}
}
*/

if ($volver <> "") { $botonCancelar = "location.href='".base64_decode($volver)."';"; } else { $botonCancelar = "parent.jQuery.fancybox.close();"; }
	
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  Editar Curso: Código Moodle
</div>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<input type="hidden" name="token" value="<?php echo($token); ?>">
<input type="hidden" name="volver" value="<?php echo($volver); ?>">

<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" value="Cancelar" onClick="<?php echo($botonCancelar); ?>">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
          <td class="celdaNombreAttr">Asignatura:</td>
          <td class="celdaValorAttr">
                  <!--<label class="texto" name="nombre_asignatura"><?php //echo($nombre_asignatura); ?></label> -->
                  <input type="text" name="nombre_asignatura" value="<?php echo($nombre_asignatura); ?>" size="64" readonly>
          </td>                  
  </tr> 
  <tr>
          <td class="celdaNombreAttr">Malla:</td>
          <td class="celdaValorAttr">
                  <!--<label class="texto" name="nombre_malla"><?php //echo($nombre_malla); ?></label> -->
                  <input type="text" name="nombre_malla" value="<?php echo($nombre_malla); ?>" size="64" readonly>
          </td>                  
  </tr> 
  <tr>
          <td class="celdaNombreAttr">Carrera:</td>
          <td class="celdaValorAttr">
                  <!--<label class="texto"><?php //echo($nombre_carrera); ?></label>-->
                  <input type="text" name="nombre_carrera" value="<?php echo($nombre_carrera); ?>" size="64" readonly>
          </td>                  
  </tr> 


  <tr>
    <td class="celdaNombreAttr">Servicio:</td>
    <td class="celdaValorAttr">
      <select name="id_moodle" id="id_moodle" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($fmoodle,$id_moodle)); ?>
      </select>
    </td>
  </tr>
  <tr>
          <td class="celdaNombreAttr">Course ID:</td>
          <td class="celdaValorAttr" colspan=3>
                  <input type="hidden" size="10" style="border: none" maxlength="20" name="id_course_id" id="id_course_id" value="<?php echo($id_course_id); ?>">
                  <select name="id_course_moodle" id="id_course_moodle" onChange="submitform();">
                    <option value="">-- Seleccione --</option>
                    <?php echo(select($fCoursemoodle,$id_course_moodle)); ?>
                  </select>

          </td>                  

  </tr> 

<!--
  <tr>
    <td class='celdaNombreAttr'>Nº Acta:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($semestre."-".$ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura . " " . $prog_asig . $HTML_fusionadas); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Profesor:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor); ?> <?php echo($ficha_prof); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Inscrito(a)s:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos); ?> alumno(a)s</td>
    <td class='celdaNombreAttr'>Asistentes:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos_asist); ?> alumno(a)s</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="3">Código Google Classroom:</td>
    <td class='celdaValorAttr'><input type="text" name="cod_classroom" size="7" class="boton" value="<?php echo($cod_google_classroom); ?>" required></td>
  </tr>
                        -->
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

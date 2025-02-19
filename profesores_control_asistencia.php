<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

$ids_carreras = $_SESSION['ids_carreras'];
$id_usuario   = $_SESSION['id_usuario'];

$jornada = $_REQUEST['jornada'];
if ($jornada <> "D" && $jornada <> "V") { $jornada = ""; }

$fecha = $_REQUEST['fecha'];

if ($fecha == "" || ($ids_carreras == "" && $_REQUEST['id_escuela_u'] == "")) {
	echo(js("window.location='$enlbase=profesores_control_asistencia_fecha_jornada';"));
	exit;
} else {
	$fecha = strftime("%Y-%m-%d",strtotime($fecha));
}

include("validar_modulo.php");

$dia_asist = strftime("%u",strtotime($fecha));
if ($dia_asist == 7) {
	echo(msje_js("El domingo no es un día válido. A continuación seleccione otra fecha."));
	echo(js("window.location='$enlbase=profesores_control_asistencia_fecha_jornada';"));
	exit;
}
 
$fecha_asistencia = strftime("%A %e de %B de %Y", strtotime($fecha));

$SQL_cursos_dia = "SELECT id,horario1 AS modulo,'$fecha'::date AS fecha FROM cursos WHERE ano=$ANO AND semestre=$SEMESTRE AND horario1 IS NOT NULL AND dia1 = $dia_asist UNION
                   SELECT id,horario2 AS modulo,'$fecha'::date AS fecha FROM cursos WHERE ano=$ANO AND semestre=$SEMESTRE AND horario2 IS NOT NULL AND dia2 = $dia_asist UNION
                   SELECT id,horario3 AS modulo,'$fecha'::date AS fecha FROM cursos WHERE ano=$ANO AND semestre=$SEMESTRE AND horario3 IS NOT NULL AND dia3 = $dia_asist";
$cursos_dia = consulta_sql($SQL_cursos_dia);

if ($_REQUEST['guardar'] == "Guardar") {
	$aId_asist     = $_REQUEST['id_asist'];
	$aHora_entrada = $_REQUEST['hora_entrada'];
	$aComentarios  = $_REQUEST['comentarios'];
	$aAsiste       = $_REQUEST['asiste'];
	
	$asistencias = array();
	$asiste_faltan = $asiste_listos = 0;	
	for($x=0;$x<count($aId_asist);$x++) {
		if($aAsiste[$id_asist] == "") {$asiste_faltan++; } else { $asiste_listos++; } 
		
		$id_asist = $aId_asist[$x];
		$asist = array(array("id_asist"     => $id_asist,
		                     "asiste"       => $aAsiste[$id_asist],
		                     "hora_entrada" => str_replace(".",":",$aHora_entrada[$id_asist]),
		                     "comentarios"  => $aComentarios[$id_asist]));
		$asistencias = array_merge($asistencias,$asist);
	}

	$SQLupdate_asist = "";
	for($x=0;$x<count($asistencias);$x++) {
		$SQLupdate_asist .= "UPDATE asist_profesores "
		                 .  "SET " . arr2sqlupd($asistencias[$x])
		                 .  " WHERE id={$asistencias[$x]['id_asist']};";
	}

	if (consulta_dml($SQLupdate_asist) > 0) {
		echo(msje_js("Se han guardado los cambios exitosamente de $asiste_listos.\\n"
		            ."Resta el ingreso de asistencia de $asiste_faltan cursos de este día"));
	} else {
		echo(msje_js("Ha ocurrido un problema. Por favor intente nuevamente guardar los cambios"));
	}
}
		
$SQL_asist_profe = "SELECT id_curso FROM asist_profesores WHERE fecha = '$fecha'::date;";
$asist_profe     = consulta_sql($SQL_asist_profe);
if (count($asist_profe) == 0 || count($asist_profe) < count($cursos_dia)) {
	$SQL_insert = "INSERT INTO asist_profesores (id_curso,modulo,fecha) 
	                    SELECT id,modulo,fecha FROM ($SQL_cursos_dia) AS foo 
	                     WHERE id::text||modulo NOT IN (SELECT id_curso||modulo FROM asist_profesores
	                                                    WHERE fecha = '$fecha'::date);";
	consulta_dml($SQL_insert);
}

$escuelas = $jornadas = "";
if ($ids_carreras <> "") {
	$id_escuela_u = $_SESSION['id_escuela'];	
} else {
	$id_escuela_u  = $_REQUEST['id_escuela_u'];
	$ids_carreras = consulta_sql("SELECT char_comma_sum(id::text) AS ids_carreras FROM carreras WHERE id_escuela=$id_escuela_u");
	$ids_carreras = $ids_carreras[0]['ids_carreras']; 
}
$condicion_carreras = "AND vc.id_carrera IN ($ids_carreras)";
$escuela = consulta_sql("SELECT nombre FROM escuelas WHERE id=$id_escuela_u");
if (count($escuela) == 1) { $escuelas = "de la escuela de {$escuela[0]['nombre']}"; } 	

switch($jornada) {
	case "D":
		$condicion_jornada = "AND modulo BETWEEN 'A' AND 'F'";
		$jornadas = "en la jornada Diurna";
		break;
	case "V":
		$condicion_jornada = "AND modulo BETWEEN 'G' AND 'H'";
		$jornadas = "en la jornada Vespertina";
		break;
	default:
		$jornadas = "en ambas jornadas";
}

$SQL_asist_profe = "SELECT ap.id AS id_asist,ap.id_curso,vc.profesor,
                           vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,ap.modulo AS mod,
                           coalesce(ap.asiste,'') AS asiste,vh.intervalo AS mod_intervalo,
                           to_char(hora_entrada,'HH24:MI') AS hora_entrada,to_char(hora_salida,'HH24:MI') AS hora_salida,
                           comentarios
                    FROM asist_profesores AS ap 
                    LEFT JOIN vista_cursos AS vc ON vc.id=ap.id_curso
                    LEFT JOIN vista_horarios AS vh ON vh.id=ap.modulo
                    WHERE fecha='$fecha'::date
                          $condicion_carreras $condicion_jornada
                    UNION
                    SELECT ap.id AS id_asist,ap.id_curso,vc.profesor,
                           vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,ap.modulo AS mod,
                           coalesce(ap.asiste,'') AS asiste,vh.intervalo AS mod_intervalo,
                           to_char(hora_entrada,'HH24:MI') AS hora_entrada,to_char(hora_salida,'HH24:MI') AS hora_salida,
                           comentarios
                    FROM asist_profesores AS ap 
                    LEFT JOIN vista_cursos AS vc ON vc.id=ap.id_curso
                    LEFT JOIN vista_horarios AS vh ON vh.id=ap.modulo
                    WHERE asiste='r' AND fecha_recup IS NOT NULL AND fecha_recup='$fecha'::date
                          $condicion_carreras $condicion_jornada
                    ORDER BY mod,profesor,asignatura;";
$asist_profe     = consulta_sql($SQL_asist_profe);
//echo($SQL_asist_profe);

$ASISTE = array(array("id" => "p","nombre" => "Presente"),
                array("id" => "a","nombre" => "Ausente"));

$Fecha = strftime("%d-%m-%Y",strtotime($fecha));
$fecha = strftime("%Y%m%d",strtotime($fecha));

$HTML = "";
for ($x=0; $x<count($asist_profe); $x++) {
	if ($mod_intervalo <> $asist_profe[$x]['mod_intervalo']) {
		$HTML .= "<tr class='filaTabla'>
		            <td class='textoTabla' colspan='7' align='center'>
		              Módulo {$asist_profe[$x]['mod']} ({$asist_profe[$x]['mod_intervalo']})
		            </td>
		          </tr>";
	}

	extract($asist_profe[$x]);

	$opciones_asiste   = select($ASISTE,$asiste);
	//if ($hora_entrada == "") { substr($mod_intervalo,0,5); }		
	
	$HTML .= "  <tr class='filaTabla'>
	            <input type='hidden' name='id_asist[]' value='$id_asist'>
		           <td class='textoTabla' align='right'>$id_curso</td>
		           <td class='textoTabla' width='200'>$asignatura</td>
		           <td class='textoTabla' width='200'>$profesor</td>
		           <td class='textoTabla'>
		             <select name='asiste[$id_asist]'>
		               <option value=''></option>
		               $opciones_asiste
		             </select>
		           </td>
		           <td class='textoTabla'><input type='text' size='3' name='hora_entrada[$id_asist]' value='$hora_entrada'></td>
		           <td class='textoTabla'><input type='text' size='3' name='hora_salida[$id_asist]' value='$hora_salida'></td>
		           <td class='textoTabla'><input type='text' size='20' name='comentarios[$id_asist]' value='$comentarios'></td>
		         </tr>";
}

//echo(js("window.location='profesores_control_asistencia_imprimir_hoja_firmas.php?fecha=$fecha&jornada=$jornada&id_escuela_u=$id_escuela_u';"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="fecha" value="<?php echo($fecha); ?>">
<input type="hidden" name="jornada" value="<?php echo($jornada); ?>">
<input type="hidden" name="id_escuela_u" value="<?php echo($id_escuela_u); ?>">

<table width="100%">
  <tr>
    <td>
      <input type="submit" name="guardar" value="Guardar">
    </td>
    <td align="right">
      <input type="button" value="Imprimir Hoja Diaria de Firmas"
             onClick="window.location='profesores_control_asistencia_imprimir_hoja_firmas.php?fecha=<?php echo($fecha); ?>&jornada=<?php echo($jornada); ?>&id_escuela_u=<?php echo($id_escuela_u); ?>';">
      <input type="button" name="cambiar_fecha" value="Cambiar Fecha y Jornada"
             onClick="window.location='principal.php?modulo=profesores_control_asistencia_fecha_jornada&fecha=<?php echo($Fecha); ?>&jornada=<?php echo($jornada); ?>&id_escuela_u=<?php echo($id_escuela_u); ?>';">
    </td>
  </tr>
</table>
<div class="texto">
  Asistencia para el día <b><?php echo($fecha_asistencia); ?></b>,
  <?php echo($jornadas); ?>
  <?php echo($escuelas); ?> 
  en total <?php echo(count($asist_profe)); ?> cursos en este día: 
</div><br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>Profesor</td>    
    <td class='tituloTabla'><u>Asistencia</u></td>
    <td class='tituloTabla'>Entrada</td>
    <td class='tituloTabla'>Salida</td>
    <td class='tituloTabla'>Observación</td>
  </tr>
  <?php echo($HTML); ?>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<!--
 ALTER TABLE asist_profesores ALTER id_coordinador DROP not null;
 INSERT INTO aplicaciones VALUES (523,'Establecer fecha y jornada para Control de Asistencia diaria','Permite indicar la fecha a la que se aceesará al libro de asistencia','profesores_control_asistencia_fecha_jornada','t','f');
 INSERT INTO aplicaciones VALUES (524,'Control Asistencia de Curso','Permite controlar la asistencia de un curso en particular','cursos_control_asistencia','t','f');
-->

<?php
function arr2sqlupd($aTabla) {
	$arr2sqlupd = "";
	$aCampos = array_keys($aTabla);
	for($x=1;$x<count($aCampos);$x++) {
		if ($aTabla[$aCampos[$x]] == "") {
			$aTabla[$aCampos[$x]] = "null";
		} else {
			$aTabla[$aCampos[$x]] = "'{$aTabla[$aCampos[$x]]}'";
		}
		$arr2sqlupd .= "{$aCampos[$x]}={$aTabla[$aCampos[$x]]},";
	}
	return substr($arr2sqlupd,0,-1);
}

/*
	foreach($_REQUEST AS $nombre_campo => $valor_campo) {
		if (substr($nombre_campo,0,7) == "asiste_") {			
			$id_asist = substr($nombre_campo,7,strlen($nombre_campo));
			$asistencia = array(array("id_asist"=>$id_asist,"asiste"=>$valor_campo));
			$asistencias = array_merge($asistencias,$asistencia);
		}		
	}
	
	$problemas = false;
	for ($x=0;$x<count($asistencias);$x++) {
		extract($asistencias[$x]);
		$SQL_update = "UPDATE asist_profesores SET id_coordinador=$id_usuario,asiste='$asiste' WHERE id=$id_asist";
		if (consulta_dml($SQL_update)<1) { $problemas = true; }
	}
	if (!$problemas) {
		echo(msje_js("Se han guardado los cambios exitosamente"));
	} else {
		echo(msje_js("Ha ocurrido un problema. Por favor intente nuevamente guardar los cambios"));
	}
*/
?>

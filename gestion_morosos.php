<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$texto_buscar     = $_REQUEST['texto_buscar'];
$buscar           = $_REQUEST['buscar'];
$id_carrera       = $_REQUEST['id_carrera'];
$jornada          = $_REQUEST['jornada'];
$semestre_cohorte = $_REQUEST['semestre_cohorte'];
$cohorte          = $_REQUEST['cohorte'];
$estado           = $_REQUEST['estado'];
$admision         = $_REQUEST['admision'];
$matriculado      = $_REQUEST['matriculado'];

if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = 2; }

$condicion = "WHERE true ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		            . " a.rut ~* '$cadena_buscada' OR "
		            . " text(a.id) ~* '$cadena_buscada' "
		            . ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	//$condicion .= "and estado<>5 ";
	$cohorte = $semestre_cohorte = $estado = $id_carrera = $admision = $matriculado = null;
} else {

	if ($cohorte > 0) { $condicion .= "AND (cohorte = '$cohorte') "; }

	if ($semestre_cohorte > 0) { $condicion .= "AND (semestre_cohorte = $semestre_cohorte) "; }
	 
	if ($estado <> "-1") { $condicion .= "AND (estado = '$estado') "; }

	if ($id_carrera <> "") { $condicion .= "AND (carrera_actual = '$id_carrera') "; }

	if ($jornada <> "") { $condicion .= "AND (a.jornada = '$jornada') "; }

	if ($admision <> "") { $condicion .= "AND (a.admision = '$admision') "; }

	if ($matriculado == "t") { $condicion .= "AND (m.id_alumno IS NOT NULL) "; } 
	elseif ($matriculado == "f") { $condicion .= "AND (m.id_alumno IS NULL) "; }
}

$SQL_alumnos = "SELECT a.id,a.rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.estado AS id_estado,
                       CASE WHEN estado_tramite IS NOT NULL THEN ae.nombre||' *' ELSE ae.nombre END AS estado,
                       CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,a.estado_tramite
                FROM alumnos AS a
                LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
                $condicion
                ORDER BY split_part(a.rut,'-',1)::int4";
$alumnos = consulta_sql($SQL_alumnos);

if ($_REQUEST["guardar"] == "Guardar y continuar" || $_REQUEST["guardar"] == "Guardar y cerrar") {
	$emails = consulta_sql("SELECT email FROM usuarios WHERE tipo=0 AND activo;");
	$asunto = "SGU: Alumno regulariza situación (Estado en trámite)";
	$cabeceras = "From: SGU" . "\r\n"
			   . "Content-Type: text/plain;charset=utf-8" . "\r\n";
	$contador = 0;
	//var_dump($_REQUEST);
	foreach ($_REQUEST['al_estado'] as $al_id_alumno => $al_estado) {
		$alumno = consulta_sql("SELECT a.estado,a.estado_tramite,va.nombre,va.carrera,a.jornada FROM alumnos a LEFT JOIN vista_alumnos va USING (id) WHERE id=$al_id_alumno");
		if (count($alumno) == 1) {
			if ($alumno[0]['estado_tramite'] == "") {
				$SQLupdate_alumno = "UPDATE alumnos SET estado=$al_estado WHERE id=$al_id_alumno";
			} else {
				$SQLupdate_alumno = "UPDATE alumnos SET estado=$al_estado,estado_tramite=null WHERE id=$al_id_alumno";
			}
			if ($al_estado == $alumno[0]['estado']) {
				$SQLupdate_alumno = "UPDATE alumnos SET estado=$al_estado WHERE id=$al_id_alumno";
			}
			//echo($SQLupdate_alumno."<br>");
			if (consulta_dml($SQLupdate_alumno) > 0) {
				$contador++;
				if ($alumno[0]['estado_tramite'] <> "") {
					$estado_nuevo = consulta_sql("SELECT nombre FROM al_estados WHERE id='$al_estado';");
					$estado_nuevo = $estado_nuevo[0]['nombre'];		
					$nombre = $alumno[0]['nombre'];
					$carrera = $alumno[0]['carrera'];
					$jornada = $alumno[0]['jornada'];
					$cuerpo = "El alumno $nombre de la carrera $carrera-$jornada, ahora tiene definitivamente el estado de $estado_nuevo.";
			
					for ($x=0;$x<count($emails);$x++) {
						$email = $emails[$x]['email'];
						mail($email,$asunto,$cuerpo,$cabeceras);
					}
				}
			}
		}
	}
	if (count($alumnos) == $contador) {
		echo(msje_js("Se han guardado exitosamente los estados de $contador alumnos"));
	} else {
		echo(msje_js("Sólo se han guardado los estados de $contador alumnos. Por favor intente nuevamente."));
	}
	if ($_REQUEST["guardar"] == "Guardar y cerrar") {
		echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;
	}
}

$HTML_alumnos = "";
if (count($alumnos) > 0) {
	for ($x=0;$x<count($alumnos);$x++) {
		extract($alumnos[$x],EXTR_PREFIX_ALL,'al');

		$id_estados = "";
		if ($al_estado_tramite <> "") {
			$id_estados = $al_estado_tramite.',2';
		} else {
			$id_estados = "0,2";			
			if ($al_matriculado == "Si") {
				$id_estados = "1,2";
			}
		}
		$al_estados = consulta_sql("SELECT id,nombre FROM al_estados WHERE id IN ($id_estados);");
		$al_estado = "<select name='al_estado[$al_id]' onChange='cambioEstado()'>"
		           . select($al_estados,$al_id_estado)
		           . "</select>";
		
		$HTML_alumnos .= "  <tr class='filaTabla'>\n"
					   . "    <td class='textoTabla'>$al_id</td>\n"
					   . "    <td class='textoTabla'>$al_rut</td>\n"
					   . "    <td class='textoTabla'><a class='enlitem' href='$enl'>$al_nombre</a></td>\n"
					   . "    <td class='textoTabla'>$al_carrera</td>\n"
					   . "    <td class='textoTabla'>$al_cohorte</td>\n"
					   . "    <td class='textoTabla'>$al_estado</td>\n"
					   . "    <td class='textoTabla'>$al_matriculado</td>\n"
					   . "  </tr>\n";
	}
} else {
	$HTML_alumnos = "  <tr>"
				  . "    <td class='textoTabla' colspan='7'>"
				  . "      No hay registros para los criterios de búsqueda/selección"
				  . "    </td>\n"
				  . "  </tr>";
}

$SQL_carreras = "SELECT id,nombre FROM carreras $condicion_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_al_estados = "SELECT id,nombre FROM al_estados WHERE id IN (1,2) ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));
?>
<script>
var cambio_estado = false;

function cambioEstado() {
	cambio_estado = true;
}

function verificar_cambios() {
	if (cambio_estado) {
		return confirm('Ha realizado cambios, desea grabarlos?');
	}
}
</script>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<form name="formulario" action="principal.php" method="post" onSubmit="if (verificar_cambios()) { return true; } else { return false; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="texto">
    <table cellpadding="2" border="0" cellspacing="0" width="auto">
      <tr>
        <td class="texto">
          Cohorte: 
          <select name="semestre_cohorte" onChange="if (!cambio_estado) { submitform(); } else { alert('No puede cambiar filtros, hay cambios por guardar'); }">
            <option value="0"></option>
            <?php echo(select($SEMESTRES_COHORTES,$semestre_cohorte)); ?>    
          </select>
          -
          <select name="cohorte" onChange="if (!cambio_estado) { submitform(); } else { alert('No puede cambiar filtros, hay cambios por guardar'); }">
            <option value="0">Todas</option>
            <?php echo(select($cohortes,$cohorte)); ?>    
          </select>
          Estado:
          <select name="estado" onChange="if (!cambio_estado) { submitform(); } else { alert('No puede cambiar filtros, hay cambios por guardar'); }">

            <?php echo(select($al_estados,$estado)); ?>
          </select>
          Admisión: 
          <select name="admision" onChange="if (!cambio_estado) { submitform(); } else { alert('No puede cambiar filtros, hay cambios por guardar'); }">
            <option value="">Todos</option>
            <?php echo(select($ADMISION,$admision)); ?>
          </select>
          Matriculado: 
          <select name="matriculado" onChange="if (!cambio_estado) { submitform(); } else { alert('No puede cambiar filtros, hay cambios por guardar'); }">

            <?php echo(select($sino,$matriculado)); ?>
          </select>
        </td>
      </tr>
      <tr>
        <td class="texto">
          Mostrar alumno(a)s de la carrera:<br>
          <select name="id_carrera" onChange="if (!cambio_estado) { submitform(); } else { alert('No puede cambiar filtros, hay cambios por guardar'); }">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>
          </select>
          de la Jornada:
          <select name="jornada" onChange="if (!cambio_estado) { submitform(); } else { alert('No puede cambiar filtros, hay cambios por guardar'); }">
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
      </tr>
      <tr valign="top">
        <td class="texto" width="auto">
          Buscar por ID, RUT o nombre:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="40" id="texto_buscar">
          <input type='submit' name='buscar' value='Buscar' onClick="if (!cambio_estado) { submitform(); } else { alert('No puede cambiar filtros, hay cambios por guardar'); }">
        </td>
      </tr>
    </table><br>
    Mostrando <b><?php echo(count($alumnos)); ?></b> alumno(s) en total
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type='submit' name='guardar' value='Guardar y continuar'></td>
    <td class="tituloTabla"><input type='submit' name='guardar' value='Guardar y cerrar'></td>
  </tr>
</table><br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Cohorte</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Mat?</td>
  </tr>
  <?php echo($HTML_alumnos); ?>
</table>
<br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type='submit' name='guardar' value='Guardar y continuar'></td>
    <td class="tituloTabla"><input type='submit' name='guardar' value='Guardar y cerrar'></td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->


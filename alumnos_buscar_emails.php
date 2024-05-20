<?php

$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) {	$cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar     = $_REQUEST['texto_buscar'];
$buscar           = $_REQUEST['buscar'];
$id_carrera       = $_REQUEST['id_carrera'];
$jornada          = $_REQUEST['jornada'];
$semestre_cohorte = $_REQUEST['semestre_cohorte'];
$cohorte          = $_REQUEST['cohorte'];
$estado           = $_REQUEST['estado'];
$admision         = $_REQUEST['admision'];
$regimen          = $_REQUEST['regimen'];
$matriculado      = $_REQUEST['matriculado'];

if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }

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

	if ($cohorte > 0) {
		$condicion .= "AND (cohorte = '$cohorte') ";
	}

	if ($semestre_cohorte > 0) {
		$condicion .= "AND (semestre_cohorte = $semestre_cohorte) ";
	}
	 
	if ($estado <> "-1") {
		$condicion .= "AND (estado = '$estado') ";
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
		$condicion .= "AND (a.regimen = '$regimen') ";
	}

	if ($matriculado == "t") {
		$condicion .= "AND (m.id_alumno IS NOT NULL) ";
	} elseif ($matriculado == "f") {
		$condicion .= "AND (m.id_alumno IS NULL) ";
	}
}

if (!empty($ids_carreras) && empty($id_carrera)) {
	$condicion .= " AND carrera_actual IN ($ids_carreras) ";
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_alumnos = "SELECT a.id,a.rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,telefono,tel_movil,
                       a.email AS email_personal,a.nombre_usuario||'@al.umcervantes.cl' AS email_umc,
                       CASE WHEN estado_tramite IS NOT NULL THEN ae.nombre||' *' ELSE ae.nombre END AS estado,
                       CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado
                FROM alumnos AS a
                LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
                $condicion
                ORDER BY nombre 
                $limite_reg
                OFFSET $reg_inicio;";
$alumnos = consulta_sql($SQL_alumnos);

$enlace_nav = "$enlbase=$modulo"
            . "&id_carrera=$id_carrera"
            . "&jornada=$jornada"
            . "&cohorte=$cohorte"
            . "&regimen=$regimen"
            . "&semestre_cohorte=$semestre_cohorte"
            . "&estado=$estado"
            . "&admision=$admision"            
            . "&matriculado=$matriculado"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";

if (count($alumnos) > 0) {
	$SQL_total_alumnos =  "SELECT count(a.id) AS total_alumnos FROM alumnos AS a LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO) $condicion;";
	$total_alumnos = consulta_sql($SQL_total_alumnos);
	$tot_reg = $total_alumnos[0]['total_alumnos'];
	
	$HTML_paginador = html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

if ($ids_carreras <> "") {
	$condicion_carreras = "WHERE id IN ($ids_carreras)";
}
$SQL_carreras = "SELECT id,nombre FROM carreras $condicion_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_al_estados = "SELECT id,nombre FROM al_estados ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

?>

<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="2" border="0" cellspacing="0" width="auto">
      <tr>
        <td class="texto">
          Cohorte: 
          <select name="semestre_cohorte" onChange="submitform();">
            <option value="0"></option>
            <?php echo(select($SEMESTRES_COHORTES,$semestre_cohorte)); ?>    
          </select>
          -
          <select name="cohorte" onChange="submitform();">
            <option value="0">Todas</option>
            <?php echo(select($cohortes,$cohorte)); ?>    
          </select>
          Estado: 
          <select name="estado" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($al_estados,$estado)); ?>
          </select>
          Admisión: 
          <select name="admision" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($ADMISION,$admision)); ?>
          </select>
          Régimen: 
          <select name="regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
          Matriculado: 
          <select name="matriculado" onChange="submitform();">
            <option value="a">Todos</option>
            <?php echo(select($sino,$matriculado)); ?>
          </select>
        </td>
      </tr>
      <tr>
        <td class="texto">
          Mostrar alumno(a)s de la carrera:<br>
          <select name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>
          </select>
          de la Jornada:
          <select name="jornada" onChange="submitform();">
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>

        </td>
      </tr>
      <tr valign="top">
        <td class="texto" width="auto">
          Buscar por ID, RUT o nombre:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="40" id="texto_buscar">
          <script>document.getElementById("texto_buscar").focus();</script>
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<br><input type='submit' name='buscar' value='Vaciar'>");          		
          	};
          ?>
        </td>
      </tr>
    </table>
    Mostrando <b><?php echo($tot_reg); ?></b> alumno(s) en total, en página(s) de
    <select name="cant_reg" onChange="submitform();">
      <option value="-1">Todos</option>
      <?php echo(select($CANT_REGS,$cant_reg)); ?>
    </select> filas<br>
  </form>
  <?php echo($HTML_paginador); ?>
</div>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>e-Mail Personal</td>
    <td class='tituloTabla'>e-Mail UMC</td>
    <td class='tituloTabla'>Teléfono</td>
    <td class='tituloTabla'>Tel. Móvil</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Cohorte</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Mat?</td>
  </tr>
<?php
	$HTML_alumnos = "";
	if (count($alumnos) > 0) {
		for ($x=0;$x<count($alumnos);$x++) {
			extract($alumnos[$x]);
			
			$enl = "$enlbase=$modulo_destino&id_alumno=$id";
			$enlace = "a class='enlitem' href='$enl'";
			
			$HTML_alumnos .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
			               . "    <td class='textoTabla'>$id</td>\n"
			               . "    <td class='textoTabla'><a class='enlitem' href='$enl'>$nombre</a></td>\n"
			               . "    <td class='textoTabla'>$email_personal</td>\n"
			               . "    <td class='textoTabla'>$email_umc</td>\n"
			               . "    <td class='textoTabla'>$telefono</td>\n"
			               . "    <td class='textoTabla'>$tel_movil</td>\n"
			               . "    <td class='textoTabla'>$carrera</td>\n"
			               . "    <td class='textoTabla'>$cohorte</td>\n"
			               . "    <td class='textoTabla'>$estado</td>\n"
			               . "    <td class='textoTabla'>$matriculado</td>\n"
			               . "  </tr>\n";
		}
	} else {
		$HTML_alumnos = "  <tr>"
		              . "    <td class='textoTabla' colspan='7'>"
		              . "      No hay registros para los criterios de búsqueda/selección"
		              . "    </td>\n"
		              . "  </tr>";
	}
	echo($HTML_alumnos);
?>
</table><br>
<div class="texto">
  <?php echo($HTML_paginador); ?>
</div>
<!-- Fin: <?php echo($modulo); ?> -->


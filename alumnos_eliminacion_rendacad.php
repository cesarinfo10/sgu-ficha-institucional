<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = 30;
$tot_reg  = 0;

$reg_inicio = $_REQUEST['reg_inicio'];
if ($reg_inicio=="") {
	$reg_inicio = 0;
};

$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$id_carrera   = $_REQUEST['id_carrera'];

if ($id_carrera <> "") {	
	$condicion = "";	
	if ($id_carrera <> "") {
		$condicion .= "AND (carrera_actual = '$id_carrera') ";
	}
}

if ($condicion <> "") {
	if ($ids_carreras <> "") {
		$condicion .= " AND carrera_actual IN ($ids_carreras) ";
	}
} else {
	if ($ids_carreras <> "") {
		$condicion = " AND carrera_actual IN ($ids_carreras)";
	}
}


if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);

	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= " AND (lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		            . " a.rut ~* '$cadena_buscada' OR "
		            . " a.id ~* '$cadena_buscada' "
		            . ")";
	}
	//$condicion=substr($condicion,0,strlen($condicion)-4);
	
}

$SQL_alumnos = "SELECT a.id,a.rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias AS carrera,
                       coalesce(a.semestre_cohorte,0)||'-'||a.cohorte AS cohorte,
                       CASE WHEN estado_tramite IS NOT NULL THEN ae.nombre||' *' ELSE ae.nombre END AS estado                       
                FROM alumnos AS a
                LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                WHERE eliminado_rendacad $condicion
                ORDER BY nombre 
                LIMIT $cant_reg
                OFFSET $reg_inicio;";
$alumnos = consulta_sql($SQL_alumnos);

$enlace_nav = "$enlbase=$modulo"
            . "&id_carrera=$id_carrera"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&reg_inicio";	

if (count($alumnos) > 0) {
	$SQL_total_alumnos =  "SELECT count(a.id) AS total_alumnos FROM alumnos AS a LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO) WHERE eliminado_rendacad $condicion;";
	$total_alumnos = consulta_sql($SQL_total_alumnos);
	$tot_reg = $total_alumnos[0]['total_alumnos'];
	
	$HTML_paginador = html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

if ($ids_carreras <> "") {
	$condicion_carreras = "WHERE id IN ($ids_carreras)";
}

$SQL_carreras = "SELECT id,nombre FROM carreras $condicion_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>

<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="2" border="0" cellspacing="0" width="auto">
      <tr>
        <td class="texto">
          Mostrar alumno(a)s de la carrera:<br>
          <select name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>
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
  </form>
  Mostrando <b><?php echo($tot_reg); ?></b> alumno(s) en total,
  en página(s) de <?php echo($cant_reg); ?> filas<br>
  <?php echo($HTML_paginador); ?>
</div>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Cohorte</td>
    <td class='tituloTabla'>Estado</td>
  </tr>
<?php
	$HTML_alumnos = "";
	if (count($alumnos) > 0) {
		for ($x=0;$x<count($alumnos);$x++) {
			extract($alumnos[$x]);
			
			$enl = "$enlbase=ver_alumno&id_alumno=$id";
			$enlace = "a class='enlitem' href='$enl'";
			
			$HTML_alumnos .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
			               . "    <td class='textoTabla'>$id</td>\n"
			               . "    <td class='textoTabla'>$rut</td>\n"
			               . "    <td class='textoTabla'><a class='enlitem' href='$enl'>$nombre</a></td>\n"
			               . "    <td class='textoTabla'>$carrera</td>\n"
			               . "    <td class='textoTabla'>$cohorte</td>\n"
			               . "    <td class='textoTabla'>$estado</td>\n"
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

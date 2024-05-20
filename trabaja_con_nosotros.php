<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//$ids_carreras = $_SESSION['ids_carreras'];
$id_usuario   = $_SESSION['id_usuario'];
 
include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar   = $_REQUEST['texto_buscar'];
$buscar         = $_REQUEST['buscar'];
$id_carrera     = $_REQUEST['id_carrera'];
$regimen        = $_REQUEST['regimen'];
$grado_acad     = $_REQUEST['grado_acad'];


$ver_datos_contacto = $_REQUEST['ver_datos_contacto'];

if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }

$condicion = "";
if (!empty($texto_buscar)) {
	$textos_buscar = explode(" ",sql_regexp($texto_buscar));
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= " AND (lower(pp.nombres) ~* '$cadena_buscada')"
		           .  " OR (lower(pp.apellidos) ~* '$cadena_buscada')"
		           .  " OR (lower(pp.rut) ~* '$cadena_buscada')";
	}
}
if (is_numeric($id_carrera)) { $condicion .= " AND $id_carrera::text = ANY(string_to_array(pp.carreras,',')) "; }
if (!empty($ids_carreras))   { $condicion .= " AND pp.carreras $ids_carreras "; }
if (is_numeric($grado_acad)) { $condicion .= " AND est_grado_acad='$grado_acad' "; }

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_cursos = "SELECT id_profesor FROM vista_cursos WHERE ";

$SQL_datos_contacto_profe = "";
if ($ver_datos_contacto == "Si") { $SQL_datos_contacto_profe = ",vu.direccion,vu.comuna,vu.region,vu.telefono,vu.tel_movil,vu.email_personal"; }

$SQL_carreras_interes = "SELECT char_comma_sum(alias) FROM carreras WHERE id::text = ANY(string_to_array(pp.carreras,','))";

$SQL_profespost = "SELECT pp.id,pp.rut,pp.apellidos,initcap(pp.nombres) AS nombres,($SQL_carreras_interes) AS carreras,pp.genero,
                          horarios,to_char(fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,ga.nombre AS grado_acad,pp.email,p.nombre AS pais
                   FROM portalweb.profes_post AS pp
                   LEFT JOIN grado_acad       AS ga ON ga.id=pp.est_grado_acad
                   LEFT JOIN pais             AS p ON p.localizacion=pp.nacionalidad
                   WHERE true $condicion
                   ORDER BY pp.fecha DESC,pp.apellidos,pp.nombres ";
$SQL_tabla_completa = "COPY ($SQL_profespost) to stdout WITH CSV HEADER";
$SQL_profespost .= "$limite_reg OFFSET $reg_inicio;";
//echo($SQL_profespost);
$profespost = consulta_sql($SQL_profespost);

$enlace_nav = "$enlbase=$modulo"
			. "&id_carrera=$id_carrera"
			. "&texto_buscar=$texto_buscar"
			. "&ver_datos_contacto=$ver_datos_contacto"
			. "&buscar=$buscar"
			. "&r_inicio";

if (count($profespost) > 0) {
	$tot_reg = consulta_sql("SELECT count(id) AS cant_profes FROM portalweb.profes_post AS pp WHERE true $condicion");
	$tot_reg = $tot_reg[0]['cant_profes']; 

	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}


$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }
$SQL_carreras = "SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$grados_academicos = consulta_sql("SELECT id,nombre FROM grado_acad WHERE id>1 ORDER BY id");

$_SESSION['enlace_volver'] = "$enlbase=$modulo&id_carrera=$id_carrera&texto_buscar=$texto_buscar&buscar=$buscar&r_inicio=$reg_inicio";

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();

$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr valign="top">
    <td class="celdaFiltro">
      Régimen: <br>
      <select class="filtro" name="regimen" onChange="submitform();">
        <option value="t">Todos</option>
        <?php echo(select($REGIMENES,$regimen)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Carrera/Programa:<br>
      <select name="id_carrera" onChange="submitform();" class='filtro'>
        <option value="">Todas</option>
        <?php echo(select($carreras,$id_carrera)); ?>    
      </select>
    </td>
    <td class="celdaFiltro">
      Grado Académico:<br>
      <select name="grado_acad" onChange="submitform();" class='filtro'>
        <option value="">Todos</option>
        <?php echo(select($grados_academicos,$grado_acad)); ?>    
      </select>
    </td>
    </td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto">
  <tr valign="top">
    <td class="celdaFiltro">
      Buscar por nombre o RUT del profesor:<br>
      <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="45" id="texto_buscar" class='boton'>
      <script>document.getElementById("texto_buscar").focus();</script>
      <input type='submit' name='buscar' value='Buscar'>          
      <?php if ($buscar == "Buscar" && $texto_buscar <> "") { echo("<input type='submit' name='buscar' value='Vaciar'>"); } ?>
    </td>
<!--    <td class="celdaFiltro">
      Otras acciones:<br>
      <a href='<?php echo("$enlbase=crear_profesor&id_prog_curso=$id"); ?>' class='boton'>Agregar un Profesor(a)</a>
    </td>
    <td class="celdaFiltro">
      Ver Datos de Contacto:<br>
      <input type="checkbox" name="ver_datos_contacto" value="Si" class='boton' onClick="submitform();" <?php if($ver_datos_contacto=="Si") { echo("checked"); } ?>>
    </td> -->
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="4">
      Mostrando <b><?php echo($tot_reg); ?></b> profesor(es) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" style='text-align: right' colspan="3">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Grado Académico</td>
    <td class='tituloTabla'>Carrera(s) de interés</td>
    <td class='tituloTabla'>Horarios</td>
    <td class='tituloTabla'>Fecha</td>
<?php if ($ver_datos_contacto == "Si") { ?>
    <td class='tituloTabla'>Dirección</td>
    <td class='tituloTabla'>Comuna</td>
    <td class='tituloTabla'>Región</td>
    <td class='tituloTabla'>Teléfono</td>
    <td class='tituloTabla'>Tel. Movil</td>
    <td class='tituloTabla'>e-Mail</td>
<?php } ?>
  </tr>
<?php
	if (count($profespost) > 0) {

		for ($x=0; $x<count($profespost); $x++) {
			extract($profespost[$x]);

			$HTML_datos_contacto = "";
			if ($ver_datos_contacto == "Si") { 
				$HTML_datos_contacto = "    <td class='textoTabla' width='300'><small>$direccion</small></td>"
									 . "    <td class='textoTabla'><small>$comuna</small></td>"
									 . "    <td class='textoTabla'><small>$region</small></td>"
									 . "    <td class='textoTabla'><small>$telefono</small></td>"
									 . "    <td class='textoTabla'><small>$tel_movil</small></td>"
									 . "    <td class='textoTabla'><small>$email_personal</small></td>";
			}
			if (empty($carreras)) { $carreras = "No informado"; }

			$enl = "$enlbase_sm=ver_profepost&id=$id";
			$nombre_profepost = "<a id='sgu_fancybox_medium' href='$enl' class='enlaces'>$apellidos<br>$nombres</a>";
			echo("  <tr class='filaTabla'>"
			    ."    <td class='textoTabla' align='right'>$id</td>"
			    ."    <td class='textoTabla' align='right'>$rut</td>"
			    ."    <td class='textoTabla'>$nombre_profepost</td>"
			    ."    <td class='textoTabla'>$grado_acad</td>"
			    ."    <td class='textoTabla'>$carreras</td>"
			    ."    <td class='textoTabla' style='width: 150px'>$horarios</td>"
			    ."    <td class='textoTabla'>$fecha</td>"
			    .$HTML_datos_contacto
			    ."  </tr>\n");
		}
	} else {
		echo("<td class='textoTabla' colspan='7'>"
		    ."  No hay registros para los criterios de búsqueda/selección"
		    ."</td>\n");
	}
?>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

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
</script>

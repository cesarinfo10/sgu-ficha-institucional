<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$regimen    = $_REQUEST['regimen'];
$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") {
	$reg_inicio = 0;
}

if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$id_carrera   = $_REQUEST['id_carrera'];
$id_escuela   = $_REQUEST['id_escuela'];
$regimen      = $_REQUEST['regimen'];

if (empty($regimen)) { $regimen = "PRE"; }

if ($texto_buscar <> "" &&  $buscar == "Buscar") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$id_carrera = $regimen = "";
	$condicion = "WHERE nombre ~* '$texto_buscar_regexp' 
	                 OR codigo ~* '$texto_buscar_regexp'
	                 OR profesor ~* '$texto_buscar_regexp'";
	$id_carrera = $regimen = "";
} else {
	$texto_buscar = "";

	$condicion = " WHERE true ";
	if ($id_carrera > 0) { $condicion .= " AND id_carrera='$id_carrera' "; }

	if ($regimen <> "t") { $condicion .= " AND regimen='$regimen' "; }
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_asignaturas = "SELECT codigo,vc.nombre,profesor,carrera 
                    FROM vista_asignaturas AS vc
                    LEFT JOIN carreras AS c ON c.id=id_carrera
                    $condicion 
                    $limite_reg
                    OFFSET $reg_inicio;";
$asignaturas = consulta_sql($SQL_asignaturas);

$enlace_nav = "$enlbase=$modulo&id_carrera=$id_carrera&regimen=$regimen&texto_buscar=$texto_buscar&buscar=$buscar&r_inicio";
           
if (count($asignaturas) > 0) {
	$SQL_total_asig = "SELECT count(codigo) AS total FROM vista_asignaturas AS vc LEFT JOIN carreras AS c ON c.id=id_carrera $condicion;";
	$total_asig = consulta_sql($SQL_total_asig);
	$tot_reg = $total_asig[0]['total'];
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

for ($x=0;$x<count($asignaturas);$x++) {
	extract($asignaturas[$x]);

	$enl = "$enlbase=ver_asignatura&cod_asignatura=$codigo";
	//$enlace = "<a class='enlitem' href='$enl'>";

	$HTML_asig .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
	           . "    <td class='textoTabla'>$codigo</td>\n"
	           . "    <td class='textoTabla'><a class='enlitem' href='$enl'>$nombre</a></td>\n"
	           . "    <td class='textoTabla'>$profesor</td>\n"
	           . "    <td class='textoTabla'>$carrera</td>\n"
	           . "  </tr>\n";
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "t")   { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

if ($_SESSION['id_escuela'] <> "") { $cond_escuelas = "WHERE id={$_SESSION['id_escuela']}"; }
$SQL_escuelas = "SELECT id,nombre FROM escuelas $cond_escuelas ORDER BY nombre";
$escuelas = consulta_sql($SQL_escuelas);

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$_SESSION['enlace_volver'] = "$enlbase=$modulo&id_carrera=$id_carrera&texto_buscar=$texto_buscar&buscar=$buscar&r_inicio=$reg_inicio";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
	<td class="celdaFiltro">
	  Escuela:<br>
	  <select class="filtro" name="id_escuela" onChange="submitform();">
		<option value="">Todas</option>
		<?php echo(select($escuelas,$id_escuela)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Carrera/Programa:<br>
	  <select class="filtro" name="id_carrera" onChange="submitform();">
		<option value="">Todas</option>
		<?php echo(select($carreras,$id_carrera)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Régimen: <br>
	  <select class="filtro" name="regimen" onChange="submitform();">
		<option value="t">Todos</option>
		<?php echo(select($REGIMENES,$regimen)); ?>
	  </select>
	</td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto">
  <tr valign="top">
	<td class="celdaFiltro">
	  Buscar por Código, nombre o profesor:<br>
	  <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="40" class='boton'>
	  <input type='submit' name='buscar' value='Buscar'>          
	  <?php 
		if ($buscar == "Buscar" && $texto_buscar <> "") {
			echo("<br><input type='submit' name='buscar' value='Vaciar'>");          		
		};
	  ?>
	</td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="2">
      Mostrando <b><?php echo($tot_reg); ?></b> alumno(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="2">
      <?php echo($HTML_paginador); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Código</td>
    <td class='tituloTabla'>Nombre Asignatura</td>
    <td class='tituloTabla'>Profesor Titular</td>
    <td class='tituloTabla'>Carrera</td>
  </tr>
  <?php echo($HTML_asig); ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->


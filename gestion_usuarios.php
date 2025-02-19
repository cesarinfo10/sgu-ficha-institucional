<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$activo = array(array('id'=>"Si",'nombre'=>"Activos"),
                array('id'=>"No",'nombre'=>"Inactivos"));

$cant_reg = 30;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") {
	$reg_inicio = 0;
};

$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$id_carrera   = $_REQUEST['id_carrera'];

if ($texto_buscar <> "" &&  $buscar == "Buscar") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$id_carrera = "";
	$condicion_buscar = "vu.nombre ~* '$texto_buscar_regexp' 
	              OR vu.nombre_usuario ~* '$texto_buscar_regexp'";
} else {
	$texto_buscar = "";
};

$activos =  $_REQUEST['activos'];
if ($activos == "") {
	$activos = "Si";
};

$id_tipo = $_REQUEST['id_tipo'];
if ($id_tipo <> "") {
	$condicion_tipo = "AND vu.id_tipo=$id_tipo";
};

if ($condicion_buscar <> "") {
	$condicion = $condicion_buscar;
	$id_tipo = "";
	$activos = "";
} else {
	$condicion = "vu.activo='$activos' $condicion_tipo";
};

$bdcon = pg_connect("dbname=regacad" . $authbd);
$SQLtxt = "SELECT vu.id,vu.nombre,vu.nombre_usuario AS \"nombre de usuario\",vu.tipo,vu.escuela,gu.alias AS unidad,vu.email
           FROM vista_usuarios AS vu
           LEFT JOIN usuarios AS u USING (id)
           LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
           WHERE $condicion
           ORDER BY vu.nombre_usuario
           LIMIT $cant_reg
           OFFSET $reg_inicio;";

$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$usuarios = pg_fetch_all($resultado);
	$SQLtxt0 = "SELECT count(id) FROM vista_usuarios AS vu WHERE $condicion;";
	$resultado0 = pg_query($bdcon, $SQLtxt0);
	$tot_reg = pg_fetch_row($resultado0, 0);
	$tot_reg = $tot_reg[0];
	$reg_ini_sgte = $reg_inicio + $cant_reg;
	$reg_ini_ante = $reg_inicio - $cant_reg;
	if ($reg_ini_ante < 0) {
		$reg_ini_ante = 0;
	};
	if ($reg_ini_sgte >= $tot_reg) {
		$reg_ini_sgte = 0;
	};
};

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div><br>
<div class="texto">
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="2" border="0" cellspacing="0" width="auto">
  <tr valign="top">
    <td class="texto" width="auto">
      Buscar por nombre de usuario o nombre real:<br>
      <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="20">
      <input type='submit' name='buscar' value='Buscar'>          
		<?php
			if ($buscar == "Buscar" && $texto_buscar <> "") {
				echo("<br><input type='submit' name='buscar' value='Vaciar'>");
			};
		?>
    </td>
    <td class="texto">
      Mostrar a usuarios de tipo:<br>
      <select name="id_tipo" onChange="submitform()">
        <option value="">Todos</option>
			<?php echo(select(tipos_usuario(null),$id_tipo)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="texto" colspan="2" align="center">
      que se encuentren:<br>
      <select name="activos" onChange="submitform()">
			<?php echo(select($activo,$activos)); ?>
      </select>
    </td>
  </tr>
</table>
	<?php
		$enlace_nav = "$enlbase=$modulo&id_carrera=$id_carrera&texto_buscar=$texto_buscar&buscar=$buscar&activos=$activos&id_tipo=$id_tipo&r_inicio";
	?>
  Mostrando <b><?php echo($tot_reg); ?></b> usuario(s) en total, en p&aacute;gina(s) de 30 filas<br>
  <a class="enlaces" href="<?php echo("$enlace_nav=$reg_ini_ante"); ?>">Anterior</a> | 
  <?php
  	for($pag=1;$pag<=ceil($tot_reg/$cant_reg);$pag++) {
  		if ($cant_reg*($pag-1)== $reg_inicio) {
  			echo(" <b>$pag</b> |");
  		} else {
  			$reg_ini_pag = ($pag - 1) * $cant_reg;
  			echo("<a class='enlaces' href='$enlace_nav=$reg_ini_pag'> $pag</a> |");
  		};  			
  	};
  ?>
  <a class="enlaces" href="<?php echo("$enlace_nav=$reg_ini_sgte"); ?>">Siguiente</a>
</form>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="auto">
  <tr class='filaTituloTabla'>
	<?php
		for ($y=0;$y<pg_num_fields($resultado);$y++) {
			$nombre_campo = ucfirst(pg_field_name($resultado,$y));
			echo("    <td class='tituloTabla'>$nombre_campo</td>\n");
		};
	?>
  </tr>
<?php
	for ($x=0; $x<$filas; $x++) {
		$enl = "$enlbase=ver_usuario&id_usuario=" . $usuarios[$x]['id'];
		echo("  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n");
		for ($y=0;$y<pg_num_fields($resultado);$y++) {
			$nombre_campo = pg_field_name($resultado,$y);
			$valor_campo = $usuarios[$x][$nombre_campo];			
			echo("    <td class='textoTabla'><a class='enlitem' href='$enl'>&nbsp;$valor_campo</a></td>\n");
		};
		echo("  </tr>\n");
	};
?>
</table>
<div class="texto">
  <a class="enlaces" href="<?php echo("$enlace_nav=$reg_ini_ante"); ?>">Anterior</a> | 
  <?php
  	for($pag=1;$pag<=ceil($tot_reg/$cant_reg);$pag++) {
  		if ($cant_reg*($pag-1)== $reg_inicio) {
  			echo(" <b>$pag</b> |");
  		} else {
  			$reg_ini_pag = ($pag - 1) * $cant_reg;
  			echo("<a class='enlaces' href='$enlace_nav=$reg_ini_pag'> $pag</a> |");
  		};  			
  	};
  ?>
  <a class="enlaces" href="<?php echo("$enlace_nav=$reg_ini_sgte"); ?>">Siguiente</a>
</div>
<!-- Fin: <?php echo($modulo); ?> -->


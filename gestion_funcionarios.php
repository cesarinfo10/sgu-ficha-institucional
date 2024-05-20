<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");


$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$texto_buscar           = $_REQUEST['texto_buscar'];
$buscar                 = $_REQUEST['buscar'];
$id_tipo                = $_REQUEST['id_tipo'];
$id_unidad              = $_REQUEST['id_unidad'];
$id_tipo_ponderaciones  = $_REQUEST['id_tipo_ponderaciones'];
$activo                 = $_REQUEST['activo'];
$id_jefe_unidad         = $_REQUEST['id_jefe_unidad'];

if (empty($activo)) { $activo = "t"; }

$condicion = "WHERE u.tipo<>3 ";
if ($texto_buscar <> "" &&  $buscar == "Buscar") {
    $texto_buscar_regexp = sql_regexp($texto_buscar);
    $textos_buscar = explode(" ",$texto_buscar_regexp);
	  $condicion .= " AND ";
    for ($x=0;$x<count($textos_buscar);$x++) {
        $cadena_buscada = strtolower($textos_buscar[$x]);
        $condicion .= "(lower(u.nombre||' '||u.apellido) ~* '$cadena_buscada' OR "
                    . " u.rut ~* '$cadena_buscada' OR "
                    . " lower(u.email) ~* '$cadena_buscada' OR "
                    . " lower(u.nombre_usuario) ~* '$cadena_buscada' OR "
                    . " text(u.id) ~* '$cadena_buscada' "
                    . ") AND ";
    }
    $condicion=substr($condicion,0,strlen($condicion)-4);
    $activo = $id_tipo = $id_unidad = $grupo_evdem = $id_jefe_unidad = null;
} else {

    if ($activo == "t") { $condicion .= " AND u.activo "; }
    if ($activo == "f") { $condicion .= " AND NOT u.activo "; }

    if ($id_unidad > 0) { $condicion .= " AND u.id_unidad=$id_unidad "; }
    
    if ($id_tipo <> "") { $condicion .= " AND u.tipo=$id_tipo "; }

    if ($id_tipo_ponderaciones > 0) { $condicion .= " AND u.id_tipo_ponderaciones=$id_tipo_ponderaciones "; }

    if ($id_jefe_unidad == "t") { $condicion .= " AND u.jefe_unidad "; }
    if ($id_jefe_unidad == "f") { $condicion .= " AND NOT u.jefe_unidad "; }

}

$enlace_nav = "$enlbase=$modulo&"
            . "id_unidad=$id_unidad&"
            . "texto_buscar=$texto_buscar&"
            . "buscar=$buscar&"
            . "activo=$activo&"
            . "id_tipo=$id_tipo&"
            . "id_tipo_ponderaciones=$id_tipo_ponderaciones&"
            . "id_jefe_unidad=$id_jefe_unidad&"
            . "r_inicio";

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_usuarios = "SELECT vu.id,vu.nombre,vu.nombre_usuario,vu.tipo,vu.escuela,gu.alias AS unidad,vu.email,
                        CASE WHEN jefe_unidad THEN '[Jefe]' ELSE '' END AS jefe_unidad
                 FROM vista_usuarios AS vu
                 LEFT JOIN usuarios AS u USING (id)
                 LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
                 $condicion
                 ORDER BY vu.nombre_usuario";
$SQL_tabla_completa = "COPY ($SQL_usuarios) to stdout WITH CSV HEADER";

$SQL_usuarios .= " $limite_reg OFFSET $reg_inicio";

$usuarios = consulta_sql($SQL_usuarios);
//echo($SQL_usuarios);

if ($usuarios > 0) {
	$SQL_tot_usuarios = "SELECT count(vu.id) as cant_usuarios
	                     FROM vista_usuarios AS vu
	                     LEFT JOIN usuarios AS u USING (id)
	                     LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
	                     $condicion";
	$tot_reg = consulta_sql($SQL_tot_usuarios);
	$tot_reg = $tot_reg[0]['cant_usuarios']; 
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);

}

$UNIDADES = consulta_sql("SELECT u.id,u.nombre||' ('||u.alias||')' AS nombre,u2.nombre AS grupo FROM gestion.unidades u LEFT JOIN gestion.unidades AS u2 ON u2.id=u.dependencia WHERE u.dependencia IS NOT NULL  ORDER BY u2.id,u.nombre");

$TIPOS_PONDERACIONES = consulta_sql("SELECT id,glosa_tipo_ponderaciones AS nombre FROM tipo_ponderaciones ORDER BY id");

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

<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
    <td class="celdaFiltro">
      Unidad:<br>
      <select name="id_unidad" onChange="submitform()">
        <option value="">Todas</option>
		    <?php echo(select_group($UNIDADES,$id_unidad)); ?>
      </select>
    </td>	        
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto">
  <tr>
    <td class="celdaFiltro">
      Tipo:<br>
      <select name="id_tipo" onChange="submitform()">
        <option value="">Todos</option>
	     	<?php echo(select(tipos_usuario(null),$id_tipo)); ?>
      </select>
    </td>
	  <td class="celdaFiltro">
      Activo:<br>
      <select name="activo" onChange="submitform()">
        <option value="">Todos</option>
		    <?php echo(select($sino,$activo)); ?>
      </select>
    </td>	
	<td class="celdaFiltro">
      Grupo Ev. Desempeño:<br>
      <select name="id_tipo_ponderaciones" onChange="submitform()">
        <option value="">Todos</option>
		    <?php echo(select($TIPOS_PONDERACIONES,$id_tipo_ponderaciones)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Jefe Unidad:<br>
      <select name="id_jefe_unidad" onChange="submitform()">
        <option value="">Todos</option>
		    <?php echo(select($sino,$id_jefe_unidad)); ?>
      </select>
    </td>
  <tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto">
  <tr>
    <td class="celdaFiltro">
	  Buscar por nombres, apellidos o nombre de usuario:<br>
      <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="45" class='boton' id="texto_buscar">
      <input type='submit' name='buscar' value='Buscar'>          
      <?php if ($buscar == "Buscar" && $texto_buscar <> "") { echo("<input type='submit' name='buscar' value='Vaciar'>"); } ?>
      <script>document.getElementById('texto_buscar').focus();</script>
    </td>
  <tr>
</table>

</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="auto">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="10">
      Mostrando <b><?php echo($tot_reg); ?></b> funcionario/a(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Nombre de Usuario</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Unidad</td>
    <td class='tituloTabla'>Escuela</td>
  </tr>
<?php
	$HTML = "";
	for ($x=0; $x<count($usuarios); $x++) {
		extract($usuarios[$x]);

		$enl = "$enlbase=ver_usuario&id_usuario=$id";
		$HTML .= "<tr class='filaTabla' $tr_style onClick=\"window.location='$enl';\">\n"
		      .  "  <td class='textoTabla'>$id</td>\n"
		      .  "  <td class='textoTabla'><a class='enlitem' href='$enl'>$nombre_usuario/$tipo</a></td>\n"
		      .  "  <td class='textoTabla'>$nombre</td>\n"
		      .  "  <td class='textoTabla'>$unidad $jefe_unidad</td>\n"
		      .  "  <td class='textoTabla'>$escuela</td>\n"
			  .  "</tr>";
	}
	echo($HTML);
?>
</table>
</form>


<!-- Fin: <?php echo($modulo); ?> -->


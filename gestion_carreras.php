<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$regimen     = $_REQUEST['regimen'];
$id_escuela  = $_REQUEST['id_escuela'];
$id_activa   = $_REQUEST['id_activa'];
$id_admision = $_REQUEST['id_admision'];

if (empty($_REQUEST['regimen'])) { $regimen = "PRE"; }
if (empty($_REQUEST['id_activa']))  { $id_activa = "t"; }

$condiciones = "WHERE true ";

if (!empty($id_escuela)) { $condiciones .= " AND c.id_escuela=$id_escuela "; }

if ($regimen <> "" && $regimen <> "t") { $condiciones .= " AND c.regimen='$regimen' "; }

if ($id_activa == "t")     { $condiciones .= " AND c.activa "; } 
elseif ($id_activa == "f") { $condiciones .= " AND NOT c.activa "; } 

if ($id_admision == "t")     { $condiciones .= " AND c.admision "; } 
elseif ($id_admision == "f") { $condiciones .= " AND NOT c.admision "; } 

$SQL_carreras = "SELECT c.id,c.nombre,c.alias,vc.coordinador,vc.escuela,vc.activa,
                        CASE WHEN c.admision THEN 'Si' ELSE 'No' END AS admision,
                        vc.malla,vc.regimen AS regimen_,cod_sies_matunif,
                        ccc.codigo_erp AS centro_costo
                 FROM vista_carreras AS vc
                 LEFT JOIN carreras AS c USING (id)
                 LEFT JOIN finanzas.conta_centrosdecosto AS ccc ON ccc.id_carrera=c.id
                 $condiciones
                 ORDER BY c.nombre";
$carreras     = consulta_sql($SQL_carreras);
$cant_campos  = count($carreras[0]);

$HTML = "";

if (count($carreras) == 0) { $HTML = "<tr><td class='textoTabla' align='center' colspan='8'><br><br>*** No hay carreras que coincidan con los filtros aplicados ***<br><br><br></td></tr>"; }

for ($x=0; $x<count($carreras); $x++) {
	extract($carreras[$x]);
	
	$enl = "$enlbase=ver_carrera&enl_volver=history.back();&id_carrera=" . $carreras[$x]['id'];
	$enlace = "<a class='enlitem' href='$enl'>";
	$HTML .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
		  .  "    <td class='textoTabla' align='right' style='color: #7F7F7F'>$id</td>\n"
		  .  "    <td class='textoTabla'>$nombre</td>\n"
		  .  "    <td class='textoTabla'>$alias</td>\n"
		  .  "    <td class='textoTabla' align='right' >$cod_sies_matunif</td>\n"
		  .  "    <td class='textoTabla'>$coordinador</td>\n"
		  .  "    <td class='textoTabla'>$escuela</td>\n"
		  .  "    <td class='textoTabla' align='center'>$activa</td>\n"
		  .  "    <td class='textoTabla' align='center'>$admision</td>\n"
		  .  "    <td class='textoTabla' align='right'>$malla</td>\n"
		  .  "    <td class='textoTabla'>$regimen_</td>\n"
		  .  "    <td class='textoTabla'>$centro_costo</td>\n"
		  .  "  </tr>";
}

$REGIMENES = consulta_sql("SELECT * FROM regimenes");
$ESCUELAS = consulta_sql("SELECT id,nombre FROM escuelas ORDER BY nombre;");

?>

<!-- Inicio: Gestion de carreras -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>	
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
	<td class="celdaFiltro">
	  Escuela:<br>
	  <select class='filtro' name="id_escuela" onChange="submitform();">
		<option value="">Todas</option>
		<?php echo(select($ESCUELAS,$id_escuela)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Régimen:<br>
	  <select class='filtro' name="regimen" onChange="submitform();">
		<option value="t">Todos</option>
		<?php echo(select($REGIMENES,$regimen)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Activa:<br>
	  <select class='filtro' name="id_activa" onChange="submitform();">
		<option value="-1">Todos</option>
		<?php echo(select($sino,$id_activa)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Admisión abierta:<br>
	  <select class='filtro' name="id_admision" onChange="submitform();">
		<option value="-1">Todos</option>
		<?php echo(select($sino,$id_admision)); ?>
	  </select>
	</td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='color: #7F7F7F'>ID</td>
    <td class='tituloTabla' width='300'>Nombre</td>
    <td class='tituloTabla'>Alias</td>
    <td class='tituloTabla'>Cod. Mat.<br>Unificada</td>    
    <td class='tituloTabla'>Coordinador</td>
    <td class='tituloTabla'>Escuela</td>
    <td class='tituloTabla'>Activa</td>
    <td class='tituloTabla'>Admisión<br>Abierta</td>
    <td class='tituloTabla'>Malla<br>Actual</td>
    <td class='tituloTabla'>Régimen</td>
    <td class='tituloTabla' style='color: #7F7F7F'><small>C. Costo (ERP)</small></td>
  </tr>
  <?php echo($HTML); ?>
</table>
</form>
<!-- Fin: Gestion de carreras -->

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1000,
		'height'			: 400,
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
		'width'				: 500,
		'height'			: 350,
		'maxHeight'			: 350,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>

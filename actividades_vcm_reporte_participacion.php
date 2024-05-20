<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
setlocale(LC,"es_CL.UTF8");
include("validar_modulo.php");
$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$id_ano          = $_REQUEST['id_ano'];
$id_estado       = $_REQUEST['id_estado'];
$id_dimension    = $_REQUEST['id_dimension'];
$id_tipo_act     = $_REQUEST['id_tipo_act'];
$id_tipo_unidad  = $_REQUEST['id_tipo_unidad'];
$id_alcance      = $_REQUEST['id_alcance'];
$id_modalidad    = $_REQUEST['id_modalidad'];
$id_unidad       = $_REQUEST['id_unidad'];
$id_usuario_resp = $_REQUEST['id_usuario_resp'];
$id_tipo_publico = $_REQUEST['id_tipo_publico'];
$fec_ini         = $_REQUEST['fec_ini'];
$fec_fin         = $_REQUEST['fec_fin'];

//if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['id_ano'])) { $id_ano = $ANO; }
if (empty($_REQUEST['id_estado'])) { $id_estado = 'Efectuadas'; }
if (empty($_REQUEST['id_dimension'])) { $id_dimension = ""; }
if (empty($_REQUEST['id_usuario_resp'])) { 
	if (count(consulta_sql("SELECT id FROM vcm.actividades WHERE ano=$id_ano AND id_responsable={$_SESSION['id_usuario']}")) > 0) {
		$id_usuario_resp = $_SESSION['id_usuario'];
	}
}
if (empty($cond_base)) { $cond_base = "true"; }
if (empty($fec_ini)) { $fec_ini = "$id_ano-01-01"; }
if (empty($fec_fin)) { $fec_fin = date("Y-m-d"); }

$condicion = "WHERE $cond_base  ";

if ($id_ano > 0) { $condicion .= "AND (ano = $id_ano) "; }

if ($id_estado <> "-1") { 
    if ($id_estado == 'Efectuadas') { $condicion .= " AND (act.estado IN ('Realizada','Pendiente','Archivada','Finalizada'))"; }
    else {$condicion .= "AND (act.estado = '$id_estado') "; }
}

if ($id_dimension <> "") { $condicion .= "AND (id_tipo IN (SELECT id FROM vcm.tipos_act WHERE dimension='$id_dimension')) "; $id_tipo = null;}

if ($id_tipo_act > 0) { $condicion .= "AND (id_tipo = $id_tipo_act) "; }

if ($id_alcance <> "") { $condicion .= "AND (alcance = '$id_alcance') "; }

if ($id_modalidad <> "") { $condicion .= "AND (modalidad = '$id_modalidad') "; }
    
if ($id_unidad > 0) { $condicion .= "AND ($id_unidad IN (id_unidad1,id_unidad2,id_unidad3)) "; }

if ($id_tipo_unidad <> "") { $condicion .= "AND ('$id_tipo_unidad' IN (tipo_unidad1,tipo_unidad2,tipo_unidad3)) "; }

if ($id_usuario_resp > 0) { $condicion .= "AND (id_responsable = $id_usuario_resp	) "; }

if ($id_tipo_publico <> "") { $condicion .= "AND (tipo_publico IN ('$id_tipo_publico')) "; }

if ($fec_ini <> "" && $fec_fin <> "") {	$condicion .= "AND fecha_termino BETWEEN '$fec_ini'::date AND '$fec_fin'::date "; }

$SQL_act = "SELECT id 
            FROM vista_vcm_actividades AS act
			$condicion";
$act = consulta_sql($SQL_act);

$SQL_act_con_asistencia = "SELECT DISTINCT ON (id_actividad) id_actividad 
                           FROM vcm.participacion_act
			               WHERE id_actividad IN ($SQL_act) AND (cant_personas IS NOT NULL OR cant_personas_virtuales IS NOT NULL)";
$act_con_asistencia = consulta_sql($SQL_act_con_asistencia);
//echo($SQL_act_con_asistencia);

$SQL_asist_tot = "SELECT tipo_publico,
                         sum(coalesce(cant_personas,0)) AS cant_personas,
						 sum(coalesce(cant_personas_virtuales,0)) AS cant_personas_virtuales,
						 (sum(coalesce(cant_personas,0)+coalesce(cant_personas_virtuales,0))) AS total_personas
                  FROM vcm.participacion_act
			      WHERE id_actividad IN ($SQL_act)
				  GROUP BY tipo_publico
				  ORDER BY tipo_publico";
$asist_tot = consulta_sql($SQL_asist_tot);
//echo($SQL_asist_tot);

if (count($asist_tot) > 0) {
	$HTML = "";
	$tot_cant_personas = $tot_cant_personas_virtuales = $tot_total_personas = 0;
	for ($x=0;$x<count($asist_tot);$x++) {
		extract($asist_tot[$x]);
		$HTML .= "<tr class='filaTabla'>"
		      .  "  <td class='textoTabla'>$tipo_publico</td>"
		      .  "  <td class='textoTabla' align='center'>$cant_personas</td>"
		      .  "  <td class='textoTabla' align='center'>$cant_personas_virtuales</td>"
		      .  "  <td class='textoTabla' align='center'><b>$total_personas</b></td>"
			  .  "</tr>";
		$tot_cant_personas           += $cant_personas;
		$tot_cant_personas_virtuales += $cant_personas_virtuales;
		$tot_total_personas          += $total_personas;
	}
	$HTML .= "<tr class='filaTabla'>"
	      .  "  <td class='celdaNombreAttr'>Total:</td>"
		  .  "  <td class='celdaNombreAttr' style='text-align: center'>$tot_cant_personas</td>"
		  .  "  <td class='celdaNombreAttr' style='text-align: center'>$tot_cant_personas_virtuales</td>"
		  .  "  <td class='celdaNombreAttr' style='text-align: center'>$tot_total_personas</td>"
		  .  "</tr>"
		  .  "<tr class='filaTabla'>"
	      .  "  <td class='tituloTabla' style='text-align: right' colspan='3'>Actividades contabilizadas:</td>"
		  .  "  <td class='textoTabla' align='center'>". count($act)."</td>"
		  .  "</tr>"
		  .  "<tr class='filaTabla'>"
	      .  "  <td class='tituloTabla' style='text-align: right' colspan='3'>Actividades contabilizadas con asistencia registrada:</td>"
		  .  "  <td class='textoTabla' align='center'>". count($act_con_asistencia)."</td>"
		  .  "</tr>";
}

$ANOS           = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM vcm.actividades ORDER BY ano DESC");
$ESTADOS        = consulta_sql("SELECT id,nombre FROM vista_vcm_estado_act");
$DIMENSIONES    = consulta_sql("SELECT id,nombre FROM vista_vcm_dimensiones_act ORDER BY nombre");
$cond_tipo_act  = ($id_dimension <> "") ? "WHERE dimension='$id_dimension'" : "";
$TIPOS          = consulta_sql("SELECT id,nombre,dimension AS grupo FROM vcm.tipos_act $cond_tipo_act ORDER BY grupo,nombre");
$cond_tipo_unidad = ($id_tipo_unidad <> "") ? "AND u.tipo='$id_tipo_unidad'" : "";
$UNIDADES       = consulta_sql("SELECT u.id,u.nombre,uu.nombre AS grupo FROM gestion.unidades u LEFT JOIN gestion.unidades uu ON uu.id=u.dependencia WHERE u.dependencia IS NOT NULL $cond_tipo_unidad ORDER BY uu.id,u.nombre");
$RESPONSABLES   = consulta_sql("SELECT id_responsable AS id,nombre_responsable||' ('||count(id)||')' AS nombre FROM vista_vcm_actividades GROUP BY id_responsable,nombre_responsable ORDER BY nombre_responsable");
$MODALIDADES    = consulta_sql("SELECT id,nombre FROM vista_vcm_modalidad_act ORDER BY nombre");
$TIPO_PUBLICO   = consulta_sql("SELECT id,nombre FROM vista_vcm_tipo_publico ORDER BY nombre");
$TIPOS_UNIDADES = consulta_sql("SELECT id,nombre FROM vista_tipos_unidades ORDER BY nombre");
$ALCANCE        = consulta_sql("SELECT id,nombre FROM vista_vcm_alcance_act ORDER BY nombre");

$ESTADOS[] = array('id' => "Efectuadas", 'nombre' => "Efectuadas (Realizadas, Pendientes, Archivadas y Finalizadas)");

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto">
  <form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="get">
	<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
	  <tr>
	    <td class="celdaFiltro">
		  Año: <br>
		  <select class="filtro" name="id_ano" onChange="submitform();">
            <option value="-1">Todos</option>
			<?php echo(select($ANOS,$id_ano)); ?>    
		  </select>
		</td>
		<td class="celdaFiltro">
		  Estado: <br>
		  <select class="filtro" name="id_estado" onChange="submitform();">
			<option value="-1">Todos</option>
			<?php echo(select($ESTADOS,$id_estado)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Dimensión: <br>
		  <select class="filtro" name="id_dimension" onChange="submitform();">
			<option value="">Todas</option>
            <?php echo(select($DIMENSIONES,$id_dimension)); ?>
		  </select>
        </td>
		<td class="celdaFiltro">
		  Tipo: <br>
		  <select class="filtro" name="id_tipo_act" onChange="submitform();">
			<option value="-1">Todas</option>
            <?php echo(select_group($TIPOS,$id_tipo_act)); ?>
		  </select>
        </td>
		<td class="celdaFiltro">
		  Alcance: <br>
		  <select class="filtro" name="id_alcance" onChange="submitform();">
			<option value="">Todos</option>
			<?php echo(select($ALCANCE,$id_alcance)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Modalidad: <br>
		  <select class="filtro" name="id_modalidad" onChange="submitform();">
			<option value="">Todas</option>
			<?php echo(select($MODALIDADES,$id_modalidad)); ?>
		  </select>
		</td>
	  </tr>
	</table>
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
	  <tr>
		<td class="celdaFiltro">
		  Tipo Unidad: <br>
		  <select class="filtro" name="id_tipo_unidad" onChange="submitform();">
			<option value="">Todas</option>
			<?php echo(select_group($TIPOS_UNIDADES,$id_tipo_unidad)); ?>    
		  </select>
		</td>
		<td class="celdaFiltro">
		  Unidad organizadora: <br>
		  <select class="filtro" name="id_unidad" onChange="submitform();">
			<option value="-1">Todas</option>
			<?php echo(select_group($UNIDADES,$id_unidad)); ?>    
		  </select>
		</td>
		<td class="celdaFiltro">
		  Responsable: <br>
		  <select class="filtro" name="id_usuario_resp" style="spacing: 0px" onChange="submitform();">
			<option value="t">Todas</option>
			<?php echo(select($RESPONSABLES,$id_usuario_resp)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Público Objetivo: <br>
		  <select class="filtro" name="id_tipo_publico" onChange="submitform();">
			<option value="">Todos</option>
			<?php echo(select($TIPO_PUBLICO,$id_tipo_publico)); ?>
		  </select>
		</td>
	  </tr>
	</table>
    <table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
	  <tr class='filaTituloTabla'>
		<td class='tituloTabla' colspan='5'>
		  Actividades cuya fecha de término está entre el 
          <input type="date" name="fec_ini" value="<?php echo($fec_ini); ?>" id="fec_ini" class="boton" style='font-size: 8pt' onChange="submitform();"> y el
          <input type="date" name="fec_fin" value="<?php echo($fec_fin); ?>" id="fec_fin" class="boton" style='font-size: 8pt' onChange="submitform();">
		</td>
      </tr>
	  <tr class='filaTituloTabla'>
		<td class='tituloTabla' rowspan='2'>Tipo de Público</td>
		<td class='tituloTabla' colspan='3'>Asistentes</td>
      </tr>
	  <tr class='filaTituloTabla'>
		<td class='tituloTabla'>Presenciales</td>
		<td class='tituloTabla'>Virtuales</td>
		<td class='tituloTabla'>Total</td>
      </tr>
	  <?php echo($HTML); ?>
    </table>
  </form>
</div>

<!-- Fin: <?php echo($modulo); ?> -->
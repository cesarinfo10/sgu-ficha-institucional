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

$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$anos              = implode(",",$_REQUEST['anos']);
$id_estado         = $_REQUEST['id_estado'];
$id_dimension      = $_REQUEST['id_dimension'];
$id_tipo           = $_REQUEST['id_tipo'];
$id_alcance        = $_REQUEST['id_alcance'];
$id_formato_public = $_REQUEST['id_formato_public'];
$id_usuario_reg    = $_REQUEST['id_usuario_reg'];

if (empty($_REQUEST['anos'])) { $anos = $ANO-1 . "," . $ANO; }
if (empty($_REQUEST['id_dimension'])) { $id_dimension = ""; }
if (empty($_REQUEST['id_usuario_reg'])) { 
	if (count(consulta_sql("SELECT id FROM dpii.productos_acad WHERE ano=$id_ano AND id_usuario_reg={$_SESSION['id_usuario']}")) > 0) {
		$id_usuario_reg = $_SESSION['id_usuario'];
	}
}
if (empty($cond_base)) { $cond_base = "true"; }

$condicion = "WHERE $cond_base AND (vpa.ano IN ($anos)) ";

if ($id_estado > 0) { $condicion .= "AND (vpa.id_estado = $id_estado) "; }
elseif ($id_estado == "Finalizados") { $condicion .= "AND (vpa.id_estado IN (SELECT id FROM dpii.estados_prod WHERE termino_exitoso)) "; }

if ($id_dimension <> "") { 
    $condicion .= "AND (vpa.id_tipo IN (SELECT id FROM dpii.tipos_prod WHERE dimension='$id_dimension')) ";
    $SQL_tipos_prod = "SELECT id FROM dpii.tipos_prod WHERE dimension='$id_dimension' AND id=$id_tipo";
    if (count(consulta_sql($SQL_tipos_prod)) == 0) { $id_tipo = null; } 
}

if ($id_tipo > 0) { $condicion .= "AND (vpa.id_tipo = $id_tipo) "; }

if ($id_alcance <> "") { $condicion .= "AND (vpa.alcance = '$id_alcance') "; }

if ($id_formato_public <> "") { $condicion .= "AND (vpa.id_formato_public = '$id_formato_public') "; }
    
if ($id_usuario_reg > 0) { $condicion .= "AND (vpa.id_usuario_reg = $id_usuario_reg	) "; }
	
$enlace_nav = "texto_buscar=$texto_buscar&"
            . "buscar=$buscar"
            . "id_ano=$id_ano"
            . "id_estado=$id_estado"
            . "id_dimension=$id_dimension"
            . "id_tipo=$id_tipo"
            . "id_alcance=$id_alcance"
            . "id_formato_public=$id_formato_public"
            . "id_usuario_reg=$id_usuario_reg";

$SQL_prod = "SELECT vpa.ano,vpa.dimension,vpa.nombre_tipo_prod,count(vpa.id) AS cant_prod
            FROM vista_dpii_productos_acad AS vpa
            $condicion
            GROUP BY vpa.dimension,vpa.nombre_tipo_prod,vpa.ano
            ORDER BY vpa.dimension,vpa.nombre_tipo_prod,vpa.ano";
//echo($SQL_prod);
$prod = consulta_sql($SQL_prod);

$aDimensiones  = array_unique(array_column($prod,"dimension"));
$aNombre_tipos = array_unique(array_column($prod,"nombre_tipo_prod"));
$aAnos         = explode(",",$anos);
//var_dump($aAnos);
$cant_anos     = count($aAnos)+1;
$tot = array();
$y = 0;
$HTML = "";
foreach($aDimensiones AS $dimension) {
	$tot_dimension = array();
	$HTML .= "<tr class='filaTabla'><td class='textoTabla' colspan='$cant_anos' style='text-align: center'><b><i>$dimension</i></b></td></tr>\n";
	foreach($aNombre_tipos AS $nombre_tipo_prod) {		
		if ($dimension == $prod[$y]['dimension'] && $nombre_tipo_prod == $prod[$y]['nombre_tipo_prod']) {
			$HTML .= "<tr class='filaTabla'>\n<td class='textoTabla'>$nombre_tipo_prod</td>\n";
			foreach($aAnos AS $ano) {
				$tot_dimension[$ano] += 0;
				if ($dimension == $prod[$y]['dimension'] && $nombre_tipo_prod == $prod[$y]['nombre_tipo_prod'] && $ano == $prod[$y]['ano']) {
					$HTML .= "<td class='textoTabla' align='right'>{$prod[$y]['cant_prod']}</td>\n";
					$tot_dimension[$ano] += $prod[$y]['cant_prod'];
					$tot[$ano] += $prod[$y]['cant_prod'];
					$y++;
				} else {
					$HTML .= "<td class='textoTabla' align='right'>0</td>\n";
				}
			}
			$HTML .= "</tr>\n";	
		} else { 
			for ($z=0;$z<count($prod);$z++) {
				if ($dimension == $prod[$z]['dimension']) { $y = $z; break; }
			}
		}
	}
	$HTML .= "<tr class='filaTabla'><td class='celdaNombreAttr' align='right'><b>SubTotal $dimension:</b></td>";
	foreach($tot_dimension AS $tot_ano) {
		$HTML .= "<td class='celdaNombreAttr' style='text-align: right'><b>$tot_ano</b></td>";
	}
	$HTML .= "</tr>\n";
	$y = 0;
}
$HTML .= "<tr class='filaTabla'><td class='textoTabla' colspan='$cant_anos' style='text-align: center'>&nbsp;</td></tr>\n"
      .  "<tr class='filaTabla'><td class='celdaNombreAttr' align='right'><b>Total:</b></td>";

foreach($aAnos AS $ano) {
	$HTML .= "<td class='celdaNombreAttr' style='text-align: right'><b>{$tot[$ano]}</b></td>";
}
$HTML .= "</tr>\n";

$enlace_nav = "$enlbase=$modulo"
			. "&anos=$anos"
			. "&id_estado=$id_estado"
			. "&id_dimension=$id_dimension"
			. "&id_tipo=$id_tipo"
			. "&id_alcance=$id_alcance"
			. "&id_modalidad=$id_modalidad"
			. "&id_responsable=$id_responsable"
			. "&texto_buscar=$texto_buscar"
			. "&buscar=$buscar"
			. "&r_inicio";

$ANOS           = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM dpii.productos_acad ORDER BY ano");

$cond_estado_prod = ($id_dimension <> "") ? "WHERE '$id_dimension' = ANY (dimension)" : "";
$ESTADOS          = consulta_sql("SELECT id,nombre,CASE WHEN NOT termino_exitoso THEN 'Avance' ELSE 'Finalizado exitoso' END AS grupo FROM dpii.estados_prod $cond_estado_prod");

$DIMENSIONES    = consulta_sql("SELECT id,nombre FROM vista_dpii_dimensiones_prod ORDER BY nombre");

$cond_tipo_prod = ($id_dimension <> "") ? "WHERE dimension='$id_dimension'" : "";
$TIPOS          = consulta_sql("SELECT id,nombre,dimension AS grupo FROM dpii.tipos_prod $cond_tipo_prod ORDER BY grupo,nombre");

$USUARIOS_REG    = consulta_sql("SELECT id_usuario_reg AS id,nombre_reg||' ('||count(id)||')' AS nombre FROM vista_dpii_productos_acad GROUP BY id_usuario_reg,nombre_reg ORDER BY nombre_reg");
$FORMATOS_PUBLIC = consulta_sql("SELECT id,nombre FROM vista_dpii_formato_public_prod ORDER BY nombre");
$ALCANCE         = consulta_sql("SELECT id,nombre FROM vista_dpii_alcance_prod ORDER BY nombre");

$ESTADOS[] = array('id' => "Finalizados", 'nombre' => "Finalizados (Terminado, Publicado o Expuesto)", 'grupo' => "Finalizado exitoso");

$HTML_filtro_anos = "";
for ($x=0;$x<count($ANOS);$x++) {
	$checked = "";
	$ano = $ANOS[$x]['id'];
	if (in_array($ano,$aAnos)) { $checked = "checked='checked'"; }
	$HTML_filtro_anos .= "<input style='vertical-align: bottom; ' type='checkbox' name='anos[]' value='$ano' id='$ano' onChange='submitform();' $checked> <label for='$ano'>$ano</label>&nbsp;&nbsp;";
}

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
  <form name="formulario" action="principal_sm.php" method="get">
	<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
	  <tr>
        <td class="celdaFiltro" colspan="1">
          Años:<br>
          <div style='vertical-align: top'><?php echo($HTML_filtro_anos); ?></div>
        </td>
		<td class="celdaFiltro">
		  Estado: <br>
		  <select class="filtro" name="id_estado" onChange="submitform();">
			<option value="">Todos</option>
			<?php echo(select_group($ESTADOS,$id_estado)); ?>
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
		  <select class="filtro" name="id_tipo" onChange="submitform();">
			<option value="-1">Todas</option>
            <?php echo(select_group($TIPOS,$id_tipo)); ?>
		  </select>
        </td>
	  </tr>
	</table>
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
       <td class="celdaFiltro">
		  Alcance: <br>
		  <select class="filtro" name="id_alcance" onChange="submitform();">
			<option value="">Todos</option>
			<?php echo(select($ALCANCE,$id_alcance)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Formato: <br>
		  <select class="filtro" name="id_formato_public" onChange="submitform();">
			<option value="">Todas</option>
			<?php echo(select($FORMATOS_PUBLIC,$id_formato_public)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Registrado por: <br>
		  <select class="filtro" name="id_usuario_reg" style="spacing: 0px" onChange="submitform();">
			<option value="t">Todas</option>
			<?php echo(select($USUARIOS_REG,$id_usuario_reg)); ?>
		  </select>
		</td>
	  </tr>
	</table>
  </form>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
	<td class='tituloTabla'>Tipo</td>
    <?php for($x=0;$x<count($aAnos);$x++) { echo("<td class='tituloTabla'>{$aAnos[$x]}</td>\n"); } ?>
  </tr>
  <?php echo($HTML); ?>
</table><br>
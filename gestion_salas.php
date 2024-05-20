<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}
 
include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) {	$cant_reg = 90; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$activas             = $_REQUEST['activas'];
$piso                = $_REQUEST['piso'];
$capacidad           = $_REQUEST['capacidad'];
$tipo_sala           = $_REQUEST['tipo_sala'];
$tipo_luminaria      = $_REQUEST['tipo_luminaria'];
$tipo_silla          = $_REQUEST['tipo_silla'];
$tipo_piso           = $_REQUEST['tipo_piso'];
$orientacion         = $_REQUEST['orientacion'];
$computador          = $_REQUEST['computador'];
$proyector           = $_REQUEST['proyector'];
$parlantes           = $_REQUEST['parlantes'];
$pizarra_interactiva = $_REQUEST['pizarra_interactiva'];
$webcam              = $_REQUEST['webcam'];
$aire_acond          = $_REQUEST['aire_acond'];
$cortinas            = $_REQUEST['cortinas'];
$doble_vidrio        = $_REQUEST['doble_vidrio'];

if (empty($activas)) { $activas = "t"; }

$condicion = "";
if ($activas == "t") { $condicion .= " AND activa "; }
if ($activas == "f") { $condicion .= " AND NOT activa "; }
if ($piso > 0)       { $condicion .= " AND piso=$piso "; }
switch ($capacidad) {
	case "60 ~ 70":
		$condicion .= " AND capacidad between 60 AND 70";
		break;
	case "50 ~ 59":
		$condicion .= " AND capacidad between 50 AND 59";
		break;
	case "30 ~ 49":
		$condicion .= " AND capacidad between 30 AND 49";
		break;
	case "29 o menos":
		$condicion .= " AND capacidad<=29";
		break;
}

if ($tipo_sala<>"") { $condicion .= " AND tipo IN ('".str_replace(",","','",$tipo_sala)."') "; }
if ($tipo_luminaria<>"") { $condicion .= " AND tipo_luminaria='$tipo_luminaria' "; }
if ($tipo_silla<>"") { $condicion .= " AND tipo_silla='$tipo_silla' "; }
if ($tipo_piso<>"") { $condicion .= " AND tipo_piso='$tipo_piso' "; }
if ($orientacion<>"") { $condicion .= " AND orientacion='$orientacion' "; }
if ($computador<>"") { $condicion .= " AND computador='$computador' "; }
if ($proyector<>"") { $condicion .= " AND proyector='$proyector' "; }
if ($parlantes<>"") { $condicion .= " AND parlantes='$parlantes' "; }
if ($pizarra_interactiva<>"") { $condicion .= " AND pizarra_interactiva='$pizarra_interactiva' "; }
if ($webcam<>"") { $condicion .= " AND webcam='$webcam' "; }
if ($aire_acond<>"") { $condicion .= " AND aire_acond='$aire_acond' "; }
if ($cortinas<>"") { $condicion .= " AND cortinas='$cortinas' "; }
if ($doble_vidrio<>"") { $condicion .= " AND doble_vidrio='$doble_vidrio' "; }

$enlace_nav = "$enlbase=$modulo"
            . "&activa=$activa"
            . "&capacidad=$capacidad"
            . "&piso=$piso"
            . "&tipo=$tipo"
            . "&cant_reg=$cant_reg"
            . "&r_inicio";

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_salas = "SELECT *,(SELECT count(id) FROM cursos 
                        WHERE semestre=$SEMESTRE AND ano=$ANO 
                          AND salas.codigo IN (sala1,sala2,sala3)) AS uso_actual
              FROM salas WHERE true $condicion 
              ORDER BY piso,nombre 
              $limite_reg OFFSET $reg_inicio;";
$salas     = consulta_sql($SQL_salas);
if (count($salas) > 0) {
	$tot_reg = consulta_sql("SELECT count(codigo) AS cant_salas FROM salas WHERE true $condicion;");
	$tot_reg = $tot_reg[0]['cant_salas']; 
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}


$ACTIVAS = array(array('id'=>"t",'nombre'=>"Si"),
                 array('id'=>'f','nombre'=>"No")
                );
                
$CAPACIDADES = array(array('id'=>"60 ~ 70",   'nombre'=>"60 ~ 70 sillas"),
                     array('id'=>"50 ~ 59",   'nombre'=>"50 ~ 59 sillas"),
                     array('id'=>"30 ~ 49",   'nombre'=>"30 ~ 49 sillas"),
                     array('id'=>"29 o menos",'nombre'=>"29 o menos sillas"));

$PISOS = consulta_sql("SELECT piso AS id,piso||'º' AS nombre FROM salas WHERE activa GROUP BY piso ORDER BY piso");

$TIPOS_SALAS      = consulta_sql("SELECT * FROM vista_salas_tipo");
$TIPOS_LUMINARIAS = consulta_sql("SELECT * FROM vista_salas_tipos_luminarias");
$TIPOS_SILLAS     = consulta_sql("SELECT * FROM vista_salas_tipos_sillas");
$TIPOS_PISO       = consulta_sql("SELECT * FROM vista_salas_tipos_piso");
$ORIENTACIONES    = consulta_sql("SELECT * FROM vista_salas_orientacion");

$TIPOS_SALAS[] = array("id"=>"Aula,Taller","nombre"=>"Aulas y Talleres");
$TIPOS_SALAS[] = array("id"=>"Laboratorio,Taller","nombre"=>"Laboratorios y Talleres");

$_SESSION['enlace_volver'] = "$enlbase=$modulo&id_carrera=$id_carrera&texto_buscar=$texto_buscar&buscar=$buscar&reg_inicio=$reg_inicio";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr valign="top">
    <td class="celdaFiltro">
      Tipo:<br>
      <select name="tipo_sala" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($TIPOS_SALAS,$tipo_sala)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Activas:<br>
      <select name="activas" onChange="submitform();" class="filtro">
        <?php echo(select($ACTIVAS,$activas)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Piso:<br>
      <select name="piso" onChange="submitform();" class="filtro">
        <option value="0">-- Todos --</option>
        <?php echo(select($PISOS,$piso)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Capacidad:<br>
      <select name="capacidad" onChange="submitform();" class="filtro">
        <option value="0">-- Todas --</option>
        <?php echo(select($CAPACIDADES,$capacidad)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Luminaria:<br>
      <select name="tipo_luminaria" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($TIPOS_LUMINARIAS,$tipo_luminaria)); ?>
      </select>
    </td>    
	<td class="celdaFiltro">
      Materialidad de Sillas:<br>
      <select name="tipo_silla" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($TIPOS_SILLAS,$tipo_silla)); ?>
      </select>
    </td>    
	<td class="celdaFiltro">
      Recubrimiento piso:<br>
      <select name="tipo_piso" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($TIPOS_PISO,$tipo_piso)); ?>
      </select>
    </td>    
	<td class="celdaFiltro">
      Orientación:<br>
      <select name="orientacion" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($ORIENTACIONES,$orientacion)); ?>
      </select>
    </td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr valign="top">
    <td class="celdaFiltro">
      Computador:<br>
      <select name="computador" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($sino,$computador)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      DataShow:<br>
      <select name="proyector" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($sino,$proyector)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Parlantes:<br>
      <select name="parlantes" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($sino,$parlantes)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Pizaara Interac.:<br>
      <select name="pizarra_interactiva" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($sino,$pizarra_interactiva)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Cámara (c. híbrida):<br>
      <select name="webcam" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($sino,$webcam)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Aire Acond.:<br>
      <select name="aire_acond" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($sino,$aire_acond)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Cortinas:<br>
      <select name="cortinas" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($sino,$cortinas)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Doble Vidrio:<br>
      <select name="doble_vidrio" onChange="submitform();" class="filtro">
        <option value="">-- Todas --</option>
        <?php echo(select($sino,$doble_vidrio)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Acciones:<br>
      <input type="button" value="Imprimir" onClick="window.open('<?php echo("$enlbase_sm=$modulo&imprimir=SI&{$_SERVER['QUERY_STRING']}"); ?>');" class="botoncito">
    </td>
  </tr>
</table>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="20">
      Mostrando <b><?php echo($tot_reg); ?></b> sala(s) en total
	  <!--, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas -->
    </td>
<!--    <td class="texto" colspan="6" align='right'>
      <?php echo($HTML_paginador); ?>
    </td> -->
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Código</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla' style="width: 15px; ">Sillas</td>
    <td class='tituloTabla' style='text-align: center'>Piso</td>
    <td class='tituloTabla'>mt<sup>2</sup><small><br>A x L</small></td>
    <td class='tituloTabla'><small>mt<sup>2</sup> por<br>silla</small></td>
    <td class='tituloTabla'><small>Compu.</small></td>
    <td class='tituloTabla'><small>Data<br>Show</small></td>
    <td class='tituloTabla'><small>Parlantes</small></td>
    <td class='tituloTabla'><small>Pizarra<br>Interac.</small></td>
    <td class='tituloTabla'><small>Cámara<br>(híbrida)</small></td>
    <td class='tituloTabla'><small>Aire<br>Acond.</small></td>
    <td class='tituloTabla'><small>Cortinas</small></td>
    <td class='tituloTabla'><small>Doble<br>Vidrio</small></td>
    <td class='tituloTabla'><small>Materialidad<br>Sillas<br>Piso</small></td>
    <td class='tituloTabla'><small>Luminaria<br>Lux</small></td>
    <td class='tituloTabla'></td>
  </tr>
<?php
	if (count($salas) > 0) {

    $tot_mt2    = array_sum(array_column($salas,"tamano"));
    $tot_sillas = array_sum(array_column($salas,"capacidad"));

    $_verde = "color: #009900;";
		$_rojo  = "color: #ff0000;";

    $HTML = "";

		for ($x=0; $x<count($salas); $x++) {
			extract($salas[$x]);
			
			if ($uso_actual > 0 && $_REQUEST['imprimir'] == "") {
				$uso_actual = "<a href='$enlbase=cursos_horarios&sala=$codigo' class='boton'>ver uso actual</a>";
			} else {
				$uso_actual = "&nbsp;";
			}

      $si = "<span style='color: green'><b><big> ✓ </big></b></span>";
      $no = "<span style='color: red'><b><big> ✗ </big></b></span>";

			$enl = "$enlbase_sm=sala_editar&cod_sala=$codigo";
			$enlace = "<a class='boton' href='$enl'>";

      $codigo = "<a href='$enl' id='sgu_fancybox' class='enlaces'>$codigo</a>";

      $mt2_x_est = round($tamano/$capacidad,1);

      $lux = ($lux_medido <> "") ? "$lux_medido lúm." : "";

			$HTML .= "  <tr class='filaTabla'>"
			      .  "    <td class='textoTabla' align='center'>$codigo<br><small>$tipo</small></td>"
			      .  "    <td class='textoTabla'><div title='header=[Propiedades] fade=[on] body=[Comentarios:<br> $comentarios]'>$nombre<br><small>$nombre_largo</small></div></td>"
			      .  "    <td class='textoTabla' align='center'>$capacidad</td>"
			      .  "    <td class='textoTabla' align='center'>".$piso."º</td>"
			      .  "    <td class='textoTabla' align='center'>$tamano<small><i><br>$ancho x $largo</i></small></td>"
			      .  "    <td class='textoTabla' align='center'>$mt2_x_est</td>"
			      .  "    <td class='textoTabla' align='center'>".(($computador=="t")?$si:$no)."</td>"
			      .  "    <td class='textoTabla' align='center'>".(($proyector=="t")?$si:$no)."</td>"
			      .  "    <td class='textoTabla' align='center'>".(($parlantes=="t")?$si:$no)."</td>"
			      .  "    <td class='textoTabla' align='center'>".(($pizarra_interactiva=="t")?$si:$no)."</td>"
			      .  "    <td class='textoTabla' align='center'>".(($webcam=="t")?$si:$no)."</td>"
			      .  "    <td class='textoTabla' align='center'>".(($aire_acond=="t")?$si:$no)."</td>"
			      .  "    <td class='textoTabla' align='center'>".(($cortinas=="t")?$si:$no)."</td>"
			      .  "    <td class='textoTabla' align='center'>".(($doble_vidrio=="t")?$si:$no)."</td>"
			      .  "    <td class='textoTabla' align='center'><small>$tipo_silla<br>$tipo_piso</small></td>"
			      .  "    <td class='textoTabla' align='center'><small>$tipo_luminaria<br>$lux</small></td>"
			      .  "    <td class='textoTabla'>$uso_actual</td>"
			      .  "  </tr>\n";			
		}
    $mt2_x_est = round($tot_mt2/$tot_sillas,1);
    $HTML .= "  <tr>"
          .  "    <td class='celdaNombreAttr' colspan='2'>Total:</td>"
          .  "    <td class='celdaNombreAttr'style='text-align: center'>$tot_sillas</td>"
          .  "    <td class='celdaNombreAttr'>&nbsp;</td>"
          .  "    <td class='celdaNombreAttr'style='text-align: center'>$tot_mt2</td>"
          .  "    <td class='celdaNombreAttr' style='text-align: center'>$mt2_x_est</td>"
          .  "    <td class='celdaNombreAttr' colspan='20'>&nbsp;</td>"
          .  "  </tr>";
	} else {
		$HTML .= "  <tr class='filaTabla'>"
          .  "    <td class='textoTabla' colspan='15'>"
		      .  "      ***  No hay registros para los criterios de búsqueda/selección ***"
		      .  "    </td>"
          .  "  </tr>\n";
	}

  echo($HTML);

?>
</table>
</form>
<?php 

if ($_REQUEST['imprimir'] == "SI") {
  echo(js("window.print();setTimeout(window.close,1000);"));
  //echo(js("window.close();"));
}
?>
<!-- Fin: <?php echo($modulo); ?> -->

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 600,
		'maxHeight'		: 600,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_big").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 1300,
		'maxHeight'		: 9999,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});
</script>
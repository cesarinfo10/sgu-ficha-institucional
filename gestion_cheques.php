<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) {	$cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$tiempo       = $_REQUEST['tiempo'];
$id_cajero    = $_REQUEST['id_cajero'];
$id_banco     = $_REQUEST['id_banco'];
$fec_ini      = $_REQUEST['fec_ini'];
$fec_fin      = $_REQUEST['fec_fin'];
$nulas        = $_REQUEST['nulas'];

$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

if (empty($tiempo) && empty($fec_ini) && empty($fec_fin)) { $tiempo=2; }
if (empty($fec_ini)) { $fec_ini = date("Y-m-d"); }
if (empty($fec_fin)) { $fec_fin = date("Y-m-d"); }

$condicion = "WHERE (p.nro_boleta IS NOT NULL OR p.nro_factura IS NOT NULL) ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion .= " AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= " (vpr.rut ~* '$cadena_buscada' OR ch.rut_emisor ~* '$cadena_buscada' OR lower(ch.nombre_emisor) ~* '$cadena_buscada' "
		           .  " OR text(nro_docto) = '$cadena_buscada' ) AND";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$tiempo = $id_cajero = $rendidas = $regimen = null;	
} else {

	switch ($tiempo) {
		case "1":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND ch.fecha_venc BETWEEN now()-'1 days'::interval AND now() ";
			break;
		case "2":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND ch.fecha_venc BETWEEN now()-'7 days'::interval AND now() ";
			break;
		case "3":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND ch.fecha_venc BETWEEN now()-'1 month'::interval AND now() ";
			break;
		case "4":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND ch.fecha_venc BETWEEN now()-'6 month'::interval AND now() ";
			break;
		case "5":
			$fec_ini = $fec_fin = "";
			$condicion .= "AND ch.fecha_venc BETWEEN now()-'1 years'::interval AND now() ";
			break;
		case "6":
			$fec_ini = $fec_fin = "";
			break;
	}

	if ($fec_ini <> "" && $fec_fin <> "") {
		if (strtotime($fec_ini) == -1 || strtotime($fec_fin) == -1) {
			echo(msje_js("Las fechas de búsqueda están mal ingresadas. Por favor use el formato DD-MM-AAAA"));
		} else {
			$condicion .= "AND ch.fecha_venc BETWEEN '$fec_ini'::date AND '$fec_fin'::date ";
		}
	}
	
	if ($id_banco <> "") {
		$condicion .= " AND ch.cod_inst_finan='$id_banco' ";
	}
	
	if ($id_cajero > 0) {
		$condicion .= "AND p.id_cajero=$id_cajero ";
	}
		
	if ($nulas == "f") { $condicion .= " AND NOT p.nulo "; }
	
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_alumno_ch = "SELECT nombre FROM (SELECT nombre FROM vista_alumnos WHERE rut=vpr.rut UNION SELECT nombre FROM vista_alumnos WHERE rut=vpr.rut";
$SQL_cheques = "SELECT p.id AS id_pago,CASE WHEN p.nro_boleta IS NOT NULL THEN 'B' WHEN p.nro_factura IS NOT NULL THEN 'F' END AS tipo_doc,
                       coalesce(p.nro_boleta,p.nro_factura) AS nro_docto,to_char(p.fecha,'DD-MM-YYYY') AS fecha,vpr.rut AS rut_alumno,
                       u.nombre_usuario AS cajero,to_char(ch.fecha_venc,'DD-MM-YYYY') AS fecha_venc,ch.nombre_emisor,ch.rut_emisor,
                       if.nombre AS banco,ch.monto,ch.numero,ch.id AS id_cheque,
                       CASE WHEN ch.depositado THEN 'Si' ELSE 'No' END AS depositado,
                       CASE ch.protestado WHEN true THEN 'Si' WHEN false THEN 'No' ELSE 'N/D' END AS protestado,
                       CASE ch.aclarado   WHEN true THEN 'Si' WHEN false THEN 'No' ELSE 'N/D' END AS aclarado
                FROM finanzas.cheques AS ch
                LEFT JOIN finanzas.inst_financieras AS if ON if.codigo=ch.cod_inst_finan
                LEFT JOIN finanzas.pagos AS p             ON p.id=ch.id_pago
                LEFT JOIN vista_pagos_rut AS vpr          ON p.id=vpr.id
                LEFT JOIN vista_usuarios AS u             ON u.id=p.id_cajero
                $condicion 
                ORDER BY ch.fecha_venc DESC ";

$SQL_tabla_completa = "COPY ($SQL_cheques) to stdout WITH CSV HEADER";
$SQL_cheques .= "$limite_reg OFFSET $reg_inicio";
$cheques      = consulta_sql($SQL_cheques);
//echo($SQL_boletas);
$HTML_cheques = "";
if (count($cheques) > 0) {
	for ($x=0;$x<count($cheques);$x++) {
		extract($cheques[$x]);
		
		$monto     = number_format($monto,0,',','.');
		$nro_docto = number_format($nro_docto,0,',','.');
		
		$numero = "<a href='$enlbase_sm=ver_cheque&id_cheque=$id_cheque' id='sgu_fancybox_small' class='enlaces'>".sprintf("%'.09d\n",$numero)."</a>";

		$nro_docto = "<a href='$enlbase_sm=ver_pago&id_pago=$id_pago' id='sgu_fancybox' class='enlaces'>$tipo_doc/$nro_docto</a>";

		$dep = $depositado=="Si" ? "No" : "Si";
		$enl_dep = "$enlbase_sm=cheques_estado&depositado=$dep&id_cheque=$id_cheque";
		if ($depositado == "No") {
			$dep = "<span id='bo_dep_$x' style='visibility: hidden'><br><a href='$enl_dep' class='boton' id='sgu_fancybox_small'><small>$dep</small></a></span>";
		} elseif ($depositado == "Si" && $protestado == "N/D") {
			$dep = "<span id='bo_dep_$x' style='visibility: hidden'><br><a href='$enl_dep' class='boton' id='sgu_fancybox_small'><small>$dep</small></a></span>";
		} elseif ($depositado == "Si" && $protestado <> "N/D") {
			$dep = "<span id='bo_dep_$x' style='visibility: hidden'></span>";
		} 

		$pro = $protestado=="Si" ? "No" : ($protestado=="No" ? "Si" : "");
		$pro_style = $pro;
		$enl_pro = "$enlbase_sm=cheques_estado&protestado=$pro&id_cheque=$id_cheque";
		if ($protestado == "N/D" && $depositado == "Si") {
			$pro = "<span id='bo_pro_$x' style='visibility: hidden'><br><a href='$enlbase_sm=cheques_estado&protestado=Si&id_cheque=$id_cheque' class='boton' id='sgu_fancybox_small'><small>Si</small></a></span> "
			     . "<span id='bo_pro2_$x' style='visibility: hidden'><a href='$enlbase_sm=cheques_estado&protestado=No&id_cheque=$id_cheque' class='boton' id='sgu_fancybox_small'><small>No</small></a></span>";
		} elseif ($protestado == "No") {
			$pro = "<span id='bo_pro_$x' style='visibility: hidden'><br><a href='$enl_pro' class='boton' id='sgu_fancybox_small'><small>$pro</small></a></span>"
			     . "<span id='bo_pro2_$x' style='visibility: hidden'></span>";
		} elseif ($protestado == "Si" && $aclarado == "N/D") {
			$pro = "<span id='bo_pro_$x' style='visibility: hidden'><br><a href='$enl_pro' class='boton' id='sgu_fancybox_small'><small>$pro</small></a></span></span>"
			     . "<span id='bo_pro2_$x' style='visibility: hidden'></span>";
		}

		$acl = $aclarado=="Si" ? "No" : ($aclarado=="No" ? "Si" : "");
		$enl_acl = "$enlbase_sm=cheques_aclarar&aclarar=Si&id_cheque=$id_cheque";
		if ($aclarado == "N/D" && $depositado == "Si" && $protestado == "Si") {
			$acl = "<span id='bo_acl_$x' style='visibility: hidden'><br><a href='$enl_acl' class='boton' id='sgu_fancybox_small'><small>Aclarar</small></a></span>";
		} else {
			$acl = "<span id='bo_acl_$x' style='visibility: hidden'></span>";
		}
		
//		$dep = $depositado=="Si" ? "No" : "Si";
//		$dep = "<span id='bo_dep_$x' style='visibility: hidden'><br><a href='$enlbase_sm=cheques_estado&depositado=$dep&id_cheque=$id_cheque' class='boton' id='sgu_fancybox_small'><small>$dep</small></a></span>";

		$onMouseOver = "document.getElementById('bo_dep_$x').style.visibility='visible';document.getElementById('bo_pro_$x').style.visibility='visible';document.getElementById('bo_pro2_$x').style.visibility='visible';document.getElementById('bo_acl_$x').style.visibility='visible'";
		$onMouseOut  = "document.getElementById('bo_dep_$x').style.visibility='hidden';document.getElementById('bo_pro_$x').style.visibility='hidden';document.getElementById('bo_pro2_$x').style.visibility='hidden';document.getElementById('bo_acl_$x').style.visibility='hidden'";
		$HTML_cheques .= "  <tr class='filaTabla' onMouseOver=\"$onMouseOver\" onMouseOut=\"$onMouseOut\">\n"
					  . "    <td class='textoTabla' align='center' style='color: #7F7F7F'><small>$id_pago<br>$cajero</small></td>\n"
					  . "    <td class='textoTabla' align='right'>$nro_docto<br><small>$fecha</small></td>\n"
					  . "    <td class='textoTabla'>$rut_alumno</td>\n"
					  . "    <td class='textoTabla'><small>$banco</small></td>\n"
					  . "    <td class='textoTabla' align='right'>$numero</td>\n"
					  . "    <td class='textoTabla' align='center'>$fecha_venc</td>\n"
					  . "    <td class='textoTabla'>$rut_emisor<br>$nombre_emisor</td>\n"
					  . "    <td class='textoTabla' align='right'>$monto</td>\n"
					  . "    <td class='textoTabla' align='center'><span class='$depositado'>$depositado</span> $dep</td>\n"
					  . "    <td class='textoTabla' align='center'><span class='$pro_style'>$protestado</span> $pro</td>\n"
					  . "    <td class='textoTabla' align='center'><span class='$aclarado'>$aclarado</span> $acl</td>\n"
					  . "  </tr>\n";
	}
} else {
	$HTML_cheques = "  <tr>"
				  . "    <td class='textoTabla' colspan='11'><br><br>"
				  . "      <center>No hay registros para los criterios de búsqueda/selección</center><br><br>"
				  . "    </td>\n"
				  . "  </tr>";
}

$enlace_nav = "$enlbase=$modulo"
            . "&fec_ini=$fec_ini"
            . "&fec_fin=$fec_fin"
            . "&tiempo=$tiempo"
            . "&id_cajero=$id_cajero"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";

if (count($cheques) > 0) {
	$SQL_total_cheques =  "SELECT count(ch.numero) AS total_cheques
                           FROM finanzas.cheques AS ch
                           LEFT JOIN finanzas.inst_financieras AS if ON if.codigo=ch.cod_inst_finan
                           LEFT JOIN finanzas.pagos AS p             ON p.id=ch.id_pago
	                       LEFT JOIN vista_pagos_rut AS vpr          ON p.id=vpr.id
                           LEFT JOIN vista_usuarios AS u             ON u.id=p.id_cajero
	                       $condicion";
	$total_cheques = consulta_sql($SQL_total_cheques);
	$tot_reg = $total_cheques[0]['total_cheques'];
	
	$HTML_paginador = html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$SQL_cajeros = "SELECT id,apellido||' '||nombre||' ('||nombre_usuario||')' AS nombre 
                FROM usuarios WHERE id IN (SELECT DISTINCT ON (id_cajero) id_cajero FROM finanzas.pagos) ORDER BY apellido,nombre";
$cajeros     = consulta_sql($SQL_cajeros);                

$tiempos = array(array('id'=>"1",'nombre'=>"último día"),
                 array('id'=>"2",'nombre'=>"última semana"),
                 array('id'=>"3",'nombre'=>"último mes"),
                 array('id'=>"4",'nombre'=>"último semestre"),
                 array('id'=>"5",'nombre'=>"último año"),
                 array('id'=>"6",'nombre'=>"Todas"),
                 array('id'=>"7",'nombre'=>"Otro (especificar)"));

$INST_FINAN = consulta_sql("SELECT codigo AS id,nombre FROM finanzas.inst_financieras ORDER BY nombre");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="2" border="0" cellspacing="1" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro">
          Mostrar cheques recibidos por el cajero(a):<br>
          <select name="id_cajero" onChange="submitform();" class="filtro">
            <option value="">Todo(a)s</option>
            <?php echo(select($cajeros,$id_cajero)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Fecha Vencimiento:<br>
          <select name="tiempo" onChange="submitform();" class="filtro">
            <?php echo(select($tiempos,$tiempo)); ?>    
          </select>
          <?php if ($tiempo == 7) { ?>
          <input type="date" name="fec_ini" value="<?php echo($fec_ini); ?>" id="fec_ini" class="boton" style='font-size: 8pt'>
          <input type="date" name="fec_fin" value="<?php echo($fec_fin); ?>" id="fec_fin" class="boton" style='font-size: 8pt'>
          <script>document.getElementById("fec_ini").focus();</script>
          <input type='submit' name='buscar' value='Buscar' style='font-size: 7pt'>
          <?php } ?>
        </td>
        <td class="celdaFiltro">
          Banco:<br>
          <select name="id_banco" onChange="submitform();" class="filtro">
            <option value="">Todo(a)s</option>
            <?php echo(select($INST_FINAN,$id_banco)); ?>    
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="2" border="0" cellspacing="1" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro">
          Buscar por Nº de Boleta/Factura, RUT del alumno/postulante, RUT o nombre del Emisor:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="60" id="texto_buscar" class="boton">
          <script>document.getElementById("texto_buscar").focus();</script>     
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	}
          ?>
        </td>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> cheques(s) en total, en página(s) de
      <select name="cant_reg" onChange="submitform();" class="filtro">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas<br>    
    </td>
    <td class="texto" colspan="6" style="align: right"><?php echo($HTML_paginador); ?> <?php echo($boton_tabla_completa); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="3">Boleta/Factura</td>
    <td class='tituloTabla' colspan="8">Cheque</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='color: #7F7F7F'><small>ID<br>Cajero</small></td>
    <td class='tituloTabla'><small>Nº Docto.<br>Fecha</small></td>
    <td class='tituloTabla'><small>Alumno</small></td>
    <td class='tituloTabla'>Banco</td>
    <td class='tituloTabla'>Número</td>
    <td class='tituloTabla'>F. Venc</td>
    <td class='tituloTabla'>Emisor</td>
    <td class='tituloTabla'>Monto</td>
    <td class='tituloTabla'><small>Dep.</small></td>
    <td class='tituloTabla' nowrap><small>Pro.</small></td>
    <td class='tituloTabla'><small>Acl.</small></td>
  </tr>
  <?php echo($HTML_cheques); ?>
</table>
</form>

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
		'width'				: 700,
		'height'			: 600,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>
<!-- Fin: <?php echo($modulo); ?> -->

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

$tipo_docto_b = $_REQUEST['tipo_docto_b'];
$nro_docto_b  = $_REQUEST['nro_docto_b'];
$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$tiempo       = $_REQUEST['tiempo'];
$id_cajero    = $_REQUEST['id_cajero'];
$id_erp       = $_REQUEST['id_erp'];
$regimen      = $_REQUEST['regimen'];
$fec_ini      = $_REQUEST['fec_ini'];
$fec_fin      = $_REQUEST['fec_fin'];
$nulas        = $_REQUEST['nulas'];

$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

if (empty($tiempo) && empty($fec_ini) && empty($fec_fin)) { $tiempo=1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($fec_ini)) { $fec_ini = date("Y-m-d"); }
if (empty($fec_fin)) { $fec_fin = date("Y-m-d"); }
if (empty($nulas))   { $nulas = "t"; }

$condicion = "WHERE (p.nro_boleta IS NOT NULL OR p.nro_boleta_e IS NOT NULL OR p.nro_factura IS NOT NULL) ";

if ($buscar == "Buscar" && is_numeric($nro_docto_b)) {
	$condicion .= " AND p.$tipo_docto_b = $nro_docto_b ";
} else {

	if ($buscar == 'Buscar' && $texto_buscar <> "") {
		$texto_buscar_regexp = sql_regexp($texto_buscar);
		$textos_buscar = explode(" ",$texto_buscar_regexp);
		$condicion = "WHERE (nro_boleta IS NOT NULL OR nro_boleta_e IS NOT NULL) AND ";
		for ($x=0;$x<count($textos_buscar);$x++) {
			$cadena_buscada = strtolower($textos_buscar[$x]);
	/*
			$condicion .= "(lower(pap.nombres||' '||pap.apellidos) ~* '$cadena_buscada' OR "
					   .  " pap.rut ~* '$cadena_buscada' OR "
					   .  "lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR lower(a3.nombres||' '||a3.apellidos) ~* '$cadena_buscada' OR "
					   .  " a.rut ~* '$cadena_buscada' OR a2.rut ~* '$cadena_buscada' OR a3.rut ~* '$cadena_buscada' "
					   .  " OR text(nro_boleta) = '$cadena_buscada' "
					   .  " OR text(nro_boleta_e) = '$cadena_buscada' "
					   .  ") AND ";
	*/
			$condicion .= "(lower(pap.nombres||' '||pap.apellidos) ~* '$cadena_buscada' OR "
					   .  " pap.rut ~* '$cadena_buscada' OR "
					   .  "lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR lower(a3.nombres||' '||a3.apellidos) ~* '$cadena_buscada' OR "
					   .  " a.rut ~* '$cadena_buscada' OR a2.rut ~* '$cadena_buscada' OR a3.rut ~* '$cadena_buscada' "
					   .  ") AND ";
		}
		$condicion=substr($condicion,0,strlen($condicion)-4);
		$tiempo = $id_cajero = $rendidas = $regimen = null;	
	} else {	

		switch ($tiempo) {
			case "1":
				$fec_ini = $fec_fin = "";
				$condicion .= "AND p.fecha BETWEEN now()-'1 days'::interval AND now() ";
				break;
			case "2":
				$fec_ini = $fec_fin = "";
				$condicion .= "AND p.fecha BETWEEN now()-'7 days'::interval AND now() ";
				break;
			case "3":
				$fec_ini = $fec_fin = "";
				$condicion .= "AND p.fecha BETWEEN now()-'1 month'::interval AND now() ";
				break;
			case "4":
				$fec_ini = $fec_fin = "";
				$condicion .= "AND p.fecha BETWEEN now()-'6 month'::interval AND now() ";
				break;
			case "5":
				$fec_ini = $fec_fin = "";
				$condicion .= "AND p.fecha BETWEEN now()-'1 years'::interval AND now() ";
				break;
			case "6":
				$fec_ini = $fec_fin = "";
				break;
		}
		
		if ($rendidas == "t") {
			$condicion .= "AND id_arqueo IS NOT NULL ";
		} elseif ($rendidas == "f") {
			$condicion .= "AND id_arqueo IS NULL ";
		}
		
		if ($id_erp == "t") { $condicion .= "AND nro_boleta_e IS NOT NULL AND bol_e_cod_erp IS NOT NULL "; } 
		elseif ($id_erp == "f") { $condicion .= "AND nro_boleta_e IS NOT NULL AND bol_e_cod_erp IS NULL "; }
		
		if ($fec_ini <> "" && $fec_fin <> "") {
			if (strtotime($fec_ini) == -1 || strtotime($fec_fin) == -1) {
				echo(msje_js("Las fechas de búsqueda están mal ingresadas. Por favor use el formato DD-MM-AAAA"));
			} else {
				$condicion .= "AND p.fecha BETWEEN '$fec_ini'::date AND '$fec_fin'::date ";
			}
		}
		
		if ($id_cajero > 0) {
			$condicion .= "AND p.id_cajero=$id_cajero ";
		}
			
		if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND ('$regimen' IN (car.regimen,car2.regimen,car3.regimen) OR p.nulo) "; }	

		if ($nulas == "f") { $condicion .= " AND NOT p.nulo "; }
		
	}
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_nc = "SELECT nro_docto FROM finanzas.notas_credito WHERE id_pago=p.id";

$SQL_boletas = "SELECT DISTINCT ON (coalesce(p.nro_boleta,p.nro_boleta_e,p.nro_factura)) p.id,coalesce(p.nro_boleta,p.nro_boleta_e,p.nro_factura) AS nro_docto,
                       CASE WHEN p.nro_boleta IS NOT NULL THEN 'B' 
                            WHEN p.nro_boleta_e IS NOT NULL THEN 'BE' 
                            WHEN p.nro_factura IS NOT NULL THEN 'F' 
                       END AS tipo_doc,p.fecha AS fecha_bol,
                       to_char(p.fecha,'DD-MM-YYYY') AS fecha,u.nombre_usuario AS cajero,cod_operacion,
                       coalesce(efectivo,0)+coalesce(deposito,0)+coalesce(cheque,0)+coalesce(cheque_afecha,0)+coalesce(transferencia,0)+coalesce(tarj_credito,0)+coalesce(tarj_debito,0) AS monto_boleta,
                       CASE WHEN id_arqueo IS NULL THEN 'No' ELSE 'Si' END AS rendida,cob.id_contrato,cob.id_convenio_ci,
                       CASE 
                         WHEN cob.id_contrato IS NOT NULL    THEN coalesce(a.rut,pap.rut)||' '||coalesce(a.apellidos||' '||a.nombres,pap.apellidos||' '||pap.nombres) 
                         WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut||' '||a3.apellidos||' '||a3.nombres 
                         WHEN cob.id_alumno IS NOT NULL      THEN a2.rut||' '||a2.apellidos||' '||a2.nombres
                         WHEN p.nulo THEN '****** NULO ******'
                       END AS alumno,
                       CASE 
                         WHEN cob.id_contrato IS NOT NULL    THEN coalesce(a.rut,pap.rut)
                         WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut
                         WHEN cob.id_alumno IS NOT NULL      THEN a2.rut
                         WHEN p.nulo THEN '****** NULO ******'
                       END AS rut_alumno,p.fecha_reg,coalesce(car.nombre,car2.nombre,car3.nombre) AS carrera_alumno,
                       efectivo,deposito,cheque,cheque_afecha,transferencia,tarj_credito,tarj_debito,p.bol_e_respuesta_api,p.bol_e_cod_erp,
					   ($SQL_nc) AS nc
                FROM finanzas.pagos AS p
                LEFT JOIN vista_usuarios AS u          ON u.id=id_cajero
                LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_pago=p.id 
                LEFT JOIN finanzas.cobros AS cob       ON cob.id=id_cobro 
                LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato 
                LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=cob.id_convenio_ci 
                LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
                LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
                LEFT JOIN alumnos AS a3                ON a3.id=cci.id_alumno
                LEFT JOIN pap                          ON pap.id=c.id_pap
                LEFT JOIN carreras AS car              ON car.id=c.id_carrera
                LEFT JOIN carreras AS car2             ON car2.id=a2.carrera_actual
                LEFT JOIN carreras AS car3             ON car3.id=a3.carrera_actual
                $condicion 
                ORDER BY coalesce(p.nro_boleta,p.nro_boleta_e,p.nro_factura) DESC ";
$SQL_boletas = "SELECT * FROM ($SQL_boletas) AS foo ORDER BY fecha_bol DESC,nro_docto DESC ";
$SQL_tabla_completa = "COPY ($SQL_boletas) to stdout WITH CSV HEADER";
$SQL_boletas .= "$limite_reg OFFSET $reg_inicio";
$boletas     = consulta_sql($SQL_boletas);
//echo($SQL_boletas);

$SQL_detalle_pagos = sql_detalle_pagos($condicion);
$SQL_tc_detalle_pagos = "COPY ($SQL_detalle_pagos) to stdout WITH CSV HEADER";

$HTML_boletas = "";

$enlace_nav = "$enlbase=$modulo"
            . "&fec_ini=$fec_ini"
            . "&fec_fin=$fec_fin"
            . "&rendidas=$rendidas"
            . "&erp=$erp"
            . "&tiempo=$tiempo"
            . "&id_cajero=$id_cajero"
            . "&regimen=$regimen"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";
            
if (count($boletas) > 0) {
	$SQL_total_boletas =  "SELECT DISTINCT ON (coalesce(p.nro_boleta,p.nro_boleta_e,p.nro_factura)) p.id
	                       FROM finanzas.pagos AS p
	                       LEFT JOIN vista_usuarios AS u          ON u.id=id_cajero
	                       LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_pago=p.id 
	                       LEFT JOIN finanzas.cobros AS cob       ON cob.id=id_cobro 
	                       LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato 
	                       LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=cob.id_convenio_ci 
	                       LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
	                       LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
	                       LEFT JOIN alumnos AS a3                ON a3.id=cci.id_alumno
	                       LEFT JOIN pap                          ON pap.id=c.id_pap
	                       LEFT JOIN carreras AS car              ON car.id=c.id_carrera
	                       LEFT JOIN carreras AS car2             ON car2.id=a2.carrera_actual
	                       LEFT JOIN carreras AS car3             ON car3.id=a3.carrera_actual
	                       $condicion";
	//echo($SQL_total_boletas);
	$total_boletas = consulta_sql($SQL_total_boletas);
	//$tot_reg = $total_boletas[0]['total_boletas'];
	$tot_reg = count($total_boletas);
	
	$HTML_paginador = html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
	
	for ($x=0;$x<count($boletas);$x++) {
		extract($boletas[$x]);
		
		$enl = "$enlbase_sm=ver_pago&id_pago=$id";
		$enlace = "a class='enlitem' href='$enl'";
		$monto_boleta = number_format($monto_boleta,0,',','.');
		$nro_docto    = number_format($nro_docto,0,',','.');
		if ($id_contrato > 0) {
			$alumno = "<a href='$enlbase=form_matricula_ver&id_contrato=$id_contrato' title='Pinche para ver Contrato Nº $id_contrato asociado' class='enlaces'>$alumno</a>";
		}
		
		$erp = "No";
		if ($bol_e_cod_erp > 0) {
			$erp = "Si";
		}
		
		if ($nc <> "") { $nc = "<br><small>(NCE/$nc)</small>"; }

		$nro_docto = "<a href='$enl' id='sgu_fancybox' class='enlaces'>$tipo_doc/$nro_docto</a>";
		$HTML_boletas .= "  <tr class='filaTabla'>\n"
					  . "    <td class='textoTabla' align='center' style='color: #7F7F7F'>$id</td>\n"
					  . "    <td class='textoTabla' align='right'>$nro_docto $nc</td>\n"
					  . "    <td class='textoTabla'>$fecha</td>\n"
					  . "    <td class='textoTabla'>$alumno</td>\n"
					  . "    <td class='textoTabla'>$cajero</td>\n"
					  . "    <td class='textoTabla' align='right'>$monto_boleta</td>\n"
					  . "    <td class='textoTabla' align='center'><span class='$erp'>$erp</span></td>\n"
					  . "  </tr>\n";
	}
} else {
	$HTML_boletas = "  <tr>"
				  . "    <td class='textoTabla' colspan='7' align='center'>"
				  . "      <br>** No hay registros para los criterios de búsqueda/selección **<br><br>"
				  . "    </td>\n"
				  . "  </tr>";
}

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$id_sesion = $_SESSION['usuario']."_".$modulo."_detalle_".session_id();
$boton_tc_detalle_pagos = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>TC Det. Pagos</small></a>";
$nombre_arch            = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tc_detalle_pagos);

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

$REGIMENES = consulta_sql("SELECT * FROM regimenes");


$TIPOS_DOCTOS = array(array('id' => "nro_boleta_e", 'nombre' => "Bol-E"),
                      array('id' => "nro_factura",  'nombre' => "Fac-E"),
                      array('id' => "nro_boleta",   'nombre' => "Boleta"));
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
          Mostrar boletas del cajero(a):<br>
          <select name="id_cajero" onChange="submitform();" class="filtro">
            <option value="">Todo(a)s</option>
            <?php echo(select($cajeros,$id_cajero)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Temporalidad:<br>
          <select name="tiempo" onChange="submitform();" class="filtro">
            <?php echo(select($tiempos,$tiempo)); ?>    
          </select>
          <?php if ($tiempo == 7) { ?>
          <input type="date" name="fec_ini" value="<?php echo($fec_ini); ?>" id="fec_ini" class="boton" style='font-size: 8pt'>
          <input type="date" name="fec_fin" value="<?php echo($fec_fin); ?>" id="fec_fin" class="boton" style='font-size: 8pt'>
          <script>document.getElementById("fec_ini").focus();</script>
          <input type='submit' name='buscar' value='Buscar' style='font-size: 10pt'>
          <?php } ?>
        </td>
        <td class="celdaFiltro">
          ERP:<br>
          <select name="id_erp" onChange="submitform();" class="filtro">
			<option value="0">Todas</option>
            <?php echo(select($sino,$id_erp)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Nulas:<br>
          <select name="nulas" onChange="submitform();" class="filtro">
            <?php echo(select($sino,$nulas)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Régimen:<br> 
          <select name="regimen" onChange="submitform();" class="filtro">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="2" border="0" cellspacing="1" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro">
          Buscar por Tipo y Nº de docto:<br>
          <select name="tipo_docto_b" class="filtro" onChange="formulario.nro_docto_b.value=null;">
            <?php echo(select($TIPOS_DOCTOS,$tipo_docto_b)); ?>
          </select> :
          <input type="text" name="nro_docto_b" value="<?php echo($nro_docto_b); ?>" size="4" id="nro_docto" class="boton">
          <input type='submit' name='buscar' value='Buscar'>
        </td>			
        <td class="celdaFiltro">
          Buscar por <!-- Nº de Boleta,--> RUT o nombre del estudiante:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="25" id="texto_buscar" class="boton">
          <script>document.getElementById("texto_buscar").focus();</script>     
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	}
          ?>
        </td>
        <td class="celdaFiltro">
          Acciones:<br>
          <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=anular_pago"); ?>' class='boton'>Anular Pago</a>
          <a href='<?php echo("$enlbase=carga_pagos_planilla"); ?>' class='boton'>Pagos por planilla</a>
        </td>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="4">
      Mostrando <b><?php echo($tot_reg); ?></b> boleta(s) en total, en página(s) de
      <select name="cant_reg" onChange="submitform();" class="filtro">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas<br>    
    </td>
    <td class="texto" colspan="3">
	  <?php echo($HTML_paginador); ?>
	  <?php echo($boton_tabla_completa); ?>
	  <?php echo($boton_tc_detalle_pagos); ?>
	</td>
  </tr>
  <tr class='filaTituloTabla'>
    <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='color: #7F7F7F'>ID</td>
    <td class='tituloTabla'>Nº Docto</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Estudiante</td>
    <td class='tituloTabla'>Cajer@</td>
    <td class='tituloTabla'>Monto Total</td>
    <td class='tituloTabla'>ERP</td>
  </tr>
  <?php echo($HTML_boletas); ?>
</table>
</form>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 1200,
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

<?php 

function sql_detalle_pagos($condicion) {
	$SQL = "SELECT coalesce(nro_boleta,nro_boleta_e,nro_factura) AS nro_docto,
	               CASE WHEN nro_boleta IS NOT NULL THEN 'Bol'
				        WHEN nro_boleta_e IS NOT NULL THEN 'Bol-E'
						WHEN nro_factura IS NOT NULL THEN 'Fac'
				   END AS tipo_docto,p.fecha::date,
				   g.nombre AS glosa,c.ano AS ano_contrato,pd.monto_pagado,
				   trim(coalesce(a.rut,a2.rut,a3.rut,pap.rut)) AS rut,
				   coalesce(car.alias,car2.alias,car3.alias) AS carrera,
				   coalesce(car.regimen,car2.regimen,car3.regimen) AS regimen,
				   g.cod_producto_erp,ccc.codigo_erp AS cod_centrodecosto_erp,
				   coalesce(g.cod_cta_contable_erp,cpc1.codigo::text,cpc2.codigo::text) AS cod_cta_contable_erp
			FROM finanzas.pagos_detalle     AS pd
			LEFT JOIN finanzas.pagos        AS p    ON p.id=pd.id_pago
			LEFT JOIN vista_usuarios        AS u    ON u.id=p.id_cajero
			LEFT JOIN finanzas.cobros       AS cob  ON cob.id=pd.id_cobro
			LEFT JOIN finanzas.glosas       AS g    ON g.id=cob.id_glosa
			LEFT JOIN finanzas.contratos    AS c    ON c.id=cob.id_contrato
			LEFT JOIN finanzas.convenios_ci AS cci  ON cci.id=cob.id_convenio_ci
			LEFT JOIN alumnos               AS a    ON a.id=c.id_alumno
			LEFT JOIN alumnos               AS a2   ON a2.id=cci.id_alumno
			LEFT JOIN alumnos               AS a3   ON a3.id=cob.id_alumno
			LEFT JOIN carreras              AS car  ON car.id=c.id_carrera
			LEFT JOIN carreras              AS car2 ON car2.id=a2.carrera_actual
			LEFT JOIN carreras              AS car3 ON car3.id=a3.carrera_actual
			LEFT JOIN pap                           ON pap.id=c.id_pap
			LEFT JOIN finanzas.conta_plandecuentas AS cpc1 ON (cpc1.ano=coalesce(c.ano,date_part('year',cci.fecha)) 
			                                               AND cpc1.regimen=coalesce(car.regimen,car2.regimen,car3.regimen) 
											               AND cpc1.docto_xcobrar=g.docto_xcobrar)
            LEFT JOIN finanzas.conta_plandecuentas AS cpc2 ON (cpc2.docto_xcobrar=g.docto_xcobrar 
			                                               AND cpc2.ano IS NULL 
											               AND cpc2.regimen IS NULL)
	        LEFT JOIN finanzas.conta_centrosdecosto AS ccc ON ccc.id_carrera=coalesce(a.carrera_actual,a2.carrera_actual,c.id_carrera)
			$condicion
			ORDER BY p.fecha";
	return $SQL;
}

?>
<!-- Fin: <?php echo($modulo); ?> -->

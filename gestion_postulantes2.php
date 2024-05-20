<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$cant_reg = 30;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$buscar_fecha = $_REQUEST['buscar_fecha'];
$id_carrera   = $_REQUEST['id_carrera'];
$jornada      = $_REQUEST['jornada'];
$cohorte      = $_REQUEST['cohorte'];
$fecha_ini    = $_REQUEST['fecha_ini'];
$fecha_fin    = $_REQUEST['fecha_fin'];

if (empty($fecha_ini) && empty($fecha_fin)) {
	$fecha_ini = $ANO."-01-01";
	$fecha_fin = $ANO."-12-31";
}

$condicion = "WHERE true ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(pap.nombres||' '||pap.apellidos) ~* '$cadena_buscada' OR "
		            . " pap.rut ~* '$cadena_buscada' OR "
		            . " text(pap.id) ~* '$cadena_buscada' "
		            . ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$fecha_ini = $fecha_fin = $id_carrera = $jornada = null;	
} else {

	if ($id_carrera <> "") {
		$condicion .= "AND ($id_carrera IN (pap.carrera1_post,pap.carrera2_post,pap.carrera3_post)) ";
	}
	
	if ($jornada <> "") {
		$condicion .= "AND ('$jornada' IN (pap.jornada1_post,pap.jornada2_post,pap.jornada3_post)) ";
	}
	
	if ($fecha_ini <> "" && $fecha_fin <> "") {
		if (strtotime($fecha_ini) == -1 || strtotime($fecha_fin) == -1) {
			echo(msje_js("Las fechas de búsqueda están mal ingresadas. Por favor use el formato AAAA-MM-DD"));
		} else {
			$condicion .= "AND pap.fecha_post BETWEEN '$fecha_ini'::date AND '$fecha_fin'::date ";
		}
	}
	
}

$SQL_postulantes = "SELECT vp.id,vp.nombre,to_char(vp.fecha_post,'DD-MM-YYYY') AS fecha_post,
                           trim(vp.carrera1)||'-'||pap.jornada1_post AS carrera1,
                           trim(vp.carrera2)||'-'||pap.jornada2_post AS carrera2,
                           trim(vp.carrera3)||'-'||pap.jornada3_post AS carrera3
                    FROM vista_pap AS vp
                    LEFT JOIN pap USING (id)
                    $condicion
                    ORDER BY pap.fecha_post DESC 
                    LIMIT $cant_reg
                    OFFSET $reg_inicio";
$postulantes     = consulta_sql($SQL_postulantes);

$enlace_nav = "$enlbase=$modulo"
            . "&fecha_ini=$fecha_ini"
            . "&fecha_fin=$fecha_fin"
            . "&id_carrera=$id_carrera"
            . "&jornada=$jornada"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";

if (count($postulantes) > 0) {
	$SQL_total_pap =  "SELECT count(id) AS total_pap FROM pap $condicion;";
	$total_pap = consulta_sql($SQL_total_pap);
	$tot_reg = $total_pap[0]['total_pap'];
	
	$HTML_paginador = html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$carreras = consulta_sql("SELECT id,nombre FROM carreras ORDER BY nombre;");

$_SESSION['enlace_volver'] = "$enlbase=$modulo&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&buscar_fecha=$buscar_fecha&id_carrera=$id_carrera&texto_buscar=$texto_buscar&buscar=$buscar&reg_inicio";

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="2" border="0" cellspacing="0" width="auto">
      <tr>
        <td class="texto">
          Fecha de postulaci&oacute;n entre:
          <input type="text" name="fecha_ini" value="<?php echo($fecha_ini); ?>" size="10" maxlength="10"> y 
          <input type="text" name="fecha_fin" value="<?php echo($fecha_fin); ?>" size="10" maxlength="10">
          <input type='button' name='buscar_fecha' value='Buscar' onClick="submitform();">
          <?php 
          	if ($buscar_fecha == "Buscar" && $fecha_ini <> "" && $fecha_fin <> "") {
          		echo("<input type='submit' name='buscar_fecha' value='Vaciar'>");          		
          	};
          ?>
        </td>
      </tr>
      <tr>
        <td class="texto">
          Mostrar postulantes de la carrera:<br>
          <select name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>    
          </select>
          de la Jornada:
          <select name="jornada" onChange="submitform();">
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
      </tr>
      <tr valign="top">
        <td class="texto" width="auto">
          Buscar por ID, RUT o nombre:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="40">
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	};
          ?>
        </td>
      </tr>
    </table>
  </form>
  Mostrando <b><?php echo($tot_reg); ?></b> postulante(s) en total, en página(s) de <?php echo($cant_reg); ?> filas<br>
  <?php echo($HTML_paginador); ?>
</div>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Fecha de<br>Postulación</td>
    <td class='tituloTabla'>1ª<br>Carrera</td>
    <td class='tituloTabla'>2ª<br>Carrera</td>
    <td class='tituloTabla'>3ª<br>Carrera</td>
  </tr>
<?php
	$HTML_pap = "";
	if (count($postulantes) > 0) {
		for ($x=0;$x<count($postulantes);$x++) {
			extract($postulantes[$x]);
			
			$enl = "$enlbase=ver_postulante&id_pap=$id";
			$enlace = "a class='enlitem' href='$enl'";
			
			$HTML_pap .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
			          . "    <td class='textoTabla'>$id</td>\n"
			          . "    <td class='textoTabla'><a class='enlitem' href='$enl'>$nombre</a></td>\n"
			          . "    <td class='textoTabla'>$fecha_post</td>\n"
			          . "    <td class='textoTabla'>$carrera1</td>\n"
			          . "    <td class='textoTabla'>$carrera2</td>\n"
			          . "    <td class='textoTabla'>$carrera3</td>\n"
			          . "  </tr>\n";
		}
	} else {
		$HTML_pap = "  <tr>"
		          . "    <td class='textoTabla' colspan='6'>"
		          . "      No hay registros para los criterios de búsqueda/selección"
		          . "    </td>\n"
		          . "  </tr>";
	}
	echo($HTML_pap);
?>
</table><br>
<div class="texto">
  <?php echo($HTML_paginador); ?>
</div>
<!-- Fin: <?php echo($modulo); ?> -->

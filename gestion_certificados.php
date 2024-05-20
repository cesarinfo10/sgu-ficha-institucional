<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo_uid_no_cero.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) {	$cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$texto_buscar   = $_REQUEST['texto_buscar'];
$buscar         = $_REQUEST['buscar'];
$ano_academico  = $_REQUEST['ano_academico'];
$id_certificado = $_REQUEST['id_certificado'];
$entregado      = $_REQUEST['entregado'];
$id_emisor      = $_REQUEST['id_emisor'];
$id_estado      = $_REQUEST['id_estado'];
$id_carrera     = $_REQUEST['id_carrera'];
$id_jornada     = $_REQUEST['id_jornada'];
$id_regimen     = $_REQUEST['id_regimen'];
$matriculado    = $_REQUEST['matriculado'];
$moroso_f       = $_REQUEST['moroso_f'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$mes_cohorte       = $_REQUEST['mes_cohorte'];
$cohorte           = $_REQUEST['cohorte'];


if (empty($_REQUEST['id_regimen'])) { $id_regimen = 'PRE'; }
if ($_REQUEST['ano_academico'] == "") { $ano_academico = $ANO; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }


$condicion = "WHERE true ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		           .  " a.rut ~* '$cadena_buscada' OR "
		           .  " text(ac.folio) ~* '$cadena_buscada' OR "
		           .  " vac.cod ~* '$cadena_buscada' "
		           .  ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$id_certificado = $entregado = $id_emisor = $id_regimen = $id_carrera = $jornada = null;	
} else {
	if ($ano_academico > 0) { $condicion .= "AND ac.ano_academico=$ano_academico "; }
	
	if ($id_certificado <> "") { $condicion .= "AND ac.id_certificado=$id_certificado "; }
	
	if ($id_carrera <> "") { $condicion .= "AND c.id=$id_carrera "; }

	if ($id_jornada <> "") { $condicion .= "AND a.jornada='$id_jornada' "; }
	
	if ($id_regimen <> "" && $id_regimen <> "t") { $condicion .= "AND (c.regimen = '$id_regimen') "; }
	
	if ($id_emisor > 0) { $condicion .= "AND ac.id_emisor=$id_emisor "; }

	if ($id_estado <> "") { $condicion .= "AND ac.estado='$id_estado' "; }

	if ($entregado == "t") { $condicion .= "AND ac.fec_entrega IS NOT NULL "; }

  if ($cohorte > 0 && $semestre_cohorte > 0 && $mes_cohorte > 0) { 
		$condicion .= "AND ((a.cohorte = $cohorte AND a.semestre_cohorte = $semestre_cohorte AND a.mes_cohorte = $mes_cohorte) ";
    if ($incluye_reinc == "si") {
      $condicion .= "     OR (a.cohorte_reinc = $cohorte AND a.semestre_cohorte_reinc = $semestre_cohorte AND a.mes_cohorte_reinc = $mes_cohorte)) ";
    } else {
      $condicion .= ") ";
    }
  } elseif ($semestre_cohorte > 0) {
		$condicion .= "AND (a.semestre_cohorte = $semestre_cohorte ";
    if ($incluye_reinc == "si") {
      $condicion .= "OR a.semestre_cohorte_reinc = '$semestre_cohorte') ";
    } else {
      $condicion .= ") ";
    }
	}

	if ($cohorte > 0) {
		$condicion .= "AND (a.cohorte = '$cohorte' ";
    if ($incluye_reinc == "si") {
      $condicion .= "OR a.cohorte_reinc = '$cohorte') ";
    } else {
      $condicion .= ") ";
    }
  }

  if ($moroso_f == "t") { $condicion .= " AND a.moroso_financiero "; }
  if ($moroso_f == "f") { $condicion .= " AND (NOT a.moroso_financiero) "; }

  if ($matriculado <> "a") {
		$SQL_mat = "SELECT id_alumno FROM matriculas WHERE ";
		switch ($matriculado) {
			case "t":
				$SQL_mat .= "ano=$ANO AND semestre=$SEMESTRE";
				$condicion .= "AND (a.id IN ($SQL_mat)) ";
				break;
			case "t1":
				$SQL_mat .= "ano=$ANO";
				$condicion .= "AND (a.id IN ($SQL_mat)) ";
				break;
			case "f":
				$SQL_mat .= "ano=$ANO AND semestre=$SEMESTRE";
				$condicion .= "AND (a.id NOT IN ($SQL_mat)) ";
		}
	}
}

$SQL_mat = "SELECT 1 FROM matriculas WHERE id_alumno=a.id AND ano=$ANO AND semestre=$SEMESTRE LIMIT 1";
if ($matriculado == "t1") { $SQL_mat = "SELECT 1 FROM matriculas WHERE id_alumno=a.id AND ano=$ANO LIMIT 1"; }

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_al_certif = "SELECT ac.folio,vac.cod,trim(a.rut) AS rut,va.nombre AS alumno,a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,
                         trim(c.alias) AS carrera,a.jornada,r.nombre AS regimen,va.estado AS estado_alumno,cert.nombre AS docto,
                         to_char(ac.fec_impresion,'DD-tmMon-YYYY') AS fec_impresion,u.nombre_usuario AS emisor,to_char(ac.fecha,'DD-tmMon-YYYY') AS fecha,
                         to_char(ac.fec_entrega,'DD-tmMon-YYYY') AS fec_entrega,u2.nombre_usuario AS entregador,ac.ano_academico,
                         ac.estado,to_char(ac.estado_fecha,'DD-tmMon-YYYY  HH24:MI') AS estado_fecha,
                         CASE WHEN length(ac.archivo)>0 THEN 'Si' ELSE 'No' END AS docto_firmado,
                         CASE WHEN ($SQL_mat)=1 THEN 'Si' ELSE 'No' END AS matric,
                         CASE WHEN a.moroso_financiero THEN '(M)' ELSE '' END AS moroso_financiero,
                         to_char(ac.archivo_fecha,'DD-tmMon-YYYY') AS archivo_fecha,u3.nombre_usuario as archivo_usuario,
                         ac.texto_adicional,u4.nombre_usuario as estado_usuario
                  FROM alumnos_certificados AS ac
                  LEFT JOIN vista_alumnos_certificados_codbarras AS vac USING (folio)
                  LEFT JOIN certificados    AS cert ON cert.id=ac.id_certificado
                  LEFT JOIN alumnos         AS a    ON a.id=ac.id_alumno
                  LEFT JOIN vista_alumnos   AS va   ON va.id=ac.id_alumno
                  LEFT JOIN carreras        AS c    ON c.id=a.carrera_actual
                  LEFT JOIN regimenes       AS r    ON r.id=c.regimen
                  LEFT JOIN usuarios        AS u    ON u.id=ac.id_emisor
                  LEFT JOIN usuarios        AS u2   ON u2.id=ac.id_entregador
                  LEFT JOIN usuarios        AS u3   ON u3.id=ac.archivo_id_usuario
                  LEFT JOIN usuarios        AS u4   ON u4.id=ac.estado_id_usuario
                  $condicion
                  ORDER BY ac.fecha DESC,va.nombre ";
$SQL_tabla_completa = "COPY ($SQL_al_certif) to stdout WITH CSV HEADER";
$SQL_al_certif .= "$limite_reg OFFSET $reg_inicio ";
$al_certif = consulta_sql($SQL_al_certif);

$enlace_nav = "$enlbase=$modulo"
            . "&id_regimen=$id_regimen"
            . "&id_carrera=$id_carrera"
            . "&jornada=$jornada"
            . "&id_certificado=$id_certificado"
            . "&id_emisor=$id_emisor"
            . "&entregado=$entregado"
            . "&estado=$estado"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&cant_reg=$cant_reg"
            . "&r_inicio";

if (count($al_certif) > 0) {
	$SQL_tot_al_certif = "SELECT count(ac.folio) AS total_reg
			    		  FROM alumnos_certificados AS ac
				    	  LEFT JOIN certificados    AS cert ON cert.id=ac.id_certificado
					      LEFT JOIN alumnos         AS a    ON a.id=ac.id_alumno
					      LEFT JOIN vista_alumnos   AS va   ON va.id=ac.id_alumno
					      LEFT JOIN carreras        AS c    ON c.id=a.carrera_actual
					      LEFT JOIN usuarios        AS u    ON u.id=ac.id_emisor
					      LEFT JOIN usuarios        AS u2   ON u2.id=ac.id_entregador
					      $condicion";
	$tot_al_certif     = consulta_sql($SQL_tot_al_certif);
	$tot_reg         = $tot_al_certif[0]['total_reg'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
	$HTML = "";
	for ($x=0;$x<count($al_certif);$x++) {
		extract($al_certif[$x]);
		$enl = "certificado.php?folio=$folio";

		$docto = "<div title='header=[Texto adicional] fade=[on] body=[$texto_adicional]' style='background: #BFE4BF; border-radius: 25px; padding: 0px 2px 0px 2px'>$docto</div>";
		//$docto = "$docto<div style='background: #BFE4BF; border-radius: 25px; padding: 0px 2px 0px 2px'><small>$texto_adicional</small></div>";

		if ($estado == "Emitido" && $docto_firmado == "No") {
			$docto_firmado = "$docto_firmado<br><a href='certificado_noimp.php?cod=$cod&alumno=no' class='botoncito' style='text-align: center'>Ver Certificado prefirmado</a>";
		}
		if ($estado == "Firmado" && $docto_firmado == "No") {
			$docto_firmado = "<a href='$enlbase_sm=alumno_certificado_subir_docto&folio=$folio' class='boton' style='text-align: center' id='sgu_fancybox'>Subir Certificado<br>Firmado y Digitalizado</a>";
		} elseif (($estado == "Firmado" || $estado == "Entregado") && $docto_firmado == "Si") {
			$docto_firmado = "<a href='certificado_digitalizado.php?cod=$cod' class='boton' style='text-align: center'>Ver Certificado</a><br>"
			               . "<a href='$enlbase_sm=alumno_certificado_subir_docto&folio=$folio' class='enlaces' id='sgu_fancybox'><small>Volver a subir Certificado<br>Firmado y Digitalizado</small></a>";
		}
			
		
		$folio = "<a href='$enl' class='enlaces'>$folio</a>";
		
		$HTML .= "  <tr class='filaTabla' $background>\n"
			  .  "    <td class='textoTabla' align='right'>$folio</td>\n"
			  .  "    <td class='textoTabla'><div>$rut <sup><small>$moroso_financiero</small></sup></div><div>$alumno</div></td>\n"
			  .  "    <td class='textoTabla'>$carrera-$jornada</td>\n"
			  .  "    <td class='textoTabla'>$regimen</td>\n"
			  .  "    <td class='textoTabla'>$docto</td>\n"
			  .  "    <td class='textoTabla'>$ano_academico</td>\n"
			  .  "    <td class='textoTabla' align='center'><div>$fecha</div><div>$emisor</div></td>\n"
			  .  "    <td class='textoTabla'>$estado<br><small>$estado_fecha<br>$estado_usuario</small></td>\n"
			  .  "    <td class='textoTabla'>$docto_firmado</td>\n"
			 // .  "    <td class='textoTabla' align='center'><div>$fec_entrega</div><div>$entregador</div></td>\n"
			  .  "  </tr>\n";
	}
} else {
	$HTML = "<tr><td class='textoTabla' colspan='8' align='center'><br> *** No hay registros que satisfagan los argumentos de búsqueda ***<br><br></td></tr>";
}
                  	
$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($id_regimen <> "")      { $cond_carreras .= "AND regimen='$id_regimen' "; }
$carreras = consulta_sql("SELECT id,nombre,CASE WHEN activa THEN 'Vigentes' ELSE 'No vigentes' END AS grupo FROM carreras $cond_carreras ORDER BY activa DESC,nombre;");
$CERTIFICADOS = consulta_sql("SELECT id,nombre FROM certificados WHERE activo ORDER BY nombre");
$CERTIFICADOS_na = consulta_sql("SELECT id,nombre FROM certificados WHERE NOT activo ORDER BY nombre");
$emisores = consulta_sql("SELECT id,nombre FROM vista_usuarios WHERE id IN (SELECT id_emisor FROM alumnos_certificados)");

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$ANOS = consulta_SQL("SELECT DISTINCT ON (ano_academico) ano_academico AS id,ano_academico AS nombre FROM alumnos_certificados order by ano_academico DESC");

$ESTADOS = array(array('id'=>"Emitido",  'nombre'=>"Emitido"),
                 array('id'=>"En firma", 'nombre'=>"En firma"),
                 array('id'=>"Firmado",  'nombre'=>"Firmado"),
                 array('id'=>"Entregado",'nombre'=>"Entregado"));
                 
$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$MATRICULADO = array(array('id'=>"t", 'nombre'=>"Sí (sólo $SEMESTRE-$ANO)"),
                     array('id'=>"t1",'nombre'=>"Sí (Año $ANO)"),
                     array('id'=>"f", 'nombre'=>"No"));

$cohortes = consulta_sql("SELECT DISTINCT ON (cohorte) cohorte AS id,cohorte AS nombre,CASE WHEN cohorte=$ANO THEN 'Nuevos' WHEN cohorte>$ANO THEN 'Futuros' ELSE 'Antiguos' END AS grupo FROM alumnos ORDER BY cohorte DESC");

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));


?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

<div class="texto" style="margin-top: 5px">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Año Académico: <br>
          <select class="filtro" name="ano_academico" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($ANOS,$ano_academico)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Documento: <br>
          <select class="filtro" name="id_certificado" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($CERTIFICADOS,$id_certificado)); ?>    
            <option value="">-- No activos --</option>
            <?php echo(select($CERTIFICADOS_na,$id_certificado)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Estado: <br>
          <select class="filtro" name="id_estado" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($ESTADOS,$id_estado)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Entregado: <br>
          <select class="filtro" name="entregado" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($sino,$entregado)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Emisor:<br>
          <select class="filtro" name="id_emisor" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($emisores,$id_emisor)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Carrera/Programa:<br>
          <select class="filtro" name="id_regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$id_regimen)); ?>
          </select>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select_group($carreras,$id_carrera)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Jornada:<br>
          <select class="filtro" name="id_jornada" onChange="submitform();">
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$id_jornada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Cohorte: <br>
<?php if ($regimen <> "PRE") { ?>          
          <select class="filtro" name="mes_cohorte" onChange="if (this.value > 6) { formulario.semestre_cohorte.value=2; } else { formulario.semestre_cohorte.value=1; } submitform();">
            <option value="0">-- mes --</option>
            <?php echo(select($meses_fn,$mes_cohorte)); ?>    
          </select>
          -
<?php } ?>
          <select class="filtro" name="semestre_cohorte" onChange="submitform();">
            <option value="0"></option>
            <?php echo(select($SEMESTRES_COHORTES,$semestre_cohorte)); ?>    
          </select>
          -
          <select class="filtro" name="cohorte" onChange="submitform();">
            <option value="0">Todas</option>
            <?php echo(select_group($cohortes,$cohorte)); ?>    
          </select>
<?php if ($cohorte > 0) { ?>
          <input type='checkbox' name='incluye_reinc' value='si' id='incluye_reinc' onClick='submitform();' <?php if ($incluye_reinc == 'si') { echo('checked'); } ?>>
          <label for='incluye_reinc'>Reincoporados</label>
<?php } ?>
        </td>
        <td class="celdaFiltro">
          Matriculado: <br>
          <select class="filtro" name="matriculado" onChange="submitform();">
            <option value="a">Todos</option>
            <?php echo(select($MATRICULADO,$matriculado)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Moroso: <br>
          <select class="filtro" name="moroso_f" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($sino,$moroso_f)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Buscar folio (o código de barras) de certificado, RUT o nombre de alumno:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="45" id="texto_buscar" class='boton'>
          <input type='submit' name='buscar' value='Buscar'>
          <script>document.getElementById("texto_buscar").focus();document.getElementById("texto_buscar").select();</script>
        </td>
        <td class="celdaFiltro">
          Cambio de estado:<br>
          <a href="<?php echo("$enlbase=certificados_cambiar_estado&nuevo_estado=En+firma"); ?>" class="boton">En firma</a>
          <a href="<?php echo("$enlbase=certificados_cambiar_estado&nuevo_estado=Firmado"); ?>" class="boton">Firmado</a>
          <a href="<?php echo("$enlbase=certificados_cambiar_estado&nuevo_estado=Entregado"); ?>" class="boton">Entregado</a>
        </td>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="4">
      Mostrando <b><?php echo($tot_reg); ?></b> certificado(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="4">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Folio</td>
    <td class='tituloTabla'>Alumno</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Régimen</td>
    <td class='tituloTabla'>Documento</td>
    <td class='tituloTabla'>Año<br>Acad</td>
    <td class='tituloTabla'>Emitido</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Docto<br>Firmado</td>
    <!-- <td class='tituloTabla'>Entregado</td> -->
  </tr>
  <?php echo($HTML); ?>
</table>
<!-- Fin: <?php echo($modulo); ?> -->

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 700,
		'maxHeight'		: 550,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});

</script>


<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso     = $_REQUEST['id_curso'];
$id_sesion    = $_REQUEST['id_sesion'];
$texto_buscar = $_REQUEST['texto_buscar'];

if ($_REQUEST["sumar"] == "Añadir") {
    if ($_REQUEST["id_alumno"] <> "") {
        $id_alumno = $_REQUEST['id_alumno'];
        $SQL_ins_ca_temp = "INSERT INTO ca_temporal (id_curso,id_alumno) VALUES ($id_curso,$id_alumno)";
    }
    if ($_REQUEST["id_pap"] <> "") {
        $id_pap = $_REQUEST['id_pap'];
        $SQL_ins_ca_temp = "INSERT INTO ca_temporal (id_curso,id_pap) VALUES ($id_curso,$id_pap)";
    }
    if ($_REQUEST["rut_prov"] <> "" && $_REQUEST["apellidos_prov"] <> "" && $_REQUEST["nombres_prov"] <> "") {
        $rut_prov       = $_REQUEST['rut_prov'];
        $apellidos_prov = $_REQUEST['apellidos_prov'];
        $nombres_prov   = $_REQUEST['nombres_prov'];
        $SQL_ins_ca_temp = "INSERT INTO ca_temporal (id_curso,rut,apellidos,nombres) VALUES ($id_curso,'$rut_prov','$apellidos_prov','$nombres_prov')";
    }
    
    if (strlen($SQL_ins_ca_temp) > 0) {
        $SQL_ins_ca_temp .= ";INSERT INTO ca_temp_asist (id_ca_temporal,id_sesion,presente) VALUES (currval('ca_temporal_id_seq'),$id_sesion,true)";
        if (consulta_dml($SQL_ins_ca_temp) > 0) {
            echo(msje_js("Se ha agregado al estudiante provisionalmente"));
            echo(js("parent.jQuery.fancybox.close();"));
        }
    }
}

if ($_REQUEST['buscar'] == "Buscar" && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(nombres||' '||apellidos) ~* '$cadena_buscada' OR "
		            . " rut ~* '$cadena_buscada' "
		            . ") AND ";
	}
    $condicion=substr($condicion,0,strlen($condicion)-4);

    $SQL_mat = "SELECT 1 FROM matriculas WHERE ano=$ANO AND semestre=$SEMESTRE LIMIT 1";

    $SQL_alumnos = "SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,a.genero,a.fec_nac,c.alias||'-'||a.jornada AS carrera,c.regimen,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,
                       ae.nombre AS estado,to_char(a.estado_fecha,'DD-MM-YYYY') AS estado_fecha,
                       CASE WHEN ($SQL_mat)=1 THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero
                FROM alumnos AS a
                LEFT JOIN carreras   AS c ON c.id=a.carrera_actual
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                WHERE c.regimen='PRE' AND $condicion";

    $alumnos = consulta_sql($SQL_alumnos);
    
	$HTML_alumnos = "";
	if (count($alumnos) > 0) {
		for ($x=0;$x<count($alumnos);$x++) {
			extract($alumnos[$x]);
			
			$enl = "$enlbase=$modulo_destino&id_alumno=$id&rut=$rut";
			$enlace = "a class='enlitem' href='$enl'";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			$HTML_alumnos .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
                           . "    <td class='textoTabla'><a href='$enlbase_sm=$modulo&id_sesion=$id_sesion&id_curso=$id_curso&id_alumno=$id&sumar=Añadir' class='botoncito'> + añadir</a></td>\n"
                           . "    <td class='textoTabla'>$id</td>\n"
			               . "    <td class='textoTabla'>$rut</td>\n"
			               . "    <td class='textoTabla'><a class='enlitem' href='$enl'>$nombre</a></td>\n"
			               . "    <td class='textoTabla'>$carrera</td>\n"
			               . "    <td class='textoTabla'>$cohorte $mes_cohorte</td>\n"
			               . "    <td class='textoTabla'>$estado</td>\n"
			               . "    <td class='textoTabla' align='center'>$matriculado</td>\n"
			               . "  </tr>\n";
		}
	} else {
		$HTML_alumnos = "  <tr>"
		              . "    <td class='textoTabla' colspan='8'>"
                      . "      *** No hay registros para los criterios de búsqueda ***<br>"
                      . "      Puede agregar manualmente si no encuentra al estudiante<br><br>"
                      . "      <form name='form2' action='principal_sm.php' method='get'>"
                      . "      <input type='hidden' name='modulo' value='$modulo'>"
                      . "      <input type='hidden' name='id_curso' value='$id_curso'>"
                      . "      <input type='hidden' name='id_sesion' value='$id_sesion'>"
                      . "      <table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>"
                      . "        <tr><td class='celdaNombreAttr' colspan='4' style='text-align: center'>Antecedentes del Estudiante Provisorio</td></tr>"
                      . "        <tr>"
                      . "          <td class='celdaNombreAttr'>RUT:</td>"
                      . "          <td class='celdaValorAttr' colspan='3'><input type='text' size='10' name='rut_prov' class='boton' onBlur='this.value=this.value.toUpperCase(); return valida_rut(this);' required></td>"
                      . "        </tr>"
                      . "        <tr>"
                      . "          <td class='celdaNombreAttr'>Apellidos:</td>"
                      . "          <td class='celdaValorAttr'><input type='text' size='20' name='apellidos_prov' class='boton' onBlur='this.value=this.value.toUpperCase();' required></td>"
                      . "          <td class='celdaNombreAttr'>Nombres:</td>"
                      . "          <td class='celdaValorAttr'><input type='text' size='20' name='nombres_prov' class='boton' onBlur='this.value=this.value.toUpperCase();' required></td>"
                      . "        </tr>"
                      . "        <tr><td class='celdaNombreAttr' colspan='4'><input type='submit' name='sumar' value='Añadir'></td></tr>"
                      . "      </table>"
                      . "      </form>"
		              . "    </td>"
		              . "  </tr>";
	}
	
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo"    value="<?php echo($modulo); ?>">
<input type="hidden" name="id_sesion" value="<?php echo($id_sesion); ?>">
<input type="hidden" name="id_curso"  value="<?php echo($id_curso); ?>">

<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
    <td class="celdaFiltro">
      Buscar por RUT o nombre:<br>
      <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="60" id="texto_buscar" class='boton'>
      <input type='submit' name='buscar' value='Buscar'>          
      <script>document.getElementById("texto_buscar").focus();</script>
    </td>
  </tr>
</table>
</form>
<?php if ($_REQUEST['buscar'] == "Buscar" && $texto_buscar <> "") { ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>&nbsp;</td>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Cohorte</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Mat?</td>
  </tr>
  <?php echo($HTML_alumnos); ?>
</table>
<?php } ?>

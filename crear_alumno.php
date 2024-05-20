<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$aCampos = array("rut","nombres","apellidos","genero","nacionalidad",
                 "pasaporte","direccion","comuna","region","telefono",
                 "tel_movil","email","admision","carrera1_post","carrera2_post",
                 "carrera3_post","cert_nacimiento","conc_notas_em","boletin_psu","copia_ced_iden",
                 "licencia_em","rbd_colegio","ano_egreso_col","promedio_col","ano_psu",
                 "puntaje_psu","id_inst_edsup_proced","carr_ies_pro","prom_nt_ies_pro","conc_nt_ies_pro",
                 "prog_as_ies_pro","id_fte_finan","paga_matricula","id_convenio","id_beca",
                 "id_credito","fec_nac","fotografias","mes_cohorte");

$aRequeridos = array(0,1,2,3,4,6,7,8,9,11,12,13,16,17,19);
$requeridos  = requeridos($aRequeridos,$aCampos);
$requeridos .= ",'fec_nac_dia','fec_nac_mes','fec_nac_ano'";

$rut        = $_REQUEST['rut'];
$validar    = $_REQUEST['validar'];
$rut_valido = $_REQUEST['rut_valido'];


if (empty($_REQUEST['rut_valido'])) { $rut_valido = false; }

if (empty($_REQUEST["semestre_cohorte"])) { $_REQUEST["semestre_cohorte"] = $SEMESTRE_MATRICULA; }
if (empty($_REQUEST["cohorte"])) { $_REQUEST["cohorte"] = $ANO_MATRICULA; }
if (empty($_REQUEST["mes_cohorte"])) { $_REQUEST["mes_cohorte"] = 3; }

if (!$rut_valido && $validar <> "" && $rut <> "") {

	$SQL_pap = "SELECT id,carrera1_post,carrera2_post,carrera3_post,carrera4_post,carrera5_post,carrera6_post,
	                   jornada1_post,jornada2_post,jornada3_post,jornada4_post,jornada5_post,jornada6_post,
	                   nombres,apellidos,cert_nacimiento,
	                   conc_notas_em,boletin_psu,copia_ced_iden,licencia_em,fotografias
	            FROM pap
	            WHERE rut = '$rut';";
	$pap = consulta_sql($SQL_pap);

	if (count($pap) > 0) {
		$alumno = consulta_sql("SELECT id FROM alumnos WHERE rut = '$rut';");
		if (count($alumno) > 0) {
			echo(msje_js("ATENCIÓN: Este rut ya está registrado como alumno.\\nSe prosigue de todas formas."));
		}
		
		$id_carreras = "";
		$ids_carreras = array();
		for ($x=1;$x<=6;$x++) { if ($pap[0]['carrera'.$x.'_post'] <> "") { $ids_carreras[$x] = $pap[0]['carrera'.$x.'_post']; } }
		//var_dump($ids_carreras);
		
		$id_carreras = implode(",",$ids_carreras);
		
		$id_pap = $pap[0]['id'];
		
		//$id_carreras = $pap[0]['carrera1_post'];
		//if ($pap[0]['carrera2_post'] <> "") { $id_carreras .= ",".$pap[0]['carrera2_post']; }
		//if ($pap[0]['carrera3_post'] <> "") { $id_carreras .= ",".$pap[0]['carrera3_post']; }
		
		$SQL_carreras = "SELECT c.id,c.nombre||' ('||c.alias||') '||coalesce('Malla: '||m.ano,'[SIN MALLA]') AS nombre,id_malla_actual,m.ano
		                 FROM carreras    AS c
                     LEFT JOIN mallas AS m ON m.id=id_malla_actual
		                 WHERE c.activa AND c.id IN ($id_carreras)
		                 ORDER BY c.nombre";
		$carreras = consulta_sql($SQL_carreras);
		$_REQUEST['carrera_actual'] = $pap[0]['carrera1_post'];
		$_REQUEST['estado'] = 1;
		$_REQUEST = array_merge($_REQUEST,$pap[0]);		
		$rut_valido = true;
	} else {
		echo(msje_js("Este rut no está registrado como postulante."));		
	}
}

if ($_REQUEST['crear'] <> "" && $_REQUEST['id_pap'] <> "") {

	$id_pap           = $_REQUEST['id_pap'];
	$carrera_actual   = $_REQUEST['carrera_actual'];
	$cohorte          = $_REQUEST['cohorte'];
	$semestre_cohorte = $_REQUEST['semestre_cohorte'];
	$mes_cohorte      = $_REQUEST['mes_cohorte'];
	$estado           = $_REQUEST['estado'];
	$jornada          = $_REQUEST['jornada'];
	
	$SQL_malla_actual = "SELECT id_malla_actual FROM carreras WHERE id=$carrera_actual";
  if (count(consulta_sql($SQL_malla_actual)) == 0) {
    echo(msje_js("ERROR: La carrera seleccionada no tiene una malla/plan de estudios asociado. No se puede continuar."));
  } else {

    $iCampos = "rut,nombres,apellidos,genero,fec_nac,nacionalidad,pasaporte,direccion,comuna,region,telefono,tel_movil,"
            . "admision,estado,cohorte,semestre_cohorte,mes_cohorte,carrera_actual,jornada,malla_actual,cert_nacimiento,conc_notas_em,boletin_psu,"
            . "copia_ced_iden,licencia_em,rbd_colegio,ano_egreso_col,promedio_col,ano_psu,puntaje_psu,"
            . "id_inst_edsup_proced,carr_ies_pro,prom_nt_ies_pro,conc_nt_ies_pro,prog_as_ies_pro,id_pap,email,fotografias";

    $sCampos = "rut,nombres,apellidos,genero,fec_nac,nacionalidad,pasaporte,direccion,comuna,region,telefono,tel_movil,"
            . "admision,$estado AS estado,$cohorte AS cohorte,$semestre_cohorte AS semestre_cohorte,$mes_cohorte AS mes_cohorte,$carrera_actual AS carrera_actual,"
            . "'$jornada' AS jornada,($SQL_malla_actual) AS malla_actual,"
            . "cert_nacimiento,conc_notas_em,boletin_psu,copia_ced_iden,licencia_em,rbd_colegio,ano_egreso_col,"
            . "promedio_col,ano_psu,puntaje_psu,id_inst_edsup_proced,carr_ies_pro,prom_nt_ies_pro,conc_nt_ies_pro,"
            . "prog_as_ies_pro,$id_pap AS id_pap,email,fotografias";

    $SQLinsert = "INSERT INTO alumnos ($iCampos) SELECT $sCampos FROM pap WHERE id=$id_pap;";
    //echo($SQLinsert);
    if (consulta_dml($SQLinsert) > 0) {
      $alumno    = consulta_sql("SELECT currval('serial_id_al') AS id;");
      $id_alumno = $alumno[0]['id'];
      consulta_dml("INSERT INTO matriculas (id_alumno,semestre,ano) VALUES ($id_alumno,$SEMESTRE_MATRICULA,$ANO_MATRICULA);");
      $confirma_msje   = "Se ha creado existosamente un nuevo alumno. Desea crear otro?";
      $confirma_url_si = "$enlbase=$modulo";
      $confirma_url_no = "$enlbase=ver_alumno&id_alumno=$id_alumno";
      echo(confirma_js($confirma_msje,$confirma_url_si,$confirma_url_no));
      exit;
    } else {
      echo(msje_js("ERROR: Este rut ya está registrado como alumno en la carrera que ha seleccionado.\\n\\n"
                  ."** NO SE HA REGISTRADO AL ALUMNO **"));
    }
  }
}

$estados = consulta_sql("SELECT id,nombre FROM al_estados WHERE nombre IN ('Vigente','Suspendido','Retirado','Abandono','Condicional') ORDER BY id;");
$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>"Primer"),
                            array("id"=>2,"nombre"=>"Segundo"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>

<?php	if ($rut_valido) { ?>
<form name="formulario" action="principal.php" method="get"
      onSubmit="if (!enblanco2(<?php echo($requeridos); ?>) || !val_nota('promedio_col','prom_nt_ies_pro') || !val_psu('puntaje_psu') || !valida_rut(formulario.rut.value)) { return false; }">

<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="validar" value="<?php echo($validar); ?>">
<input type="hidden" name="rut_valido" value="<?php echo($rut_valido); ?>">
<input type="hidden" name="id_pap" value="<?php echo($id_pap); ?>">

<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="submit" name="crear" value="Crear" tabindex="99">
    </td>
    <td class="tituloTabla">
      <input type="button" name="cancelar" value="Cancelar" onclick="history.back();">
    </td>
  </tr>
</table>
<br>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Postulante</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'>
      <input type="text" size="15" name="rut" value="<?php echo($_REQUEST['rut']); ?>" readonly>
    </td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Nombres:</u></td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['nombres']); ?></td>
    <td class='celdaNombreAttr'><u>Apellidos:</u></td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['apellidos']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Carrera:</u></td>
    <td class='celdaValorAttr'>
      <select name='carrera_actual'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($carreras,$_REQUEST['carrera_actual'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'><u>Jornada:</u></td>
    <td class='celdaValorAttr'>
      <select name='jornada'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($JORNADAS,$_REQUEST['jornada1_post'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Cohorte:</u></td>
    <td class='celdaValorAttr'>
      <select name='semestre_cohorte'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($SEMESTRES_COHORTES,$_REQUEST['semestre_cohorte'])); ?>        
      </select> - 
      <input type="text" size="4" name="cohorte" value="<?php echo($_REQUEST['cohorte']); ?>">
    </td>
    <td class='celdaNombreAttr'><u>Mes Cohorte:</u></td>
    <td class='celdaValorAttr'>
      <select name='mes_cohorte'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($meses_fn,$_REQUEST['mes_cohorte'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Estado:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='estado'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($estados,$_REQUEST['estado'])); ?>
      </select>
    </td>
  </tr>
<!--  <tr>
    <td class='celdaValorAttr' colspan="2">
      <b>Documentaci&oacute;n Requerida</b><br>
      <input type='checkbox' name='cert_nacimiento' value="t"
             <?php if ($_REQUEST['cert_nacimiento'] == "t") { echo("checked"); } ?>>
      Certificado de nacimiento<br>
      <input type='checkbox' name='conc_notas_em' value="t"
             <?php if ($_REQUEST['conc_notas_em'] == "t") { echo("checked"); } ?>>
      Concentraci&oacute;n de notas EM<br>
      <input type='checkbox' name='boletin_psu' value="t"
             <?php if ($_REQUEST['boletin_psu'] == "t") { echo("checked"); } ?>>
      Bolet&iacute;n PSU
    </td>      
    <td class='celdaValorAttr' colspan="2"><br>
      <input type='checkbox' name='copia_ced_iden' value="t"
             <?php if ($_REQUEST['copia_ced_iden'] == "t") { echo("checked"); } ?>>
      Fotocopia C&eacute;dula Nacional de Identidad<br>
      <input type='checkbox' name='licencia_em' value="t"
             <?php if ($_REQUEST['licencia_em'] == "t") { echo("checked"); } ?>>
      Licencia de Ense&ntilde;anza Media<br>
      <sup><b>
        NOTA: El postulante debe presentar el Certificado de Nacimiento (ORIGINAL)<br>
        o la Fotocopia de la Cédula Nacional de Identidad.        
      </b></sup>
    </td>
  </tr> -->
</table>	
<?php	} else { ?>
<form name="formulario" action="principal.php" method="get" onSubmit="return valida_rut(formulario.rut);">
  <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
  <table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>
    <tr>
      <td class='celdaNombreAttr'>RUT:</td>
      <td class='celdaValorAttr'>
        <input type='text' size="12" name='rut' onChange="var valor=this.value;this.value=valor.toUpperCase();" tabindex="1">
        <script>formulario.rut.focus();</script>
        <input type="submit" name="validar" value="Validar" tabindex="2">
      </td>
    </tr>
  </table>
</form>
<?php	} ?>

<!-- Fin: <?php echo($modulo); ?> -->


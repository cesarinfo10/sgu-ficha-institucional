<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if ($_REQUEST['tipo_contrato'] == "") { $_REQUEST['tipo_contrato'] = "Semestral"; }
$id_alumno  = $_REQUEST['id_alumno'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='$enlbase=gestion_alumnos';"));
	exit;
}

$rf_rut      = $_REQUEST['rf_rut'];

$SQL_alumno = "SELECT va.id,trim(va.rut) AS rut,va.nombre,va.direccion,va.comuna,va.region,al.apellidos,al.nombres,
                      al.genero AS id_genero,al.nacionalidad AS id_nacionalidad,al.comuna AS id_comuna,
                      al.region AS id_region,al.email,al.pasaporte,al.telefono,al.tel_movil,a.rf_rut,c.nombre AS carrera,
                      CASE al.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,va.estado,al.moroso_financiero,
                      al.regimen
               FROM alumnos AS al
               LEFT JOIN vista_alumnos AS va USING (id)
               LEFT JOIN carreras AS c ON c.id=carrera_actual
               LEFT JOIN avales AS a ON a.id=al.id_aval
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno)>0) {
	$msje = "";
	
	$al_indocumentado = consulta_sql("SELECT id_alumno,regexp_replace(doc_adeudado,'\n',' ') AS doc_adeudado FROM alumnos_indocumentados WHERE id_alumno=$id_alumno");
	if (count($al_indocumentado) > 0) {
		$msje = "Actualmente este alumno debe documentos. "
		      . "Estos son {$al_indocumentado[0]['doc_adeudado']} (según información de Registro Académico)\\n\\n"
			  . "No puede matricularse";
	} else {

		switch ($alumno[0]['estado']) {
			case "Vigente":
				break;
			case "Egresado":
				break;
			case "Moroso":
				$msje = "Actualmente este alumno tiene el estado de Moroso.\\n\\n"
				      . "No puede matricularse";
				break;
			default:
				$msje = "Actualmente el alumno tiene el estado {$alumno[0]['estado']} y debe solicitar formalmente "
				      . "a su escuela la reincoporación o bien el cambio de estado que corresponda.\\n\\n"
				      . "No puede matricularse";
		}
		
		if ($alumno[0]['moroso_financiero'] == "t") {
			$msje = "Actualmente este alumno tiene el estado de Moroso.\\n\\n"
			      . "No puede matricularse";
		}
		
	}
	
	if (!empty($msje)) {
		echo(msje_js($msje));
		echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;
	}
	
	if ($_REQUEST['chequear'] == "" && $_REQUEST['guardar'] == "") {
		
		$SQL_contrato = "SELECT id FROM finanzas.contratos 
		                 WHERE id_alumno=$id_alumno AND ano=$ANO_MATRICULA AND estado IN ('E','F')";
		$contrato = consulta_sql($SQL_contrato);
		if (count($contrato) > 0) {
			echo(msje_js("Este alumno ya tiene un contrato emitido para este periodo. "
			            ."Revise los documentos existentes en el módulo <Documentos de Matrícula>.\\n\\n "
			            ."El SGU le permitirá seguir adelante."));
	//		echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
	//		exit;
		}
	}
	
	if ($_REQUEST['rf_rut'] == "") { $_REQUEST['rf_rut'] = $alumno[0]['rut']; }

} else {
	echo(js("location.href='$enlbase=gestion_alumnos';"));
	exit;
}

if ($alumno[0]['rf_rut'] == "") {
	$chequeo_rut = false;
	$sololectura = "";
} elseif ($_REQUEST['chequear'] <> "Chequear") {
//	$_REQUEST['chequear'] = "Chequear";
	$_REQUEST['rf_rut'] = $alumno[0]['rf_rut'];
}

if ($_REQUEST['chequear'] == "Chequear") {
	$SQL_aval = "SELECT * FROM vista_avales WHERE rf_rut='$rf_rut';";
	$aval = consulta_sql($SQL_aval);
	if (count($aval) > 0 && $_REQUEST['guardar'] == "") {
		$_REQUEST = array_merge($_REQUEST,$aval[0]);
	} elseif ($rf_rut == $alumno[0]['rut'] && $_REQUEST['guardar'] == "") {
		foreach ($alumno[0] AS $nombre_campo => $valor_campo) {
			if (substr($nombre_campo,0,3) == "id_") { $nombre_campo = str_replace("id_","",$nombre_campo); }
			$_REQUEST = array_merge($_REQUEST,array("rf_$nombre_campo" => $valor_campo));
		}
	}

	$chequeo_rut = true;
	$sololectura = "readonly";

	$aCampos = array("rf_rut","rf_parentezco","rf_apellidos","rf_nombres","rf_est_civil",
	                 "rf_profesion","rf_nacionalidad","rf_pasaporte","rf_direccion","rf_comuna",
	                 "rf_region","rf_telefono","rf_tel_movil","rf_email","rf_nombre_empresa",
	                 "rf_cargo_empresa","rf_antiguedad_empresa","rf_sueldo_liquido","rf_direccion_empresa",
	                 "rf_comuna_empresa","rf_region_empresa","rf_telefono_empresa","rf_email_empresa","rf_teletrabajo");
	
	$aRequeridos = array(0,1,2,3,4,5,6,8,9,10,11,12);
	$requeridos  = requeridos($aRequeridos,$aCampos);
	
	if ($rf_rut == $alumno[0]['rut'] && $_REQUEST['guardar'] == "") { $_REQUEST['rf_parentezco'] = "Ninguno"; }
		
	$comunas        = consulta_sql("SELECT id,nombre FROM comunas;");
	$regiones       = consulta_sql("SELECT id,nombre||' ('||romano||')' AS nombre FROM regiones;");
	$nacionalidades = consulta_sql("SELECT localizacion AS id,nacionalidad AS nombre FROM pais ORDER BY nacionalidad;");

} else {

	$requeridos  = "'rf_rut','tipo_contrato'";
	
}

if ($_REQUEST['guardar'] == "Guardar y Continuar") {
	//guardar datos
	$_REQUEST['rf_sueldo_liquido'] = str_replace(".","",$_REQUEST['rf_sueldo_liquido']);
	if (count($aval) == 0) {
		$SQL_insert_update = "INSERT INTO avales " . arr2sqlinsert($_REQUEST,$aCampos)
		                   . ";UPDATE alumnos SET id_aval=currval('avales_id_seq') WHERE id=$id_alumno";
	} else {
		$id_aval = $aval[0]['id'];
		$SQL_insert_update = "UPDATE avales SET ".arr2sqlupdate($_REQUEST,$aCampos)." WHERE id=$id_aval;"
		                   . "UPDATE alumnos SET id_aval=$id_aval WHERE id=$id_alumno";
	}
	consulta_dml($SQL_insert_update);
	
	$tipo_contrato     = $_REQUEST['tipo_contrato'];
	$ano_contrato      = $_REQUEST['ano_contrato'];
	$semestre_contrato = $_REQUEST['semestre_contrato'];
	
	switch ($_REQUEST['tipo_contrato']) {
		case "Anual":
			echo(js("location.href='$enlbase=alumno_form_matricula&id_alumno=$id_alumno&semestre=$semestre_contrato&ano=$ano_contrato&tipo=$tipo_contrato';"));
			break;
		case "Semestral":
			echo(js("location.href='$enlbase=alumno_form_matricula&id_alumno=$id_alumno&semestre=$semestre_contrato&ano=$ano_contrato&tipo=$tipo_contrato';"));
			break;
		case "Estival":
			echo(js("location.href='$enlbase=alumno_form_matricula_estival&id_alumno=$id_alumno&semestre=$semestre_contrato&ano=$ano_contrato';"));
			break;
		case "Modular":
			echo(js("location.href='$enlbase=alumno_form_matricula&id_alumno=$id_alumno&semestre=$semestre_contrato&ano=$ano_contrato&tipo=$tipo_contrato';"));
			break;
		case "Egresado":
			echo(js("location.href='$enlbase=alumno_form_matricula_egresado&id_alumno=$id_alumno&semestre=$semestre_contrato&ano=$ano_contrato&tipo=$tipo_contrato';"));
			break;

	}
	exit;
}



$TIPOS_CONTRATO = array(array("id"=>"Anual"    ,"nombre"=>"Anual"),
                        array("id"=>"Semestral","nombre"=>"Semestral"),
                        array("id"=>"Estival"  ,"nombre"=>"Estival"),
                        array("id"=>"Modular"  ,"nombre"=>"Modular"));

if ($alumno[0]['estado'] == "Egresado") {
	$TIPOS_CONTRATO = array(array("id"=>"Egresado"  ,"nombre"=>"Egresado"));
}

$ANOS_CONTRATOS = array(array("id"=>$ANO   , "nombre"=>$ANO),
                        array("id"=>$ANO+1 , "nombre"=>$ANO+1));
/*
if ($alumno[0]['regimen'] == "POST") { 
	$ANOS_CONTRATOS = array_merge(array(array("id"=>$ANO-1, "nombre"=>$ANO-1)),$ANOS_CONTRATOS);
}
*/
                        
$SEMESTRES_CONTRATOS = array($semestres[1],$semestres[2]);

if ($_REQUEST['ano_contrato'] == "")      { $_REQUEST['ano_contrato'] = $ANO_MATRICULA; }
if ($alumno[0]['regimen'] == "POST" && $_REQUEST['ano_contrato'] <> "") { $_REQUEST['ano_contrato']--; }
if (empty($_REQUEST['semestre_contrato'])) { $_REQUEST['semestre_contrato'] = $SEMESTRE_MATRICULA; }
if (empty($_REQUEST['tipo_contrato'])) { $_REQUEST['tipo_contrato'] = "Anual"; }

$SQL_presel_mineduc = "SELECT CASE WHEN glosa_bea = '40'  THEN true ELSE false END AS bea,
                              CASE WHEN glosa_bdte = '40' THEN true ELSE false END AS bdte,
                              CASE WHEN glosa_bjgm = '40' THEN true ELSE false END AS bjgm,
                              CASE WHEN glosa_bhpe = '40' THEN true ELSE false END AS bhpe
                       FROM dae.vista_presel_mineduc AS pbm 
					   LEFT JOIN alumnos AS a ON split_part(a.rut,'-',1)::bigint=pbm.rut
					   WHERE pbm.ano={$_REQUEST['ano_contrato']} AND a.id=$id_alumno AND pbm.cant_becas > 0";
$presel_mineduc = consulta_sql($SQL_presel_mineduc);

if (count($presel_mineduc) > 0) {
	echo(msje_js("ATENCIÓN: El/la estudiante está preseleccionado/a en una o más Becas del MINEDUC. Debe verificar el portal de Beneficios Estudiantiles"));
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<br>
<form name="formulario" action="principal.php" method="post"
      onSubmit="if (!enblanco2(<?php echo($requeridos); ?>) || !valida_rut(formulario.rf_rut)) { return false; }">
<input type="hidden" name="modulo"   value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno"   value="<?php echo($id_alumno); ?>">
<input type="hidden" name="chequear" value="<?php echo($_REQUEST['chequear']); ?>">

<?php if ($chequeo_rut) { ?>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="guardar" value="Guardar y Continuar"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onclick="history.back();"></td>
  </tr>
</table><br>
<?php } ?>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($alumno[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($alumno[0]['direccion']); ?>, <?php echo($alumno[0]['comuna']); ?>, <?php echo($alumno[0]['region']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Carrera en que se Matricula</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Carrera:</u></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($alumno[0]['carrera']." <b>jornada</b> ".$alumno[0]['jornada']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Tipo de Contrato:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='tipo_contrato' onChange="contrato_tipo(this.value);">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($TIPOS_CONTRATO,$_REQUEST['tipo_contrato'])); ?>        
      </select>
      <b>Año:</b>
      <select name='ano_contrato' disabled>
        <?php echo(select($ANOS_CONTRATOS,$_REQUEST['ano_contrato'])); ?>        
      </select>
      <b>Semestre:</b>
      <select name='semestre_contrato' disabled>
        <?php echo(select($SEMESTRES_CONTRATOS,$_REQUEST['semestre_contrato'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes del Responsable Financiero<br><sup>(Codeudor Solidario: Apoderado, Sostenedor o Aval)</sup> 
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'>
      <input type="text" size="10" name="rf_rut" value="<?php echo($_REQUEST['rf_rut']); ?>"
             onKeyUp="var valor=this.value;this.value=valor.toUpperCase();"
             <?php echo($sololectura); ?> class='boton' required>
      <?php if (!$chequeo_rut) { ?>
      <input type="submit" name="chequear" value="Chequear">
      <?php } else { ?>
    </td>
    <td class='celdaNombreAttr'><u>Parentezco:</u></td>
    <td class='celdaValorAttr'>
      <select name='rf_parentezco'  class='filtro' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($parentezcos,$_REQUEST['rf_parentezco'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Apellidos:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='rf_apellidos' value="<?php echo($_REQUEST['rf_apellidos']); ?>"
             onBlur="var valor=this.value;this.value=valor.toUpperCase();" class='boton' required>
    </td>
    <td class='celdaNombreAttr'><u>Nombres:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='rf_nombres' value="<?php echo($_REQUEST['rf_nombres']); ?>"
             onBlur="var valor=this.value;this.value=valor.toUpperCase();" class='boton' required>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Estado Civil:</u></td>
    <td class='celdaValorAttr'>
      <select name='rf_est_civil' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($estados_civiles,$_REQUEST['rf_est_civil'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'><u>Profesión:</u></td>
    <td class='celdaValorAttr'><input type='text' size="20" name='rf_profesion' value="<?php echo($_REQUEST['rf_profesion']); ?>" class="boton"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Nacionalidad:</u></td>
    <td class='celdaValorAttr'>
      <select name='rf_nacionalidad' onChange="if (this.value == 'CL') { formulario.rf_pasaporte.disabled = true; } else { formulario.rf_pasaporte.disabled = false; }">
        <option value=''>-- Seleccione --</option>
			<?php echo(select($nacionalidades,$_REQUEST['rf_nacionalidad'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'>Nro. Pasaporte:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="15" name='rf_pasaporte' value="<?php echo($_REQUEST[0]['rf_pasaporte']); ?>"  class='boton' disabled>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Dirección:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='rf_direccion' value="<?php echo($_REQUEST['rf_direccion']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" class='boton' required><br>
      <sup>Av./Calle/Psje. # Villa/Población</sup>
    </td>
    <td class='celdaNombreAttr'><u>Comuna:</u></td>
    <td class='celdaValorAttr'>
      <select name='rf_comuna' class='filtro' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($comunas,$_REQUEST['rf_comuna'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Región:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='rf_region' class='filtro' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($regiones,$_REQUEST['rf_region'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Teléfono fijo:</u></td>
    <td class='celdaValorAttr'>
      +56 <input type='number' min='100000000' max='999999999' size="10" name='rf_telefono' value="<?php echo($_REQUEST['rf_telefono']); ?>" class='boton' required>
    </td>
    <td class='celdaNombreAttr'><u>Teléfono móvil:</u></td>
    <td class='celdaValorAttr'>
      +56 <input type='number' min='100000000' max='999999999' size="10" name='rf_tel_movil' value="<?php echo($_REQUEST['rf_tel_movil']); ?>" class='boton' required>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-mail:</td>
    <td class='celdaValorAttr' colspan="3">
      <input type='email' size="30" name='rf_email' value="<?php echo($_REQUEST['rf_email']); ?>" onKeyUp="var valor=this.value;this.value=valor.toLowerCase();" class='boton' required>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      <b>Antecedentes Laborales del Responsable Financiero</b>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre Empresa:</td>
    <td class='celdaValorAttr' colspan="3">
      <input type='text' size="50" name='rf_nombre_empresa' value="<?php echo($_REQUEST['rf_nombre_empresa']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" class='boton'><br>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cargo:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="20" name='rf_cargo_empresa' value="<?php echo($_REQUEST['rf_cargo_empresa']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" class='boton'>
    </td>
    <td class='celdaNombreAttr'>Tele-trabajo:</td>
    <td class='celdaValorAttr'>
      <select name='rf_teletrabajo' class='filtro' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($sino,$_REQUEST['rf_teletrabajo'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Antigüedad:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="2" name='rf_antiguedad_empresa' value="<?php echo($_REQUEST['rf_antiguedad_empresa']); ?>" class='boton'> año(s)
    </td>
    <td class='celdaNombreAttr'>Sueldo líquido:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='rf_sueldo_liquido' 
              value="<?php echo($_REQUEST['rf_sueldo_liquido']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),'rf_sueldo_liquido')" class='boton'>
    </td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='rf_direccion_empresa' value="<?php echo($_REQUEST['rf_direccion_empresa']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" class='boton'><br>
      <sup>Av./Calle/Psje. # Villa/Población</sup>
    </td>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'>
      <select name='rf_comuna_empresa' class='filtro'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($comunas,$_REQUEST['rf_comuna_empresa'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='rf_region_empresa' class='filtro'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($regiones,$_REQUEST['rf_region_empresa'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Teléfono:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="10" name='rf_telefono_empresa' value="<?php echo($_REQUEST['rf_telefono_empresa']); ?>" class='boton'>
    </td>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr'>
      <input type='email' size="20" name='rf_email_empresa' value="<?php echo($_REQUEST['rf_email_empresa']); ?>" class='boton'>
    </td>
  </tr>
    <?php } ?>
</table>
</form>

<script>

function contrato_tipo(tipo) {
    
	if (tipo == "Anual" || tipo == "Egresado") {
		formulario.ano_contrato.disabled=false;
		formulario.semestre_contrato.disabled=true;
	} 
	
	if (tipo == "Semestral" || tipo == "Modular") {
		formulario.ano_contrato.disabled=false;
		formulario.semestre_contrato.disabled=false;		
	}
	
	if (tipo == "") {
		formulario.ano_contrato.disabled=true;
		formulario.semestre_contrato.disabled=true;
	}
	
}

contrato_tipo("<?php echo($_REQUEST['tipo_contrato']); ?>");

</script>

<!-- Fin: <?php echo($modulo); ?> -->


<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

//$ANO_MATRICULA = 2012;
//$SEMESTRE_MATRICULA = 1;

$id_pap  = $_REQUEST['id_pap'];
if (!is_numeric($id_pap)) {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}
$carrera_mat   = $_REQUEST['carrera_mat'];
$rf_rut        = $_REQUEST['rf_rut'];

$SQL_pap = "SELECT vp.id,trim(vp.rut) AS rut,vp.nombre,vp.direccion,vp.comuna,vp.region,pap.apellidos,pap.nombres,
                   pap.genero AS id_genero,pap.nacionalidad AS id_nacionalidad,pap.est_civil,pap.profesion,
                   pap.comuna AS id_comuna,pap.region AS id_region,pap.email,pap.pasaporte,pap.telefono,
                   pap.tel_movil,a.rf_rut,
                   c1.nombre AS carrera1,c2.nombre AS carrera2,c3.nombre AS carrera3,c4.nombre AS carrera4,c5.nombre AS carrera5,c6.nombre AS carrera6,
                   pap.carrera1_post,pap.carrera2_post,pap.carrera3_post,pap.carrera4_post,pap.carrera5_post,pap.carrera6_post,
                   CASE pap.jornada1_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada1,
                   CASE pap.jornada2_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada2,
                   CASE pap.jornada3_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada3,
                   CASE pap.jornada4_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada4,
                   CASE pap.jornada5_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada5,
                   CASE pap.jornada6_post WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada6,
                   pap.jornada1_post,pap.jornada2_post,pap.jornada3_post,pap.jornada4_post,pap.jornada5_post,pap.jornada6_post,
                   vp.admision
            FROM pap
            LEFT JOIN vista_pap AS vp USING (id)
            LEFT JOIN avales AS a ON a.id=pap.id_aval
            LEFT JOIN carreras AS c1 ON c1.id=pap.carrera1_post
            LEFT JOIN carreras AS c2 ON c2.id=pap.carrera2_post
            LEFT JOIN carreras AS c3 ON c3.id=pap.carrera3_post
            LEFT JOIN carreras AS c4 ON c4.id=pap.carrera4_post
            LEFT JOIN carreras AS c5 ON c5.id=pap.carrera5_post
            LEFT JOIN carreras AS c6 ON c6.id=pap.carrera6_post
            WHERE vp.id=$id_pap;";
$pap = consulta_sql($SQL_pap);
if (count($pap)>0) {
	$SQL_contrato = "SELECT id FROM finanzas.contratos 
	                 WHERE id_pap=$id_pap AND id_carrera=$carrera_mat AND ano=$ANO_MATRICULA AND semestre=$SEMESTRE_MATRICULA AND estado IS NOT NULL";
	$contrato = consulta_sql($SQL_contrato);
	if (count($contrato) > 0) {
		echo(msje_js("Este postulante ya tiene un contrato emitido. "
		            ."Revise los documentos existentes en el módulo <Documentos de Matrícula>"));
		echo(js("location.href='principal.php?modulo=ver_postulante&id_pap=$id_pap';"));
		exit;
	}
	
	if ($carrera_mat > 0) {
		$carr_mat = $pap[0]['carrera'.$carrera_mat.'_post'];
		$alumno = consulta_sql("SELECT id FROM alumnos WHERE id_pap=$id_pap AND carrera_actual=$carr_mat AND semestre_cohorte||'-'||cohorte<>'$SEMESTRE_MATRICULA-$ANO_MATRICULA'");
		if (count($alumno) > 0) {
			$msje = "Este postulante ya es Alumno, por lo que debe ser matriculado "
				  . "a través de su ficha respectiva (módulo Gestión de Alumnos). Ahora se le redirigirá a la ficha de este alumno.";
			echo(msje_js($msje));
			$id_alumno = $alumno[0]['id'];
			echo(js("location.href='principal.php?modulo=ver_alumno&id_alumno=$id_alumno';"));
			exit;
		}
		$carrera = consulta_sql("SELECT regimen FROM carreras WHERE id=$carr_mat");
		switch ($carrera[0]['regimen']) {
			case "PRE":
			case "DIP":
			case "SEM":
				$tipo_contrato = "";
				switch (trim($pap[0]['admision'])) {
					case "Normal":
					case "Regular":
					case "Extraordinaria":
						$tipo_contrato = "Semestral";
						break;
					case "Modular":
					case "Modular (Extr.)":
						$tipo_contrato = "Modular";
						break;
				}
				break;
			default:
				$tipo_contrato = "Anual";
				break;
		}
		if ($_REQUEST['tipo_contrato'] == "") { $_REQUEST['tipo_contrato'] = $tipo_contrato; }

	}
	
	if ($_REQUEST['rf_rut'] == "") { $_REQUEST['rf_rut'] = $pap[0]['rut']; }
	$carreras = array();
	for ($x=1;$x<=6;$x++) {
		if ($pap[0]['carrera'.$x.'_post'] > 0) {			
			$carr = array("id"     => $x,"nombre" => $pap[0]['carrera'.$x]." Jornada ".$pap[0]['jornada'.$x]);
			$carreras = array_merge($carreras,array($carr));
		}
	}
} else {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}

if ($pap[0]['rf_rut'] == "") {
	$chequeo_rut = false;
	$sololectura = "";
} elseif ($_REQUEST['chequear'] <> "Chequear") {
//	$_REQUEST['chequear'] = "Chequear";
	$_REQUEST['rf_rut'] = $pap[0]['rf_rut'];
}

if ($_REQUEST['chequear'] == "Chequear") {
	$SQL_aval = "SELECT * FROM vista_avales WHERE rf_rut='$rf_rut';";
	$aval = consulta_sql($SQL_aval);
	if (count($aval) > 0 && $_REQUEST['guardar'] == "") {
		$_REQUEST = array_merge($_REQUEST,$aval[0]);
	} elseif ($rf_rut == $pap[0]['rut'] && $_REQUEST['guardar'] == "") {
		foreach ($pap[0] AS $nombre_campo => $valor_campo) {
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
	                 "rf_comuna_empresa","rf_region_empresa","rf_telefono_empresa","rf_email_empresa");
	
	$aRequeridos = array(0,1,2,3,4,5,6,8,9,10,11,12);
	$requeridos  = "'carrera_mat',".requeridos($aRequeridos,$aCampos);	
	
	if ($rf_rut == $pap[0]['rut'] && $_REQUEST['guardar'] == "") { $_REQUEST['rf_parentezco'] = "Ninguno"; }
		
	$comunas        = consulta_sql("SELECT id,nombre FROM comunas;");
	$regiones       = consulta_sql("SELECT id,nombre||' ('||romano||')' AS nombre FROM regiones;");
	$nacionalidades = consulta_sql("SELECT localizacion AS id,nacionalidad AS nombre FROM pais ORDER BY nacionalidad;");

} else {
	$requeridos  = "'carrera_mat','rf_rut'";
}

if ($_REQUEST['guardar'] == "Guardar y Continuar") {
	//guardar datos
	$_REQUEST['rf_sueldo_liquido'] = str_replace(".","",$_REQUEST['rf_sueldo_liquido']);
	if (count($aval) == 0) {
		$SQL_insert_update = "INSERT INTO avales " . arr2sqlinsert($_REQUEST,$aCampos)
		                   . ";UPDATE pap SET id_aval=currval('avales_id_seq') WHERE id=$id_pap";
	} else {
		$id_aval = $aval[0]['id'];
		$SQL_insert_update = "UPDATE avales SET ".arr2sqlupdate($_REQUEST,$aCampos)." WHERE id=$id_aval;"
		                   . "UPDATE pap SET id_aval=$id_aval WHERE id=$id_pap";
	}
	consulta_dml($SQL_insert_update);
	$id_carrera    = $pap[0]['carrera'.$carrera_mat.'_post'];
	$jornada       = $pap[0]['jornada'.$carrera_mat.'_post'];
	$tipo_contrato = $_REQUEST['tipo_contrato'];
	$ano_contrato      = $_REQUEST['ano_contrato'];
	$semestre_contrato = $_REQUEST['semestre_contrato'];
	echo(js("location.href='$enlbase=postulante_form_matricula&id_pap=$id_pap&carrera_mat=$id_carrera&jornada_mat=$jornada&tipo_contrato=$tipo_contrato&ano=$ano_contrato&semestre=$semestre_contrato';"));
	exit;
}

if (empty($_REQUEST['semestre_contrato'])) { $_REQUEST['semestre_contrato'] = $SEMESTRE_MATRICULA; }
//if (empty($_REQUEST['ano_contrato'])) { $_REQUEST['ano_contrato'] = $ANO_MATRICULA; }

$TIPOS_CONTRATO = array(array("id"=>"Semestral","nombre"=>"Semestral"));
switch ($tipo_contrato) {
	case "Anual":
		$TIPOS_CONTRATO = array(array('id'=>"Anual"    ,'nombre'=>"Anual"),
		                        array('id'=>"Semestral",'nombre'=>"Semestral"));
		break;
	case "Modular":
		$TIPOS_CONTRATO = array(array('id'=>"Modular"  ,'nombre'=>"Modular"));
		break;
}
  
$SEMESTRES_CONTRATOS = array($semestres[1],$semestres[2]);
//$SEMESTRES_CONTRATOS = array($semestres[$SEMESTRE_MATRICULA]);
             
//$ANOS_CONTRATOS = array(array("id"=>$ANO-1 , "nombre"=>$ANO-1),
//                        array("id"=>$ANO   , "nombre"=>$ANO),
//                        array("id"=>$ANO+1 , "nombre"=>$ANO+1));

//$ANOS_CONTRATOS = array(array("id"=>$ANO_MATRICULA-1, "nombre"=>$ANO_MATRICULA-1),
//                        array("id"=>$ANO_MATRICULA ,  "nombre"=>$ANO_MATRICULA));

$ANOS_CONTRATOS = array(array("id"=>$ANO_MATRICULA, "nombre"=>$ANO_MATRICULA),
                        array("id"=>$ANO_MATRICULA+1 ,  "nombre"=>$ANO_MATRICULA+1));
if ($ANO < $ANO_MATRICULA) { $ANOS_CONTRATOS = array_merge(array(array("id"=>$ANO, "nombre"=>$ANO)),$ANOS_CONTRATOS); }
                        
if ($_REQUEST['ano_contrato'] == "") { $_REQUEST['ano_contrato'] = $ANO_MATRICULA; }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<br>
<form name="formulario" action="principal.php" method="get"
      onSubmit="if (!enblanco2(<?php echo($requeridos); ?>) || !valida_rut(formulario.rf_rut)) { return false; }">
<input type="hidden" name="modulo"   value="<?php echo($modulo); ?>">
<input type="hidden" name="id_pap"   value="<?php echo($id_pap); ?>">
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
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Postulante</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($pap[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($pap[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($pap[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($pap[0]['direccion']); ?>, <?php echo($pap[0]['comuna']); ?>, <?php echo($pap[0]['region']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Carrera en que se Matricula</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Carrera:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='carrera_mat' onChange="submitform();">
		<option>-- Seleccione --</option>
        <?php echo(select($carreras,$_REQUEST['carrera_mat'])); ?>
      </select>
    </td>
  </tr>
  
<?php if ($carrera_mat > 0) { ?>  
  <tr>
    <td class='celdaNombreAttr'>Tipo Contrato:</td>
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
      Antecedentes del Responsable Financiero<br><sup>(Apoderado, Sostenedor o Aval)</sup> 
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'>
      <input type="text" size="15" name="rf_rut" value="<?php echo($_REQUEST['rf_rut']); ?>"
             onKeyUp="var valor=this.value;this.value=valor.toUpperCase();"
             <?php echo($sololectura); ?>>
      <?php if (!$chequeo_rut) { ?>
      <input type="submit" name="chequear" value="Chequear">
      <?php } else { ?>
    </td>
    <td class='celdaNombreAttr'><u>Parentezco:</u></td>
    <td class='celdaValorAttr'>
      <select name='rf_parentezco'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($parentezcos,$_REQUEST['rf_parentezco'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Apellidos:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='rf_apellidos' value="<?php echo($_REQUEST['rf_apellidos']); ?>"
             onBlur="var valor=this.value;this.value=valor.toUpperCase();">
    </td>
    <td class='celdaNombreAttr'><u>Nombres:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='rf_nombres' value="<?php echo($_REQUEST['rf_nombres']); ?>"
             onBlur="var valor=this.value;this.value=valor.toUpperCase();">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Estado Civil:</u></td>
    <td class='celdaValorAttr'>
      <select name='rf_est_civil'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($estados_civiles,$_REQUEST['rf_est_civil'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'>Profesión:</td>
    <td class='celdaValorAttr'><input type='text' size="20" name='rf_profesion' value="<?php echo($_REQUEST['rf_profesion']); ?>"></td>
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
      <input type='text' size="15" name="rf_pasaporte" value="<?php echo($pap[0]['rf_pasaporte']); ?>" disabled>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Dirección:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='rf_direccion' value="<?php echo($_REQUEST['rf_direccion']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();"><br>
      <sup>Av./Calle/Psje. # Villa/Población</sup>
    </td>
    <td class='celdaNombreAttr'><u>Comuna:</u></td>
    <td class='celdaValorAttr'>
      <select name='rf_comuna'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($comunas,$_REQUEST['rf_comuna'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Región:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='rf_region'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($regiones,$_REQUEST['rf_region'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Teléfono fijo:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size="10" name='rf_telefono' value="<?php echo($_REQUEST['rf_telefono']); ?>">
    </td>
    <td class='celdaNombreAttr'><u>Teléfono móvil:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size="10" name='rf_tel_movil' value="<?php echo($_REQUEST['rf_tel_movil']); ?>">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-mail:</td>
    <td class='celdaValorAttr' colspan="3">
      <input type='text' size="60" name='rf_email' value="<?php echo($_REQUEST['rf_email']); ?>" onKeyUp="var valor=this.value;this.value=valor.toLowerCase();">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      <b>Antecedentes Laborales del Responsable Financiero</b>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre Empresa:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='rf_nombre_empresa' value="<?php echo($_REQUEST['rf_nombre_empresa']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();"><br>
    </td>
    <td class='celdaNombreAttr'>Cargo:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="20" name='rf_cargo_empresa' value="<?php echo($_REQUEST['rf_cargo_empresa']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Antigüedad:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="2" name='rf_antiguedad_empresa' value="<?php echo($_REQUEST['rf_antiguedad_empresa']); ?>"> año(s)
    </td>
    <td class='celdaNombreAttr'>Sueldo líquido:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='rf_sueldo_liquido' 
              value="<?php echo($_REQUEST['rf_sueldo_liquido']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),'rf_sueldo_liquido')">
    </td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='rf_direccion_empresa' value="<?php echo($_REQUEST['rf_direccion_empresa']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();"><br>
      <sup>Av./Calle/Psje. # Villa/Población</sup>
    </td>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'>
      <select name='rf_comuna_empresa'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($comunas,$_REQUEST['rf_comuna_empresa'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='rf_region_empresa'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($regiones,$_REQUEST['rf_region_empresa'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Teléfono:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="10" name='rf_telefono_empresa' value="<?php echo($_REQUEST['rf_telefono_empresa']); ?>">
    </td>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr'>
      <input type='text' size="20" name='rf_email_empresa' value="<?php echo($_REQUEST['rf_email_empresa']); ?>">
    </td>
  </tr>
    <?php } ?>
<?php } ?>
</table>
</form>

<script>

function contrato_tipo(tipo) {
    
	if (tipo == "Anual") {
		formulario.ano_contrato.disabled=false;
		formulario.semestre_contrato.disabled=true;
	} 
	
	if (tipo == "Semestral") {
		formulario.ano_contrato.disabled=false;
		formulario.semestre_contrato.disabled=false;		
	}
	
	if (tipo == "Modular") {
		formulario.ano_contrato.disabled=false;
		formulario.semestre_contrato.disabled=true;
	}
	
}

contrato_tipo("<?php echo($_REQUEST['tipo_contrato']); ?>");

</script>

<!-- Fin: <?php echo($modulo); ?> -->


<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$aCampos = array('id_unidad','nombre','descripcion','alias','cod_procedencia','relevancia','pde','pde_nro_indicador',
                 'agrupador','subagrupador','mecanismo','mecanismo_cadena','valor_porcentaje',
                 'valor_decimales','periodicidad','period_anual_dia','period_anual_mes','period_anual_dia_ini','period_anual_mes_ini','period_semestral_1ro_dia',
                 'period_semestral_1ro_mes','period_semestral_2do_dia','period_semestral_2do_mes','period_mensual_dia','period_semanal_dia_sem',
                 'period_hora','orden','estandar','abierto','activo','estandar_tipo','totalizador','subitem'
                );

if ($_REQUEST['guardar'] == "Guardar") {
	$_REQUEST['subitem'] = ($_REQUEST['subitem'] == "") ? "f" : "t"; 
	$relevancia = array();
	foreach($_REQUEST['relevancia'] AS $relev => $valor) { if ($valor=="on") { $relevancia[] = $relev; } }
	$_REQUEST['relevancia'] = "{".implode(",",$relevancia)."}";
	$SQL_ins = "INSERT INTO gestion.indicadores_categorias ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQL_ins) > 0) {
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

$UNIDADES         = consulta_sql("SELECT id,'('||alias||') '||nombre AS nombre FROM gestion.unidades $cond_unidades ORDER BY nombre");
$AGRUPADORES      = consulta_sql("SELECT id,nombre FROM vista_ind_cat_agrupador");
$SUBAGRUPADORES   = consulta_sql("SELECT id,nombre FROM vista_ind_cat_subagrupador");
$MECANISMOS       = consulta_sql("SELECT id,nombre FROM vista_ind_cat_mecanismo");
$PERIODICIDADES   = consulta_sql("SELECT id,nombre FROM vista_ind_cat_periodicidad");
$PROCEDENCIAS_int = consulta_sql("SELECT codigo AS id,'('||codigo||') '||nombre AS nombre FROM gestion.indicadores_procedencia WHERE interno ORDER BY nombre");
$PROCEDENCIAS_ext = consulta_sql("SELECT codigo AS id,'('||codigo||') '||nombre AS nombre FROM gestion.indicadores_procedencia WHERE NOT interno ORDER BY nombre");
$RELEVANCIA       = consulta_sql("SELECT id,nombre FROM vista_indicadores_cat_relevancia");
$ABIERTOS = array(array('id'=>"t",'nombre'=>"🔓 Si"),array('id'=>"f",'nombre'=>"🔒 No"));

$dias_palabra[] = array('id'=>7,'nombre'=>"Domingo");

$meses_fn_1er_sem = $meses_fn_2do_sem = array();
for ($x=0;$x<=6;$x++) { $meses_fn_1er_sem[$x] = $meses_fn[$x]; }
for ($y=7;$y<12;$y++) { $meses_fn_2do_sem[$y-7] = $meses_fn[$y]; }

$ESTANDARES_TIPO = array(array('id' => "MIN", 'nombre' => "Mínimo"),
                         array('id' => "MAX", 'nombre' => "Máximo"));

$HTML_relevancia = "";
$relevancia = explode(",",trim($_REQUEST['relevancia'],"{}"));
for ($x=0;$x<count($RELEVANCIA);$x++) { 
	$checked = "";	
	if (in_array($RELEVANCIA[$x]['id'],$relevancia)) { $checked = "checked"; }
	$HTML_relevancia .= "<input type='checkbox' name='relevancia[{$RELEVANCIA[$x]['id']}]' id='relevancia_{$RELEVANCIA[$x]['id']}' $checked> "
                     .  "<label for='relevancia_{$RELEVANCIA[$x]['id']}'>{$RELEVANCIA[$x]['id']}</label> &nbsp;&nbsp;&nbsp;";
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_indicador_cat" value="<?php echo($id_indicador_cat); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name='guardar' value="Guardar">
  <input type="button" name='cancelar' value="Cancelar">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width='100%' style='margin-top: 5px'>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Indicador</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><input type="text" size='70' name="nombre" value="<?php echo($_REQUEST['nombre']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Descripción:</td>
    <td class='celdaValorAttr' colspan="3"><textarea name="descripcion" class="general" required><?php echo($_REQUEST['descripcion']); ?></textarea></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Alcance:</td>
    <td class='celdaValorAttr'>
      <select name="id_unidad" class='filtro'>
        <option value=''>* Institucional *</option>
        <?php echo(select($UNIDADES,$_REQUEST['id_unidad'])); ?>    
      </select>
    </td>
    <td class='celdaNombreAttr'>Orden:</td>
    <td class='celdaValorAttr'><input type="number" size='4' min="0" max="9999" name="orden" value="<?php echo($_REQUEST['orden']); ?>" class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Alias:</td>
    <td class='celdaValorAttr'><input type="text" size='20' name="alias" value="<?php echo($_REQUEST['alias']); ?>" <?php echo($readonly); ?> class='boton' required></td>
    <td class='celdaNombreAttr'>Fuente:</td>
    <td class='celdaValorAttr'>
      <select name="cod_procedencia" class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <optgroup label="Internos">
          <?php echo(select($PROCEDENCIAS_int,$_REQUEST['cod_procedencia'])); ?>
        </optgroup>
        <optgroup label="Externos">
          <?php echo(select($PROCEDENCIAS_ext,$_REQUEST['cod_procedencia'])); ?>
        </optgroup>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Relevancia:</td>
    <td class='celdaValorAttr'><?php echo($HTML_relevancia); ?></td>
    <td class='celdaNombreAttr'>PDE:</td>
    <td class='celdaValorAttr'>
      <select name="pde" class='filtro' required>
        <option value=''>-- Sel --</option>
        <?php echo(select($sino,$_REQUEST['pde'])); ?>    
      </select>
      Ind:
      <input type="text" size='2' name="pde_nro_indicador" value="<?php echo($_REQUEST['pde_nro_indicador']); ?>" <?php echo($readonly); ?> class='boton'>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Ámbito:</td>
    <td class='celdaValorAttr'>
      <select name="agrupador" class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($AGRUPADORES,$_REQUEST['agrupador'])); ?>    
      </select>
    </td>
    <td class='celdaNombreAttr'>Clase:</td>
    <td class='celdaValorAttr'>
      <select name="subagrupador" class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($SUBAGRUPADORES,$_REQUEST['subagrupador'])); ?>    
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Abierto:</td>
    <td class='celdaValorAttr'>
      <select name="abierto" class='filtro' required>
        <?php echo(select($ABIERTOS,$_REQUEST['abierto'])); ?>    
      </select>
    </td>
    <td class='celdaNombreAttr'>Activo:</td>
    <td class='celdaValorAttr'>
      <select name="activo" class='filtro' required>
        <?php echo(select($sino,$_REQUEST['activo'])); ?>    
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estándar:</td>
    <td class='celdaValorAttr'>
      <input type="number" size="5" step="any" min="-99999" max="99999" name="estandar" value="<?php echo($_REQUEST['estandar']); ?>" class="boton" onBlur="mostrar_estandar_tipo(this.value);">      
      <select name="estandar_tipo" id="estandar_tipo" class='filtro' style="display: none" disabled>
        <?php echo(select($ESTANDARES_TIPO,$_REQUEST['estandar_tipo'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Totalizador:</td>
    <td class='celdaValorAttr'>
      <select name="totalizador" class='filtro' required>
        <?php echo(select($sino,$_REQUEST['totalizador'])); ?>    
      </select>
      <input type="checkbox" name="subitem" id="subitem" value="t" <?php echo(($_REQUEST['subitem'] == "t") ? "checked" : ""); ?>>
      <label for="subitem">Subitem</label>
    </td>
  </tr>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Captura de Información</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Mecanismo:</td>
    <td class='celdaValorAttr'>
      <select name="mecanismo" class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($MECANISMOS,$_REQUEST['mecanismo'])); ?>    
      </select>
    </td>
    <td class='celdaNombreAttr'>Es porcentaje?:</td>
    <td class='celdaValorAttr'>
      <select name="valor_porcentaje" class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($sino,$_REQUEST['valor_porcentaje'])); ?>    
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cadena (SQL/URL):</td>
    <td class='celdaValorAttr' colspan="3"><textarea name="mecanismo_cadena" wrap="off" style='font-family: monospace; height: 150px;' class="general"><?php echo($_REQUEST['mecanismo_cadena']); ?></textarea></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Periodicidad:</td>
    <td class='celdaValorAttr'>
      <select name="periodicidad" id="periodicidad" onChange="mostrar_conf_periodicidad(this.value)" class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($PERIODICIDADES,$_REQUEST['periodicidad'])); ?>    
      </select><br><br>
      <span id="period_Anual" style="display: none">
		    entre el 
        <select name="period_anual_dia_ini" class='filtro'>
          <option value=''>Día</option>
          <?php echo(select($dias_fn,$_REQUEST['period_anual_dia_ini'])); ?>    
        </select> de
        <select name="period_anual_mes_ini" class='filtro'>
          <option value=''>Mes</option>
          <?php echo(select($meses_fn,$_REQUEST['period_anual_mes_ini'])); ?>    
        </select> y el 
        <select name="period_anual_dia" class='filtro'>
          <option value=''>Día</option>
          <?php echo(select($dias_fn,$_REQUEST['period_anual_dia'])); ?>    
        </select> de
        <select name="period_anual_mes" class='filtro'>
          <option value=''>Mes</option>
          <?php echo(select($meses_fn,$_REQUEST['period_anual_mes'])); ?>    
        </select> de cualquier año
      </span>
      <span id="period_Semestral" style="display: none">
        <table cellpadding='2' cellspacing='1' class="texto">
          <tr class='filaTituloTabla'><td align='center' class='tituloTabla'>Primero</td><td align='center' class='tituloTabla'>Segundo</td></tr>
          <tr>
            <td class='celdaValorAttr'>
              <select name="period_semestral_1ro_dia" class='filtro'>
                <option value=''>Día</option>
                <?php echo(select($dias_fn,$_REQUEST['period_semestral_1ro_dia'])); ?>    
              </select> del
              <select name="period_anual_1ro_mes" class='filtro'>
                <option value=''>Mes</option>
                <?php echo(select($meses_fn_1er_sem,$_REQUEST['period_anual_1ro_mes'])); ?>    
              </select> de cualquier año
            </td>
            <td class='celdaValorAttr'>
              <select name="period_semestral_2do_dia" class='filtro'>
                <option value=''>Día</option>
                <?php echo(select($dias_fn,$_REQUEST['period_semestral_2do_dia'])); ?>    
              </select> del
              <select name="period_anual_2do_mes" class='filtro'>
                <option value=''>Mes</option>
                <?php echo(select($meses_fn_2do_sem,$_REQUEST['period_anual_2do_mes'])); ?>    
              </select> de cualquier año
            </td>
          </tr>
        </table>
      </span>
      <span id="period_Mensual" style="display: none">
		el
        <select name="period_mensual_dia" class='filtro'>
          <option value=''>Día</option>
          <?php echo(select($dias_fn,$_REQUEST['period_mensual_dia'])); ?>    
        </select> de todos los meses de cualquier año<br>
        <small>
          NOTA: Sólo el día 30 es comodin para el 28 o 29 de febrero, según corresponda.<br> 
          Otros valores podrían impedir la captura de datos.
        </small>  
      </span>
      <span id="period_Semanal" style="display: none">
		los 
        <select name="period_semanal_dia" class='filtro'>
          <option value=''>Día</option>
          <?php echo(select($dias_palabra,$_REQUEST['period_semanal_dia'])); ?>    
        </select> de todas las semanas de cualquier año
      </span>
    </td>
    <td class='celdaNombreAttr'>Decimales:</td>
    <td class='celdaValorAttr'><input type="number" size='1' min="0" max="9" name="valor_decimales" value="<?php echo($_REQUEST['valor_decimales']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>

  <!-- <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Configuración de la Periodicidad</td></tr> -->
  <tr id="period_hora" style="display: none">
    <td class='celdaNombreAttr'>Hora de Captura:</td>
    <td class='celdaValorAttr' colspan="3"><input type="time" name="period_hora" value="<?php echo($_REQUEST['period_hora']); ?>" <?php echo($readonly); ?> class='boton'></td>
  </tr>

</table>
</form>

<script>

mostrar_conf_periodicidad("<?php echo($_REQUEST['periodicidad']); ?>");
mostrar_estandar_tipo("<?php echo($_REQUEST['estandar']); ?>");

function mostrar_conf_periodicidad(periodicidad) {
	var period = document.getElementById("periodicidad"),x,filaOculta;
	for (x=2; x < period.options.length; x++) {
		filaOculta = "period_"+period.options[x].text;
		document.getElementById(filaOculta).style.display='none';
	}

	document.getElementById('period_'+periodicidad).style.display='';
	document.getElementById('period_hora').style.display='';
}

function mostrar_estandar_tipo(estandar) {
	if (estandar === "") {
		document.getElementById("estandar_tipo").style.display='none';
		document.getElementById("estandar_tipo").disabled='disabled';
	} else {
		document.getElementById("estandar_tipo").style.display='';
		document.getElementById("estandar_tipo").disabled='';
	}
}

</script>
<!-- Fin: <?php echo($modulo); ?> -->

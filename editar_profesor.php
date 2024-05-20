<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

if (!is_numeric($_REQUEST['id_profesor'])) {
	echo(js("location.href='principal.php?modulo=gestion_profesores';"));
	exit;
}
$id_profesor = $_REQUEST['id_profesor'];
$mod_ant     = $_REQUEST['mod_ant'];

$aCampos = array('rut','nombre','apellido','sexo','fec_nac',
                 'direccion','comuna','region','telefono','tel_movil',
                 'email_personal','nacionalidad','categorizacion','grado_academico','grado_acad_fecha',
                 'grado_acad_nombre','grado_acad_universidad','doc_fotocopia_ci','doc_curriculum_vitae','doc_certif_grado_acad',
                 'id_escuela','grado_acad_pais','horas_planta','horas_plazo_fijo','horas_honorarios','estado_carpeta_docto',
                 'funcion','horas_planta_docencia','horas_plazo_fijo_docencia','horas_honorarios_docencia','id_cargo_normalizado_sies'
                );
$aRequeridos = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20);

if ($_REQUEST['guardar'] == "Guardar") {
	if($_REQUEST['doc_curriculum_vitae'] == "") {
		$aCampos[] = "arch_cv";
		$_REQUEST['arch_cv'] = "";
	}

	$SQLupdate_profe = "UPDATE usuarios SET ".arr2sqlupdate($_REQUEST,$aCampos). " WHERE id=$id_profesor";
	//echo($SQLupdate_profe);
	if (consulta_dml($SQLupdate_profe) > 0) {
		echo(msje_js("Se han guardado exitosamente los datos."));
	} else {
		echo(msje_js("ATENCIÓN: Ha ocurrido un error y NO se han guardado los datos."));
	}
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_profesor = "SELECT u.id,u.rut,u.nombre,u.apellido,u.sexo,u.fec_nac,
                        u.direccion,u.comuna,u.region,u.telefono,u.tel_movil,u.email,u.email_personal,u.nacionalidad,
                        u.nombre_usuario,u.grado_academico,u.grado_acad_fecha,u.grado_acad_universidad,
                        u.doc_fotocopia_ci,u.doc_curriculum_vitae,u.doc_certif_grado_acad,u.id_escuela,u.categorizacion,u.grado_acad_nombre,u.grado_acad_pais,
                        u.horas_planta,u.horas_plazo_fijo,u.horas_planta_docencia,u.horas_plazo_fijo_docencia,u.horas_honorarios,u.horas_honorarios_docencia,
                        u.funcion,u.id_cargo_normalizado_sies,u.estado_carpeta_docto
               FROM usuarios AS u
               WHERE u.id=$id_profesor AND tipo=3;";
$profesor = consulta_sql($SQL_profesor);
if (count($profesor) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_profesores';"));
	exit;
}
$_REQUEST = array_merge($_REQUEST,$profesor[0]);

$nacionalidades    = consulta_sql("SELECT localizacion AS id,nacionalidad AS nombre FROM pais ORDER BY nacionalidad");
$paises            = consulta_sql("SELECT localizacion AS id,nombre FROM pais ORDER BY nacionalidad");
$grados_academicos = consulta_sql("SELECT * FROM grado_acad ORDER BY id");
$comunas           = consulta_sql("SELECT id,nombre FROM comunas ORDER BY nombre");
$regiones          = consulta_sql("SELECT id,romano||' '||nombre AS nombre FROM regiones ORDER BY id");
$escuelas          = consulta_sql("SELECT id,nombre FROM escuelas ORDER BY nombre");
$funciones         = consulta_sql("SELECT * FROM vista_usuarios_funciones");
$cargos_normalizados = consulta_sql("SELECT * FROM docentes_sies_cargos_normalizados ORDER BY id");
$estados_carpetas  = consulta_sql("SELECT * FROM vista_estado_carpeta_docente");

$readonly = "disabled";
if ($_SESSION['tipo'] == 0 || $mod_ant == "crear_profesor") { $readonly = ""; }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_profesor" value="<?php echo($id_profesor); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name='guardar' value="Guardar">
  <input type="button" name='cancelar' value="Cancelar">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Profesor</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['id']); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'>
      <input type="text" size="12" name="rut" value="<?php echo($_REQUEST['rut']); ?>"
             onKeyUp="var valor=this.value;this.value=valor.toUpperCase();"
             onBlur="valida_rut(this);" <?php echo($readonly); ?> class='boton'>
      <?php if ($readonly == "disabled") { echo("<input type='hidden' name='rut' value='{$_REQUEST['rut']}'>"); } ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombres:</td>
    <td class='celdaValorAttr'>
      <input type="text" name="nombre" value="<?php echo($_REQUEST['nombre']); ?>"
             onBlur="var valor=this.value;this.value=valor.toUpperCase();" <?php echo($readonly); ?> class='boton'>
      <?php if ($readonly == "disabled") { echo("<input type='hidden' name='nombre' value='{$_REQUEST['nombre']}'>"); } ?>
    </td>
    <td class='celdaNombreAttr'>Apellidos:</td>
    <td class='celdaValorAttr'>
      <input type="text" name="apellido" value="<?php echo($_REQUEST['apellido']); ?>"
             onBlur="var valor=this.value;this.value=valor.toUpperCase();" <?php echo($readonly); ?> class='boton'>
      <?php if ($readonly == "disabled") { echo("<input type='hidden' name='apellido' value='{$_REQUEST['apellido']}'>"); } ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Género:</td>
    <td class='celdaValorAttr'>
      <select name="sexo" class='filtro'>
        <?php echo(select($generos,$_REQUEST['sexo'])); ?>    
      </select>
    </td>
    <td class='celdaNombreAttr' nowrap>Fecha de nacimiento:</td>
    <td class='celdaValorAttr'>
      <input type="date"  name="fec_nac" value="<?php echo($_REQUEST['fec_nac']); ?>" class='boton'>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nacionalidad:</td>
    <td class='celdaValorAttr' colspan='3'>
      <select name="nacionalidad" class='filtro'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($nacionalidades,$_REQUEST['nacionalidad'])); ?>    
      </select>
    </td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Académicos del Profesor</td>
  </tr>

<?php if ($mod_ant <> "crear_profesor" || $_SESSION['tipo'] == 0) { ?>
  <tr>
    <td class='celdaNombreAttr'>Categorización Docente:</td>
    <td class='celdaValorAttr' colspan='3'>
      <select name="categorizacion" <?php echo($readonly); ?> class='filtro'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($CATEG_DOCENTE,$_REQUEST['categorizacion'])); ?>    
      </select>
      <?php if ($readonly == "disabled") { echo("<input type='hidden' name='categorizacion' value='{$_REQUEST['categorizacion']}'>"); } ?>
    </td>
  </tr>
<?php } ?>

  <tr>
    <td class='celdaNombreAttr'>Grado Académico:</td>
    <td class='celdaValorAttr'>
      <select name="grado_academico" <?php echo($readonly); ?> class='filtro'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($grados_academicos,$_REQUEST['grado_academico'])); ?>    
      </select>
      <?php if ($readonly == "disabled") { echo("<input type='hidden' name='grado_academico' value='{$_REQUEST['grado_academico']}'>"); } ?>
    </td>
    <td class='celdaNombreAttr' nowrap>Fecha de obtención:</td>
    <td class='celdaValorAttr'>
      <input type="date" size="10" name="grado_acad_fecha" value="<?php echo($_REQUEST['grado_acad_fecha']); ?>" class='boton'>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre del Grado/Título:</td>
    <td class='celdaValorAttr' colspan='3'>
      <input type="text" size="60" name="grado_acad_nombre" value="<?php echo($_REQUEST['grado_acad_nombre']); ?>"
             onKeyUp="var valor=this.value;this.value=valor.toUpperCase();" class='boton'>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Institución otorgante:</td>
    <td class='celdaValorAttr' colspan='3'>
      <input type="text" size="60" name="grado_acad_universidad" value="<?php echo($_REQUEST['grado_acad_universidad']); ?>"
             onKeyUp="var valor=this.value;this.value=valor.toUpperCase();" class='boton'>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>País de la Institución:</td>
    <td class='celdaValorAttr' colspan='3'>
      <select name="grado_acad_pais" class='filtro'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($paises,$_REQUEST['grado_acad_pais'])); ?>    
      </select>
    </td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Documentación presentada del Profesor</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Curriculum Vitae:</td>
    <td class='celdaValorAttr' colspan="2"><input type="checkbox" name="doc_curriculum_vitae" value="t" <?php if ($_REQUEST['doc_curriculum_vitae'] == 't') { echo("checked"); } ?> onClick="arch_cv(this.value);"></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Certificado de Grado Académico:</td>
    <td class='celdaValorAttr' colspan="2"><input type="checkbox" name="doc_certif_grado_acad" value="t"  <?php if ($_REQUEST['doc_certif_grado_acad'] == 't') { echo("checked"); } ?>></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Estado Carpeta Documentos:</td>
    <td class='celdaValorAttr' colspan="2">
      <select name="estado_carpeta_docto" class='filtro'>
          <?php echo(select($estados_carpetas,$_REQUEST['estado_carpeta_docto'])); ?>    
      </select>
    </td>
  </tr>
<!--
  <tr>
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Fotocopia C.I.:</td>
    <td class='celdaValorAttr' colspan="2"><input type="checkbox" name="doc_fotocopia_ci"  value="t" <?php if ($_REQUEST['doc_fotocopia_ci'] == 't') { echo("checked"); } ?>></td>
  </tr>
-->
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Contacto del Profesor</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan='3'>
      <input type="text" size="50" name="direccion" value="<?php echo($_REQUEST['direccion']); ?>"
             onKeyUp="var valor=this.value;this.value=valor.toUpperCase();" class='boton'>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'>
      <select name="comuna" class='filtro'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($comunas,$_REQUEST['comuna'])); ?>    
      </select>
    </td>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr' nowrap>
      <select name="region"  class='filtro'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($regiones,$_REQUEST['region'])); ?>    
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Telefóno fijo:</td>
    <td class='celdaValorAttr'><input type="text" size="15" name="telefono" value="<?php echo($_REQUEST['telefono']); ?>" class='boton'></td>
    <td class='celdaNombreAttr'>Telefóno móvil:</td>
    <td class='celdaValorAttr'><input type="text" size="15" name="tel_movil" value="<?php echo($_REQUEST['tel_movil']); ?>" class='boton'></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail Personal:</td>
    <td class='celdaValorAttr' colspan='3'>
      <input type="email" size="30" name="email_personal" value="<?php echo($_REQUEST['email_personal']); ?>"
             onKeyUp="var valor=this.value;this.value=valor.toLowerCase();" class='boton'>
    </td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Datos internos del Profesor</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Escuela:</td>
    <td class='celdaValorAttr'>
      <select name="id_escuela" class='filtro'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($escuelas,$_REQUEST['id_escuela'])); ?>    
      </select>
    </td>
    <td class='celdaNombreAttr'>Función:</td>
    <td class='celdaValorAttr'>
      <select name="funcion" class='filtro'>
        <?php echo(select($funciones,$_REQUEST['funcion'])); ?>    
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cargo Normalizado (SIES):</td>
    <td class='celdaValorAttr' colspan='3'>
      <select name="id_cargo_normalizado_sies" class='filtro' style="max-width: none">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($cargos_normalizados,$_REQUEST['id_cargo_normalizado_sies'])); ?>    
      </select>
    </td>
  </tr>
<?php if ($_SESSION['tipo'] == 0) { ?>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="2" style="text-align: center; "></td>
    <td class='tituloTabla' style="text-align: center; ">Horas Contratadas</td>
    <td class='tituloTabla' style="text-align: center; " nowrap>Horas sólo Docencia<br><small>(incluidas en las Horas Contratadas)</small></td>
  </tr>
  <tr>
    <td class='tituloTabla' colspan="2" style="text-align: right">Planta:</td>
    <td class='celdaValorAttr' align='center'><input type="text" size="2" name="horas_planta" value="<?php echo($_REQUEST['horas_planta']); ?>" class='boton' style='text-align: center'> semanales</td>
    <td class='celdaValorAttr' align='center'><input type="text" size="2" name="horas_planta_docencia" value="<?php echo($_REQUEST['horas_planta_docencia']); ?>" class='boton' style='text-align: center'> semanales</td>
  </tr>
  <tr>
    <td class='tituloTabla' colspan="2" style="text-align: right">Plazo Fijo:</td>
    <td class='celdaValorAttr' align='center'><input type="text" size="2" name="horas_plazo_fijo" value="<?php echo($_REQUEST['horas_plazo_fijo']); ?>" class='boton' style='text-align: center'> semanales</td>
    <td class='celdaValorAttr' align='center'><input type="text" size="2" name="horas_plazo_fijo_docencia" value="<?php echo($_REQUEST['horas_plazo_fijo_docencia']); ?>" class='boton' style='text-align: center'> semanales</td>
  </tr>
  <tr>
    <td class='tituloTabla' colspan="2" style="text-align: right">Honorarios:</td>
    <td class='celdaValorAttr' align='center'><input type="text" size="2" name="horas_honorarios" value="<?php echo($_REQUEST['horas_honorarios']); ?>" class='boton' style='text-align: center'> semanales</td>
    <td class='celdaValorAttr' align='center'><input type="text" size="2" name="horas_honorarios_docencia" value="<?php echo($_REQUEST['horas_honorarios_docencia']); ?>" class='boton' style='text-align: center'> semanales</td>
  </tr>
<?php } ?>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<script>
function arch_cv(valor) {
	if (valor=='t') {
		alert('Considere que si actualmente hay un documento cargado para este profesor y desmarca esta opción, dicho documento será eliminado');
	}
}
</script>

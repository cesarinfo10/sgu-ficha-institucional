<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}
$rut = $_REQUEST['rut'];

$mod_ant = $_SERVER['HTTP_REFERER'];
$enlbase = $_SERVER['SCRIPT_NAME'] . "?modulo";

if ($mod_ant == "") { $mod_ant = "$enlbase=gestion_alumnos"; }

$fichas = array(0 => array("nombre" => "Datos<br>Personales",                     "enlace" => "alumno_ficha_datos_personales"),
                1 => array("nombre" => "Antecedentes<br>Escolares/Universitarios","enlace" => "alumno_ficha_datos_esc_ies"),
                2 => array("nombre" => "Control<br>Interno",                      "enlace" => "alumno_ficha_control_interno")
               );
$ficha = $_REQUEST['ficha'];
if ($ficha == "") { $ficha = $fichas[0]['enlace']; }

$HTML_botones_ficha = "";
for($x=0;$x<count($fichas);$x++) {
	$boton_ficha = $fichas[$x]['nombre'];
	$estilo_boton = "background: #DEF1FF";
	if ($fichas[$x]['enlace'] <> $ficha) {
		$enlace_ficha = "$enlbase=$modulo&id_alumno=$id_alumno&ficha=".$fichas[$x]['enlace'];
		$boton_ficha  = "<a class='enlaces' href='$enlace_ficha'>$boton_ficha</a>";
		$estilo_boton = "";
	}
	$HTML_botones_ficha .= "<td width='33%' class='tituloTabla' style='$estilo_boton'>$boton_ficha</td>";
}

$vista  = $_REQUEST['vista'];
$VISTAS = array(0 => array("id" => "avance_cronologico","nombre" => "Rendimiento académico cronológico"),
                1 => array("id" => "avance_malla",      "nombre" => "Rendimiento académico según avance en malla"),
                2 => array("id" => "homologaciones",    "nombre" => "Homologaciones"),
                3 => array("id" => "convalidaciones",   "nombre" => "Convalidaciones"),
                4 => array("id" => "examenes_con_rel",  "nombre" => "Examenes de Conocimientos relevantes"),
                5 => array("id" => "anotaciones",       "nombre" => "Anotaciones")
               );

if ($vista == "") { $vista = $VISTAS[0]['id']; } 
switch ($vista) {
	case "avance_cronologico":
		$HTML_vista_rend_acad = avance_cronologico();
		break;
	case "avance_malla":
		$HTML_vista_rend_acad = avance_malla();
		break;
	case "homologaciones":
		$HTML_vista_rend_acad = vista_homologaciones();
		break;
	case "convalidaciones":
		$HTML_vista_rend_acad = vista_convalidaciones();
		break;
	case "examenes_con_rel":
		$HTML_vista_rend_acad = vista_examenes_con_rel();
		break;
	case "anotaciones":
		$HTML_vista_rend_acad = vista_anotaciones();
		break;
}			

$GESTION_CARGA = array(array("id"     => "$enlbase=tomar_ramos&id_alumno=$id_alumno",
                             "nombre" => "Tomar Ramos"),
                       array("id"     => "$enlbase=alumno_eliminar_cursos&id_alumno=$id_alumno",
                             "nombre" => "Eliminar cursos"),
                       array("id"     => "$enlbase=alumno_ingresar_calificaciones&id_alumno=$id_alumno",
                             "nombre" => "Ingresar calificaciones"),
                       array("id"     => "$enlbase=alumno_mod_calificaciones&id_alumno=$id_alumno",
                             "nombre" => "Modificar calificaciones"),
                       array("id"     => "$enlbase=crear_convalidacion&id_alumno=$id_alumno",
                             "nombre" => "Convalidar"),
                       array("id"     => "$enlbase=crear_homologacion&id_alumno=$id_alumno",
                             "nombre" => "Homologar"),
                       array("id"     => "$enlbase=alumno_homologar&id_alumno=$id_alumno",
                             "nombre" => "Registrar Homologación y obterner Acta"),
                       array("id"     => "$enlbase=crear_examen_conrel&id_alumno=$id_alumno",
                             "nombre" => "Registrar Examen de Conocimientos Relevantes"),
                       array("id"     => "$enlbase=editar_alumno_malla&id_alumno=$id_alumno",
                             "nombre" => "Cambio de Carrera/Programa/Malla"),
                       array("id"     => "$enlbase=editar_alumno_egreso&id_alumno=$id_alumno",
                             "nombre" => "Antecedentes de Egreso/Titulación/Graduación")
                      );

$otros_cobros_pend = count(consulta_sql("SELECT id FROM finanzas.cobros WHERE id_alumno=$id_alumno AND NOT pagado"));
if ($otros_cobros_pend > 0) { $otros_cobros_pend = "<sup style='color: #ff0000'>($otros_cobros_pend)</sup>"; } 
else { $otros_cobros_pend = ""; }

$matriculado = count(consulta_sql("SELECT 1 FROM matriculas WHERE ano=$ANO AND semestre=$SEMESTRE AND id_alumno=$id_alumno"));

$id_foto = "";
$SQL_foto = "SELECT dd.id FROM doctos_digitalizados dd LEFT JOIN doctos_digital_tipos ddt ON dd.id_tipo=ddt.id WHERE rut='$rut' AND ddt.alias='fotos' AND NOT eliminado";
$foto = consulta_sql($SQL_foto);
if (count($foto) > 0) { $id_foto = $foto[0]['id']; }

$al_indocumentado = consulta_sql("SELECT id_alumno,regexp_replace(doc_adeudado,'\n',' ') AS doc_adeudado FROM alumnos_indocumentados WHERE id_alumno=$id_alumno");
if (count($al_indocumentado) > 0) {
	$msje = "Actualmente este alumno debe documentos. "
		  . "Estos son {$al_indocumentado[0]['doc_adeudado']} (según información de Registro Académico)\\n\\n";
	echo(msje_js($msje));
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<table cellpadding="2" cellspacing="1" border="0" style='margin-top: 5px'>
  <tr>
    <td class='celdafiltro' nowrap>
      Acciones:<br>
<?php
	if ($_SESSION['tipo'] <=2 || $_SESSION['tipo'] == 6) {
		//echo("<a href='$enlbase=editar_alumno&id_alumno=$id_alumno' class='boton'>Editar</a> ");
		echo("<a href='alumno_imprimir_ficha_datos_personales.php?id_alumno=$id_alumno&vista=$vista' class='boton'>Imprimir</a> ");
	}
	echo("<a href='$mod_ant' class='boton'>Volver</a> ");	
?>      
    </td>
    <td class='celdafiltro'>
      Gestión de Carga Académica:<br>
      <select name="gestion_carga" onChange="window.location=this.value;" class='filtro'>
        <option value="">-- Selecccione --</option>
        <?php echo(select($GESTION_CARGA,"")); ?>
      </select>
    </td>
    <td class='celdafiltro' nowrap>
      Gestión Académica:<br>
      <?php echo("<a id='sgu_fancybox' href='$enlbase_sm=doctos_digitalizados&rut=$rut' class='boton'>Doctos Digitalizados</a>"); ?>

<?php
	if (perm_ejec_modulo($_SESSION['id_usuario'],'est_academico')) {
		echo("<a id='sgu_fancybox' href='$enlbase_sm=est_academico&id_alumno=$id_alumno' class='boton'>Estado Académico</a> ");
	}
	if ($_SESSION['tipo'] == 0 || $_SESSION['tipo'] == 6) {
		echo("<a href='alumno_conc_notas.php?id_alumno=$id_alumno' target='_blank' class='boton'>Conc. de Notas</a> ");
		//echo("<a href='alumno_conc_notas_parcial.php?id_alumno=$id_alumno' target='_blank' class='boton'>C. de Notas Parcial</a> ");
	}
?>
    </td>
    <td class='celdafiltro' nowrap>
      Certificaciones:<br>
<?php
	if ($_SESSION['tipo'] == 0 || perm_ejec_modulo($_SESSION['id_usuario'],'alumno_emitir_certif')) {
		echo("<a href='$enlbase_sm=alumno_emitir_certif&id_alumno=$id_alumno' class='boton' id='sgu_fancybox'>Emitir</a> ");
	}
	echo("<a href='$enlbase=gestion_certificados&texto_buscar=$rut&buscar=Buscar' class='boton'>Consulta</a> "); 
?>
    </td>
  </tr>
</table>
<table cellpadding="2" cellspacing="1" border="0">
  <tr>
    <td class='celdafiltro' nowrap>
      Gestión Finanzas:<br>
<?php
	if ($_SESSION['tipo'] == 0 || $_SESSION['tipo'] == 5 || $_SESSION['tipo'] == 4 || $_SESSION['tipo'] == 2) {
		echo("<a href='$enlbase=alumno_matricular&id_alumno=$id_alumno' class='boton'>Matricular</a> ");
		//echo("<a id='sgu_fancybox' href='$enlbase_sm=alumno_arancel&id_alumno=$id_alumno' class='boton'>Arancel</a> ");
	}
	if ($_SESSION['tipo'] == 0 || $_SESSION['tipo'] == 4 || $_SESSION['tipo'] == 5 || $_SESSION['tipo'] == 1 || $_SESSION['tipo'] == 6) {    
		echo("<a href='$enlbase=alumnos_doctos_matricula&id_alumno=$id_alumno' class='boton'>Doctos de Matrícula</a> ");
	}
	if ($_SESSION['tipo'] == 0 || $_SESSION['tipo'] == 5) {
		echo("<a id='sgu_fancybox' href='$enlbase_sm=est_financiero&id_alumno=$id_alumno' class='boton'>Estado Financiero</a> ");
//				echo("<a href='$enlbase=est_matricula&id_alumno=$id_alumno' class='boton'>Matriculas</a> ");
	}
	echo("<a id='sgu_fancybox_big' href='$enlbase_sm=alumno_otros_cobros&id_alumno=$id_alumno' class='boton'>Otros Cobros $otros_cobros_pend</a> ");

?>
    </td>
    <td class='celdafiltro' nowrap>
      Atenciones:<br>
      <?php echo("<a id='sgu_fancybox' href='$enlbase_sm=remat_atenciones&id_alumno=$id_alumno' class='boton'>Rematrícula</a> "); ?>
      <?php echo("<a id='sgu_fancybox' href='$enlbase_sm=registro_atenciones_new&id_alumno=$id_alumno' class='boton'>Pro-retención</a> "); ?>
    </td>

   
  </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" class="tabla" width="auto" style='margin-top: 5px'>
  <tr style="padding: 5px">
    <?php echo($HTML_botones_ficha); ?>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="<?php echo(count($fichas)); ?>" width='100%'>
      <table cellspacing="1" cellpadding="2" class="tabla" width="100%">
	    <tr>
		  <td>
		    <?php include($ficha.".php"); ?>
          </td>
          <td valign="top">
            <?php if ($id_foto <> "") { ?>
             <a align="right" href="doctos_digitalizados_ver.php?id=<?php echo($id_foto); ?>">
               <img align="right" src="doctos_digitalizados_ver.php?id=<?php echo($id_foto); ?>" width="200">
             </a>
            <?php } ?>
          </td>
        </tr>
        <tr>
		  <td colspan="2" class="textotabla" align="center">
            <br>
            <?php if ($_SESSION['tipo'] < 3 || $_SESSION['tipo'] == 6 || $_SESSION['tipo'] == 5) { ?>
            <form name="formulario" action="principal.php">
            <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
            <input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
            Vista: 
            <select name="vista" onChange="submitform();">
              <option value="">-- Seleccione --</option>
              <?php echo(select($VISTAS,$vista)); ?>
            </select>
            </form>

            <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
              <?php echo($HTML_vista_rend_acad); ?>
            </table>
      
            <?php } ?>

		  </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<div class="texto">
  <b>N/D:</b> No Disponible. Las calificaciones con esta leyenda no están disponibles debido a incumplimientos
              financieros del alumno con la Universidad. El alumno debe regularizar su situación, para tener
              acceso nuevamente a sus calificaciones obtenidas.
</div>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 800,
		'maxHeight'		: 9999,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_big").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 1000,
		'maxHeight'		: 9999,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});
</script>

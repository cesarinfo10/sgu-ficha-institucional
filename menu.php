<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

$id_usuario = $_SESSION['id_usuario'];

$SQL_menu = "SELECT id,nombre,descripcion,ejecutable FROM permisos_apps as p_apps 
             INNER JOIN aplicaciones as apps ON apps.id=p_apps.id_aplicacion
             WHERE id_usuario=$id_usuario AND menu AND activa
             ORDER BY id;";
$menu = consulta_sql($SQL_menu);

?>

<!-- inicio: menu -->
<div id="menu">
  <ul>
<?php
	if (count($menu) > 0) {
		echo("    <li><a target='_blank' href='https://www.umcervantes.cl/calendario-academico/' title='Pinche aquí para ver el Calendario Académico'><blink>Calendario Académico</blink></a></li>\n");
		echo("    <ul><li><a href='archivos/Manual_de_Docencia_24-09-2019.pdf' title='Pinche aquí para ver la Reseña del Modelo Educativo'>Manual De Docencia</a></li></ul>\n");
		echo("    <ul><li><a href='archivos/codigo_etica_umc_II_jun-22.pdf' title='Pinche aquí para ver el Código de Ética'>Código de Ética v2</a></li></ul>\n");
		//echo("    <ul><li><a href='archivos/metodologias_inclusivas.pdf' title='Pinche aquí para ver Metodologías Inclusivas'><blink>NUEVO: Metodologías Inclusivas</blink></a></li></ul>\n");
		echo("    <ul><li><a href='archivos/Manual_de_Acompañante_24-09-2019.pdf' title='Pinche aquí para ver el Manual de Acompañamiento Académico'>Manual de Acompañamiento Académico</a></li></ul>\n");
		echo("    <li><a href='http://sic.umcervantes.cl' target='_blank' title='Pinche aquí para ver entrar al Sistema de Información Centralizada'>SIC</a></li>\n");
		if ($_SESSION["tipo"] == 3) {
			echo("    <li><a href='archivos/manual_para_la_docencia.pdf' target='_blank' title='Descargar Manual para la Docencia'>Manual para la Docencia</a></li>\n");
		}
		for($x=0;$x<count($menu);$x++) {
			$nombre_app  = $menu[$x]['nombre'];
			$ejec_app    = $menu[$x]['ejecutable'];
			$descrip_app = $menu[$x]['descripcion'];
			$enlace = "principal.php?modulo=$ejec_app";
			if (($menu[$x]['id'] % 100) <> 0) {
				echo("    <ul><li><a href='$enlace' title='$descrip_app'>$nombre_app</a></li></ul>\n");
			} else {
				echo("    <li><a href='$enlace' title='$descrip_app'>$nombre_app</a></li>\n");
			}
		}
		if ($_SESSION["tipo"] <> 3) {
			echo("    <li><a target='_blank' href='https://correo.umcervantes.cl/roundcube/?_task=settings&_action=plugin.password' title='Cambiar contraseña'>Cambio de Contraseña</a></li>\n");
		}
		echo("    <li><a target='_blank' href='https://ucervantes.mirexmas.com/' title='Portal REX+'>Portal REX+</a></li>\n");
		echo("    <ul><li><a target='_blank' href='https://ucervantes.mirexmas.com/seguridad/account/reset_password' title='Portal REX+'>REX+: Activar acceso</a></li></ul>\n");
		
	} else {
		echo("<li>Usted no tiene un perfil de usuario creado.
		          Pongase en contacto con su unidad Acad&eacute;mica correspondiente</li>");
	};
?>
  </ul>
</div>
<br>
<table bgcolor='#ffffff' class="tabla" cellspacing="1" cellpadding="2" align='center'>
  <tr class="filaTituloTabla">
    <th class="tituloTabla" align="center">Nombre del<br>M&oacute;dulo</th>
    <th class="tituloTabla" align="center">Intervalo</th>
  </tr>
	<?php
		$horarios = consulta_sql("SELECT id,to_char(hora_inicio,'HH24:MI')||' - '||to_char(hora_fin,'HH24:MI') as nombre FROM horarios ORDER BY id;");
		$HTML = "";
		for ($x=0;$x<count($horarios);$x++) {
			extract($horarios[$x]);			
			$HTML .= "<tr class='filaTabla' valign='top'><td class='textoTabla' align='center'>$id</td><td class='textoTabla' align='left'>$nombre</td></tr>";
		}
		echo($HTML);
	?>
</table>
<!-- fin: menu -->

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fbox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 1000,
		'height'			: 600,
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fbox_small").fancybox({
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

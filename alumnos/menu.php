<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

?>

<!-- inicio: menu -->
<div id="menu">
  <ul>
	<li><a target='_blank' href='https://www.umcervantes.cl/calendario-academico/' title='Pinche aquí para ver el Calendario Académico'><blink>Calendario Académico</blink></a></li>
    <!-- <li><a href='archivos/modelo_educativo_umc.pdf' title='Pinche aquí para ver la Reseña del Modelo Educativo'>Reseña del Modelo Educativo</a></li> -->
	<li><a href='/sgu/archivos/codigo_etica_umc.pdf' title='Pinche aquí para ver el Código de Ética'>Código de Ética</a></li>
    <li><a href='/sgu/archivos/Manual_de_Acompañante_24-09-2019.pdf' title='Pinche aquí para ver el Manual de Acompañamiento Académico'>Manual de Acompañamiento Académico</a></li>

    <li><a href="principal.php?modulo=portada">Portada</a></li>

    <li><a href="principal.php?modulo=mi_registro_academico">Mi Registro Académico</a></li>
      <ul><li><a href='principal.php?modulo=mis_cursos' title='Ver cursos actuales'>Mis Cursos</a></li></ul>
    <li><a href="principal.php?modulo=contratos_estudiante">Mis Contratos<br>(pagos y deuda)</a></li>

    <?php if ((time() >= $FEC_INI_TOMA_RAMOS && time() <= $FEC_FIN_TOMA_RAMOS) && ($_SESSION['semestre_cohorte']."-".$_SESSION['cohorte'] <> $SEMESTRE."-".$ANO)) { ?><li><a href="principal.php?modulo=inscripcion_asignaturas">Toma de Ramos</a></li><?php } ?>

    <li><a target='_blank' href='https://correo.al.umcervantes.cl/roundcube/?_task=settings&_action=plugin.password' title='Cambiar contraseña'>Cambio de Contraseña</a></li>
  </ul>
</div>
<br><br>
<div class="texto" align="justify">
  <center><b>Tabla de Horarios</b></center>
<table border="1" class="tabla" cellspacing="0" cellpadding="2">
  <tr class="filaTituloTabla">
    <th class="tituloTabla" align="center">Nombre del<br>M&oacute;dulo</th>
    <th class="tituloTabla" align="center">Intervalo</th>
  </tr>
	<?php
		$horarios = consulta_sql("SELECT id,to_char(hora_inicio,'HH24:MI')||' - '||to_char(hora_fin,'HH24:MI') as nombre FROM horarios;");
		$HTML = "";
		for ($x=0;$x<count($horarios);$x++) {
			extract($horarios[$x]);			
			$HTML .= "<tr class='filaTabla' valign='top'><td class='textoTabla' align='center'>$id</td><td class='textoTabla' align='left'>$nombre</td></tr>";
		}
		echo($HTML);
	?>
</table>
</div>
<!-- fin: menu -->


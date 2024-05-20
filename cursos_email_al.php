<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

//include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
$gsuite   = $_REQUEST['gsuite'];

if (!is_numeric($id_curso)) {
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}

$SQL_curso = "SELECT vc.id AS nro_acta,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,
                     vc.semestre||'-'||vc.ano AS periodo,vc.profesor,vc.carrera,
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,c.cerrado
              FROM vista_cursos AS vc
              LEFT JOIN cursos AS c ON c.id=vc.id 
              WHERE vc.id=$id_curso;";
$curso = consulta_sql($SQL_curso);
           
if (count($curso) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}

extract($curso[0]);

$SQL_cursos = "SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion)";
$SQL_alumnos = "SELECT va.nombre,coalesce(a.email,va.email) as email,a.nombre_usuario||'@alumni.umc.cl' AS email_gsuite
                FROM vista_alumnos va
                LEFT JOIN alumnos AS a USING (id)
                WHERE a.id IN (SELECT id_alumno
                             FROM cargas_academicas
                             WHERE id_curso IN ($SQL_cursos))
                  AND a.email IS NOT NULL";
$alumnos = consulta_sql($SQL_alumnos);
$cant_al = count($alumnos);
if ($cant_al>0) {
	$HTML_email_alumnos = "<tr><td class='celdaValorAttr'>";
	$emails_alumnos = "";
	for ($x=0; $x<count($alumnos); $x++) {
		$HTML_email_alumnos .= $alumnos[$x]['email'];
		if ($gsuite=="Si") { $emails_alumnos .= $alumnos[$x]['email_gsuite'].", "; }
		else { $emails_alumnos .= $alumnos[$x]['email'].", "; }
		if ($x+1<count($alumnos)) {$HTML_email_alumnos .= ",<br>";} 
	}
	$HTML_email_alumnos .= "</td></tr>";
	$emails_alumnos = substr($emails_alumnos,0,-2);
}	
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  Emails de Estudiantes Inscritos en <?php echo($asignatura); ?>  
</div>
<br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class='celdaNombreAttr'>Nº Acta:</td>
    <td class='celdaValorAttr'><?php echo($nro_acta); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($periodo); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura); ?> <?php echo($prog_asig); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Profesor:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor); ?> <?php echo($ficha_prof); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Horario:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($horario); ?></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Direcciones electrónicas</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>
      <textarea name="emails_alumnos" id="emails_alumnos" readonly><?php echo($emails_alumnos); ?></textarea><br>
	  <button class="boton" onclick="myFunction()">Copiar al portapapeles</button>
    </td>
  </tr>
  <?php //echo($HTML_email_alumnos); ?>
</table><br>
<div class='texto'>
  Seleccione con el mouse las direcciones electrónicas que se muestran para luego copiarlas 
  (presionando el botón derecho del mouse sobre el texto seleccionado, y opción "Copiar").
  A continuación puede pegarlas directamente en el campo "Para:" en un mensaje nuevo de su sistema
  de correo.
</div>
<!-- Fin: <?php echo($modulo); ?> -->
<script>
function myFunction() {
  var copyText = document.getElementById("emails_alumnos");
  copyText.select();
  copyText.setSelectionRange(0, 99999)
  document.execCommand("copy");
  alert("Se han copiado los emails. Ahora puede pegar este contenido donde necesite.");
}
</script>

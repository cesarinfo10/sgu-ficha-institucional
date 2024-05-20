<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if (!is_numeric($_REQUEST['id_prog_curso'])) {
	echo(js("window.location='$enlbase=';"));
	exit;
} else {
	$id_prog_curso = $_REQUEST['id_prog_curso'];
}

$prog_curso = consulta_sql("SELECT * FROM vista_prog_cursos WHERE id=$id_prog_curso");
if (count($prog_curso) == 0) {
	echo(js("window.location='$enlbase=';"));
	exit;
}
extract($prog_curso[0],EXTR_PREFIX_ALL,"pc");

$SQL_pc_det = "SELECT pcd.id,vpa.cod_asignatura,vpa.asignatura,seccion,vp.nombre AS profesor,
                      horario1 AS horario,dia1 AS dia                                          
               FROM prog_cursos_detalle AS pcd
               LEFT JOIN vista_profesores AS vp ON vp.id=pcd.id_profesor
               LEFT JOIN vista_prog_asig AS vpa ON vpa.id=pcd.id_prog_asig
               WHERE id_prog_curso=$id_prog_curso AND tipo<>'m'
               UNION
               SELECT pcd.id,vpa.cod_asignatura,vpa.asignatura,seccion,vp.nombre AS profesor,
                      horario2 AS horario,dia2 AS dia                                          
               FROM prog_cursos_detalle AS pcd
               LEFT JOIN vista_profesores AS vp ON vp.id=pcd.id_profesor
               LEFT JOIN vista_prog_asig AS vpa ON vpa.id=pcd.id_prog_asig
               WHERE id_prog_curso=$id_prog_curso AND tipo<>'m'
               UNION
               SELECT pcd.id,vpa.cod_asignatura,vpa.asignatura,seccion,vp.nombre AS profesor,
                      horario3 AS horario,dia3 AS dia                                          
               FROM prog_cursos_detalle AS pcd
               LEFT JOIN vista_profesores AS vp ON vp.id=pcd.id_profesor
               LEFT JOIN vista_prog_asig AS vpa ON vpa.id=pcd.id_prog_asig
               WHERE id_prog_curso=$id_prog_curso AND tipo<>'m'
               ORDER BY horario,dia,cod_asignatura";
$pc_det = consulta_sql($SQL_pc_det);

$horarios = consulta_sql("SELECT id,intervalo FROM vista_horarios ORDER BY id");
$y=0;	
$HTML_pc_det = "";
for ($x=0;$x<count($horarios);$x++) {
	$HTML_pc_det .= "<tr><td class='tituloTabla' align='center' valign='middle'>{$horarios[$x]['id']}<br>{$horarios[$x]['intervalo']}</td>";
	$id_horario = $horarios[$x]['id'];
	for($d=1;$d<7;$d++) {
		$HTML_cursos = "";
		while ($id_horario == $pc_det[$y]['horario'] && $d == $pc_det[$y]['dia']) {
			$enl = "$enlbase=prog_cursos_editar_curso&id_pc_det={$pc_det[$y]['id']}&id_prog_curso=$id_prog_curso";			
			$asignatura = trim($pc_det[$y]['cod_asignatura'])."-".$pc_det[$y]['seccion']."<br><b>".$pc_det[$y]['asignatura']."</b>";
			$HTML_cursos .= "<div class='horarioCurso' onClick=\"window.location='$enl';\">$asignatura<br><u>{$pc_det[$y]['profesor']}</u></div>";
			
			if ($y < count($pc_det)) { $y++; } else { break; }				
		}
		$HTML_pc_det .= "<td class='celdaHorarios' valign='top'>$HTML_cursos</td>\n";
	}
	$HTML_pc_det . "</tr>\n";
}	

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>

<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr>
    <td align="center" class="textoTabla" style="vertical-align: middle;">
      <a href='<?php echo("$enlbase=prog_cursos_horarios_editar&id_prog_curso=$pc_id"); ?>' class='boton'>Editar Horario</a>
      <a href='<?php echo("$enlbase=prog_cursos_ver&id_prog_curso=$pc_id"); ?>' class='boton'>Volver</a>
    </td>
  </tr>
</table><br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr"><?php echo($pc_escuela); ?></td>
    <td class="celdaNombreAttr">Periodo:</td>
    <td class="celdaValorAttr"><?php echo($pc_periodo); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Creador:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($pc_creador); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Fec. Creación:</td>
    <td class="celdaValorAttr"><?php echo($pc_fecha); ?></td>
    <td class="celdaNombreAttr">Fec. Últ. Mod.:</td>
    <td class="celdaValorAttr"><?php echo($pc_fecha_mod); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Cant. cursos:</td>
    <td class="celdaValorAttr"><?php echo($pc_cant_cursos); ?></td>
    <td class="celdaNombreAttr">Costo Semestral:</td>
    <td class="celdaValorAttr">$<?php echo(number_format($pc_costo_semestral,0,',','.')); ?></td>
  </tr>
</table><br>

<div class="texto" style="color: #DF0000">
  NOTA: Se excluyen de esta tabla los cursos de tipo Modular, ya que estos tienen un horario predefinido.
</div><br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="8">Horario Semanal</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>&nbsp;</td>
    <td class='tituloTabla' width="125">Lunes</td>
    <td class='tituloTabla' width="125">Martes</td>
    <td class='tituloTabla' width="125">Miércoles</td>
    <td class='tituloTabla' width="125">Jueves</td>
    <td class='tituloTabla' width="125">Viernes</td>
    <td class='tituloTabla' width="125">Sábado</td>
  </tr>
  <?php echo($HTML_pc_det); ?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
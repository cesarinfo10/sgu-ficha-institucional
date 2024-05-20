<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");


$semestre   = $_REQUEST['semestre'];
$ano        = $_REQUEST['ano'];
$id_carrera = $_REQUEST['id_carrera'];
$jornada    = $_REQUEST['jornada'];
$sala       = $_REQUEST['sala'];

if(empty($semestre)) { $semestre = $SEMESTRE; }
if(empty($ano))      { $ano      = $ANO; }

$ids_carreras = $_SESSION['ids_carreras'];

if (empty($_REQUEST['ano'])) { $ano = $ANO; }
if (empty($_REQUEST['semestre'])) { $semestre = $SEMESTRE; }

$condiciones = " AND c.ano=$ano AND c.semestre=$semestre AND tipo IN ('r','t') ";

if ($id_carrera > 0) { $condiciones .= " AND vc.id_carrera=$id_carrera "; }

if ($jornada == 'D')     { $condiciones .= " AND c.seccion BETWEEN 1 AND 4 "; }
elseif ($jornada == 'V') { $condiciones .= " AND c.seccion BETWEEN 5 AND 9 "; }

if(!empty($sala)) { $condiciones .= " AND (c.sala1='$sala' OR c.sala2='$sala' OR c.sala3='$sala') "; }

if ($ids_carreras <> "") { $condiciones .= " AND vc.id_carrera IN ($ids_carreras) "; }

$SQL_cursos = "SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario1 AS horario,c.dia1 AS dia,c.sala1 AS sala,cant_alumnos_asist(vc.id) AS alumnos_asist
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE true $condiciones
               UNION
               SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario2 AS horario,c.dia2 AS dia,c.sala2 AS sala,cant_alumnos_asist(vc.id) AS alumnos_asist
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE true $condiciones
               UNION
               SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario3 AS horario,c.dia3 AS dia,c.sala3 AS sala,cant_alumnos_asist(vc.id) AS alumnos_asist
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE true $condiciones
               ORDER BY horario,dia,seccion,cod_asignatura";
if (!empty($sala)) { $SQL_cursos = "SELECT * FROM ($SQL_cursos) AS foo WHERE sala='$sala'"; }
$cursos = consulta_sql($SQL_cursos);
//echo($SQL_cursos);
$horarios = consulta_sql("SELECT id,intervalo FROM vista_horarios ORDER BY id");
$y=0;	
$HTML_horarios = "";
for ($x=0;$x<count($horarios);$x++) {
	$HTML_horarios .= "<tr>
	                   <td class='tituloTabla' align='center' valign='middle'>
	                     {$horarios[$x]['id']}<br>{$horarios[$x]['intervalo']}
	                   </td>";
	$id_horario = $horarios[$x]['id'];
	for($d=1;$d<7;$d++) {
		$HTML_cursos = "";
		while ($id_horario == $cursos[$y]['horario'] && $d == $cursos[$y]['dia']) {
			$enl = "$enlbase=ver_curso&id_curso={$cursos[$y]['id']}";
			$asignatura = trim($cursos[$y]['cod_asignatura'])."-".$cursos[$y]['seccion']."<br><b>".$cursos[$y]['asignatura']."</b>";
			$HTML_cursos .= "<div class='horarioCurso' style='width: 120px' onClick=\"window.location='$enl';\">"
			             .  "  $asignatura<br>"
			             .  "  <u>{$cursos[$y]['profesor']}</u><br>"
			             .  "  <sub>Sala {$cursos[$y]['sala']} | {$cursos[$y]['alumnos_asist']} als.</sub>"
			             .  "</div>";
			
			if ($y < count($cursos)) { $y++; } else { break; }				
		}
		$HTML_horarios .= "<td class='celdaHorarios' valign='top'>$HTML_cursos</td>\n";
	}
	$HTML_horarios . "</tr>\n";
}	

/*
if (!empty($ids_carreras)) {
	$SQL_carreras = "SELECT id,nombre FROM carreras WHERE id IN ($ids_carreras) ORDER BY nombre;";
} else {
	$SQL_carreras = "SELECT id,nombre FROM carreras ORDER BY nombre;";
}
*/

$SQL_carreras = "SELECT id,nombre FROM carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$salas     = consulta_sql("SELECT trim(codigo) AS id,nombre||' (cap. '||capacidad||')' AS nombre,capacidad FROM salas WHERE activa ORDER BY piso,codigo");

$enl = "ano=$ano&semestre=$semestre&jornada=$jornada&sala=$sala&id_carrera=$id_carrera";
?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div><br>

<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr>
    <td align="center" class="textoTabla" style="vertical-align: middle;">
      <a href="cursos_horarios_imprimir.php?<?php echo($enl); ?>" target='_blank' class='boton'>Imprimir</a>
      <!--<a href="cursos_horarios_imprimir_salas.php?<?php echo($enl); ?>" target='_blank' class='boton'>Imprimir todas las salas</a>-->
      <a href="<?php echo("$enlbase=cursos_horarios_salas&$enl"); ?>" class='boton'>Horarios por salas</a>
    </td>
  </tr>
</table><br>
<div class="texto">
  <?php echo($boton_horarios); ?>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="2" border="0" cellspacing="0" width="auto">
      <tr valign="top">
        <td class="texto" width="auto">
          Filtrar por a&ntilde;o
          <select name="ano" onChange="submitform();">
            <?php echo(select($anos,$ano)); ?>
          </select>
          y/o semestre:
          <select name="semestre" onChange="submitform();">
            <?php echo(select($semestres,$semestre)); ?>
          </select>
          de la jornada:
          <select name="jornada" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
          de la sala:
          <select name="sala" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($salas,$sala)); ?>
          </select>
        </td>
      </tr>
      <tr valign="top">
        <td class="texto">
          Mostrar cursos de la carrera:<br>
          <select name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>    
          </select>
        </td>
      </tr>
    </table>
  </form>
</div>

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
  <?php echo($HTML_horarios); ?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->

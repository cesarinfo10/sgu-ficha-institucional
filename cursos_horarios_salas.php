<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//include("validar_modulo.php");


$semestre   = $_REQUEST['semestre'];
$ano        = $_REQUEST['ano'];
$id_carrera = $_REQUEST['id_carrera'];
$jornada    = $_REQUEST['jornada'];
$dia        = $_REQUEST['dia'];
$vacias     = $_REQUEST['vacias'];

if(empty($semestre)) { $semestre = $SEMESTRE; }
if(empty($ano))      { $ano      = $ANO; }
if(empty($vacias))   { $vacias   = "f"; }

$ids_carreras = $_SESSION['ids_carreras'];

if (empty($_REQUEST['ano']))      { $ano = $ANO; }
if (empty($_REQUEST['semestre'])) { $semestre = $SEMESTRE; }
if (empty($dia))                  { $dia = strftime("%u"); }

$condiciones = " AND c.ano=$ano AND c.semestre=$semestre AND tipo IN ('r','t') ";

if ($id_carrera > 0) { $condiciones .= " AND vc.id_carrera=$id_carrera "; }

if ($jornada == 'D')     { $condiciones .= " AND c.seccion BETWEEN 1 AND 4 "; }
elseif ($jornada == 'V') { $condiciones .= " AND c.seccion BETWEEN 5 AND 9 "; }

//if (!empty($ids_carreras)) { $condiciones .= " AND vc.id_carrera IN ($ids_carreras) "; }

$SQL_cursos = "SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario1 AS horario,c.dia1 AS dia,trim(c.sala1) AS sala
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE c.dia1=$dia AND c.sala1 IS NOT NULL $condiciones
               UNION
               SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario2 AS horario,c.dia2 AS dia,trim(c.sala2) AS sala
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE c.dia2=$dia AND c.sala2 IS NOT NULL  $condiciones
               UNION
               SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario3 AS horario,c.dia3 AS dia,trim(c.sala3) AS sala
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE c.dia3=$dia AND c.sala3 IS NOT NULL  $condiciones
               ORDER BY horario,sala,cod_asignatura";
$cursos = consulta_sql($SQL_cursos);
//echo($SQL_cursos);

$salas_utilizadas = "";
if ($vacias == "f") {
	$salas_utilizadas = array();
	for($x=0;$x<count($cursos);$x++) {
		$sala = $cursos[$x]['sala'];
		if (!in_array($sala,$salas_utilizadas)) {
			$salas_utilizadas = array_merge($salas_utilizadas,array($sala));
		}
	}
	$salas_utilizadas = "'".str_replace(",","','",implode("," , $salas_utilizadas))."'";
} else {
	$salas = consulta_sql("SELECT codigo FROM salas WHERE activa ORDER BY piso,codigo");
	for ($x=0;$x<count($salas);$x++) {
		$salas_utilizadas .= "'{$salas[$x]['codigo']}',";
	}
	$salas_utilizadas = substr($salas_utilizadas,0,-1);
}

$salas = consulta_sql("SELECT trim(codigo) AS id,nombre,capacidad FROM salas WHERE codigo IN ($salas_utilizadas) ORDER BY codigo");
$cant_salas = count($salas);

$HTML_salas = "";
for($x=0;$x<count($salas);$x++) {
	$HTML_salas .= "<td class='tituloTabla' width='50' nowrap>{$salas[$x]['nombre']}<br><sup>(cap. {$salas[$x]['capacidad']})</sup></td>";
}

$mods = "'A','B','C','D','E','F','G','H'";
if ($dia == 6) { $mods="'A','B','C','Ds','E'"; }
$horarios = consulta_sql("SELECT id,intervalo FROM vista_horarios WHERE id IN ($mods) ORDER BY id");

$y=0;	
$HTML_horarios = "";
for ($x=0;$x<count($horarios);$x++) {
	$HTML_horarios .= "<tr>
	                   <td class='tituloTabla' align='center' valign='middle'>
	                     {$horarios[$x]['id']}<br>{$horarios[$x]['intervalo']}
	                   </td>";
	$id_horario = $horarios[$x]['id'];
	for($s=0;$s<count($salas);$s++) {
		$HTML_cursos = "";
		while ($id_horario == $cursos[$y]['horario'] && $salas[$s]['id'] == $cursos[$y]['sala']) {
			$enl = "$enlbase=ver_curso&id_curso={$cursos[$y]['id']}";
			$asignatura = trim($cursos[$y]['cod_asignatura'])."-".$cursos[$y]['seccion']."<br><b>".$cursos[$y]['asignatura']."</b>";
			$HTML_cursos .= "<div class='horarioCurso' onClick=\"window.location='$enl';\">$asignatura<br><u>{$cursos[$y]['profesor']}</u></div>";
			
			if ($y < count($cursos)) { $y++; } else { break; }				
		}
		$HTML_horarios .= "<td class='celdaHorarios' valign='top'>$HTML_cursos</td>\n";
	}
	$HTML_horarios . "</tr>\n";
}	

$SQL_carreras = "SELECT id,nombre FROM carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$enl = "ano=$ano&semestre=$semestre&jornada=$jornada&dia=$dia&id_carrera=$id_carrera&vacias=$vacias";
?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  Horario semestral por días y salas
</div><br>

<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr>
    <td align="center" class="textoTabla" style="vertical-align: middle;">
      <a href="cursos_horarios_salas_imprimir.php?<?php echo($enl); ?>" target='_blank' class='boton'>Imprimir</a>
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
          del día:
          <select name="dia" onChange="submitform();">            
            <?php echo(select($dias_palabra,$dia)); ?>
          </select>
          salas vacias:
          <select name="vacias" onChange="submitform();">            
            <?php echo(select($sino,$vacias)); ?>
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
    <td class='tituloTabla' colspan="<?php echo($cant_salas+1); ?>">Horario Semanal del día <?php echo($dias_palabra[$dia-1]['nombre']); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>&nbsp;</td>
    <?php echo($HTML_salas); ?>
  </tr>
  <?php echo($HTML_horarios); ?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->

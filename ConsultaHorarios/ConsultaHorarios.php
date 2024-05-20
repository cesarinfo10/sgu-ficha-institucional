<?php

$id_carrera = $_REQUEST['id_carrera'];
$id_regimen = $_REQUEST['id_regimen'];

$semestre = $SEMESTRE;
$ano      = $ANO;
$vacias   = "f";
$dia      = strftime("%u");

$condiciones = " AND c.ano=$ano AND c.semestre=$semestre AND tipo IN ('r','t') ";

if ($id_carrera > 0) { $condiciones .= " AND vc.id_carrera=$id_carrera "; }

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

$salas = consulta_sql("SELECT trim(codigo) AS id,nombre,capacidad,piso FROM salas WHERE codigo IN ($salas_utilizadas) ORDER BY codigo");
$cant_salas = count($salas);

$HTML_salas = "";
for($x=0;$x<count($salas);$x++) {
	$HTML_salas .= "<td class='tituloTabla' width='50' nowrap>Sala {$salas[$x]['nombre']}<br><sub>Piso {$salas[$x]['piso']}°</sub></td>";
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
		while ($id_horario == $cursos[$y]['horario'] && $salas[$s]['nombre'] == $cursos[$y]['sala']) {
			$enl = "$enlbase=ver_curso&id_curso={$cursos[$y]['id']}";
			$asignatura = trim($cursos[$y]['cod_asignatura'])."-".$cursos[$y]['seccion']."<br><b>".$cursos[$y]['asignatura']."</b>";
			$HTML_cursos .= "<div class='ramomalla' style='margin-top: 5px; width: 150px'>$asignatura<br><u>{$cursos[$y]['profesor']}</u></div>";
			
			if ($y < count($cursos)) { $y++; } else { break; }				
		}
		$HTML_horarios .= "<td class='celdaHorarios' valign='top'>$HTML_cursos</td>\n";
	}
	$HTML_horarios . "</tr>\n";
}	

//$SQL_carreras = "SELECT id,nombre FROM carreras ORDER BY nombre;";
//$carreras = consulta_sql($SQL_carreras);

$nombre_carrera = $nombre_regimen = $boton_inicio = "";
if ($id_regimen <> "" && $id_carrera == "") {
	$SQL_carreras = "SELECT DISTINCT ON (id_carrera,carrera) id_carrera AS id,carrera AS nombre
					 FROM vista_cursos AS vc 
					 LEFT JOIN carreras AS c ON c.id=vc.id_carrera
					 WHERE c.regimen='$id_regimen' AND ano=$ANO AND semestre=$SEMESTRE
					 ORDER BY carrera";
	$carreras = consulta_sql($SQL_carreras);
	$HTML_carreras = "<br>Escoge tu Carrera:<br><br>"
	               . "<table width='100%'>";
	for ($x=0;$x<count($carreras);$x++) {
		$enl = "?id_regimen=$id_regimen&id_carrera={$carreras[$x]['id']}";
		$HTML_carreras .= "<tr>"
		               .  "  <td><a href='$enl' class='boton' style='font-size: 14pt'>{$carreras[$x]['nombre']}</a><br><br></td>";
		$x++;
		if ($x < count($carreras)) {
			$enl = "?id_regimen=$id_regimen&id_carrera={$carreras[$x]['id']}";
			$HTML_carreras .= "  <td><a href='$enl' class='boton' style='font-size: 14pt'>{$carreras[$x]['nombre']}</a><br><br></td>"
						   .  "</tr>";
		}
	}
} elseif ($id_carrera > 0) {
	$carrera = consulta_sql("SELECT nombre FROM carreras WHERE id=$id_carrera");	
	$nombre_carrera = " <span style='font-variant: none;font-weight: normal'>carrera</span> {$carrera[0]['nombre']}";
}

if ($id_regimen == "") {
	$SQL_regimenes = "SELECT id,nombre 
	                  FROM regimenes 
	                  WHERE id IN (SELECT regimen FROM carreras 
	                               WHERE id IN (SELECT id_carrera FROM vista_cursos 
	                                            WHERE ano=$ano AND semestre=$semestre 
	                                              AND (sesion1 IS NOT NULL OR sesion2 IS NOT NULL OR sesion3 IS NOT NULL)
	                                            )
	                               )";
	//$regimenes = consulta_sql("SELECT id,nombre FROM regimenes WHERE id IN ('PRE','POST-G','POST-T','DIP')");
	$regimenes = consulta_sql($SQL_regimenes);
	$HTML_regimenes = "<br>Escoge tu Regimen de Estudios:<br><br>";
	for ($x=0;$x<count($regimenes);$x++) {
		$HTML_regimenes .= "<div><a href='?id_regimen={$regimenes[$x]['id']}' class='boton' style='font-size: 16pt'>{$regimenes[$x]['nombre']}</a></div><br><br>";
	}
} elseif ($id_regimen <> "") { 
	$regimen = consulta_sql("SELECT nombre FROM regimenes WHERE id='$id_regimen'");
	$nombre_regimen = " <span style='font-variant: none;font-weight: normal'>regimen</span> {$regimen[0]['nombre']} ";
}

if ($nombre_carrera <> "" || $nombre_regimen <> "") { 
	$boton_inicio = "<a href='.' class='boton' style='font-size: 14pt'>↺ Volver a empezar</a>";
}

$enl = "dia=$dia&id_carrera=$id_carrera&id_regimen=$id_regimen";
?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo" style="font-size: 12pt">
  Consulta Horarios <?php echo("<span style='font-size: 16pt'>$nombre_regimen $nombre_carrera $boton_inicio</span>"); ?>
</div>

<div class="texto" style='font-size: 11pt'>
<?php 
	if ($id_regimen == "") { echo($HTML_regimenes); }
	if ($id_regimen <> "" && $id_carrera =="") { echo($HTML_carreras); }
?>
</div>

<?php if ($id_regimen<>"" && $id_carrera<>"") { ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="<?php echo($cant_salas+1); ?>" style='font-size: 11pt'>
      Horario del día <?php echo($dias_palabra[$dia-1]['nombre']); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>&nbsp;</td>
    <?php echo($HTML_salas); ?>
  </tr>
  <?php echo($HTML_horarios); ?>
</table><br>
<div class="texto" style="color: #DF0000">
  NOTA: Se excluyen de esta tabla los cursos de tipo Modular, ya que estos tienen un horario predefinido.
</div>
<?php } ?>

<!-- Fin: <?php echo($modulo); ?> -->

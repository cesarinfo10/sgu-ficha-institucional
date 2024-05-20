<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
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

$SQL_asist_curso = "SELECT fecha_hora::date AS fec,to_char(fecha_hora,'MM') AS mes_fec,to_char(fecha_hora,'YYYY') AS ano_fec,
                           to_char(fecha_hora,'HH24:MI') AS hora,to_char(fecha_hora,'YYYY-MM-DD HH24:MI:SS') AS fec_hora
                    FROM asist_cursos
                    WHERE id_curso=$id_curso ORDER BY fecha_hora";
$asist_curso     = consulta_sql($SQL_asist_curso);

$HTML_asist_curso = "";
$x = 0;
$horas = 0;
while ($x<count($asist_curso)) {
	if ($mes_fec <> $asist_curso[$x]['mes_fec']) {
		$mes = $meses_palabra[$asist_curso[$x]['mes_fec']-1]['nombre'];
		$HTML_asist_curso .= "<tr class='filaTabla'>
		                        <td class='textoTabla' colspan='5' align='center'>
		                          $mes de {$asist_curso[$x]['ano_fec']}
		                        </td>
		                      </tr>";	
	}
	
	extract($asist_curso[$x]);	
	
	$enl        = "$enlbase=editar_asistencia_profesor&id_asist=$id_asist";
	$enlace     = "<a class='enlitem' href='$enl'>";
	$js_onClick = "\"window.location='$enl';\"";
	
	$_azul = "color: #000099";
	$_rojo = "color: #ff0000";
		
	$hora_entrada = strtotime($fec_hora);
	$hora_ent = strftime("%T",$hora_entrada);
	$hora_salida = $hora_sal = $horas_dia = "";
	$x++;
	if ($fec == $asist_curso[$x]['fec']) {
		$hora_salida = strtotime($asist_curso[$x]['fec_hora']);
		$hora_sal = strftime("%T",$hora_salida);
		$x++;
	}
	
	$fec = ucfirst(strftime("%a %d",strtotime($fec)));
	$horas_dia_ped = 0;
	if ($hora_entrada <> "" && $hora_salida <> "") {
		$horas_dia = $hora_salida - $hora_entrada; // esto devuelva la diferencia en segundos
		// Para modulos de 1 hora y 30 mins: 2400s = 40m; 4500s = 1h 15m; 9900s = 2h 45m
		/*
		if ($horas_dia >= 2400 && $horas_dia < 4500) { $horas_dia_ped = 1; }
		if ($horas_dia >= 4500 && $horas_dia < 9900) { $horas_dia_ped = 2; }
		if ($horas_dia >= 9900)                      { $horas_dia_ped = 4; }
		*/
                // Modulos de 1 hora y 20 mins: 2100s = 35m; 4500s = 1h 15m; 9600s = 2h 40m
                if ($horas_dia >= 2100 && $horas_dia < 4500) { $horas_dia_ped = 1; }
                if ($horas_dia >= 4500 && $horas_dia < 9600) { $horas_dia_ped = 2; }
                if ($horas_dia >= 9600)                      { $horas_dia_ped = 4; }
		$horas += $horas_dia_ped;
		$horas_dia = date("H:i", strtotime("00:00") + $hora_salida - $hora_entrada);
	}
	
	$HTML_asist_curso .= "<tr class='filaTabla' onClick=$js_onClick>\n"
	                  .  "  <td class='textoTabla'> $fec</td>\n"
	                  .  "  <td class='textoTabla' align='center'>&nbsp; $hora_ent &nbsp;</td>\n"
	                  .  "  <td class='textoTabla' align='center'>&nbsp; $hora_sal &nbsp;</td>\n"
	                  .  "  <td class='textoTabla' align='center'>&nbsp; $horas_dia &nbsp;</td>\n"
	                  .  "  <td class='textoTabla' align='center'>&nbsp; $horas_dia_ped horas &nbsp;</td>\n"
	                  .  "</tr>\n";
}

$HTML_asist_curso .= "<tr class='filaTabla'>
		                <td class='textoTabla' colspan='4' align='right'>Total Pedagógicas:</td>
		                <td class='textoTabla' align='center'>$horas horas</td>
		              </tr>";	

//lista_control_asist_2011($id_curso);

//echo("horas: ".total_horas_control_asist_2011($id_curso));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class='texto' style='margin-top: 5px'>
<?php 
	if ($_SESSION['tipo'] <> 3) { 
		$enl  = "$enlbase_sm=profesores_reg_electronico_asistencia_tardia&id_curso=$nro_acta";
		$HTML = "<a href='$enl' class='boton'>Registro Tardío</a>";
	}
	echo ($HTML);
?>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
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
<script>
	alert("ATENCIÓN: La información que a continuación se muestra, corresponde "
	     +"a las horas que la Universidad reconoce y entiende realizadas por "
	     +"Ud. (<?php echo($profesor); ?>) en este curso en particular. Si esta información está incompleta "
	     +"a su juicio, comuníquese con el Director(a) de Escuela correspondiente "
	     +"y solicite la regularización de las horas realizadas.");
</script>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan="2">Día</td>
    <td class='tituloTabla' colspan="2">Hora de</td>
    <td class='tituloTabla' colspan="2">Total Horas</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Entrada</td>
    <td class='tituloTabla'>Salida</td>
    <td class='tituloTabla'>Reloj</td>
    <td class='tituloTabla'>Ped</td>
  </tr>
  <?php echo($HTML_asist_curso); ?>
</table>
<div class='texto' style='margin-top: 5px'>
  <b>Cálculo de Horas Pedagógicas:</b>
  <ul>
    <li>Una hora pedagógica equivale a 40 minutos cronológicos o de reloj</li>
    <li>Dos horas pedagógicas equivalen a 80 minutos (1 hora y 20 minutos) cronológicos</li>
    <li>Cuatro horas pedagógicas equivalen a 160 minutos (2 horas y 40 minutos) cronológicos</li>
  </ul>
</div>

<!-- Fin: <?php echo($modulo); ?> -->


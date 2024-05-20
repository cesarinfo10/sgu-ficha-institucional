<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$aSQLtxt[1][1]="SELECT id AS \"Código Interno\",nombre,rut, genero AS \"género\",
                       fec_nac AS \"fecha de nacimiento\",nacionalidad,
                       coalesce(pasaporte,'**No corresponde**') AS pasaporte,
                       direccion AS \"dirección\",comuna,region AS \"región\",
                       email,telefono AS \"teléfono fijo\",tel_movil AS \"teléfono móvil\",
                       admision AS \"Admisión\",estado,cohorte,carrera AS \"carrera actual\",
                       malla_actual AS \"año malla actual\",id_pap,id_malla_actual";
$aSQLtxt[1][2]="Datos<br>personales";

$aSQLtxt[2][1]="SELECT id AS \"Código Interno\",nombre,colegio,ano_egreso_col AS \"año egreso Enseñanza Media\",
                       ano_psu AS \"año PSU\",puntaje_psu AS \"puntaje PSU\",ies AS \"Estudios anteriores en\",
                       carr_ies_pro AS \"En la carrera de\",prom_nt_ies_pro AS \"promedio de notas\",
                       CASE conc_nt_ies_pro WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente Concentración de notas?\",
                       CASE prog_as_ies_pro WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente programas de asignatura?\",
                       id_pap,id_malla_actual";
$aSQLtxt[2][2]="Antecedentes<br>escolares/universitarios";

/*
$aSQLtxt[3][1]="SELECT id AS \"Código Interno\",nombre,cert_nacimiento,arch_cert_nac,copia_ced_iden,
                    arch_cp_cedid,
                    conc_notas_em,arch_conc_n_em,licencia_em,arch_lic_em,boletin_psu";
*/

$aSQLtxt[3][1]="SELECT id AS \"Código Interno\",nombre,
                       CASE cert_nacimiento WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente Certificado de Nacimiento?\",
                       CASE copia_ced_iden WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente fotocopia de C.I.?\",
                       CASE conc_notas_em  WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente Concentración de Notas EM?\",
                       CASE licencia_em WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente Licencia de Enseñanza Media?\",
                       CASE boletin_psu WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente Boletín de resultados PSU?\",
                       id_pap,id_malla_actual";
$aSQLtxt[3][2]="Datos de<br>control interno";

$id_alumno = $_REQUEST['id_alumno'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_escuelas';"));
	exit;
};

$ficha = $_REQUEST['ficha'];
if ($ficha == "") {
	$ficha = 1;
};

$bdcon = pg_connect("dbname=regacad" . $authbd);
            
$SQLtxt = $aSQLtxt[$ficha][1] . " FROM vista_alumnos WHERE id=$id_alumno;";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$alumno = utf2html(pg_fetch_all($resultado));
	$id_pap = $alumno[0]['id_pap'];
	
	$SQLtxt2 = "SELECT vac.id_curso, vac.id_pa, vac.id_pa_homo, vac.id_estado, 
	                    CASE WHEN vac.id_curso IS NOT NULL
	                         THEN coalesce(vac.ano,'0')||'-'||coalesce(vac.semestre,'0')
	                         WHEN vac.id_pa IS NOT NULL AND vac.id_pa_homo IS NULL
	                         THEN a.cohorte::text||'-0'
	                         WHEN vac.id_pa IS NOT NULL AND vac.id_pa_homo IS NOT NULL
	                         THEN extract(YEAR FROM vac.fec_mod)::text||'-0'
	                    END AS periodo, vac.asignatura, vac.s1, vac.nc, vac.s2, vac.recuperativa,
	                    vac.nf, vac.situacion, vac.ano_programa AS programa, vac.id_prog_asig, 
	                    vac.fecha_mod
	            FROM vista_alumnos_cursos AS vac
	            JOIN alumnos AS a ON a.id=vac.id_alumno
	            WHERE vac.id_alumno=$id_alumno 
	            ORDER BY periodo,vac.asignatura;";
	$resultado2 = pg_query($bdcon, $SQLtxt2);
	$filas2 = pg_numrows($resultado2);
	if ($filas2 > 0) {
		$alumno_cursos = utf2html(pg_fetch_all($resultado2));

		$SQLtxt3 = "SELECT avg(nota_final)::numeric(4,2) AS prom_nf
		            FROM cargas_academicas
		            WHERE id_alumno=$id_alumno AND id_estado IN (1,2);";
		$resultado3 = pg_query($bdcon, $SQLtxt3);
		$filas3 = pg_numrows($resultado3);
		if ($filas3 > 0) {
			$promedio_gen_nf = pg_fetch_result($resultado3,0,0);

			$SQLtxt4 = "SELECT avg(nota_final)::numeric(4,2) AS prom_nf,count(*) as cant_cursos_aprobados
			            FROM cargas_academicas
			            WHERE id_alumno=$id_alumno AND id_estado=1;";
			$resultado4 = pg_query($bdcon, $SQLtxt4);
			$filas4 = pg_numrows($resultado4);
			if ($filas4 > 0) {
				$promedio_aprob_nf     = pg_fetch_result($resultado4,0,0);
				$cant_cursos_aprobados = pg_fetch_result($resultado4,0,1);
			};
			
		};
		
	};
	
};

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($alumno[0]['nombre']); ?>
</div><br>
<table class="tabla">
<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="editar_alumno">
<input type="hidden" name="id_escuela" value="<?php echo($id_alumno); ?>">
  <tr>
<?php
	switch ($_SESSION['tipo']) {
		case 0:
?>
    <td class="tituloTabla"><input type="submit" name="editar" value="Editar"></td>
<?php
		case 1 || 2:
?>
    <td class="tituloTabla">
      <input type="button" name="convalidar" value="Convalidar" onClick="window.location='<?php echo("$enlbase=crear_convalidacion&id_alumno=$id_alumno&id_pap=$id_pap"); ?>';">
    </td>    
    <td class="tituloTabla">
      <input type="button" name="homologar" value="Homologar" onClick="window.location='<?php echo("$enlbase=crear_homologacion&id_alumno=$id_alumno&id_pap=$id_pap"); ?>';">
<!--      <input type="button" name="homologar" value="Homologar" onClick="alert('Módulo desactivado');"> -->
    </td>    
    <td class="tituloTabla">
      <input type="button" name="exconrelev" value="Ex. Conoc. Relev." onClick="window.location='<?php echo("$enlbase=crear_examen_conrel&id_alumno=$id_alumno&id_pap=$id_pap"); ?>';">
<!--      <input type="button" name="exconrelev" value="Ex. Conoc. Relev." onClick="alert('Módulo desactivado');"> -->
    </td>    
<?php
	};
?>
    <td class="tituloTabla"><input type="button" name="volver" value="Volver" onClick="history.back()"></td>
  </tr>
</form>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="2" cellpadding="4" class="tabla">
  <tr>
		<?php
			$enlace_ficha = "?modulo=$modulo&id_alumno=$id_alumno&ficha";			
			for($y=1;$y<=3;$y++) {
				$texto_ficha = $aSQLtxt[$y][2];
				if ($y <> $ficha) {
					echo("<td class='texto'><center>");
					echo("<a href='$enlace_ficha=$y'>$texto_ficha</a>");
				} else {
					echo("<td class='celdaNombreAttr'><center>");
					echo("<b>$texto_ficha</b>");
				};					
				echo("</center></td>\n");
			};
		?>
  </tr>
  <tr>
    <td colspan="3">
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
<?php
	for($x=0;$x<pg_num_fields($resultado)-2;$x++) {
		$nombre_campo = ucfirst(pg_field_name($resultado,$x));
		$valor_campo = $alumno[0][pg_field_name($resultado,$x)];
		$enlace = $enlace_fin = "";
		if ($nombre_campo == ucfirst("año malla actual")) {
			$href_enlace = "$enlbase=ver_malla&id_malla=" . $alumno[0]['id_malla_actual'];			
			$enlace = "<a href='$href_enlace'>";
			$enlace_fin = "</a>";
		};
		echo("  <tr>");
		echo("    <td class='celdaNombreAttr'>$nombre_campo:</td>");
		echo("    <td class='celdaValorAttr'>&nbsp;$enlace$valor_campo$enlace_fin</td>");
		echo("  </tr>");
	};
?>
</table>
    </td>
  </tr>
</table>
<br>
<?php
	if ($_SESSION['tipo'] < 3) {
?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class="tituloTabla" colspan="9">Rendimiento acad&eacute;mico del alumno</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>NC</td>
    <td class='tituloTabla'>S2</td>
    <td class='tituloTabla'>Recup.</td>
    <td class='tituloTabla'>NF</td>
    <td class='tituloTabla'>Situaci&oacute;n</td>
    <td class='tituloTabla'>Prg. Asig.</td>
  </tr>
<?php
	$periodo_inicio      = $alumno_cursos[0]['periodo'];
	for ($x=0; $x<$filas2; $x++) {
		$id_ca            = $alumno_cursos[$x]['id'];

		$id_curso         = $alumno_cursos[$x]['id_curso'];
		$id_prog_asig     = $alumno_cursos[$x]['id_prog_asig'];

		$id_pa            = $alumno_cursos[$x]['id_pa'];
		
		$id_pa_homo       = $alumno_cursos[$x]['id_pa_homo'];
		
		$periodo          = $alumno_cursos[$x]['periodo'];
		$asignatura       = $alumno_cursos[$x]['asignatura'];
		$s1               = $alumno_cursos[$x]['s1'];
		$nc               = $alumno_cursos[$x]['nc'];
		$s2               = $alumno_cursos[$x]['s2'];
		$recup            = $alumno_cursos[$x]['recuperativa'];
		$nf               = $alumno_cursos[$x]['nf'];
		$id_estado        = $alumno_cursos[$x]['id_estado'];
		$situacion        = $alumno_cursos[$x]['situacion'];
		$ano_prog_asig    = $alumno_cursos[$x]['programa'];
		$fecha_mod        = $alumno_cursos[$x]['fecha_mod'];

		$enl_curso        = "$enlbase=ver_curso&id_curso=$id_curso";
		$enl_prog_asig    = "$enlbase=ver_prog_asig&id_prog_asig=$id_prog_asig";

//		$enl_ca           = "$enlbase=ver_carga_academica&id_ca=$id_ca";
		$enl_ca           = "$enlbase=ver_prog_asig&id_prog_asig=$id_pa";
		
		$enlace_curso     = "<a class='enlitem' href='$enl_curso' title='$fecha_mod'>";
		$enlace_prog_asig = "<a class='enlitem' href='$enl_prog_asig'>";

		if ($id_curso <> "") {
			$enl     = $enl_curso;
			$enlace  = $enlace_curso;
		};

		if ($id_pa <> "" || $id_pa_homo <> "" ) {
			$enl    = $enl_ca;
			$enlace = $enlace_ca;
		};
		
		if ($id_estado == "1") {
			$color_situacion = "color='#000099'";
		} elseif ($id_estado == "2") {
				$color_situacion = "color='#ff0000'";
		} else {
			$color_situacion = "color='#000000'";
		};

		if ($periodo_inicio <> $periodo) {
			echo("  <tr class='filaTabla'><td colspan='9' class='textoTabla'>&nbsp;</td></tr>\n");
			$periodo_inicio = $periodo;			
		};

		echo("  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n");
		echo("    <td class='textoTabla'>&nbsp;$enlace$periodo</a></td>\n");
		echo("    <td class='textoTabla'>&nbsp;$enlace$asignatura</a></td>\n");
		echo("    <td class='textoTabla'>&nbsp;$enlace$s1</a></td>\n");
		echo("    <td class='textoTabla'>&nbsp;$enlace$nc</a></td>\n");
		echo("    <td class='textoTabla'>&nbsp;$enlace$s2</a></td>\n");
		echo("    <td class='textoTabla'>&nbsp;$enlace$recup</a></td>\n");
		echo("    <td class='textoTabla'>&nbsp;$enlace<font $color_situacion>$nf</font></a></td>\n");
		echo("    <td class='textoTabla'>&nbsp;$enlace<font $color_situacion>$situacion</font></a></td>\n");
		echo("    <td class='textoTabla'>&nbsp;$enlace_prog_asig$ano_prog_asig</a></td>\n");
		echo("  </tr>\n");

	};
	echo("  <tr>\n");
	echo("    <td class='textoTabla' align='right' colspan='6'>Promedio General de Notas Finales:</td>\n");
	echo("    <td class='textoTabla' colspan='3'>&nbsp;$promedio_gen_nf</td>\n");
	echo("  </tr>\n");
	echo("  <tr>\n");
	echo("    <td class='textoTabla' align='right' colspan='6'>Promedio s&oacute;lo cursos Aprobados de Notas Finales:</td>\n");
	echo("    <td class='textoTabla' colspan='3'>&nbsp;$promedio_aprob_nf</td>\n");
	echo("  </tr>\n");
	echo("  <tr>\n");
	echo("    <td class='textoTabla' align='right' colspan='6'>Cursos/programas aprobados:</td>\n");
	echo("    <td class='textoTabla' colspan='3'>&nbsp;$cant_cursos_aprobados</td>\n");
	echo("  </tr>\n");
?>
</table>
<?php

};

?>

<!-- Fin: <?php echo($modulo); ?> -->


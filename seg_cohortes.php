<?php

include("funciones.php");

$seg_cohortes = array();
$x = 0;
$SQL = "";
for ($cohorte=2006;$cohorte<=2011;$cohorte++) {
	for ($sem_cohorte=1;$sem_cohorte<=2;$sem_cohorte++) {
		for ($ano=$cohorte;$ano<=2011;$ano++) {
			for ($sem=1;$sem<=2;$sem++) {
				if ($cohorte==$ano && $sem_cohorte==2 && $sem==1) { $sem=2; }
				$SQL_ca = "select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=$ano and semestre=$sem) and id_alumno=va.id";
				$SQL_als = "SELECT va.carrera,a.cohorte,a.semestre_cohorte,$ano AS ano,$sem AS semestre,ano_egreso,semestre_egreso,
								   ($SQL_ca) as cursos_insc,
								   ($SQL_ca and id_estado=1) as cursos_aprob,
								   ($SQL_ca and id_estado=2) as cursos_reprob,
								   ($SQL_ca and id_estado=6) as cursos_susp,
								   ($SQL_ca and id_estado=10) as cursos_ret,
								   ($SQL_ca and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=$ano AND semestre=$sem and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=$sem AND ano=$ano LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=$cohorte and a.semestre_cohorte=$sem_cohorte 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre";
				$SQL .= $SQL_als."\n";
				$seg_cohorte = consulta_sql($SQL_als);
				if (count($seg_cohorte) > 0) {
					$seg_cohortes[$x]['carrera']          = $seg_cohorte[0]['carrera'];
					$seg_cohortes[$x]['cohorte']          = $cohorte;
					$seg_cohortes[$x]['semestre_cohorte'] = $sem_cohorte;
					$seg_cohortes[$x]['ano']              = $ano;
					$seg_cohortes[$x]['semestre']         = $sem;
					$seg_cohortes[$x]['tot_cohorte']      = 0;
					$seg_cohortes[$x]['matriculados']     = 0;
					$seg_cohortes[$x]['cursos_insc']      = 0;
					$seg_cohortes[$x]['ret_susp']         = 0;
					$seg_cohortes[$x]['abandonados']      = 0;
					$seg_cohortes[$x]['egresados']        = 0;
					for($y=0;$y<count($seg_cohorte);$y++) {
						if ($seg_cohorte[$y]['carrera']<>$seg_cohortes[$x]['carrera']) {
							$x++;
							$seg_cohortes[$x]['carrera']          = $seg_cohorte[$y]['carrera'];
							$seg_cohortes[$x]['cohorte']          = $cohorte;
							$seg_cohortes[$x]['semestre_cohorte'] = $sem_cohorte;
							$seg_cohortes[$x]['ano']              = $ano;
							$seg_cohortes[$x]['semestre']         = $sem;
							$seg_cohortes[$x]['tot_cohorte']      = 0;
							$seg_cohortes[$x]['matriculados']     = 0;
							$seg_cohortes[$x]['cursos_insc']      = 0;
							$seg_cohortes[$x]['ret_susp']         = 0;
							$seg_cohortes[$x]['abandonados']      = 0;
							$seg_cohortes[$x]['egresados']        = 0;
						}
						
						$seg_cohortes[$x]['tot_cohorte']++;

						if ($seg_cohorte[$y]['matriculado'] == 1) {
							$seg_cohortes[$x]['matriculados']++;
						}
						
						if ($seg_cohorte[$y]['cursos_insc']>0) {
							$seg_cohortes[$x]['cursos_insc']++;
						}
						
						if ($seg_cohorte[$y]['ret_susp']==1 || $seg_cohorte[$y]['cursos_insc']==0 || $seg_cohorte[$y]['cursos_insc']==$seg_cohorte[$y]['cursos_ret'] || $seg_cohorte[$y]['cursos_insc']==$seg_cohorte[$y]['cursos_susp']) { 
							$seg_cohortes[$x]['ret_susp']++;
						} elseif ($seg_cohorte[$y]['cursos_insc']==$seg_cohorte[$y]['cursos_aband'] || ($seg_cohorte[$y]['cursos_insc']>0 && $seg_cohorte[$y]['cursos_reprob']/$seg_cohorte[$y]['cursos_insc']>=0.6)) {
							$seg_cohortes[$x]['abandonados']++;
						}
						
						if ($ano>=$seg_cohorte[$y]['ano_egreso'] && $sem>=$seg_cohorte[$y]['semestre_egreso']) {
							$seg_cohortes[$x]['egresados']++;
						}
					}
				}
				$x++;
			}
		}
	}
}

file_put_contents("seg_cohortes.sql",$SQL);

for ($x=0;$x<count($seg_cohortes);$x++) {
	echo(implode(",",$seg_cohortes[$x])."\n");
}
?>

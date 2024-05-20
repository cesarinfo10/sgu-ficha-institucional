SELECT va.carrera,a.cohorte,a.semestre_cohorte,2006 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2006 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2006 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2006 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2006 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2006 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2007 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2007 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2007 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2007 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2007 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2007 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2006 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2006 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2006 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2006 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2007 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2007 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2007 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2007 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2007 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2007 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2006 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2007 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2007 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2007 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2007 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2007 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2007 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2007 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2007 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2007 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2007 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2007 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2008 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2008 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2008 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2008 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2008 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2009 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2009 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2009 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2009 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2009 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2010 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2010 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2010 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2010 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2010 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2010 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2010 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2010 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2010 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2010 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2010 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,1 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=1) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=1 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=1 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2011 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2011 and a.semestre_cohorte=1 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre
SELECT va.carrera,a.cohorte,a.semestre_cohorte,2011 AS ano,2 AS semestre,ano_egreso,semestre_egreso,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id) as cursos_insc,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=1) as cursos_aprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=2) as cursos_reprob,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=6) as cursos_susp,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and id_estado=10) as cursos_ret,
								   (select count(id) from cargas_academicas where id_curso in (select id from cursos where ano=2011 and semestre=2) and id_alumno=va.id and (nota_final=1 or (solemne1=-1 and solemne2=-1))) as cursos_aband,
								   (SELECT 1 FROM matriculas WHERE ano=2011 AND semestre=2 and id_alumno=va.id) AS matriculado,
								   (SELECT 1 FROM vista_solicitudes WHERE semestre=2 AND ano=2011 LIMIT 1) AS ret_susp
							FROM vista_alumnos AS va 
							LEFT JOIN alumnos AS a using (id)
							WHERE a.cohorte=2011 and a.semestre_cohorte=2 
							ORDER BY carrera,a.cohorte,a.semestre_cohorte,ano,semestre

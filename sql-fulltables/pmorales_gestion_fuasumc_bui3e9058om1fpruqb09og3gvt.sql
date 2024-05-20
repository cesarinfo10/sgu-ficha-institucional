COPY (SELECT fuas.id,fuas.ano,a.id AS id_alumno,a.rut,a.nombres,apellidos,c.alias||'-'||a.jornada AS carrera,
	                semestre_cohorte||'-'||cohorte AS cohorte,a.mes_cohorte,fuas.estado,
	                to_char(greatest(fecha_creacion,fecha_presentacion,fecha_validacion,fecha_rechazo),'DD-tmMon-YYYY') as fecha_estado,
	                fuas.email,fuas.telefono,fuas.tel_movil,ne.nombre AS nivel_educ,fuas.estado_civil,
	                CASE WHEN fuas.enfermo_cronico THEN 'Si' ELSE 'No' END AS enfermo_cronico,fuas.nombre_enfermedad,
                    fuas.pertenece_pueblo_orig,CASE WHEN fuas.acred_pert_pueblo_orig THEN 'Si' ELSE 'No' END AS acred_pert_pueblo_orig,
                    fuas.cat_ocupacional,act.nombre AS cat_ocupacional_nombre,
                    CASE WHEN fuas.jefe_hogar THEN 'Si' ELSE 'No' END AS jefe_hogar,fuas.ing_liq_mensual_prom,
                    fuas.domicilio_grupo_fam,com.nombre AS comuna_grupo_fam,reg.nombre AS region_grupo_fam,tenencia_dom_grupo_fam,
                    round((coalesce((SELECT sum(ing_liq_mensual_prom) FROM dae.fuas_grupo_familiar WHERE id_fuas=fuas.id),0) + fuas.ing_liq_mensual_prom)/((SELECT count(id) FROM dae.fuas_grupo_familiar WHERE id_fuas=fuas.id) + 1),0) AS ingreso_percapita,
                    puntaje_socioeconomico,puntaje_notas,puntaje_sit_financiera,puntaje_comp_cervantino,
                    beca_otorgada
             FROM dae.fuas
             LEFT JOIN alumnos            AS a   ON a.id=fuas.id_alumno
             LEFT JOIN carreras           AS c   ON c.id=a.carrera_actual
             LEFT JOIN comunas            AS com ON com.id=fuas.comuna_grupo_fam
             LEFT JOIN regiones           AS reg ON reg.id=fuas.region_grupo_fam
             LEFT JOIN dae.nivel_estudios AS ne  ON ne.id=nivel_educ
             LEFT JOIN dae.actividades    AS act ON act.id=fuas.cat_ocupacional
             WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '16389333' OR  a.rut ~* '16389333' OR  text(a.id) ~* '16389333' ) 
             ORDER BY fuas.fecha_creacion DESC ) to stdout WITH CSV HEADER
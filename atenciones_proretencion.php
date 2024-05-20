<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

//$id_alumno = $_REQUEST['id_alumno'];
//$periodo   = $_REQUEST['periodo'];

//if (empty($periodo)) { $periodo = "$SEMESTRE_MATRICULA-$ANO_MATRICULA"; } 
//list($per_sem,$per_ano) = explode("-",$periodo);

//$alumno = consulta_sql("SELECT va.id,trim(va.rut) AS rut,nombre,a.email,a.telefono,a.tel_movil FROM vista_alumnos va LEFT JOIN alumnos a USING(id) WHERE a.id=$id_alumno");
//if (count($alumno) == 0) {
//	$postulante = consulta_sql("SELECT id,trim(rut) AS rut,nombre FROM vista_pap WHERE rut='$rut'");
//	extract($postulante[0]);
//} else {
//	extract($alumno[0]);
//}
$id_usuario = $_SESSION['id_usuario'];
//echo("<br>id_usuario = $id_usuario");
//$SQL_unidad_usuario = "select id_unidad as id_unidad from usuarios where id = $id_usuario;";
//echo("<br>SQL_unidad_usuario = $SQL_unidad_usuario");
//$funidad_usuario = consulta_sql($SQL_unidad_usuario);

//$unidad_usuario = $funidad_usuario['id_unidad'];



$dias_urgencia = 3;

$SQL_unidad_usuario = "select id_unidad as id_unidad from usuarios where id = $id_usuario;";
        $fff = consulta_sql($SQL_unidad_usuario);	
        $id_unidad = $fff[0]['id_unidad'];







        $SQL_mis_casos = "
        select 
        atpr.id id, 
        (select concat(a.nombres,' ', a.apellidos) nombre_alumno from alumnos a where a.id = atpr.id_alumno) as nombre_alumno,
        (select u.id_unidad from usuarios u where id = atpr.id_usuario_origen) unidad_origen,
        (select concat(u.nombre,' ', u.apellido) from usuarios u where u.id = atpr.id_usuario_origen) as nombre_usuario,
        (select gu.nombre from gestion.unidades gu where gu.id = atpr.id_unidad_derivada)  derivado_nombre,                       
        atpr.id_alumno id_alumno,
        atpr.id_motivo id_motivo,
        --(select concat(moti.clasificacion,': ',moti.nombre) from tipo_motivo_aux moti where moti.id = atpr.id_motivo) nombre_motivo,
        (
          SELECT concat(
            (
              select b.clasificacion from tipo_motivo_clasif_proretencion b
              where b.id = a.id_clasificacion
            ),': <br>',a.nombre) AS nombre 
            FROM tipo_motivo_proretencion a 
            where a.id =  atpr.id_motivo                       
        ) nombre_motivo, 
        to_char(atpr.fecha,'dd/mm/yyyy') fecha,       
        to_char(atpr.fecha_derivacion,'dd/mm/yyyy') fecha_derivacion,   
        --atpr.fecha fecha,
        atpr.comentarios comentarios,
        atpr.comentarios_derivado comentarios_derivado,
        atpr.tipo_contacto tipo_contacto,
        atpr.respuesta_contacto respuesta_contacto,                      
        atpr.resuelto resuelto,
        atpr.id_unidad_derivada id_unidad_derivada,
        (select nombre from gestion.unidades where id = atpr.id_unidad_derivada) glosa_derivado ,
atpr.comentarios comentarios
        from atenciones_proretencion atpr
        where 
resuelto = 'f' 
and (resuelto_derivado = 'f' or resuelto_derivado is null)
and (
      (
        (select u.id_unidad from usuarios u where id = atpr.id_usuario_origen) = $id_unidad) and (atpr.id_unidad_derivada is null)
        or (atpr.id_unidad_derivada = $id_unidad)
    )

        order by atpr.fecha desc, atpr.id desc
        ";


$mis_casos = consulta_sql($SQL_mis_casos);
$HTML_mis_casos = "";

for ($x=0;$x<count($mis_casos);$x++) {
extract($mis_casos[$x]);


//echo("<br>que ssss : $id");
//$compromiso_color = (strtotime($fecha_comp) > time()) ? "green" : "red";

if ($resuelto=='t') {
$resueltoFinal = "<span style='color: green'>Sí</span>";
} else {
$resueltoFinal = "<span style='color: red'>No</span>";
}
if ($respuesta_contacto=='t') {
$respuesta_contactoFinal = "<span style='color: green'>Contactado</span>";

} else {
$respuesta_contactoFinal = "<span style='color: red'>Sin Respuesta</span>";
}
/*
if ($unidad_origen == $id_unidad_derivada) {
$mini = "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=NO
&comentarios_derivado=$comentarios_derivado  
&es_mi_caso=SI
' class='boton'>Resolver</a></td>\n";
} else {
$mini = "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=SI
&comentarios_derivado=$comentarios_derivado
&es_mi_caso=NO
' class='boton'>Ver</a></td>\n";
}
*/
if ($unidad_origen == $id_unidad_derivada) {
$mini = "<a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=NO
&comentarios_derivado=$comentarios_derivado  
&es_mi_caso=SI
'>$nombre_motivo</a>";
} else {
$mini = "<a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=NO
&comentarios_derivado=$comentarios_derivado
&es_mi_caso=SI
'>$nombre_motivo</a>";
  }
  

$HTML_mis_casos .= "<tr class='filaTabla' style='vertical-align: middle'>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$id</td>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: center;'><small>$fecha</small></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$nombre_alumno</small></td>\n"
//. "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$nombre_usuario</small></td>\n"
//. "  <td class='textoTabla' style='vertical-align: middle' align='left'>$nombre_motivo</td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='left'>$mini</td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='center'>$tipo_contacto<br><small>$respuesta_contactoFinal</small></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$nombre_usuario</small></td>\n"
//. "  <td class='textoTabla' style='vertical-align: middle' align='center'>$respuesta_contactoFinal</td>\n"
//. "  <td class='textoTabla' style='vertical-align: middle' align='center'>$resueltoFinal</td>\n"
//. "  <td class='textoTabla' style='vertical-align: middle'>$glosa_derivado</td>\n"
/*
. "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
                        href='$enlbase=registro_atenciones_agregar_new_derivado
                        &id_tabla=$id
                        &id_alumno=$id_alumno
                        &id_motivo=$id_motivo
                        &tipo_contacto=$tipo_contacto
                        &obtiene_respuesta=$respuesta_contacto
                        &obtiene_resuelto=$resuelto
                        &comentarios=$comentarios
                        &id_area_derivacion=$id_unidad_derivada	
                        &modo_ver=NO 	
                        &comentarios_derivado=$comentarios_derivado  
                        ' class='boton'>Resolver</a></td>\n"
*/
//.$mini																





. "</tr>\n";
}

if (count($mis_casos) == 0) {
$HTML_mis_casos = "<tr class='filaTabla' style='vertical-align: middle'>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: center;' colspan='10'>\n"
. "    ** No hay derivaciones registradas **"
. "  </td>\n"
. "</tr>\n";
}
























$SQL_casos_no_plus = "
select 
atpr.id id, 
(select concat(a.nombres,' ', a.apellidos) nombre_alumno from alumnos a where a.id = atpr.id_alumno) as nombre_alumno,
(select u.id_unidad from usuarios u where id = atpr.id_usuario_origen) unidad_origen,
(select concat(u.nombre,' ', u.apellido) from usuarios u where u.id = atpr.id_usuario_origen) as nombre_usuario,
(select gu.nombre from gestion.unidades gu where gu.id = atpr.id_unidad_derivada)  derivado_nombre,                       
atpr.id_alumno id_alumno,
atpr.id_motivo id_motivo,
--(select concat(moti.clasificacion,': ',moti.nombre) from tipo_motivo_aux moti where moti.id = atpr.id_motivo) nombre_motivo,
(
  SELECT concat(
    (
      select b.clasificacion from tipo_motivo_clasif_proretencion b
      where b.id = a.id_clasificacion
    ),': <br>',a.nombre) AS nombre 
    FROM tipo_motivo_proretencion a 
    where a.id =  atpr.id_motivo                       
) nombre_motivo,
to_char(atpr.fecha,'dd/mm/yyyy') fecha,
to_char(atpr.fecha_derivacion,'dd/mm/yyyy') fecha_derivacion,   
--atpr.fecha fecha,
atpr.comentarios comentarios,
atpr.comentarios_derivado comentarios_derivado,
atpr.tipo_contacto tipo_contacto,
atpr.respuesta_contacto respuesta_contacto,                      
atpr.resuelto resuelto,
atpr.id_unidad_derivada id_unidad_derivada,
(select nombre from gestion.unidades where id = atpr.id_unidad_derivada) glosa_derivado ,
atpr.comentarios comentarios
from atenciones_proretencion atpr
where 
resuelto = 'f' 
and (resuelto_derivado = 'f' or resuelto_derivado is null)
and (
(
(select u.id_unidad from usuarios u where id = atpr.id_usuario_origen) <> $id_unidad) 
or (atpr.id_unidad_derivada <> $id_unidad)
)

order by atpr.fecha desc, atpr.id desc
";


$casos_no_plus = consulta_sql($SQL_casos_no_plus);
$HTML_casos_no_plus = "";

for ($x=0;$x<count($casos_no_plus);$x++) {
extract($casos_no_plus[$x]);


//echo("<br>que ssss : $id");
//$compromiso_color = (strtotime($fecha_comp) > time()) ? "green" : "red";

if ($resuelto=='t') {
$resueltoFinal = "<span style='color: green'>Sí</span>";
} else {
$resueltoFinal = "<span style='color: red'>No</span>";
}
if ($respuesta_contacto=='t') {
$respuesta_contactoFinal = "<span style='color: green'>Contactado</span>";

} else {
$respuesta_contactoFinal = "<span style='color: red'>Sin Respuesta</span>";
}
/*
if ($unidad_origen == $id_unidad_derivada) {
$mini = "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=NO
&comentarios_derivado=$comentarios_derivado  
&es_mi_caso=SI
' class='boton'>Resolver</a></td>\n";
} else {
$mini = "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=SI
&comentarios_derivado=$comentarios_derivado
&es_mi_caso=NO  
' class='boton'>Ver</a></td>\n";
}
*/
if ($unidad_origen == $id_unidad_derivada) {
$mini = "<a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=NO
&comentarios_derivado=$comentarios_derivado  
&es_mi_caso=SI
'>$nombre_motivo</a>";
} else {
/* 
$mini = "<a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=SI
&comentarios_derivado=$comentarios_derivado
&es_mi_caso=NO  
'>$nombre_motivo</a>";
*/
$mini = "<a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=NO
&comentarios_derivado=$comentarios_derivado
&es_mi_caso=NO  
'>$nombre_motivo</a>";
}


$HTML_casos_no_plus .= "<tr class='filaTabla' style='vertical-align: middle'>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$id</td>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: center;'><small>$fecha</small></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$nombre_alumno</small></td>\n"
//. "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$nombre_usuario</small></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='left'>$mini</td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='center'>$tipo_contacto<br><small>$respuesta_contactoFinal</small></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$nombre_usuario</small></td>\n"
//. "  <td class='textoTabla' style='vertical-align: middle' align='center'>$respuesta_contactoFinal</td>\n"
//. "  <td class='textoTabla' style='vertical-align: middle' align='center'>$resueltoFinal</td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='left'>$glosa_derivado</td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='center'>$fecha_derivacion</td>\n"
/*
. "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
                href='$enlbase=registro_atenciones_agregar_new_derivado
                &id_tabla=$id
                &id_alumno=$id_alumno
                &id_motivo=$id_motivo
                &tipo_contacto=$tipo_contacto
                &obtiene_respuesta=$respuesta_contacto
                &obtiene_resuelto=$resuelto
                &comentarios=$comentarios
                &id_area_derivacion=$id_unidad_derivada	
                &modo_ver=NO 	
                &comentarios_derivado=$comentarios_derivado  
                ' class='boton'>Resolver</a></td>\n"
*/
//.$mini																





. "</tr>\n";
}

if (count($casos_no_plus) == 0) {
$HTML_casos_no_plus = "<tr class='filaTabla' style='vertical-align: middle'>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: center;' colspan='10'>\n"
. "    ** No hay derivaciones registradas **"
. "  </td>\n"
. "</tr>\n";
}



$SQL_tabla_completa_no_resueltos = "COPY ($SQL_casos_no_plus) to stdout WITH CSV HEADER";
//$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$id_sesion_no_resueltos = $_SESSION['usuario']."_atenciones_proretencion_resueltos_de_los_demas_".session_id();
$boton_tabla_completa_no_resueltos = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion_no_resueltos');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch_no_resueltos = "sql-fulltables/$id_sesion_no_resueltos.sql";
file_put_contents($nombre_arch_no_resueltos,$SQL_tabla_completa_no_resueltos);

























/*
        $SQL_urgentes = "
        select 
        atpr.id id,
        (select u.id_unidad from usuarios u where id = atpr.id_usuario_origen) unidad_origen,
        atpr.id_usuario_origen id_usuario_origen,
        (select concat(u.nombre,' ', u.apellido) from usuarios u where u.id = atpr.id_usuario_origen) as nombre_usuario,
        (select gu.nombre from gestion.unidades gu where gu.id = atpr.id_unidad_derivada)  derivado_nombre, 
        atpr.id_alumno id_alumno,
        atpr.id_motivo id_motivo,
        (select concat(moti.clasificacion,': ',moti.nombre) from tipo_motivo_aux moti where moti.id = atpr.id_motivo) nombre_motivo,
        atpr.fecha fecha,
        atpr.comentarios comentarios,
        atpr.comentarios_derivado comentarios_derivado,
        atpr.tipo_contacto tipo_contacto,
        atpr.respuesta_contacto respuesta_contacto,                      
        atpr.resuelto resuelto,
        atpr.id_unidad_derivada id_unidad_derivada,
        (select nombre from gestion.unidades where id = atpr.id_unidad_derivada) glosa_derivado ,
atpr.comentarios comentarios
        from atenciones_proretencion atpr
        where 
resuelto = 'f' 
and (resuelto_derivado = 'f' or resuelto_derivado is null)
and fecha <= (current_date - INTERVAL '$dias_urgencia day') 
        order by atpr.fecha , atpr.id desc
        ";

//echo("<br>$SQL_no_resueltos");
$no_resueltos_urgentes = consulta_sql($SQL_urgentes);
$HTML_urgentes = "";

for ($x=0;$x<count($no_resueltos_urgentes);$x++) {
extract($no_resueltos_urgentes[$x]);


//echo("<br>que ssss : $id");
//$compromiso_color = (strtotime($fecha_comp) > time()) ? "green" : "red";

if ($resuelto=='t') {
$resueltoFinal = "<span style='color: green'>Sí</span>";
} else {
$resueltoFinal = "<span style='color: red'>No</span>";
}
if ($respuesta_contacto=='t') {
$respuesta_contactoFinal = "<span style='color: red'>Contactado</span>";

} else {
$respuesta_contactoFinal = "<span style='color: red'>Sin Respuesta</span>";
}


if ($unidad_origen == $id_unidad_derivada) {
  $mini = "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
  href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=NO
&comentarios_derivado=$comentarios_derivado  
' class='boton'>Resolver</a></td>\n";
} else {
  $mini = "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=SI
&comentarios_derivado=$comentarios_derivado  
' class='boton'>Ver</a></td>\n";
}
$HTML_urgentes .= "<tr class='filaTabla' style='vertical-align: middle'>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: right;'><span style='color: red'>$id</span></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: center;'><small><span style='color: red'>$fecha</span></small></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small><span style='color: red'>$nombre_usuario</span></small></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='left'><span style='color: red'>$nombre_motivo</span></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='center'><span style='color: red'>$tipo_contacto</span></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='center'><span style='color: red'>$respuesta_contactoFinal</span></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='center'><span style='color: red'>$resueltoFinal</span></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle'><span style='color: red'>$glosa_derivado</span></td>\n"
.$mini




. "</tr>\n";
}

if (count($no_resueltos_urgentes) == 0) {
$HTML_urgentes = "<tr class='filaTabla' style='vertical-align: middle'>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: center;' colspan='10'>\n"
. "    ** No hay derivaciones registradas **"
. "  </td>\n"
. "</tr>\n";
}

*/














/*
$SQL_no_resueltos = "
                      select 
                      atpr.id id, 
                      (select u.id_unidad from usuarios u where id = atpr.id_usuario_origen) unidad_origen,
                      (select concat(u.nombre,' ', u.apellido) from usuarios u where u.id = atpr.id_usuario_origen) as nombre_usuario,
                      (select gu.nombre from gestion.unidades gu where gu.id = atpr.id_unidad_derivada)  derivado_nombre,                       
                      atpr.id_alumno id_alumno,
                      atpr.id_motivo id_motivo,
                      (select concat(moti.clasificacion,': ',moti.nombre) from tipo_motivo_aux moti where moti.id = atpr.id_motivo) nombre_motivo,
                      atpr.fecha fecha,
                      atpr.comentarios comentarios,
                      atpr.comentarios_derivado comentarios_derivado,
                      atpr.tipo_contacto tipo_contacto,
                      atpr.respuesta_contacto respuesta_contacto,                      
                      atpr.resuelto resuelto,
                      atpr.id_unidad_derivada id_unidad_derivada,
                      (select nombre from gestion.unidades where id = atpr.id_unidad_derivada) glosa_derivado ,
					  atpr.comentarios comentarios
                      from atenciones_proretencion atpr
                      where 
            resuelto = 'f' 
					  and (resuelto_derivado = 'f' or resuelto_derivado is null)
            and fecha > (current_date - INTERVAL '$dias_urgencia day') 
                      order by atpr.fecha desc, atpr.id desc
                      ";

					//echo("<br>$SQL_no_resueltos");
$no_resueltos = consulta_sql($SQL_no_resueltos);
$HTML = "";

for ($x=0;$x<count($no_resueltos);$x++) {
	extract($no_resueltos[$x]);


//echo("<br>que ssss : $id");
  //$compromiso_color = (strtotime($fecha_comp) > time()) ? "green" : "red";

  if ($resuelto=='t') {
        $resueltoFinal = "<span style='color: green'>Sí</span>";
  } else {
    $resueltoFinal = "<span style='color: red'>No</span>";
  }
  if ($respuesta_contacto=='t') {
      $respuesta_contactoFinal = "<span style='color: green'>Contactado</span>";
      
  } else {
    $respuesta_contactoFinal = "<span style='color: red'>Sin Respuesta</span>";
  }

  if ($unidad_origen == $id_unidad_derivada) {
    $mini = "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
    href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=NO
&comentarios_derivado=$comentarios_derivado  
&es_mi_caso=SI
' class='boton'>Resolver</a></td>\n";
  } else {
    $mini = "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=SI
&comentarios_derivado=$comentarios_derivado
&es_mi_caso=NO  
' class='boton'>Ver</a></td>\n";
  }
  


	$HTML .= "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$id</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;'><small>$fecha</small></td>\n"
      . "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$nombre_usuario</small></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='left'>$nombre_motivo</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$tipo_contacto</td>\n"
      . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$respuesta_contactoFinal</td>\n"
      . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$resueltoFinal</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle'>$glosa_derivado</td>\n"
		
      .$mini																

 



		  . "</tr>\n";
}

if (count($no_resueltos) == 0) {
	$HTML = "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;' colspan='10'>\n"
		  . "    ** No hay derivaciones registradas **"
		  . "  </td>\n"
		  . "</tr>\n";
}

*/






/*
$SQL_resueltos = "
                      select 
                      atpr.id id,
                      (select concat(a.nombres,' ', a.apellidos) nombre_alumno from alumnos a where a.id = atpr.id_alumno) as nombre_alumno,
                      (select u.id_unidad from usuarios u where id = atpr.id_usuario_origen) unidad_origen,
                      (select concat(u.nombre,' ', u.apellido) from usuarios u where u.id = atpr.id_usuario_origen) as nombre_usuario,
                      (select gu.nombre from gestion.unidades gu where gu.id = atpr.id_unidad_derivada)  derivado_nombre, 
                      atpr.id_alumno id_alumno,
                      atpr.id_motivo id_motivo,
                      --(select concat(moti.clasificacion,': ',moti.nombre) from tipo_motivo_aux moti where moti.id = atpr.id_motivo) nombre_motivo,
                      (
                        SELECT concat(
                          (
                            select b.clasificacion from tipo_motivo_clasif_proretencion b
                            where b.id = a.id_clasificacion
                          ),': <br>',a.nombre) AS nombre 
                          FROM tipo_motivo_proretencion a 
                          where a.id =  atpr.id_motivo                       
                      ) nombre_motivo,                      
                      atpr.fecha fecha,
                      atpr.comentarios comentarios,
                      atpr.comentarios_derivado comentarios_derivado,
                      atpr.tipo_contacto tipo_contacto,
                      atpr.respuesta_contacto respuesta_contacto,

                      (
                        case 
                        when (atpr.resuelto = 't') then atpr.resuelto
                        else atpr.resuelto_derivado
                        end) as resuelto,
--to_char(to_timestamp(coalesce(atpr.fecha_derivado, atpr.fecha),'yyyymmdd HH24:MI:SS'),'dd/mm/yyyy HH24:MI:SS') fecha_resuelto,
                        to_char(coalesce(atpr.fecha_derivado, atpr.fecha),'dd/mm/yyyy') fecha_resuelto,
					  --coalesce(atpr.fecha_derivado, atpr.fecha) fecha_resuelto,
                      atpr.id_unidad_derivada id_unidad_derivada,
                      (select nombre from gestion.unidades where id = atpr.id_unidad_derivada) glosa_derivado 
                      from atenciones_proretencion atpr
                      where 
  --atpr.id_unidad_derivada = $id_unidad
   (resuelto = 'f' and resuelto_derivado = 't')
   or (resuelto = 't')
                      order by atpr.fecha desc, atpr.id desc
                      ";
*/

                      $SQL_resueltos = "
                      select 
                      atpr.id id,
                      (select concat(a.nombres,' ', a.apellidos) nombre_alumno from alumnos a where a.id = atpr.id_alumno) as nombre_alumno,
                      (select u.id_unidad from usuarios u where id = atpr.id_usuario_origen) unidad_origen,
                      (select concat(u.nombre,' ', u.apellido) from usuarios u where u.id = atpr.id_usuario_origen) as nombre_usuario,
                      (select gu.nombre from gestion.unidades gu where gu.id = atpr.id_unidad_derivada)  derivado_nombre, 
                      atpr.id_alumno id_alumno,
                      atpr.id_motivo id_motivo,
                      (
                        SELECT concat(
                          (
                            select b.clasificacion from tipo_motivo_clasif_proretencion b
                            where b.id = a.id_clasificacion
                          ),': <br>',a.nombre) AS nombre 
                          FROM tipo_motivo_proretencion a 
                          where a.id =  atpr.id_motivo                       
                      ) nombre_motivo,                      
                      atpr.fecha fecha,
                      atpr.comentarios comentarios,
                      atpr.comentarios_derivado comentarios_derivado,
                      atpr.tipo_contacto tipo_contacto,
                      atpr.respuesta_contacto respuesta_contacto,

                      (
                        case 
                        when (atpr.resuelto = 't') then atpr.resuelto
                        else atpr.resuelto_derivado
                        end) as resuelto,
                        to_char(coalesce(atpr.fecha_derivado, atpr.fecha),'dd/mm/yyyy') fecha_resuelto,
                      atpr.id_unidad_derivada id_unidad_derivada,
                      (select nombre from gestion.unidades where id = atpr.id_unidad_derivada) glosa_derivado 
                      from atenciones_proretencion atpr
                      where 
   (resuelto = 'f' and resuelto_derivado = 't')
   or (resuelto = 't')
                      order by atpr.fecha desc, atpr.id desc
                      ";

					  //echo("<br>$SQL_remat_atenciones");
$resueltos = consulta_sql($SQL_resueltos);
$HTML_resueltos = "";
for ($x=0;$x<count($resueltos);$x++) {
	extract($resueltos[$x]);
//echo("<br>que ssss : $id");
  //$compromiso_color = (strtotime($fecha_comp) > time()) ? "green" : "red";

  if ($resuelto=='t') {
        $resueltoFinal = "<span style='color: green'>Sí</span>";
  } else {
    $resueltoFinal = "<span style='color: red'>No</span>";
  }
  if ($respuesta_contacto=='t') {
      $respuesta_contactoFinal = "<span style='color: green'>Contactado</span>";
      
  } else {
    $respuesta_contactoFinal = "<span style='color: red'>Sin Respuesta</span>";
  }

$mini = "<a id='sgu_fancybox' 
href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=SI
&comentarios_derivado=$comentarios_derivado
'>$nombre_motivo</a>"
;


	$HTML_resueltos .= "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$id</td>\n"
		  //. "  <td class='textoTabla' style='vertical-align: middle; text-align: center;'><small>$fecha</small></td>\n"
      . "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$nombre_alumno</small></td>\n"
      //. "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$nombre_usuario</small></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='left'>$mini</td>\n"
      . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$tipo_contacto<br><small>$respuesta_contactoFinal</small></td>\n"
      . "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$nombre_usuario</small></td>\n"
      //. "  <td class='textoTabla' style='vertical-align: middle' align='center'>$respuesta_contactoFinal</td>\n"
     // . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$resueltoFinal</td>\n"
		 // . "  <td class='textoTabla' style='vertical-align: middle'>$glosa_derivado</td>\n"
		 . "  <td class='textoTabla' style='vertical-align: middle' align='center'><small>$fecha_resuelto</small></td>\n"
		 
/*
     . "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
     href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=SI
&comentarios_derivado=$comentarios_derivado
' class='boton'>Ver</a></td>\n"
*/
. "</tr>\n";
}

if (count($resueltos) == 0) {
	$HTML_resueltos = "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;' colspan='10'>\n"
		  . "    ** No hay derivaciones registradas **"
		  . "  </td>\n"
		  . "</tr>\n";
}


$SQL_tabla_completa = "COPY ($SQL_resueltos) to stdout WITH CSV HEADER";
//$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$id_sesion = $_SESSION['usuario']."_atenciones_proretencion_resueltos_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

?>

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' action='principal.php' method='get'>
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>
<!--
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes Personales del Estudiante</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php //echo($id_alumno); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php //echo($rut); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php //echo($nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Teléfono:</td>
    <td class='celdaValorAttr'><?php //echo($telefono); ?></td>
    <td class='celdaNombreAttr'>Tel. Móvil:</td>
    <td class='celdaValorAttr'><?php //echo($tel_movil); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr' colspan="3"><?php //echo($email); ?></td>
  </tr>
</table>
-->
<!--
<table class="tabla" style="margin-top: 5px">
  <tr>
	  
	<td class='celdaFiltro'>
	  Acciones:<br>
      <input type='button' onClick="window.location='<?php //echo("$enlbase_sm=registro_atenciones_agregar_new&id_alumno=$id_alumno"); ?>';" value="Agregar">
    </td>
    
  	<td class='celdaFiltro'>
      Periodo:<br>
      <select class="filtro" name="periodo" onChange="submitform();">
        <?php //echo(select($PERIODOS_REMAT,$periodo)); ?>
      </select>
  	</td>

  </tr>
</table>
-->
<table class="tabla" style="margin-top: 5px">
  <tr>
	<td class='celdaFiltro'>
	  Acciones:<br>
<!--      <input type='button' onClick="window.location='<?php echo("$enlbase_sm=registro_atenciones_agregar_new&id_alumno=$id_alumno"); ?>';" value="Agregar"> -->
      <input type="button" name="mantenedor_motivos" value="Gestionar Problemáticas" onclick="window.location='principal.php?modulo=crud_motivos_proretencion';">
    </td>
  </tr>
</table>
<!--
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="button" name="mantenedor_clasificaciones" value="Edita Clasificaciones" onclick="window.location='principal.php?modulo=crud_clasificaciones_proretencion';"></td>
    <td class="tituloTabla"><input type="button" name="mantenedor_motivos" value="Edita Motivos" onclick="window.location='principal.php?modulo=crud_motivos_proretencion';"></td>
  </tr>
</table>
-->
<br>




<!--
<div class="texto">
  Mis Casos :
</div>
-->


<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr>
    <td class="tituloTabla" colspan=8 style="text-align:center">
      Mis Casos
    </td>
  <tr>

  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style="color: gray">ID</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Alumno</td>
<!--    <td class='tituloTabla'>Usuario</td>-->
    <td class='tituloTabla'>Problemática</td> <!--motivo-->
    <td class='tituloTabla'>Tipo</td> <!-- tipo contacto -->
    <td class='tituloTabla'>Informante</td>
<!--    <td class='tituloTabla'>Respuesta</td>-->
<!--    <td class='tituloTabla'>Resuelto</td>
    <td class='tituloTabla'>Derivación</td>-->
<!--	<td class='tituloTabla'>Acción</td> -->
  </tr>
  <?php echo($HTML_mis_casos); ?>
</table>
</br></br>

<!--
<div class="texto">
  Casos no resueltos de los demás :
</div>
-->
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
<tr>
  <td class="tituloTabla" colspan=8 style="text-align:center">
    Casos no resueltos de los demás &nbsp; &nbsp;
    <?php echo($boton_tabla_completa_no_resueltos); ?>
    </td>
  <tr>

  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style="color: gray">ID</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Alumno</td>
<!--    <td class='tituloTabla'>Usuario</td> -->
    

    <td class='tituloTabla'>Problemática</td><!--motivo-->
    <td class='tituloTabla'>Tipo</td> <!-- tipo contacto -->
    <td class='tituloTabla'>Informante</td>
<!--    <td class='tituloTabla'>Respuesta</td>-->
    <!--<td class='tituloTabla'>Resuelto</td>-->
    <td class='tituloTabla'>Derivación</td>
    <td class='tituloTabla'>Fecha Derivación</td>
<!--	<td class='tituloTabla'>Acción</td> -->
  </tr>
  <?php echo($HTML_casos_no_plus); ?>
</table>
</br></br>

<!--

<div class="texto">
  Casos Urgentes :
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style="color: gray">ID</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Usuario</td>
    <td class='tituloTabla'>Motivo</td>
    <td class='tituloTabla'>Tipo Contacto</td>
    <td class='tituloTabla'>Respuesta</td>
    <td class='tituloTabla'>Resuelto</td>
    <td class='tituloTabla'>Derivado a:</td>
	<td class='tituloTabla'>Acción</td>
  </tr>
  <?php //echo($HTML_urgentes); ?>
</table>
-->
<!--
</br></br>
<div class="texto">
  Casos no resueltos :
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style="color: gray">ID</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Usuario</td>
    <td class='tituloTabla'>Motivo</td>
    <td class='tituloTabla'>Tipo Contacto</td>
    <td class='tituloTabla'>Respuesta</td>
    <td class='tituloTabla'>Resuelto</td>
    <td class='tituloTabla'>Derivado a:</td>
	<td class='tituloTabla'>Acción</td>
  </tr>
  <?php //echo($HTML); ?>
</table>


</br></br>
-->
<!--
<div class="texto">
  Casos resueltos :
</div>
-->
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
<td class="tituloTabla" colspan=8 style="text-align:center">
    Casos resueltos &nbsp; &nbsp;
    <?php echo($boton_tabla_completa); ?>
    </td>
  <tr>

  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style="color: gray">ID</td>
<!--    <td class='tituloTabla'>Fecha</td> -->
    <td class='tituloTabla'>Alumno</td>
<!--    <td class='tituloTabla'>Usuario</td> -->
    <td class='tituloTabla'>Problemática</td><!--motivo-->
    <td class='tituloTabla'>Tipo</td> <!-- tipo contacto -->
    <td class='tituloTabla'>Informante</td>
<!--    <td class='tituloTabla'>Respuesta</td> -->
    <!--<td class='tituloTabla'>Resuelto</td>-->
	<td class='tituloTabla'>Fecha Resolución</td>
    <!--<td class='tituloTabla'>...</td>-->
  </tr>
  <?php echo($HTML_resueltos); ?>
</table>



</form>


<!-- Fin: <?php //echo($modulo); ?> -->

<script>
	
	function mostrarPantallaDerivar(url) {
		alert("he entrado");
		var pSaltar = "http://" + window.location.hostname + ":" + window.location.port + "/sgu/" + url;
		console.log(pSaltar);
		window.location.href = pSaltar;
		//console.log(url);
	}

</script>

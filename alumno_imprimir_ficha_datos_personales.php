<html>
  <head>
      <link href="sgu.css" rel="stylesheet" type="text/css">
  </head>
</head>
<body>
<?php

include("funciones.php");
$id_alumno = $_REQUEST['id_alumno'];
$vista     = $_REQUEST['vista'];

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.genero,va.fec_nac,va.nacionalidad,coalesce(va.pasaporte,'**No corresponde**') AS pasaporte,
                      va.direccion,va.comuna,va.region,va.telefono,coalesce(va.tel_movil,'** No se registra **') AS tel_movil,va.email,va.admision,
                      coalesce(va.semestre_cohorte,0) AS semestre_cohorte,va.cohorte,c.nombre AS carrera,va.malla_actual,va.estado,va.id_malla_actual,
                      CASE WHEN a.jornada='D' THEN 'Diurna' WHEN a.jornada='V' THEN 'Vespertina' END as jornada,
                      CASE WHEN a.moroso_financiero THEN 'Moroso' ELSE 'Al día' END as moroso_financiero
               FROM vista_alumnos AS va
               LEFT JOIN alumnos  AS a USING (id)
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               WHERE a.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
extract($alumno[0]);
$HTML_datos_personales_alumno = datos_personales_alumno();
if ($_REQUEST['vista']=="") { $_REQUEST['vista']="avance_cronologico"; } 

switch ($_REQUEST['vista']) {
	case "avance_cronologico":
		$HTML_vista_rend_acad = avance_cronologico();
		break;
	case "avance_malla":
		$HTML_vista_rend_acad = avance_malla();
		break;
	case "homologaciones":
		$HTML_vista_rend_acad = vista_homologaciones();
		break;
	case "convalidaciones":
		$HTML_vista_rend_acad = vista_convalidaciones();
		break;
	case "examenes_con_rel":
		$HTML_vista_rend_acad = vista_examenes_con_rel();
		break;
}			
header("Content-Type: text/html; charset=UTF-8");

?>
<div>
 <h3>Ficha de Antecedentes del Alumno</h3>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <?php echo($HTML_datos_personales_alumno); ?>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
  <?php echo($HTML_vista_rend_acad); ?>
</table>
<script>
  window.print();
  history.back();
</script>
</body>
</html>

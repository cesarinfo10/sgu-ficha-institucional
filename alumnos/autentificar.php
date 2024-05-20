<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="es">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <link rel="shortcut icon" href="../img/logo_sgu.ico">
    <title>UMC - SGU</title>
    <script language="JavaScript1.2" src="../funciones.js"></script>
  </head>
  <body>
<?php

include("funciones.php");

$usuario  = $_REQUEST['nombre_usuario'];
$rut      = $_REQUEST['rut'];
$servidor = $_REQUEST['servidor'];
$modulo   = $_REQUEST['modulo'];

if ($_REQUEST['entrar'] <> "Entrar" && ($usuario == "" || $rut == "")) {
	header("Location: index.php");
}

$servidor = str_replace("correo.","",$servidor);
//$servidor = "al.umcervantes.cl";
if (stripos($usuario,"@") !== false)  { 
	$servidor = substr($usuario,stripos($usuario,"@")+1,strlen($usuario));
	switch ($servidor) {
		case "alumni.umc.cl":
			$servidor = "al.umcervantes.cl";
			break;
		case "postgrado.umc.cl":
			$servidor = "postgrado.umcervantes.cl";
			break;
	}
}

$SQL_alumno = "SELECT a.id,initcap(a.nombres||' '||a.apellidos) AS nombre,a.carrera_actual AS id_carrera,c.id_escuela,c.nombre AS carrera,
                      e.nombre AS escuela,malla_actual,ae.nombre AS estado,a.jornada,a.moroso_financiero,a.id_pap,a.cohorte,a.semestre_cohorte,m.niveles
               FROM alumnos AS a
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               LEFT JOIN escuelas AS e ON e.id=c.id_escuela
               LEFT JOIN al_estados AS ae ON ae.id=a.estado
			   LEFT JOIN mallas AS m ON m.id=a.malla_actual
               WHERE nombre_usuario='$usuario' AND servidor_nombre_usuario='$servidor' AND rut='$rut';";
$alumno = consulta_sql($SQL_alumno);

if (count($alumno) > 0) {
	$problemas = false;

	$id_alumno = $alumno[0]['id'];

	$SQL_comp_matric = "SELECT id FROM matriculas WHERE id_alumno=$id_alumno AND ano=$ANO AND semestre=$SEMESTRE";
	$comp_matric = consulta_sql($SQL_comp_matric);
	if (count($comp_matric) == 0 || $alumno[0]['estado'] <> "Vigente") {
		$msje = "ATENCIÓN: Nuestros registros indican que no has completado tu proceso de Matrícula "
		      . "para el periodo actual o no tienes estado de Vigente .\\n"
		      . "Por favor acercate a las oficinas de Contabilidad, en el 9º piso del efidicio para que "
		      . "realices este vital trámite.\\n"
		      . "De todas maneras, tendrás acceso a SGU"
		      . ", pero no podrás realizar la Inscripción de Asignaturas."
		      . "";
		echo(msje_js($msje));
		//$problemas = true;
		$modulo = "portada";
	}

	//chequeo del estado del alumno (moroso, vigente)..
	$estado_alumno     = $alumno[0]['estado'];
	$moroso_financiero = $alumno[0]['moroso_financiero'];
	
//	if ($estado_alumno <> "Vigente" || $moroso_financiero == "t") {
	if ($estado_alumno <> "Vigente") {
		$msje = "Actualmente no eres alumno regular o Vigente.\\n";

/*		
		if ($moroso_financiero == "t") {
			$SQL_morosidad = "SELECT sum(monto_moroso) AS monto_moroso,sum(cuotas_morosas) AS cuotas_morosas
			                  FROM vista_contratos AS vc 
			                  LEFT JOIN vista_contratos_rut AS vcr ON vcr.id=vc.id 
			                  WHERE vcr.rut='$rut' AND vc.estado IS NOT NULL";
			$morosidad     = consulta_sql($SQL_morosidad);
			$monto_moroso  = number_format($morosidad[0]['monto_moroso'],0,",",".");
			$cuotas_morosas = $morosidad[0]['cuotas_morosas'];
			
			$msje = "ATENCIÓN: Información no disponible por compromisos financieros pendientes con la UMC.\\n\\n "
			      . "Se encuentran $$monto_moroso en morosidad en $cuotas_morosas cuotas.\\n\\n"
			      . "Para normalizar este situación, por favor dirigite a la Oficina de Finanzas, en el noveno piso.";
		}
*/
		switch ($estado_alumno) {
/*
			case "Moroso":
				$msje = "Información no disponible por compromisos financieros pendientes con la UMC. "
				      . "Para normalizar este situación, por favor dirigite a la Oficina de Finanzas, en el noveno piso.";
				break;
*/ 
			case "Eliminado" || "Abandono":
				$msje_adic = "$msje Por favor dirigite a tu escuela, Piso 10.";
				break;
		}			
		echo(msje_js($msje));
		$problemas = true;
	}

	//chequeo de alumnos indocumentados
/*
	$SQL_al_indoc = "SELECT doc_adeudado FROM alumnos_indocumentados WHERE id_alumno='$id_alumno';";
	$al_indoc = consulta_sql($SQL_al_indoc);
	if (count($al_indoc) > 0) {
		$doc_adeudado_alumno = $al_indoc[0]['doc_adeudado'];
		$msje = "Actualmente nuestros registros indican que no has presentado toda la documentación requerida.\\n"
		      . "Por favor acércate a las oficinas de Registro Académico para regularizar tu situación.\\n"
		      . "La documentación adeudada es: $doc_adeudado_alumno";
		echo(msje_js($msje));
		$problemas = true;
	}
*/
	if ($problemas) {
		echo(js("window.location='https://www.umcervantes.cl';"));
		exit;
	}

	session_start();
	$_SESSION['autentificado'] = true;
	$_SESSION['usuario']       = $usuario;
	$_SESSION['nombre_alumno'] = $alumno[0]['nombre'];
	$_SESSION['id']            = $alumno[0]['id'];
	$_SESSION['id_pap']        = $alumno[0]['id_pap'];
	$_SESSION['malla_actual']  = $alumno[0]['malla_actual'];
	$_SESSION['id_carrera']    = $alumno[0]['id_carrera'];
	$_SESSION['cohorte']           = $alumno[0]['cohorte'];
	$_SESSION['semestre_cohorte']  = $alumno[0]['semestre_cohorte'];
	$_SESSION['id_escuela']        = $alumno[0]['id_escuela'];
	$_SESSION['carrera']           = $alumno[0]['carrera'];
	$_SESSION['escuela']           = $alumno[0]['escuela'];
	$_SESSION['jornada']           = $alumno[0]['jornada'];
	$_SESSION['moroso_financiero'] = $alumno[0]['moroso_financiero'];
	$_SESSION['enlace_volver'] = "";
	//header("Location: principal.php?modulo=$modulo");

	$cohortes_pruebas_psico = array($ANO,$ANO-2,$ANO-4,$ANO-5);

// Validación de pruebas sinergia contestadas
	$SQL_pruebas_psico = "SELECT id FROM sinergia.respuestas WHERE ano=$ANO AND semestre=$SEMESTRE AND rut_alumno='$rut'";
	if ($alumno[0]['niveles'] >= 8 && count(consulta_sql($SQL_pruebas_psico)) < 2 && in_array($alumno[0]['cohorte'],$cohortes_pruebas_psico)) {
		$msje = "AVISO: Aún no has terminado con el proceso de Pruebas Psicométricas que debes contestar en este periodo.\\n\\n"
		      . "Por favor, pincha en «Aceptar» para contestar las pruebas que tienes pendientes.";
		$url_si = "https://sgu.umc.cl/sgu/sinergia/index.php?rut=$rut";
		$url_no = "https://sgu.umc.cl/sgu/alumnos/principal.php?modulo=$modulo";
		echo(confirma_js($msje,$url_si,$url_no));
		//echo(msje_js($msje));
		echo(js("window.location='$url_si';"));
		exit;
	}

	echo(js("window.location='principal.php?modulo=$modulo'"));
} else {
	$msje = " Ha ocurrido un error en el ingreso.\\n Por favor intentalo nuevamente.";
	echo(msje_js($msje));
	echo(js("window.locarion='http://www.umcervantes.cl/'"));
	exit;	
}
	
?>
</body>

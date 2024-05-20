<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
$id_profesor = $_REQUEST['id'];
if (!is_numeric($id_profesor)) {
	echo(js("location.href='principal.php?modulo=gestion_profesores';"));
	exit;
}

$SQL_profepost = "SELECT pp.id,pp.rut,pp.apellidos,pp.nombres,p.nacionalidad,pp.estado_civil,pp.ciudad_nac,
                         CASE pp.genero WHEN 'm' THEN 'Masculino' WHEN 'f' THEN 'Femenino' END AS genero,pp.profesion,
                         to_char(pp.fec_nac,'DD-tmMon-YYYY') AS fec_nac,pp.tel_fijo,pp.tel_movil,pp.email,
                         ga.nombre AS est_grado_acad,to_char(pp.est_fec_obtencion,'DD-tmMon-YYYY') as est_fec_obtencion,
                         pp.est_inst,pp.est_grado_titulo,pp.est_inst_pais,pp.emp_inst,pp.emp_periodo,pp.emp_cargo,pp.emp_funciones,
                         pp.pub_titulo,pp.pub_ano,pp.pub_editorial,pp.como_conoce,pp.porque_trabajar,pp.carreras,pp.horarios
                  FROM portalweb.profes_post AS pp
                  LEFT JOIN grado_acad       AS ga ON ga.id=pp.est_grado_acad
                  LEFT JOIN pais             AS p  ON p.localizacion=pp.nacionalidad
                  WHERE pp.id=$id_profesor;";
$profepost = consulta_sql($SQL_profepost);
if (count($profepost) == 0) {
	exit;
} else {
	extract($profepost[0]);
	if (!empty($carreras)) {
		$carreras = consulta_sql("SELECT nombre FROM carreras WHERE id IN ($carreras)");
		$HTML_carreras = "";
		for($x=0;$x<count($carreras);$x++) { $HTML_carreras .= "- {$carreras[$x]['nombre']} <br>"; }
		$carreras = $HTML_carreras;
	} else {
		$carreras = "** No se ha indicado **";
	}
	
	if (!empty($horarios)) {
		$dias_palabra = array(array('id'=>1,'nombre'=>"Lunes",    'cod'=>"Lun"),
		                      array('id'=>2,'nombre'=>"Martes",   'cod'=>"Mar"),
		                      array('id'=>3,'nombre'=>"Miércoles",'cod'=>"Mie"),
		                      array('id'=>4,'nombre'=>"Jueves",   'cod'=>"Jue"),
		                      array('id'=>5,'nombre'=>"Viernes",  'cod'=>"Vie"),
		                      array('id'=>6,'nombre'=>"Sábado",   'cod'=>"Sáb"));
		$hor = consulta_sql("SELECT id,to_char(hora_inicio,'HH24:MI')||' ~ '||to_char(hora_fin,'HH24:MI') AS horario FROM horarios	WHERE id <> 'Ds'");

		$horarios = explode(",",$horarios);
		$HTML_horarios = "<table cellpadding='2' cellspacing='0' class='texto' style='font-size: 9px'>"
		               . "  <tr><td></td><td>Lun</td><td>Mar</td><td>Miŕ</td><td>Jue</td><td>Vie</td><td>Sáb</td></tr>";
		for($x=0;$x<count($hor);$x++) {
			$HTML_horarios .= "<tr><td style='border-top: 1px dotted #4C6082'>{$hor[$x]['horario']}</td>";
			for($dia=0;$dia<6;$dia++) {
				$dia_horario = $dias_palabra[$dia]['cod']." ".trim($hor[$x]['id']);
				if (in_array($dia_horario,$horarios)) { 
					$HTML_horarios .= "<td style='border-top: 1px dotted #4C6082' align='center' class='si'>✔</td>"; 
				} else { 
					$HTML_horarios .= "<td style='border-top: 1px dotted #4C6082' align='center' class='no'><small>✘</small></td>";
				}
			}
			$HTML_horarios .= "</tr>";
		}
		$HTML_horarios .= "</table>";
		$horarios = $HTML_horarios;
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style='margin-top: 5px'>
  <input type='button' onClick="window.open('ver_cv_profepost.php?id=<?php echo($id); ?>');" value='Descargar Currículum Vitae'>
</div>
<!--<table cellpadding="4" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: middle;">
      Acciones:<br>
      <?php
			echo("<a href='$enlbase=editar_profesor&id_profesor=$id_profesor' class='boton'>Editar</a> ");
			echo("<a href='$enlbase=profesor_adjuntar_cv&id_profesor=$id_profesor' class='boton'>Adjuntar Curriculum Vitae</a> ");
			echo("<a href='$mod_ant' class='boton'>Volver</a> ");
      ?>
    </td>
    <td class="celdaFiltro" style="vertical-align: middle;">
      Gestión:<br>
      <?php
			echo("<a href='$enlbase=gestion_ev_docente_profes&id_profesor=$id_profesor' class='boton'>Evaluación Docente</a> ");
			//echo("<a href='$enlbase=profesor_asignar_nombre_usuario&id_profesor=$id_profesor' class='boton'>Asignar Nombre de Usuario</a> ");
			echo("<a href='$enlbase=profesor_passwd&id_profesor=$id_profesor' class='boton'>Crear nueva Contraseña</a> ");
      ?>
    </td>
  </tr>
</table> -->
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Profesor</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($nombres.' '.$apellidos); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Género:</td>
    <td class='celdaValorAttr'><?php echo($genero); ?></td>
    <td class='celdaNombreAttr'>Estado Civil:</td>
    <td class='celdaValorAttr'><?php echo($estado_civil); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' nowrap>Fecha de nacimiento:</td>
    <td class='celdaValorAttr'><?php echo($fec_nac); ?></td>
    <td class='celdaNombreAttr' nowrap>Ciudad de Nacimiento:</td>
    <td class='celdaValorAttr'><?php echo($ciudad_nac); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nacionalidad:</td>
    <td class='celdaValorAttr'><?php echo($nacionalidad); ?></td>
    <td class='celdaNombreAttr' nowrap>Profesión:</td>
    <td class='celdaValorAttr'><?php echo($profesion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Email:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($email); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' nowrap>Tel. Fijo:</td>
    <td class='celdaValorAttr'><?php echo($tel_fijo); ?></td>
    <td class='celdaNombreAttr' nowrap>Tel. Móvil:</td>
    <td class='celdaValorAttr'><?php echo($tel_movil); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Estudios Realizados</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Institución:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($est_inst); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre del Título y/o Grado:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($est_grado_titulo); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo de Grado Académico:</td>
    <td class='celdaValorAttr'><?php echo($est_grado_acad); ?></td>
    <td class='celdaNombreAttr' nowrap>Fecha de obtención:</td>
    <td class='celdaValorAttr'><?php echo($est_fec_obtencion); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Ocupacionales</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Empleador o Institución:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($emp_inst); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($emp_periodo); ?></td>
    <td class='celdaNombreAttr' nowrap>Cargo:</td>
    <td class='celdaValorAttr'><?php echo($emp_cargo); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Funciones:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo(nl2br($emp_funciones)); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Publicaciones realizadas</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Título:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($pub_titulo); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Editorial:</td>
    <td class='celdaValorAttr'><?php echo($pub_editorial); ?></td>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr' nowrap><?php echo($pub_ano); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="2" style="text-align: center; ">Carrera(s) de Interés</td><td class='celdaNombreAttr' colspan="2" style="text-align: center; ">Disponibilidad Horaria</td></tr>
  <tr><td class='celdaValorAttr' colspan='2'><?php echo($carreras); ?><td class='celdaValorAttr' colspan='2'><?php echo($horarios); ?></td></td></tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Motivaciones</td></tr>
  <tr><td class='celdaNombreAttr' colspan="2" style="text-align: center; ">Cómo conoce a la UMC</td><td class='celdaNombreAttr' colspan="2" style="text-align: center; ">Por qué trabajar en la UMC</td></tr>
  <tr><td class='celdaValorAttr' colspan='2'><?php echo(nl2br($como_conoce)); ?></td><td class='celdaValorAttr' colspan='2'><?php echo(nl2br($porque_trabajar)); ?></td></tr>
  <tr><td class='celdaValorAttr' colspan='4' style='text-align: center'><input type='button' onClick="window.open('ver_cv_profepost.php?id=<?php echo($id); ?>');" value='Descargar Currículum Vitae'></td></tr>
</table>

<!-- Fin: <?php echo($modulo); ?> -->

<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}
/*
echo(msje_js("En mantenimiento. Deshabilitado"));
echo(js("history.back();")); 
exit;
*/
include("validar_modulo.php");

$mod_ant = $_SERVER['HTTP_REFERER'];
if ($mod_ant == "") { $mod_ant = "$enlbase=gestion_profesores"; }

$id_profesor = $_REQUEST['id_profesor'];
if (!is_numeric($id_profesor)) {
	echo(js("location.href='$mod_ant';"));
	exit;
}
$cod_cont    = $_REQUEST['cod_cont'];

$SQL_profesor = "SELECT vp.id,vp.rut,vp.nombre,vp.genero,vp.fec_nac,vp.direccion,vp.comuna,vp.region,
                        vp.telefono,vp.tel_movil,vp.email,vp.email_personal,vp.escuela,vp.nacionalidad,
                        vp.nombre_usuario,vp.grado_academico,vp.grado_acad_ano,vp.grado_acad_universidad,
                        vp.doc_fotocopia_ci,vp.doc_curriculum_vitae,vp.doc_certif_grado_acad,
                        CASE id_genero WHEN 'f' THEN 'Estimada' WHEN 'm' THEN 'Estimado' END AS vocativo
               FROM vista_profesores AS vp
               WHERE vp.id=$id_profesor;";
$profesor = consulta_sql($SQL_profesor);
if (count($profesor) == 0) {
	echo(js("location.href='$enlbase=gestion_profesores';")); 
	exit;
}

extract($profesor[0]);

if ($cod_cont == md5($nombre_usuario)) {

	$bdcon = pg_connect("dbname=auth_profesores" . $authbd);

	$bienvenida   = false;
	$profe_shadow = pg_fetch_all(pg_query($bdcon,"SELECT date_newtok FROM shadow WHERE login_name='$nombre_usuario';"));
	if ($profe_shadow[0]['date_newtok'] == "") {
		$bienvenida   = true;
	}		
	
	$SQL_profesor_pw = "UPDATE shadow 
	                    SET enc_password='',newtok=true,date_newtok=now()
	                    WHERE login_name='$nombre_usuario'";
	if (pg_affected_rows(pg_query($bdcon, $SQL_profesor_pw)) > 0) {
		
		$SQL_profesor_pw = "SELECT md5(login_name||date_newtok),
		                           to_char(date_newtok+'3 days','DD-MM-YYYY HH24:MI') as date_newtok
		                    FROM shadow
		                    WHERE login_name = '$nombre_usuario'";
		$profesor        = pg_fetch_all(pg_query($bdcon, $SQL_profesor_pw));
		$cod_ch          = $profesor[0]['md5'];
		$fecha_newtok    = $profesor[0]['date_newtok'];
		
		$url_cambio_passwd = "http://ugs.umcervantes.cl/sgu/passwd_ch.php?login_name=$nombre_usuario&cod_ch=$cod_ch&t=pr";
		
		$CR = "\r\n";
				
		$cabeceras = "From: SGU" . $CR
			       . "Content-Type: text/plain;charset=utf-8" . $CR;
		
		$asunto  = "Solicitud de cambio de contraseña de Acceso";
		if ($bienvenida) { $asunto  = "Bienvenido a la Universidad Miguel de Cervantes"; }
	
		$mensaje = "$vocativo $nombre,".$CR.$CR
		         . "Se ha generado una solicitud de cambio de contraseña para acceder a SGU.".$CR.$CR
		         . "Por lo tanto su actual contraseña ya no es válida y debe crear una nueva. Para realizar el cambio "
		         . "debe ingresar al siguiente enlace:".$CR.$CR
		         . $url_cambio_passwd . $CR.$CR
		         . "(Si al pinchar sobre el enlace no se abre su navegador, por favor copie el enlace, abra su "
		         . "navegador de internet y pegue el enlace en la barra de direcciones y luego presione ENTER).".$CR.$CR
		         . "Una vez que entre al enlace, se le pedirá que ingrese una nueva contraseña y su verificación "
		         . "correspondiente. Luego que complete el ingreso, se dará un mensaje de aviso indicando si se ha "
		         . "logrado establecer la nueva contraseña.".$CR.$CR
		         . "Esta solicitud de cambio de contraseña tiene vigencia hasta el $fecha_newtok. Si no realiza el "
		         . "cambio de contraseña antes de la fecha y hora indicados, tendrá que pedir una nueva solicitud de "
		         . "cambio de contraseña.".$CR.$CR
		         . "Saluda atentamente,".$CR
		         . "Departamento de Informática".$CR
		         . "Universidad Miguel de Cervantes";

		if ($bienvenida) {
			$mensaje = "$vocativo $nombre,".$CR.$CR
					 . "Le damos la bienvenida a la Universidad Miguel de Cervantes. "
					 . "El Departamento de Informática a través de este mensaje le informa sobre el "
					 . "acceso a SGU.".$CR.$CR
					 . "Se le ha asignado una cuenta con el nombre de usuario '$nombre_usuario'. A continuación "
					 . "debe crear una contraseña, para acceder a SGU. "
					 . "Por favor pinche en el siguiente enlace para establecer su contraseña:".$CR.$CR
					 . $url_cambio_passwd . $CR.$CR
					 . "(Si al pinchar sobre el enlace no se abre su navegador, por favor copie el enlace, abra su "
					 . "navegador de internet y pegue el enlace en la barra de direcciones y luego presione ENTER).".$CR.$CR
					 . "Una vez que entre al enlace, se le pedirá que ingrese una nueva contraseña y su respectiva verificación. "
					 . "Luego que complete el ingreso, se dará un mensaje de aviso indicando si se ha "
					 . "logrado establecer la nueva contraseña.".$CR.$CR
					 . "Este procedimiento tiene vigencia hasta el $fecha_newtok. Si no establece su "
					 . "contraseña antes de la fecha y hora indicados, tendrá que pedir una solicitud de "
					 . "creación de nueva contraseña, directamente en su Escuela.".$CR.$CR
					 . "Saluda atentamente,".$CR
					 . "Departamento de Informática".$CR
					 . "Universidad Miguel de Cervantes";
		}
		
		mail($email_personal,$asunto,$mensaje,$cabeceras);
		$email_gsuite = $nombre_usuario."@profe.umc.cl";
		mail($email_gsuite,$asunto,$mensaje,$cabeceras);
		echo(msje_js("Se ha enviado la solicitud de cambio de contraseña al email del profesor(a) a $email_personal"));
	} else {
		echo(msje_js("No se ha podido establecer el bit de nueva contraseña. "
		            ."Póngase en contacto con el Departamento de Informática"));
	}
	echo(js("location.href='$enlbase=ver_profesor&id_profesor=$id_profesor';"));
	exit;
}	

$problemas = false;
if ($email_personal == "") {
	echo(msje_js("Debe ingresar un email personal del profesor antes de continuar. "
	            ."Use el botón Editar para ingresar un email personal (si tiene acceso)"));
	$problemas = true;
}

if ($nombre_usuario == "") {	
	$login_sug = consulta_sql("SELECT nombre_usuario(nombre,apellido) AS login_sug,apellido FROM usuarios WHERE id=$id_profesor");
	if (count($login_sug) > 0) { 
		$login_sug = $login_sug[0]['login_sug'];
		if (consulta_dml("UPDATE usuarios SET nombre_usuario='$login_sug' WHERE id=$id_profesor") > 0) {
			consulta_dml("INSERT INTO permisos_apps SELECT $id_profesor,id_aplicacion FROM perfiles WHERE id_tipo_usuario=3");

			$gecos   = urlencode($nombre);
			$usuario = urlencode($login_sug);
			
			$datos = array("gecos"   => $nombre,
			               "usuario" => $login_sug);
			$datos = http_build_query($datos);
			
			$opciones = array('http' => array('method'  => 'POST',
			                                  'header'  => 'Content-type: application/x-www-form-urlencoded',
                                              'content' => $datos
                                             )
                             );
			$contexto = stream_context_create($opciones);

			$crear_usuario = file_get_contents('http://10.111.103.219/sumarusuario.php', false, $contexto);
			
			
			//$crear_usuario = file_get_contents(htmlentities("http://profe.umcervantes.cl/sumarusuario.php?usuario=$usuario&gecos=$gecos"));
			//$crear_usuario = file_get_contents("http://10.111.103.219/sumarusuario.php?".urlencode("usuario=$login_sug&gecos=$nombre"));
			
			if ($crear_usuario == "true") {
				echo(msje_js("Creado el buzón de correo y cuenta de usuario."));
				$nombre_usuario = $login_sug;
			} else {
				echo(msje_js("ERROR: No ha sido posible crear el usuario y el buzón."));
				$problemas = true;
			}
		} else {
			echo(msje_js("ERROR: No ha sido posible asignar un nombre de usuario.\\n\\n"
			            ."Debe asignarse un nombre de usuario al profesor antes de continuar. "
						."Por favor indique este error al Departamento de Informática."));
			$problemas = true;
		}
	} else {
		echo(msje_js("ERROR: No ha sido posible ejecutar una consulta de nombre de usuario."));
		$problemas = true;
	}
}

if ($problemas) {	            
	echo(js("location.href='$mod_ant';"));
	exit;
} else {
	$texto_confirmar = "Está a punto de enviar una solicitud de cambio de contraseña para este profesor.\\n\\n"
	                 . "Considere que la contraseña actual que el profesor tiene asignada, será borrada y "
	                 . "se le enviará un mensaje de correo electrónico a $email_personal "
	                 . "solicitando que establezca una nueva contraseña para acceder al SGU (y al correo electrónico).\\n\\n"
	                 . "¿Desea continuar?";
	$url_si = "$enlbase=$modulo&id_profesor=$id_profesor&cod_cont=".md5($nombre_usuario);
	$url_no = "$enlbase=ver_profesor&id_profesor=$id_profesor";
	echo(confirma_js($texto_confirmar,$url_si,$url_no));
}

?>

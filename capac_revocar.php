<?php
function existenRegistros($ano, 
//$id_asiscapac_origen, 
$id_capacitacion, 
$id_usuario_seleccionado) {

try {
$ss = "
select count(*) as cuenta from asiscapac_capacitaciones_funcionarios
where
ano = $ano 
and id_asiscapac_capacitaciones = $id_capacitacion
and id_usuario = $id_usuario_seleccionado
"; 



$sqlCuenta     = consulta_sql($ss);

//        echo("<br>".$ss);


extract($sqlCuenta[0]);
} catch (Exception $e) {
$cuenta = 0;
}

return $cuenta;

}




if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ano            = $_REQUEST['ano'];
$id_origen      = $_REQUEST['id_origen']; 
$id_campo_capacitaciones  = $_REQUEST['id_campo_capacitaciones'];
$id_estado_check  = $_REQUEST['id_estado_check'];
$id_usuario_confirmar  = $_REQUEST['id_usuario_confirmar'];
$confirmar  = $_REQUEST['confirmar'];
$observacion  = $_REQUEST['observacion'];
$observacionrevocar  = $_REQUEST['observacionrevocar'];
$param_observacion  = $_REQUEST['param_observacion'];
$param_observacionrevocar  = $_REQUEST['param_observacionrevocar'];


$ss = "
select observacion as db_observacion, observacion_revocar as db_observacionrevocar from asiscapac_capacitaciones_funcionarios
where
ano = $ano 
and id_asiscapac_capacitaciones = $id_campo_capacitaciones
and id_usuario = $id_usuario_confirmar
"; 

$sqlobs     = consulta_sql($ss);
extract($sqlobs[0]);

//echo("$ss <br>");
//echo("db_observacion= $db_observacion <br>");



/*
if ($confirmar == "SI") {
	$campo_obs = $observacion;
} else {
	$campo_obs = $observacionrevocar;
}
*/

$gg = $_REQUEST['guardar'];
//if ($gg = "Confirmar") {
//	$confirmar = "SI";
//}
//if ($gg = "Revocar") {
//	$confirmar = "NO";
//}

if ($confirmar == "SI") {
	$textoBoton = "Confirmar";
}
if ($confirmar =="NO") {
	$textoBoton = "Revocar";
}

if ($confirmar == "SI") {
	$campo_obs = $observacion;
} else {
	if ($confirmar == "NO") {
		$campo_obs = $observacionrevocar;
	} else {
		$campo_obs = "";
	}
}






//echo("ano = $ano <br>");
//echo("id_origen = $id_origen <br>");
//echo("id_campo_capacitaciones = $id_campo_capacitaciones <br>");
//echo("id_estado_check = $id_estado_check <br>");
//echo("id_usuario_confirmar = $id_usuario_confirmar <br>");
//echo("confirmar = $confirmar <br>");
//echo("guardar = $gg <br>");
//echo("textoBoton = $textoBoton <br>");



if (($gg == "Confirmar") || ($gg == "Revocar")){
	//if ($gg == $textoBoton) 
	{ 
//		echo("UNO");
		
		if ($confirmar <> "") {
//			echo("DOS");
			if ($id_usuario_confirmar <> "") {
//				echo("TRES");
				if ($gg == "Confirmar") {
					$confirmar = "SI";
					
				}
				if ($gg == "Revocar") {
					$confirmar = "NO";
				}
				//echo("gg = $gg, confirmar=$confirmar<br>");
			  $cuenta = existenRegistros($ano, 
							  //$id_asiscapac_origen, 
							  $id_campo_capacitaciones, 
							  $id_usuario_confirmar);
	//						  echo("CUATRO");
			  //echo("<br>cuenta = $cuenta");
			  //if ($confirmar == "SI") {
				//$bConfirmar = 't';
				//$sCampoObs = "observacion";
				//$sObs = $observacion;
			  //} else {
				//$bConfirmar = 'f';
				//$sCampoObs = "observacion_revocar";
				//$sObs = $observacionrevocar;
			  //}
			  if ($confirmar == "SI") {
							if ($cuenta==0) {
							
								//$fecha = date("Y-m-d");
								$SQL = "
								insert into asiscapac_capacitaciones_funcionarios
								(ano, 
								id_asiscapac_capacitaciones, 
								id_usuario, 
								convocado,
								confirmado,
								observacion,
								fecha_aceptacion
								) 
								(select 
								$ano, 
								$id_campo_capacitaciones, 
								id,
								't',
								't',
								'$observacion',
								now()
								from usuarios where id = $id_usuario_confirmar
								)
								;";
												echo("<br>$SQL");
								if (consulta_dml($SQL) > 0) {
									echo(msje_js("*Registro exitoso"));
									echo(js("parent.jQuery.fancybox.close();"));
						
								} else {
								}                  
						} else {
								$SQL = "
								update asiscapac_capacitaciones_funcionarios
								set 
								confirmado = 't',
								observacion = '$observacion',
								fecha_aceptacion = now()
								where 
								ano = $ano
								and id_asiscapac_capacitaciones = $id_campo_capacitaciones
								and id_usuario = $id_usuario_confirmar
								;"; 
												// echo("<br>$SQL");
								consulta_dml($SQL);      
								echo(msje_js("Registro exitoso"));
								echo(js("parent.jQuery.fancybox.close();"));
						
						}
				exit;
			  }
			  if ($confirmar == "NO") { //REVOCAR
				if ($cuenta==0) {
				
					//$fecha = date("Y-m-d");
					$SQL = "
					insert into asiscapac_capacitaciones_funcionarios
					(ano, 
					id_asiscapac_capacitaciones, 
					id_usuario, 
					convocado,
					confirmado,
					observacion_revocar,
					fecha_revocar
					) 
					(select 
					$ano, 
					$id_campo_capacitaciones, 
					id,
					't',
					'f',
					'$observacion',
					now()
					from usuarios where id = $id_usuario_confirmar
					)
					;";
									echo("<br>$SQL");
					if (consulta_dml($SQL) > 0) {
						echo(msje_js("*Registro exitoso"));
						echo(js("parent.jQuery.fancybox.close();"));
			
					} else {
					}                  
				} else {
						$SQL = "
						update asiscapac_capacitaciones_funcionarios
						set 
						confirmado = 'f',
						--observacion = null,
						observacion_revocar = '$observacion',
						fecha_revocar = now()
						where 
						ano = $ano
						and id_asiscapac_capacitaciones = $id_campo_capacitaciones
						and id_usuario = $id_usuario_confirmar
						;"; 
						//				 echo("<br>$SQL");
						consulta_dml($SQL);      
						echo(msje_js("Registro exitoso"));
						echo(js("parent.jQuery.fancybox.close();"));			
				}
	
			}
	
			  exit;
			}

			//	$aCampos = array("id_usuario_reg","tipo_act","prioridad","estado","id_unidad","actividad","id_proyecto","fecha_prog_termino","comentarios");
/*	
	if (!empty($_REQUEST['comentarios'])) { 
		$fecha_comentario = strftime("%A %e-%b-%Y a las %R");
		$_REQUEST['comentarios'] = "El $fecha_comentario, {$_SESSION['usuario']} escribió:\n\n"
		                         . $_REQUEST['comentarios']."\n"
		                         . "<hr>";
	}
	if (strtotime($_REQUEST['fecha_prog_termino']) < time()) { $_REQUEST['estado'] = "Pendiente"; }
	
	$SQLinsert = "INSERT INTO gestion.poas " . arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQLinsert) == 1) {
		email_nueva_tarea($_REQUEST['id_unidad'],$_REQUEST['actividad'],$_REQUEST['fecha_prog_termino']);
		echo(msje_js("Se ha creado una nueva tarea con los datos ingresados exitósamente"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
	*/

		  }
		  
	}
	
	
}
if ($param_observacion != "") {
	$observacion = $param_observacion;
}
if ($param_observacionrevocar != "") {
	$observacion = $param_observacionrevocar;
}


//echo("y???? observacion = $observacion");
//echo("gg = $gg");
?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>	
<!--<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME'])?>" method="post"> -->
<form name="formulario" action="principal_sm.php" method="get">
	

<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="ano" id="ano" value="<?php echo($ano);?>">
<input type="hidden" name="id_origen" id="id_origen" value="<?php echo($id_origen);?>">
<input type="hidden" name="id_campo_capacitaciones" id="id_campo_capacitaciones" value="<?php echo($id_campo_capacitaciones);?>">
<input type="hidden" name="id_estado_check" id="id_estado_check" value="<?php echo($id_estado_check);?>">
<input type="hidden" name="id_usuario_confirmar" id="id_usuario_confirmar" value="<?php echo($id_usuario_confirmar);?>">
<input type="hidden" name="confirmar" id="confirmar" value="<?php echo($confirmar);?>">

<?php

?>

<div style="margin-top: 5px">
  <!--<input type="submit" name="guardar" value="<?php echo($textoBoton); ?>"> -->
  <input type="submit" name="guardar" value="Confirmar">
  <input type="submit" name="guardar" value="Revocar">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <!--<tr><td class="celdaNombreAttr" colspan="4" style="text-align: center">Ingrese Observación</td></tr>-->
<!--
  <tr>
    <td class="celdaValorAttr" colspan="3">
	  <select class='filtro' name="id_proyecto" style="max-width: none">
		<option value="">Ninguno</option>
		<optgroup label="Mis Proyectos">
		  <?php echo(select($MIS_PROYECTOS,$_REQUEST[0]['id_proyecto'])); ?>
		</optgroup>
		<optgroup label="Proyectos de otros">
          <?php echo(select($OTROS_PROYECTOS,$_REQUEST[0]['id_proyecto'])); ?>
		</optgroup>
	  </select>
	</td>
  </tr>
-->

  <tr>
    <td class="celdaNombreAttr">Observación</td>
    <td class="celdaValorAttr" colspan="3">
      <textarea name="observacion" cols="40" rows="20" class="general"><?php echo($observacion); ?></textarea>
	</td>
  </tr>

</table>
</form>

<?php

function email_nueva_tarea($id_unidad,$actividad,$fecha_prog_termino) {
	
	$fecha_prog_termino = strftime("%A %e-%b-%Y",strtotime($fecha_prog_termino));
	
	$unidad = consulta_sql("SELECT nombre FROM gestion.unidades WHERE id=$id_unidad");
	$unidad = $unidad[0]['nombre'];
	
	$usuarios = consulta_sql("SELECT email FROM usuarios WHERE id_unidad=$id_unidad");
	
	$CR = "\r\n";
			
	$cabeceras = "From: SGU" . $CR
	           . "Content-Type: text/plain;charset=utf-8" . $CR;
	           
	$asunto = "POA: Nueva Tarea";
	
	$cuerpo = "Estimad@ Responsable de $unidad" . $CR.$CR
	        . "Se agregó una nueva tarea a su POA:" . $CR.$CR
	        . $actividad . $CR
	        . "Fecha de Término: $fecha_prog_termino" . $CR.$CR
	        . "Para completar esta tarea, debe usar el módulo POA en el "
	        . "SGU y usar el botón «Terminar». El SGU le pedirá que "
	        . "escoja y suba el archivo de evidencia respectivo." . $CR.$CR
	        . "Luego, la unidad de Aseguramiento de la Calidad, "
	        . "evaluará el contenido del archivo y si corresponde otorgará "
	        . "el OK a la tarea." . $CR.$CR
	        . "Gracias!" . $CR
	        . "Atte.," . $CR.$CR
	        . "Dirección de Aseguramiento de la Calidad";
	        
	for ($x=0;$x<count($usuarios);$x++) { mail($usuarios[$x]['email'],$asunto,$cuerpo,$cabeceras); }
}

?>

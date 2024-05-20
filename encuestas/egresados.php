<?php

if (!is_numeric($_REQUEST['id_alumno']) || !is_numeric($_REQUEST['id_carrera'])) {
	echo(js("window.location='http://www.umcervantes.cl';"));
}


$ANO_Encuesta      = $_REQUEST['ano'];
$id_alumno         = $_REQUEST['id_alumno'];
$id_carrera        = $_REQUEST['id_carrera'];

if ($_REQUEST['enviar'] == "  -->  Terminar Encuesta  <--  ") {
	$aCampos = array('id_alumno','id_carrera','ano');
	for ($x=1;$x<=29;$x++) { $aCampos = array_merge($aCampos,array("p$x")); }
	$aCampos[] = "p8_1";
	$aCampos[] = "p12_1";
	$SQLinsert = "INSERT INTO encuestas.egresados ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQLinsert) == 1) {
		echo(msje_js("Se ha recibido tu encuesta contestada y los datos se almacenaron exitosamente\\n"
		            ."Gracias!"));
		echo(js("window.location='http://www.umcervantes.cl';"));
   } 
} else {
	consulta_dml("INSERT INTO encuestas.egresados_aud (ano,id_alumno) VALUES ($ANO_Encuesta,$id_alumno)");
}

$SQL_estados_alumnos = "SELECT id FROM al_estados WHERE nombre IN ('Egresado','Graduado','Licenciado','Titulado','Post-Titulado')";
$SQL_alumno_mat = "SELECT id,nombres||' '||apellidos AS nombre_alumno,genero FROM alumnos WHERE id=$id_alumno AND estado IN ($SQL_estados_alumnos)";
$alumno_mat = consulta_sql($SQL_alumno_mat);
if (count($alumno_mat) == 1) {
	$nombre_alumno = $alumno_mat[0]['nombre_alumno'];
	if ($alumno_mat[0]['genero'] == "f") { $vocativo = "Estimada"; } else { $vocativo = "Estimada"; }
	$arch_encuesta = "egresados.csv";
	$I_item = file($arch_encuesta);
	$nro_preg = 1;
	$HTML_I_item = "";
	$requeridos = "";
	for ($x=0;$x<count($I_item);$x++) {
		$linea_pregunta = explode("#",$I_item[$x]);

		if (substr($I_item[$x],0,1) == "#") {
			$HTML_I_item .= "<tr class='filaTituloTabla'>";
			$titulo_ambito = substr($I_item[$x],1,-1);
			$HTML_I_item .= "<td class='tituloTabla' colspan='2' align='center'><b>$titulo_ambito</b></td>";
			
		} else {
			
			$nombre_pregunta = $linea_pregunta[0];
			$pregunta        = $linea_pregunta[1];
			
			if ($nombre_pregunta <> "p0") {	$requeridos .= "'$nombre_pregunta',"; }

			$opciones = "";
			
			if (count($linea_pregunta) > 2) {
				$opciones = array();
				for($y=2;$y<count($linea_pregunta);$y++) {
					$opciones = array_merge($opciones, array(array("id"=>$y-1,"nombre"=>trim($linea_pregunta[$y]))));
				}
				$opciones = "    <select name='$nombre_pregunta' class='filtro'>\n"
			              . "      <option value=''>-- Seleccione --</option>\n"
			              .        select($opciones,$_REQUEST["$nombre_pregunta"])
			              . "    </select>\n";
			} else {
				$opciones = "<textarea name='$nombre_pregunta' style='width: 100%; height: 40px'>{$_REQUEST['$nombre_pregunta']}</textarea>";
			}
			
			$HTML_I_item .= "<tr class='filaTabla'>\n"
			             .  "  <td class='textoTabla'>$pregunta</td>\n<td class='textoTabla'>$opciones</td>\n"
			             .  "</tr>\n";
			$nro_preg++;
		}
	}
	$requeridos = substr($requeridos,0,-1);
} else {
	echo(msje_js("ERROR: Aparentemente no tienes estado terminal (Egresado, Graduado, Licenciado, Titulado o Post-Titulado).\\n"));
	echo(js("window.location='http://www.umcervantes.cl';"));
	exit;
}	

?>

<div align="center" class="tituloModulo">
  Encuesta para Alumnos Egresados <?php echo($ANO_Encuesta); ?>
</div>
<br>
<form name="formulario" action="index.php" onSubmit="return contestada(<?php echo($requeridos); ?>);" method="post">
<input type="hidden" name="modulo"     value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno"  value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_carrera" value="<?php echo($id_carrera); ?>">
<input type="hidden" name="ano"        value="<?php echo($ANO_Encuesta); ?>">
<div class="texto" align="justify">
  <?php echo("$vocativo $nombre_alumno"); ?>, junto con agradecerle su participación en la encuesta y con el objetivo de mejorar la calidad de nuestra
  gestión Académica-administrativa, queremos conocer su opinión sobre distintos aspectos.<br>
  <br>
  Esta encuesta es anónima. Le pedimos por favor que responda todas las preguntas con la mayor objetividad posible.<br>
  <br>
  ¡Muchas gracias!<br>
  <br>
  <table cellpadding="2" cellspacing="1" class="tabla"  bgcolor="#FFFFFF" width="980">
    <?php echo($HTML_I_item);?>
  </table>
  <br><br>
  <div align="center">
    <input type="submit" name="enviar" value="  -->  Terminar Encuesta  <--  ">
  </div>
</div>
</form>

<script>
function contestada() {
	var largo=0, x=0, problemas=false, campos=contestada.arguments;
	for(x=0; x<campos.length; x++) {
		campo = eval("document.formulario."+campos[x]);
		largo = eval("document.formulario."+campos[x]+".length");		
		pregunta = campos[x];
		if (campo.value != "") {
			problemas = false;
		} else {
			problemas = true;
		}
		if (problemas) { break; }
	}
	if (problemas) {
		return confirm("No has contestado una o algunas de las preguntas de esta encuesta. Estas seguro de querer Terminar la encuesta?.");
	}
}
</script>

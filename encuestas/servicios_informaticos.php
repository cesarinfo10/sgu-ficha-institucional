<?php
/*
if (!is_numeric($_REQUEST['id_alumno'])) {
	echo(js("window.location='http://www.umcervantes.cl';"));
}
*/

$ANO_Encuesta = $_REQUEST['ano'];
$SEMESTRE_Encuesta = 2;
$id_alumno = $_REQUEST['id_alumno'];

$enc_al = consulta_sql("SELECT id_alumno FROM encuestas.servicios_informaticos WHERE id_alumno=$id_alumno AND ano=$ANO_Encuesta");
if (count($enc_al) > 0) {
	echo(msje_js("Ha ocurrido un lamentable error. Tu Encuesta de Servicios Informáticos ya está contestada e "
	            ."inexplicablemente nuestros sistemas te han reenviado a esta página. Es posible también que hayas "
	            ."olvidado la contestación de la encuesta y has entrado nuevamente.\\n"
	            ."Gracias!"));
	echo(js("window.location='http://www.umcervantes.cl';"));
}

if ($_REQUEST['enviar'] == "  -->  Terminar Encuesta  <--  ") {
	$aCampos = array('id_alumno');
	for ($x=1;$x<=24;$x++) { $aCampos = array_merge($aCampos,array("p$x")); }
	$SQLinsert = "INSERT INTO encuestas.servicios_informaticos ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQLinsert) == 1) {
		echo(msje_js("Se ha recibido tu encuesta contestada y los datos se almacenaron exitosamente\\n"
		            ."Gracias!"));
		echo(js("window.location='http://www.umcervantes.cl';"));
   } 
}

$SQL_alumno_mat = "SELECT id_alumno FROM matriculas WHERE semestre=$SEMESTRE_Encuesta AND ano=$ANO_Encuesta AND id_alumno=$id_alumno";
$alumno_mat = consulta_sql($SQL_alumno_mat);
if (count($alumno_mat) == 1) {
	$arch_encuesta = "servicios_informaticos.txt"	;
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
				$opciones = "    <select name='$nombre_pregunta'>\n"
			              . "      <option value=''>-- Seleccione --</option>\n"
			              .        select($opciones,$_REQUEST["$nombre_pregunta"])
			              . "    </select>\n";
			}
			
			$HTML_I_item .= "<tr class='filaTabla'>\n"
			             .  "  <td class='textoTabla'>$pregunta</td>\n<td class='textoTabla'>$opciones</td>\n"
			             .  "</tr>\n";
			$nro_preg++;
		}
	}
	$requeridos = substr($requeridos,0,-1);
} else {
	echo(msje_js("ERROR: Aparentemente no ha estado matriculado en el periodo $ANO_Encuesta.\\n"));
	echo(js("window.location='http://www.umcervantes.cl';"));
	exit;
}	

?>

<div align="center" class="tituloModulo">
  Encuesta de Servicios Informáticos <?php echo($ANO_Encuesta); ?>
</div>
<br>
<form name="formulario" action="index.php" onSubmit="return contestada(<?php echo($requeridos); ?>);" method="post">
<input type="hidden" name="modulo"    value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<div class="texto" align="justify">
  Junto con agradecerle su participación en la encuesta y con el objetivo de mejorar la calidad de nuestra
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

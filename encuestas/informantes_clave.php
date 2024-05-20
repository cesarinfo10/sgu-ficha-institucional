<?php

if (!is_numeric($_REQUEST['id_alumno'])) {
	echo(js("window.location='http://www.umcervantes.cl';"));
}

$id_alumno = $_REQUEST['id_alumno'];

$enc_al = consulta_sql("SELECT id_alumno FROM encuestas.informantes_claves WHERE id_alumno=$id_alumno");
if (count($enc_al) > 0) {
	echo(msje_js("Ha ocurrido un lamentable error. Tu Encuesta de Informante Clave ya está contestada e "
	            ."inexplicablemente nuestros sistemas te han reenviado a esta página. Es posible también que hayas "
	            ."olvidado la contestación de la encuesta y has entrado nuevamente.\\n"
	            ."Gracias!"));
	echo(js("window.location='http://www.umcervantes.cl';"));
}

$ANO_Encuesta = $_REQUEST['ano'];
$SEMESTRE_Encuesta = 2;

if ($_REQUEST['enviar'] == "  -->  Terminar Encuesta  <--  ") {
	$aCampos = array('id_alumno');
	for ($x=1;$x<=51;$x++) { $aCampos = array_merge($aCampos,array("p$x")); }
	$SQLinsert = "INSERT INTO encuestas.informantes_claves ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQLinsert) == 1) {
		echo(msje_js("Se ha recibido su encuesta contestada y los datos se almacenaron exitosamente\\n"
		            ."Gracias!"));
		echo(js("window.location='http://www.umcervantes.cl';"));
	}
	exit;
}

$SQL_alumno_mat = "SELECT id_alumno FROM matriculas WHERE semestre=$SEMESTRE_Encuesta AND ano=$ANO_Encuesta AND id_alumno=$id_alumno";
$alumno_mat = consulta_sql($SQL_alumno_mat);
if (count($alumno_mat) == 1) {
	$arch_encuesta = "informantes_clave.txt"	;
	$Encuesta = file($arch_encuesta);
	$nro_preg = 1;
	$HTML_Encuesta = $requeridos = "";
	for ($x=0;$x<count($Encuesta);$x++) {
		$linea_pregunta = explode("#",$Encuesta[$x]);
		if (substr($Encuesta[$x],0,1) == "#") {
			$HTML_Encuesta .= "<tr class='filaTituloTabla'>";
			$titulo_ambito = substr($Encuesta[$x],1,-1);
			$HTML_Encuesta .= "<td class='tituloTabla' colspan='3' align='center'><b>$titulo_ambito</b></td>";
		} else {
			$opciones = array();
			for($y=2;$y<count($linea_pregunta);$y++) {
				$opciones = array_merge($opciones, array(array("id"=>$y-1,"nombre"=>trim($linea_pregunta[$y]))));
			}
			$requeridos .= "'p$nro_preg',";
			$HTML_Encuesta .= "<tr class='filaTabla'>\n";
			$HTML_Encuesta .= "<td class='textoTabla' align='right'>$nro_preg.</td><td class='textoTabla'>{$linea_pregunta[1]}</td>\n"
			               .  "<td class='textoTabla'>"
			               .  "  <select name='p$nro_preg'>"
			               .  "  <option>-- Selecccione --</option>"
			               .  select($opciones,$_REQUEST['p$nro_preg'])	
			               .  "  </select>";
			$nro_preg++;
		}
		$HTML_Encuesta .= "</tr>\n";
	}
} else {
	echo(msje_js("ERROR: Aparentemente no ha estado matriculado en el periodo $ANO_Encuesta.\\n"));
	echo(js("window.location='http://www.umcervantes.cl';"));
	exit;
}	

?>

<div align="center" class="tituloModulo">
  Encuesta de Informantes Clave y Uso Multimedia <?php echo($ANO_Encuesta); ?>
</div>
<br>
<form name="formulario" action="index.php" onSubmit="if (!enblanco2(<?php echo($requeridos); ?>)) { return false; }" method="post">
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
  Seleccione una alternativa para cada una de las preguntas expuestas:<br>
  <br>
  <table cellpadding="2" cellspacing="1" class="tabla"  bgcolor="#FFFFFF" width="980">
    <?php echo($HTML_Encuesta);?>
  </table>

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
		for(y=0; y<largo; y++) {
			if (campo[y].checked) {
				problemas = false;
				break;				
			} else {
				problemas = true;
			}
		}
		if (problemas) { break; }
	}
	if (problemas) {
		alert("No ha contestado una o algunas de las preguntas de esta encuesta. El ítem I y III debe ser contestado completamente.");
		return false;
	} else {
		return true;
	}
}
</script>

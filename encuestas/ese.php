<?php

if (!is_numeric($_REQUEST['id_alumno'])) {
	echo(js("window.location='https://www.umcervantes.cl';"));
}

$id_alumno = $_REQUEST['id_alumno'];

$enc_al = consulta_sql("SELECT id_alumno FROM encuestas.ese WHERE id_alumno=$id_alumno");
if (count($enc_al) > 0) {
	echo(msje_js("Ha ocurrido un lamentable error. Tu Encuesta de Satisfacción Estudiantil ya está contestada e "
	            ."inexplicablemente nuestros sistemas te han reenviado a esta página. Es posible también que hayas "
	            ."olvidado la contestación de la encuesta y has entrado nuevamente.\\n"
	            ."Gracias!"));
	echo(js("window.location='http://www.umcervantes.cl';"));
}

$ANO_Encuesta = $_REQUEST['ano'];
$SEMESTRE_Encuesta = 2;

if ($_REQUEST['enviar'] == "  -->  Terminar Encuesta  <--  ") {
	$aCampos = array('id_alumno');
	for ($x=1;$x<=75;$x++) { $aCampos = array_merge($aCampos,array("p$x")); }
	$SQLinsert = "INSERT INTO encuestas.ese ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQLinsert) == 1) {
		echo(msje_js("Se ha recibido su encuesta contestada y los datos se almacenaron exitosamente\\n"
		            ."Gracias!"));
		echo(js("window.location='http://www.umcervantes.cl';"));
   } 
}

$SQL_alumno_mat = "SELECT id_alumno FROM matriculas WHERE semestre=$SEMESTRE_Encuesta AND ano=$ANO_Encuesta AND id_alumno=$id_alumno";
$alumno_mat = consulta_sql($SQL_alumno_mat);
if (count($alumno_mat) == 1) {
//	$arch_encuesta = "ESE2009_preguntas_I_item.txt"	;
//	$arch_encuesta = "ESE2020_preguntas_I_item.txt"	; // encuesta adaptada para contexto pandemia
	$arch_encuesta = "ESE2022_preguntas_I_item.txt"	; // encuesta adaptada para post-pandemia
	$I_item = file($arch_encuesta);
	//var_dump($I_item);
	$nro_preg = 1;
	$HTML_I_item = "";
	$requeridos = "";
	for ($x=0;$x<count($I_item);$x++) {
		if (substr($I_item[$x],0,1) == "#") {
			$HTML_I_item .= "<tr class='filaTituloTabla'>";
			$titulo_ambito = substr($I_item[$x],1,-1);
			$HTML_I_item .= "<td class='tituloTabla' colspan='2' align='center'><b>$titulo_ambito</b></td>";
			for ($y=1;$y<=7;$y++) { $HTML_I_item .= "<td class='tituloTabla' align='center'>$y</td>"; }
		} else {
			if (!empty(trim($I_item[$x]))) {
				$requeridos .= "'p$nro_preg',";
				$HTML_I_item .= "<tr class='filaTabla'>\n";
				$HTML_I_item .= "<td class='textoTabla' align='right'><!-- $nro_preg. --></td><td class='textoTabla'>".trim($I_item[$x])."</td>\n";
				for ($y=1;$y<=7;$y++) {
					$HTML_I_item .= "<td class='textoTabla' align='center'><input type='radio' name='p$nro_preg' value='$y' required></td>\n";
				}
			}
			$nro_preg++;
		}
		$HTML_I_item .= "</tr>\n";
	}
	$requeridos .= "'p75'";
} else {
	echo(msje_js("ERROR: Aparentemente no ha estado matriculado en el periodo $ANO_Encuesta.\\n"));
	echo(js("window.location='http://www.umcervantes.cl';"));
	exit;
}	

?>

<div align="center" class="tituloModulo">
  Encuesta de Satisfacción Estudiantil <?php echo($ANO_Encuesta); ?>
</div>
<br>
<form name="formulario" action="index.php" onSubmit="if (!contestada(<?php echo($requeridos); ?>) || !enblanco2('p71','p72','p73','p74')) { return false; }" method="post">
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
  I. Marque sobre el círculo calificando con la escala de notas de 1 a 7 los siguientes aspectos:<br>
  <br>
  <table cellpadding="2" cellspacing="1" class="tabla"  bgcolor="#FFFFFF" width="980">
    <?php echo($HTML_I_item);?>
  </table>
  <br><br>
  II. Si lo desea, puede ingresar algún comentario relevante:<br><br>
  <table cellpadding="2" cellspacing="1" class="tabla"  bgcolor="#FFFFFF">
    <tr class='filaTabla'>
      <td class='textoTabla'>1. <input type='text' name='p71'></td>
    </tr>
  </table>
<!--
  <br>
  III. En general, Usted considera que la UMC satisface sus expectativas:
  <input type="radio" name="p75" value="t">Si
  <input type="radio" name="p75" value="f">No
-->
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

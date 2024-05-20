<?php

$rut_alumno = $_REQUEST['rut_alumno'];
$resp       = $_REQUEST['resp'];
$id_prueba  = $_REQUEST['id_prueba'];
$modulo     = $_REQUEST['modulo'];

// Transformar CSV a matriz, al estilo de pg_fetch_all()
$test       = file('af5.csv',FILE_IGNORE_NEW_LINES);
$aKeys_test = explode(',',$test[0]);
$aTest      = array();
for ($x=1;$x<count($test);$x++) {
	$aTest[$x-1] = array_combine($aKeys_test,explode(',',$test[$x]));
}

if ($_REQUEST['guardar'] == "-->  Terminar  <--") {
	$resp = "{".implode(",",$resp)."}";
	$SQL_insert = "INSERT INTO sinergia.respuestas (id_prueba,semestre,ano,rut_alumno,resp) VALUES ($id_prueba,$SEMESTRE,$ANO,'$rut_alumno','$resp');"
	            . "SELECT currval('sinergia.respuestas_id_seq')";
	$folio = consulta_sql($SQL_insert);
	$folio = $folio[0]['currval'];
	if ($folio > 0) {
		echo(msje_js("Has terminado tu prueba y se han guardado tus respuestas correctamente.\\n\\n"
		            ."En el formulario en papel que se te entregó por favor anota tus datos y este folio:\\n\\n"
		            .$folio."\\n\\n"
		            ."Gracias. Ahora sigue las instrucciones que te dará el profesor."));
	} else {
		echo(msje_js("ERROR: Ha ocurrido un problema al guardar tu prueba.\\n\\n"
		            ."Lo más probable es que ya se encuentre contestada esta prueba y "
		            ."no fue posible guardarla nuevamente"));
	}
	echo(js("window.location='index.php?modulo=validar&rut=$rut_alumno';"));
	exit;
}

$HTML = "";
for ($x=0;$x<count($aTest);$x++) {
	$msje_err = "La respuesta ingresada está fuera del intervalo";
	$HTML .= "<tr class='filaTabla'>\n"
	      .  "  <td class='textoTabla' align='right' valign='middle'>{$aTest[$x]['nro_pregunta']}</td>\n"
	      .  "  <td class='textoTabla' valign='middle'>{$aTest[$x]['pregunta']}<br><br></td>\n"
	      .  "  <td class='textoTabla' align='center'>"
	      .  "    <input type='text' name='resp[{$aTest[$x]['nro_pregunta']}]' size='2' 
	                   onBlur=\"if (isNaN(this.value) || this.value<1 || this.value>99) { alert('$msje_err'); this.focus(); this.select(); }\">"
	      .  "  </td>\n"
	      .  "</tr>\n";
}

?>

<div class="texto" align="justify">
  <div class='titulomodulo'>
    Autoconcepto Forma 5
  </div><br>
  <div style='text-align: center'><b>INSTRUCCIONES</b></div>
  <br>
  A continuación encontrará una serie de frases. Lea cada una de ellas cuidadosamente y conteste con un valor entre 1 y 99
  según su grado de acuerdo con cada frase. Por ejemplo, si una frase dice "La música ayuda al bienestar humano" y usted está muy de acuerdo,
  contestará con un valor alto, como por ejemplo el 94. Por el contrario, si usted está muy poco de acuerdo, elegirá un valor
  bajo; por ejemplo el 9.<br>
  <br>
  No olvide que dispone de muchas opciones de respuesta, en concreto puede elegir entre 99 valores. Escoja el que más se ajuste a
  su criterio.<br>
  <br>
  <div style='text-align: center'>RECUERDE, CONTESTE CON LA MÁXIMA SINCERIDAD</div>
  <br>
  <div style='border: 1px solid #000000; width: 740px; padding: 10px; text-align: center'>
    NOTA: Se han redactado las frases en masculino, para facilitar su lectura. Cada persona deberá adaptarlas a su propio sexo.
  </div>
  <br>
  Por favor contesta todas las preguntas. El software no te dejará guardar la prueba hasta que no hayas contestado todas las preguntas.<br>
  Para contestar debes usar el <i>mouse</i> o ratón para hacer click en el casillero de respuesta y luego con el teclado ingresar el
  valor númerico según las intrucciones de más arriba.
</div>
<br>
<form action='index.php' method="post" onSubmit="return confirm('Estas seguro de dar por terminada la prueba y remitir estas respuestas que has ingresado');">	
<input type='hidden' name='rut_alumno' value='<?php echo($rut_alumno); ?>'>
<input type='hidden' name='modulo'     value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_prueba'  value='<?php echo($id_prueba); ?>'>

<table cellpadding="2" cellspacing="1" class="tabla"  bgcolor="#FFFFFF">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>N°</td>
    <td class='tituloTabla'>Pregunta</td>
    <td class='tituloTabla'>Respuesta</td>
  </tr>
  <?php echo($HTML); ?>
  <tr><td class='tituloTabla' colspan='3' align='center'><br><input type='submit' name='guardar' value='-->  Terminar  <--'><br><br></td></tr>
</table>
</form>

<?php

$rut_alumno = $_REQUEST['rut_alumno'];
$resp       = $_REQUEST['resp'];
$modulo     = $_REQUEST['modulo'];
$id_prueba  = $_REQUEST['id_prueba'];

// Transformar CSV a matriz, al estilo de pg_fetch_all()
$test       = file('otis_sencillo.csv',FILE_IGNORE_NEW_LINES);
$aKeys_test = str_getcsv($test[0]);
$aTest      = array();
for ($x=1;$x<count($test);$x++) {
    $aTest[$x-1] = array_combine($aKeys_test,str_getcsv($test[$x]));
}

if ($_REQUEST['guardar'] == "-->  Terminar  <--") {
	var_dump($resp);
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

$HTML = $pregunta = "";
for ($x=0;$x<count($aTest);$x++) {
	$msje_err = "La respuesta ingresada está fuera del intervalo";
	
	$respuesta = "<input type='hidden' name='resp[p{$aTest[$x]['nro_pregunta']}]' value='0'>"
	           . "<table class='texto'>\n<tr>\n";

	$pregunta = "{$aTest[$x]['pregunta']}<br><br>";
	for ($z=97;$z<=101;$z++)  {
	    $letra = chr($z);
	    $val = $z-96;
	    $alt="alter_".$letra;
	    
	    $LF = "&nbsp;&nbsp;&nbsp;";
	    if (strlen($aTest[$x][$alt]) > 12  && str_word_count($aTest[$x][$alt],0) > 2) { $LF = "<br>";}
	    
	    $letra = strtoupper($letra);
	    if ($aTest[$x][$alt] <> "") { $pregunta .= "<b>$letra.</b> {$aTest[$x][$alt]}".$LF; }
	    
	    $checked = "";
	    if ($val == $resp["p".$aTest[$x]['nro_pregunta']]) { $checked = "checked"; }
	    
	    $respuesta .= "<td align='center'>$letra<br><input type='radio' name='resp[p{$aTest[$x]['nro_pregunta']}]' value='$val' $checked></td>\n";
	}
	$respuesta .= "</tr>\n</table>\n";
	
	$HTML .= "<tr class='filaTabla'>\n"
	      .  "  <td class='textoTabla' align='right' valign='middle'>{$aTest[$x]['nro_pregunta']}.</td>\n"
	      .  "  <td class='textoTabla' valign='middle'>$pregunta</td>\n"
	      .  "  <td class='textoTabla' align='center' valign='baseline'>$respuesta</td>\n"
	      .  "</tr>\n";
}

?>


<div class="texto" align="justify">
  <div class='titulomodulo'>
    Inteligencia y Orientación Vocacional
  </div><br>
  <div style='text-align: center'><b>INSTRUCCIONES</b></div>
  <br>
  Esta prueba se compone de diversas preguntas y problemas que usted habrá de resolver.<br>
  Fíjese en este ejemplo para que sepa cómo ha de contestar.<br>
  <br>
  <table class='texto'>
    <tr>
      <td><div style='border: 1px solid #000000; padding: 5px; text-align: center; background: #e5e5e5'>Ejemplo 1</div></td>
      <td>
        ¿Cuál de estas cinco palabras nos indica lo que es una manzana?<br>
        <b>A.</b> flor&nbsp;&nbsp;&nbsp;<b>B.</b> árbol&nbsp;&nbsp;&nbsp;<b>C.</b> legumbre&nbsp;&nbsp;&nbsp;<b>D.</b> fruto&nbsp;&nbsp;&nbsp;<b>E.</b> animal
      </td>
      <td>
        <table class='texto'>
          <tr>
            <td align='center'>A<br><input type='radio' name='ejemplo1' value='A'></td>
            <td align='center'>B<br><input type='radio' name='ejemplo1' value='B'></td>
            <td align='center'>C<br><input type='radio' name='ejemplo1' value='C'></td>
            <td align='center'>D<br><input type='radio' name='ejemplo1' value='D' checked></td>
            <td align='center'>E<br><input type='radio' name='ejemplo1' value='E'></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td></td>
      <td colspan='2'>
        La respuesta exacta es “fruto”.  Por eso se ha contestado la letra D que es la que va delante de la palabra “fruto”.<br>
        Usted deberá responder de esta misma forma, es decir, señalando la letra que está delante de esa respuesta,<br> 
	haciendo click en el círculo que está debajo de la letra que ha escogido, marcándose un punto.<br>
        Resuelva ahora estos otros ejemplos.<br><br>
      </td>	
    </tr>
    <tr>
      <td><div style='border: 1px solid #000000; padding: 5px; text-align: center; background: #e5e5e5'>Ejemplo 2</div></td>
      <td>
        ¿Cuál de estas cosas es redonda?<br>
        <b>A.</b> libro&nbsp;&nbsp;&nbsp;<b>B.</b> ladrillo&nbsp;&nbsp;&nbsp;<b>C.</b> pelota&nbsp;&nbsp;&nbsp;<b>D.</b> casa&nbsp;&nbsp;&nbsp;<b>E.</b> baúl
      </td>
      <td>
        <table class='texto'>
          <tr>
            <td>A<br><input type='radio' name='ejemplo2' value='A'></td>
            <td>B<br><input type='radio' name='ejemplo2' value='B'></td>
            <td>C<br><input type='radio' name='ejemplo2' value='C'></td>
            <td>D<br><input type='radio' name='ejemplo2' value='D'></td>
            <td>E<br><input type='radio' name='ejemplo2' value='E'></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td></td>
      <td colspan='2'>
        La contestación correcta es “pelota”.  Así pues, la respuesta es C.<br><br>
     </td>	
    </tr>
    <tr>
      <td><div style='border: 1px solid #000000; padding: 5px; text-align: center; background: #e5e5e5'>Ejemplo 3</div></td>
      <td>
        ¿Cuál de estos números tiene todas sus cifras impares?<br>
	<b>A.</b> 243&nbsp;&nbsp;&nbsp;<b>B</b>. 9.871&nbsp;&nbsp;&nbsp;<b>C.</b> 6.482&nbsp;&nbsp;&nbsp;<b>D.</b> 3.175&nbsp;&nbsp;&nbsp;<b>E.</b> 19.783
      </td>
      <td>
        <table class='texto'>
          <tr>
            <td>A<br><input type='radio' name='ejemplo3' value='A'></td>
            <td>B<br><input type='radio' name='ejemplo3' value='B'></td>
            <td>C<br><input type='radio' name='ejemplo3' value='C'></td>
            <td>D<br><input type='radio' name='ejemplo3' value='D'></td>
            <td>E<br><input type='radio' name='ejemplo3' value='E'></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td></td>
      <td colspan='2'>
        La respuesta correcta es D.<br><br>
     </td>	
    </tr>
    <tr>
      <td><div style='border: 1px solid #000000; padding: 5px; text-align: center; background: #e5e5e5'>Ejemplo 4</div></td>
      <td>
        El precio de una pastilla es de $200. ¿Cuánto costarán seis pastillas?<br>
        <b>A.</b> $1.800&nbsp;&nbsp;&nbsp;<b>B.</b> $1.200&nbsp;&nbsp;&nbsp;<b>C.</b> $900&nbsp;&nbsp;&nbsp;<b>D.</b> $1.300&nbsp;&nbsp;&nbsp;<b>E.</b> $750
      </td>
      <td>
        <table class='texto'>
          <tr>
            <td>A<br><input type='radio' name='ejemplo4' value='A'></td>
            <td>B<br><input type='radio' name='ejemplo4' value='B'></td>
            <td>C<br><input type='radio' name='ejemplo4' value='C'></td>
            <td>D<br><input type='radio' name='ejemplo4' value='D'></td>
            <td>E<br><input type='radio' name='ejemplo4' value='E'></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td></td>
      <td colspan='2'>
        La solución es $1.200. Así pues, la respuesta correcta es B.
     </td>	
    </tr>
  </table>
  <br>
  Esta prueba consta de 75 ejercicios; resuelva todos los que pueda.<br>
  A partir de la señal dada por el examinador, dispondrá de media hora. Trabaje lo más rápida y 
  exactamente que pueda. No se entretenga mucho en una misma pregunta. Si llega a una que no entiende pase a la siguiente.<br>
  Use sólo el dispositivo apuntador (<i>mouse</i> o ratón) para contestar esta prueba.<br>
  ¿Ha comprendido? ¿Quiere hacer alguna pregunta?
</div>
<br>
<form action='index.php' method="post">
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
  <tr>
    <td class='tituloTabla' colspan='3' align='center'>
      <br>
      <input type='submit' id='guardar' name='guardar' value='-->  Terminar  <--'
             onClick="return confirm('Estas seguro de dar por terminada la prueba y remitir estas respuestas que has ingresado');"><br><br>
    </td>
  </tr>
</table>
<input type='submit' id='guardar2' name='guardar' value='-->  Terminar  <--' style='display: none;'>
</form>

<script>
	setTimeout ("alert('Ha terminado el tiempo (30 minutos). La prueba será terminada y guardada automáticamente.');document.getElementById('guardar2').click();", 30 * 60 * 1000);
</script>

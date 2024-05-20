<?php

$rut_alumno = $_REQUEST['rut_alumno'];
$resp       = $_REQUEST['resp'];
$modulo     = $_REQUEST['modulo'];
$id_prueba  = $_REQUEST['id_prueba'];

// Transformar CSV a matriz, al estilo de pg_fetch_all()
$test       = file('acra.csv',FILE_IGNORE_NEW_LINES);
$aKeys_test = str_getcsv($test[0]);
$aTest      = array();
for ($x=1;$x<count($test);$x++) {
    $aTest[$x-1] = array_combine($aKeys_test,str_getcsv($test[$x]));
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

$aRespuestas = array(array("id"=>1, "nombre"=>'A. Nunca o casi nunca'),
                     array("id"=>2, "nombre"=>'B. Algunas veces'),
                     array("id"=>3, "nombre"=>'C. Bastantes veces'),
                     array("id"=>4, "nombre"=>'D. Siempre o casi siempre'));
		     
$HTML = $pregunta = "";
for ($x=0;$x<count($aTest);$x++) {
    if ($escala <> $aTest[$x]['escala']) {
	$HTML .= "<tr><td class='textoTabla' colspan='2'><br><b>{$aTest[$x]['escala']}</b><br><br></td></tr>";
    }
    
    $escala = $aTest[$x]['escala'];
    	
    $respuesta = "<select name='resp[{$aTest[$x]['nro_pregunta']}]'>"
	       . "  <option value='0'>-- Seleccione --</option>"
	       .    select($aRespuestas,$resp[$aTest[$x]['nro_pregunta']])
	       . "</select>";

    $pregunta = "{$aTest[$x]['pregunta']}<br><br>";
    
    $HTML .= "<tr class='filaTabla'>\n"
	  //.  "  <td class='textoTabla' align='right' valign='middle'>{$aTest[$x]['nro_pregunta']}.</td>\n"
	  .  "  <td class='textoTabla' valign='middle'>$pregunta</td>\n"
	  .  "  <td class='textoTabla' align='center' valign='baseline'>$respuesta</td>\n"
	  .  "</tr>\n";
}

?>


<div class="texto" align="justify">
  <div class='titulomodulo'>
    Escala de Estrategias de Aprendizaje
  </div><br>
  <div style='text-align: center'><b>INSTRUCCIONES</b></div>
  <br>
  Esta escala tiene por objeto identificar las estrategias de aprendizaje más frecuentemente
  utilizadas por los estudiantes cuando están asimilando la información contenida en un texto, 
  en un artículo, en unos apuntes... es decir, cuando están estudiando.<br>
  <br>
  Cada estrategia puede haberla utilizado con mayor o menor frecuencia. Algunas puede que no
  las hayas utilizado nunca y otras, en cambio muchísimas veces.  Esta frecuencia es
  precisamente la que queremos conocer.<br>
  <br>
  Para ello se han establecido cuatro grados posibles según la frecuencia con la que tú sueles
  usar normalmente dichas estrategias de aprendizaje:<br>
  <br>
  <b>A</b>	NUNCA O CASI NUNCA<br>
  <b>B</b>	ALGUNAS VECES<br>
  <b>C</b>	BASTANTES VECES<br>
  <b>D</b>	SIEMPRE O CASI SIEMPRE<br>
  <br>
  Para contestar, lee la frase que describe la estrategia y, a continuación, marca la opción 
  que mejor se ajuste a la frecuencia con que la usas.  Siempre en tu opinión y desde el
  conocimiento que tienes de tus procesos de aprendizaje.<br>
  <br>
  EJEMPLO<br>
  <br>
  <table cellpadding="2" cellspacing="1" class='tabla' bgcolor="#FFFFFF">
    <tr class='filaTabla'>
      <td class='textoTabla'>
        1. Antes de comenzar a estudiar leo el índice, o el resumen, o los apartados, cuadros, 
        gráficos, negritas o cursivas del material a aprender<br><br>
      </td>
      <td class='textoTabla'>
        <select>
          <option>-- Seleccione --</option>
          <option>A. Nunca o casi nunca</option>
          <option>B. Algunas veces</option>
          <option selected>C. Bastantes veces</option>
          <option>D. Siempre o casi siempre</option>
	</select>
      </td>
    </tr>
  </table>
  <br>
  En este ejemplo el estudiante hace uso de esta estrategia BASTANTES VECES y por eso contesta 
  la alternativa C.<br>
  <br>
  Esta escala no tiene límite de tiempo para su contestación.  Lo importante es que las respuestas
  reflejen lo mejor posible tu manera de procesar la información cuando estás estudiando artículos,
  monografías, textos, apuntes... es decir, cualquier material a aprender.<br>
  <br>
  SI NO HAS ENTENDIDO BIEN LO QUE HAY QUE HACER, PREGUNTA AHORA Y SI LO HAS ENTENDIDO CORRECTAMENTE
  COMIENZA YA.<br>
  <br>
</div>
<br>
<form action='index.php' method="post" onSubmit="return confirm('Estas seguro de dar por terminada la prueba y remitir estas respuestas que has ingresado');">	
<input type='hidden' name='rut_alumno' value='<?php echo($rut_alumno); ?>'>
<input type='hidden' name='modulo'     value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_prueba'  value='<?php echo($id_prueba); ?>'>

<table cellpadding="2" cellspacing="1" class="tabla"  bgcolor="#FFFFFF">
  <tr class='filaTituloTabla'>
    <!-- <td class='tituloTabla'>N°</td> -->
    <td class='tituloTabla'>Pregunta</td>
    <td class='tituloTabla'>Respuesta</td>
  </tr>
  <?php echo($HTML); ?>
  <tr><td class='tituloTabla' colspan='3' align='center'><br><input type='submit' name='guardar' value='-->  Terminar  <--'><br><br></td></tr>
</table>
</form>

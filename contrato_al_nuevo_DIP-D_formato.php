<?php

if (empty($tipo_contrato)) { $tipo_contrato = "Anual"; }

$titulo_periodo = $texto_periodo = $texto_2do_sem = "";
if (trim($tipo_contrato) == "Semestral") {
	
	if ($semestre == 1) { 
		$titulo_periodo = "PRIMER SEMESTRE OTOÑO $ano_contrato";
		$texto_2do_sem = "El Derecho de matrícula del Segundo Semestre de Primavera es de \$ $monto_matricula "
		               . "($monto_matricula_palabras pesos), y el derecho de colegiatura del Segundo Semestre de "
		               . "Primavera es de \$ $monto_arancel ($monto_arancel_palabras pesos), los que se pagarán "
		               . "o documentarán al momento de su matrícula.<br><br>";
	}
		
	if ($semestre == 2) { $titulo_periodo = "SEGUNDO SEMESTRE PRIMAVERA $ano_contrato"; }
	
	$texto_periodo = "en el ".mb_strtolower($titulo_periodo).", ";
	
} elseif ($tipo_contrato == "Anual") {
	
	$titulo_periodo = "AÑO $ano_contrato";
	$texto_periodo = "para el año $ano_contrato";
	
}


$titulo = "<table width='100%'>"
        . "  <tr>"
        . "    <td align='left'><img src='../img/logoumc_apaisado.jpg' height='60'></td>"
        . "    <td align='right'><img src='https://sgu.umc.cl/sgu/php-barcode/barcode.php?code=$id_contrato&scale=1'></td>"
        . "  </tr>"
        . "</table>"
        . "<b>CONTRATO DE PRESTACIÓN DE SERVICIOS EDUCACIONALES</b><br>"
        . "<b>ALUMNOS</b><br>"
        . "<b>$carrera_al</b><br>";

$TEXTO = "Entre la Universidad Miguel de Cervantes, Corporación de Derecho Privado de Educación Superior, "
       . "Rut: 73.124.400-6, representada por su $REPRESENTANTE_LEGAL, ambos domiciliados "
       . "calle Mac Iver N° 370, de esta ciudad, en adelante «la Universidad» y/o «UMC», por una parte, y "
       . "por la otra, don(ña) $nombre_al, RUT $rut_al, de nacionalidad $nacionalidad_al, domicilio en $domicilio_al, en adelante "
       . "«el alumno», en conformidad a las disposiciones legales y al Código Civil se ha convenido el "
       . "siguiente contrato de prestación de servicios educacionales:<br><br>"
       
       . "<b>PRIMERO: </b>"
       . "Por el presente acto el (compareciente don(ña) $nombre_al) entenderá matriculado como alumno regular de la "
       . "Universidad Miguel de Cervantes, en el $carrera_al. <br><br> "

       . "El alumno declara que previo a la suscripción del contrato, la UMC ha puesto a su disposición, en forma oportuna la "
       . "información relativa a los servicios educacionales que se contratan, declarando ambos, que han tomado conocimiento "
       . "previo a través de la página web oficial www.umcervantes.cl y por medio de los canales de atención de admisión, que "
       . "la Universidad ha puesto a disposición de los alumnos."
       . "<br><br>"
       
       . "<b>SEGUNDO: </b>"
       . "El alumno en este acto se obliga a conocer y a respetar la reglamentación interna de la Universidad, y en general "
       . "toda otra norma que emane de la institucionalidad universitaria. El incumplimiento por parte del alumno de dicha "
       . "reglamentación dará derecho a la UMC a poner término al presente contrato, sin necesidad de declaración judicial."
       . "<br><br>"
       . "Las actividades académicas correspondientes al $carrera_al en que se matriculó el alumno/a, se "
       . "realizarán vía on line; el alumno declara contar con la implementación necesaria para acceder electrónicamente vía internet "
       . "a la educación a distancia, esto es conectividad, accesibilidad, y, equipos adecuados para participar en clases "
       . "virtuales sincrónicas y asincrónicas. <br><br>"
       
       . "<b>TERCERO: </b>"
       . "Si no se completare un número mínimo de 20 alumnos en el $carrera_al, la UMC podrá optar por no "
       . "impartir dicho Diplomado. Dada esta situación, la Universidad podrá ofrecer otras alternativas al alumno o, si éste "
       . "lo solicita, se procederá a dejar sin efecto el contrato firmado, reintegrando los eventuales pagos efectuados y la "
       . "documentación entregada por el alumno y recibida por la UMC.<br><br>"
       
       . "<b>CUARTO: </b>"
       . "El valor del derecho de matrícula y el valor del arancel semestral corresponden al total de la prestación contratada para dicho período. "
       . "<br><br>"
       . "Los pagos correspondientes, se efectuarán mediante transferencia electrónica debidamente informada a la UMC al correo electrónico "
       . "«transferencias@corp.umc.cl» indicando el nombre y el RUT del alumno, con tarjeta de crédito o por medio de la Institución financiera "
       . "a la cual la UMC hubiere encomendado su recaudación y/o cobro, no estando obligadas ni la Universidad ni la Institución financiera a "
       . "notificar en cada oportunidad las fechas de vencimiento del compromiso de pago."
       . "<br><br>"       
       . "El alumno manifiesta su conformidad en que, a partir de esta fecha, toda información y comunicación oficial de la UMC pueda ser enviada "
       . "a través de la dirección personal de email. En todo caso será responsabilidad del alumno informar del cambio de domicilio y/o de sus "
       . "correos electrónicos personales. El alumno deberá validar al momento de la matrícula respectiva, su domicilio, teléfono móvil y su "
       . "correo electrónico personal, como requisito de matrícula y a su vez deberá revalidarlo cuando así se lo solicite la UMC."
       . "<br><br>"
       
       . "<b>QUINTO: </b>"
       . "En el caso de alumnos nuevos, incorporados como postulantes, esto es, aquellos que ingresan por primera vez a la Universidad, la UMC "
       . "podrá poner término al presente contrato, si el alumno no hubiere cumplido con los requisitos de admisión e ingreso. En este caso, junto "
       . "a la rescisión del presente contrato, la Universidad devolverá los pagos efectuados por el alumno por los servicios inicialmente convenidos, "
       . "a excepción del derecho de matrícula, así como la documentación entregada por el alumno y recibida por la UMC."
       . "<br><br>"

       . "<b>SEXTO: </b>"
       . "De todas las obligaciones económicas y financieras estipuladas en el presente contrato o que se deriven de él, será responsable el alumno. "
       . "Por este instrumento, el alumno autoriza expresamente y en conformidad a la ley, a la UMC para en caso de simple retardo, mora o incumplimiento "
       . "total o parcial de las obligaciones contraídas en este contrato, sus datos personales y las demás derivadas de éste puedan ser tratados y/o "
       . "comunicados a terceros sin restricciones, a efectos de su cobranza."
       . "<br><br>"
       . "En el caso que el alumno presente formalmente su solicitud de retiro y/o de suspensión de estudios y esta fuere aprobada, deberá pagar el 50% "
       . "de lo no pagado o no vencido a la fecha de presentación de dicha solicitud."
       . "<br><br>"
       
       . "<b>SÉPTIMO: </b>" 
       . "La individualización del alumno y los datos de los servicios educacionales "
       . "contratados son:<br><br>"
       . "<table cellpadding='2' cellspacing='0' border='0.5' align='center' width='90%'>"
       . "  <tr><td width='20%'>Alumna/alumno</td> <td width='80%' colspan='3'>$nombre_al</td></tr>"
       . "  <tr><td width='20%'>RUT Alumna/o</td>  <td width='80%' colspan='3'>$rut_al</td></tr>"
       . "  <tr><td width='20%'>Domicilio</td>     <td width='80%' colspan='3'>$domicilio_al</td></tr>"
       . "  <tr><td width='20%'>Fono Fijo</td>     <td width='30%'>$telefono_al</td><td width='20%'>Fono Celular</td>  <td width='30%'>$tel_movil_al</td></tr>"
       . "  <tr><td width='20%'>e-Mail Personal</td>  <td width='80%' colspan='3'>$email_al</td></tr>"
       . "</table><br>"
       . "<table cellpadding='2' cellspacing='0' border='0.5' align='center' width='90%'>"
       . "  <tr><td width='20%'>Carrera</td> <td width='80%'>$carrera_al</td></tr>"
       . "</table><br>"
//       . "<!-- PAGE BREAK -->"

       . "<table cellpadding='2' cellspacing='0' border='0.5' align='center' width='90%'>"
       . "  <tr><td width='20%'>Apoderado</td>     <td width='80%' colspan='3'>$nombre_rf</td></tr>"
       . "  <tr><td width='20%'>RUT Apoderado</td> <td width='80%' colspan='3'>$rut_rf</td></tr>"
       . "  <tr><td width='20%'>Domicilio</td>     <td width='80%' colspan='3'>$domicilio_rf</td></tr>"
       . "  <tr><td width='20%'>Fono Fijo</td>     <td width='30%'>$telefono_rf</td><td width='20%'>Fono Celular</td>  <td width='30%'>$tel_movil_rf</td></tr>"
       . "  <tr><td width='20%'>e-Mail Personal</td>  <td width='80%' colspan='3'>$email_rf</td></tr>"
       . "</table><br>"

//       . "<!-- PAGE BREAK -->"
       . "El Derecho de Matrícula ".mb_strtolower($titulo_periodo)." es de \$ $monto_matricula ($monto_matricula_palabras pesos), que "
       . "se pagará de la siguiente forma:<br><br>"
       . $HTML_mat ."<br>"

//       . "<!-- PAGE BREAK -->"
       . "El Derecho de Colegiatura ".mb_strtolower($titulo_periodo)." convenido es de \$ $monto_arancel ($monto_arancel_palabras pesos) que se "
       . "paga de la siguiente forma:<br><br>"       
       . $HTML_arancel
       . "<br>"     
       . $tabla_pagare_cheques

       . "Los Derechos de Matrícula $tipo_contrato a los que se hace mención en esta cláusula, bajo ninguna circunstancia estarán sujetos "
       . "a devolución, atendido que la UMC ha debido incurrir en gastos para cumplir con la prestación de servicios pactada.<br><br>"      
       
       . "El proceso de matrícula se realizará a distancia o presencialmente, según la conveniencia del alumno.<br><br>"       

       . "<b>OCTAVO: </b>"
       . "Se establece un derecho a titulación y entrega de Diploma de 1UF.<br><br>"
       
       . "<b>NOVENO: </b>"
       . "Las partes dejan constancia de lo siguiente:<br>"
       . "Que el incumplimiento o mora en el pago del servicio que por este acto se suscribe, facultará a la Universidad para suspender "
       . "al alumno la prestación del servicio educacional contratado; y de persistir la morosidad, para poner término a la calidad de "
       . "alumno regular. <br>"
       . "Se aplicarán intereses a los montos en mora, de acuerdo a lo establecido en la Ley."
       . "<br><br>"
       
       . "<b>DÉCIMO: </b>"
       . "El alumno declara haber leído detalladamente el presente contrato y estar de acuerdo con todas y cada una de sus estipulaciones, "
       . "y toman debida nota que el contenido de este documento es el único que regula la relación contractual entre la UMC y el alumno y su apoderado.<br>"
       . "La vigencia del presente contrato es exclusivamente por el período académico correspondiente al $carrera_al.<br>"
       . "El presente contrato es firmado por parte de la Universidad bajo el supuesto que la documentación requerida y proporcionada por el alumno y su "
       . "apoderado, son fidedignos y legalmente válidos. En el evento que ello no fuere efectivo, el presente contrato terminará de pleno derecho, no "
       . "correspondiendo a la Universidad efectuar devolución alguna de las sumas ya pagadas o documentadas de la matrícula, el arancel, o cualquiera de "
       . "las sumas o cuotas pactadas.<br><br>"

       . "<b>DÉCIMO PRIMERO: </b>"
       . "Declaro conocer y aceptar la normativa vigente en documentos de la UMC, en el marco de la Ley 21.369, política, "
       . "procedimientos y protocolo en materia de acoso sexual, de violencia sexual y discriminación de género, además del "
       . "Código de Ética y Buena Convivencia, entre otros. Asimismo me comprometo a respetar lo dispuesto en estos textos, "
       . "todos los cuales están disponibles en www.umcervantes.cl.<br><br>"
       
       . "Para todos los efectos legales derivados del presente contrato, las partes fijan domicilio en la ciudad de Santiago "
       . "y se someten a la jurisdicción de los tribunales de justicia. El presente contrato se firma en tres ejemplares del "
       . "mismo tenor, quedando uno en poder del alumno(a), y dos en poder de la UMC.<br><br>"

       . "<br><br><br>"

       . "<table width='100%' align='center'>"
       . "  <tr>"
       . "    <td width='30%' align='center'><br><br><hr size='1' noshade>Apoderado</td>"
       . "    <td width='40%' align='center'><hr size='1' noshade>p.p. Universidad Miguel de Cervantes</td>"
       . "    <td width='30%' align='center'><br><br><hr size='1' noshade>Alumno</td>"
       . "  </tr>"
       . "</table><br>"
       . "En Santiago de Chile, a $fecha_contrato";

$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <title>UMC - SGU - Contrato de Servicios Educacionales</title>".$LF
      . "    <style>".$LF
      . "      td { font-size: 11px; font-family: sans,arial,helvetica; }".$LF
      . "      @media print {".$LF
      . "        @page {page-break-after: always; size: 21.5cm 25cm; }".$LF
      . "        td { font-size: 11px; font-family: sans,arial,helvetica; }".$LF
      . "      }".$LF
      . "    </style>".$LF
      . "  </head>".$LF
      . "  <body background='$imagen_fondo'>".$LF
      . "    <table width='100%'>".$LF
      . "      <tr>".$LF
      . "        <td>".$LF
      . "          <table width='100%'><tr><td align='center'>$titulo</td>/tr></table><br>".$LF
      . "          <table width='100%'>".$LF
      . "            <tr><td valign='top' align='justify'>$TEXTO</td></tr>".$LF
      . "          </table>".$LF
      . "        </td>".$LF
      . "      </tr>".$LF
      . "    </table>".$LF
      . "  </body>".$LF
      . "</html>".$LF;

?>

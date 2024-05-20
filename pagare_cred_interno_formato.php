<?php

$titulo = "<table width='100%'>"
        . "  <tr>"
        . "    <td align='left'   width='25%'><img src='../img/logo_umc_apaisado.jpg'></td>"
        . "    <td align='center' valign='bottom' width='50%'><h1>P A G A R É</h1><b>Nº $id_pagare_cred_interno</b></td>"
        . "    <td width='25%'>&nbsp;</td>"
        . "  </tr>"
        . "</table>";

$TEXTO = "Debo y pagaré a la orden de la Universidad Miguel de Cervantes, la suma de UF $monto.- "
       . "valor que me obligo a pagar en moneda nacional, en las oficinas de la Universidad ubicadas "
       . "en calle Enrique Mac Iver # 370 de la comuna de Santiago, ciudad de Santiago de Chile o en el domicilio que esta "
       . "tuviere a la fecha del vencimiento.<br>"       
       . "<br>"
       . "La fecha de vencimiento de este instrumento es el día $fecha_venc y, hasta esa fecha, esta obligación "
       . "no devengará intereses de ninguna naturaleza. Por el mero acto de matricularse en los periodos académicos sucesivos, "
       . "se extenderá el vencimiento de este instrumento.<br>"
       . "<br>"
       . "Una vez que alcance la condición de Egresado/a en el plan de estudios que esté cursando, deberá liquidar este "
       . "crédito bien pagando su totalidad o pactando un nuevo Pagaré Notarial, en cuotas, con una máximo de 6 meses de gracia "
       . "para el inicio del primer vencimiento. En el caso que no prosiga sus estudios por Retiro o Abandono, se procederá a "
       . "ejecutar la deuda que este instrumento establece.<br>"
       . "<br>"
       . "En mi calidad de deudor podré anticipar el pago total o parcial de esta obligación.  Sin embargo, el no "
       . "pago oportuno de esta obligación a su vencimiento dará derecho al acreedor a cobrar además del capital, "
       . "los intereses, gastos e impuestos que su cobro implique.<br>"
       . "<br>"
       . "Para todos los efectos de este instrumento constituyo domicilio en la ciudad y comuna de Santiago y me "
       . "someto a la competencia de sus Tribunales.<br>"
       . "<br>"
       . "En Santiago de Chile a $fecha_pagare<br>"
       . "<br>"
       . "<table cellpadding='2' cellspacing='0' border='1' width='100%'>"
       . "  <tr><td width='15%'>Deudor(a)<br><sub>Alumno(a)</sub></td> <td width='60%'>$nombre_al</td><td width='25%' rowspan='3' align='center' valign='bottom'><br><br><br><hr size='1' noshade><sup>Firma y Huella Dactilar</sup></td></tr>"
       . "  <tr><td width='15%'>RUT</td>                               <td width='60%'>$rut_al</td></tr>"
       . "  <tr><td width='15%'>Domicilio</td>                         <td width='60%'>$domicilio_al</td></tr>"
       . "</table><br>" 
       . "<table cellpadding='2' cellspacing='0' border='1' width='100%'>"
       . "  <tr><td width='15%'>Deudor(a)<br><sub>Apoderado(a)</sub></td> <td width='60%'>$nombre_rf</td><td width='25%' rowspan='3' align='center' valign='bottom'><br><br><br><hr size='1' noshade><sup>Firma y Huella Dactilar</sup></td></tr>"
       . "  <tr><td width='15%'>RUT</td>                                  <td width='60%'>$rut_rf</td></tr>"
       . "  <tr><td width='15%'>Domicilio</td>                            <td width='60%'>$domicilio_rf</td></tr>"
       . "</table><br>"
       . "<b>NOTA : Exento del Impuesto de la Ley de Timbres y Estampillas, Decreto Ley Nº 3.475 de 1980, Artículo 23 Nº 3</b>";

$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <title>UMC - SGU - Pagaré de Crédito Interno</title>".$LF
      . "    <style>".$LF
      . "      td { font-size: 12px; font-family: sans,arial,helvetica; }".$LF
      . "      @media print {".$LF
      . "        @page {page-break-after: always; size: 21.5cm 25cm; }".$LF
      . "        td { font-size: 12px; font-family: sans,arial,helvetica; }".$LF
      . "      }".$LF
      . "    </style>".$LF
      . "  </head>".$LF
      . "  <body background='$imagen_fondo'>".$LF
      . "    <table width='100%'>".$LF
      . "      <tr>".$LF
      . "        <td>".$LF
      . "          <table width='100%'><tr><td align='center'>$titulo</td>/tr></table><br>".$LF
      . "          <table>".$LF
      . "            <tr><td valign='top' align='justify'>$TEXTO</td></tr>".$LF
      . "          </table>".$LF
      . "        </td>".$LF
      . "      </tr>".$LF
      . "    </table>".$LF
      . "  </body>".$LF
      . "</html>".$LF;

?>

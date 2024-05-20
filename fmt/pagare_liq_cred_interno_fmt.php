<?php

$titulo = "<table width='100%'>"
        . "  <tr>"
        . "    <td align='left'   width='25%'><img src='img/logo_umc_apaisado.jpg'></td>"
        . "    <td align='center' valign='bottom' width='50%'><h1>P A G A R É</h1><b>Nº $id_pagare_liqci-$version</b></td>"
        . "    <td width='25%'>&nbsp;</td>"
        . "  </tr>"
        . "</table>";

$TEXTO = "Debo y pagaré a la orden de Corporación Universidad Miguel de Cervantes RUT 73.124.400-6, la suma de UF $monto.- "
       . "valor que me obligo a pagar en $cuotas cuotas mensuales susesivas en moneda nacional, a partir del día $fecha_ini "
       . "en las oficinas de la Universidad ubicadas en calle Enrique Mac Iver # 370 de la comuna de Santiago, ciudad de "
       . "Santiago de Chile o en el domicilio que esta tuviere a la fecha del vencimiento.<br>"       
       . "<br>"
       . "La fecha de vencimiento de este instrumento es el día $fecha_venc y, hasta esa fecha, esta obligación "
       . "no devengará intereses de ninguna naturaleza.<br>"
       . "<br>"
       . "El no pago oportuno de una o cualquiera de las cuotas en que se divide la presente obligación, dará derecho al "
       . "acreedor a hacer exigible de inmediato y anticipadamente el monto del saldo insoluto adeudado a esa fecha, el "
       . "que desde esa misma fecha, si así lo dispone el acreedor, ella se considera de plazo vencido y devengará en "
       . "favor del acreedor o de quien sus derechos represente, el interés máximo convencional que rija durante la "
       . "mora o el simple retardo.<br>"
       . "<br>"
       . "Se deja expresamente establecido que el ejercicio de este derecho constituye una sanción al suscriptor por "
       . "el no pago de la deuda e importa una mera facultad establecida en beneficio exclusivo del acreedor, que no "
       . "altera en caso alguno la fecha de vencimiento del pagaré originalmente pactada, ni la exigibilidad de las "
       . "acciones cambiarias y ejecutivas derivadas de éste.<br>"
       . "<br>"
       . "Se deja expresamente establecido que corresponderá al deudor acreditar el pago de las cuotas en que se divide "
       . "el presente instrumento, en caso de cobro judicial.<br><br>"
       . "Todas las obligaciones que emanen de este pagaré, serán solidarias para él o los suscriptores, y demás "
       . "obligándose a su pago y serán consideradas indivisibles para los efectos de los artículo 1.526 No 4 y "
       . "1.528 del código civil.<br>"
       . "<br>"
       . "Cualquier derecho o gasto que devengue la modificación de este pagaré, o cualquier otra circunstancia "
       . "relativa a aquél o producida con ocasión o motivo del mismo, será de cargo del suscriptor. <br><br>"
       . "El suscriptor libera al portador legítimo del documento de la obligación de protesto, pero éste podrá "
       . "optar por efectuarlo a su arbitrio. En el evento del protesto el suscriptor se obliga a pagar los gastos "
       . "e impuestos que dicha diligencia devengue, en conformidad a la ley.<br><br> "
       . "Para todos los efectos legales, judiciales o extrajudiciales de este pagaré, prorrogo expresamente la "
       . "competencia de los tribunales de justicia con asiento en la comuna y ciudad de Santiago, prórroga que "
       . "será obligatoria para el suscriptor. <br>"
       . "<br>"
       . "Sin perjuicio de ésta prórroga de competencia, declaro solo para efectos de emplazamiento judicial, que "
       . "mi domicilio y residencia son los indicados en el cuerpo de éste título, comprometiéndome a dar aviso por "
       . "escrito a la Universidad, en el evento que realice cualquier cambio de domicilio en el futuro.<br><br> "
       . "Autorizo a la Corporación Universidad Miguel de Cervantes, para que en caso de simple retardo, mora o "
       . "incumplimiento de las obligaciones contraídas en el presente pagaré, mis datos personales y los demás "
       . "derivados del presente pagaré puedan ser ingresados, procesados, tratados y comunicados a terceros sin "
       . "restricciones, en la base de datos o sistema de información comercial BED (Boletín Electrónico DICOM).<br>"
       . "<br>"
       . "Para todos los efectos de este instrumento constituyo domicilio en la ciudad y comuna de Santiago y me "
       . "someto a la competencia de sus Tribunales.<br>"
       . "<br>"
       . "En Santiago de Chile a $fecha_pagare<br>"
       . "<br>"
       . "<table cellpadding='2' cellspacing='0' border='1' width='100%'>"
       . "  <tr><td width='15%'>Deudor(a)<br><sub>Alumno(a)</sub></td> <td width='60%'>$nombre</td><td width='25%' rowspan='3' align='center' valign='bottom'><br><br><br><hr size='1' noshade><sup>Firma y Huella Dactilar</sup></td></tr>"
       . "  <tr><td width='15%'>RUT</td>                               <td width='60%'>$rut</td></tr>"
       . "  <tr><td width='15%'>Domicilio</td>                         <td width='60%'>$direccion, $comuna, $region</td></tr>"
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

<?php


$titulo = "<table width='100%'>"
        . "  <tr>"
        . "    <td align='left' width='25%'><img src='../img/logoumc_apaisado.jpg' width='200'></td>"
        . "    <td align='center' valign='middle' width='75%'><b>PAGARÉ <br> Nº $id_contrato</b></td>"
        . "    <td align='right'><img src='https://sgu.umc.cl/sgu/php-barcode/barcode.php?code=0$id_contrato&scale=1'></td>"
        . "  </tr>"
        . "</table>";

$TEXTO = "Debo y pagaré a la orden de Corporación Universidad Miguel de Cervantes Rut Nº 73.124.400-6, la suma de: "
       . "$ ______________ (____________________________________________________________________ pesos) valor que "
       . "me obligo a pagar en ____ cuotas mensuales iguales y sucesivas, por la suma de $ ____________ "
       . "(______________________________________________________________________ pesos) cada una con vencimiento "
       . "la primera de ellas el día ___ del mes de _______________  del año ___________, y las restantes de los "
       . "meses siguientes, siempre que sean días hábiles bancarios. En caso contrario, se pagará el día hábil bancario siguiente. "
       . "<br><br>"
       
       . "El no pago oportuno de una o cualquiera de las cuotas en que se divide la presente obligación, dará derecho al acreedor "
       . "a hacer exigible de inmediato y anticipadamente el monto del saldo insoluto adeudado a esa fecha, el que, desde esa misma "
       . "fecha, si así lo dispone el acreedor, esta se considerará de plazo vencido y se convertirá en su equivalente en Unidades "
       . "de Fomento hasta su pago efectivo, devengando en favor del acreedor o de quien sus derechos represente, el interés máximo "
       . "convencional que rija durante la mora o el simple retardo. <br><br>"
       
       . "Se deja expresamente establecido que el ejercicio de este derecho constituye una sanción al suscriptor por el no pago de "
       . "la deuda e importa una mera facultad establecida en beneficio exclusivo del acreedor, que no altera en caso alguno la fecha "
       . "de vencimiento del pagaré originalmente pactada, ni la exigibilidad de las acciones cambiarias y ejecutivas derivadas de éste. "
       . "<br><br>"
       
       . "Se deja expresamente establecido que corresponderá al deudor acreditar el pago de las cuotas en que se divide el presente "
       . "instrumento, en caso de cobro judicial. <br><br>"
       
       . "Todas las obligaciones que emanen de este pagaré serán solidarias para él o los suscriptores, y demás obligándose a su pago "
       . "y serán consideradas indivisibles para los efectos de los artículos 1.526 No 4 y 1.528 del código civil. <br><br>"
       . "Cualquier derecho o gasto que devengue la modificación de este pagaré, o cualquier otra circunstancia relativa a aquél o "
       . "producida con ocasión o motivo de este, será de cargo del suscriptor."
       . "El suscriptor libera al portador legítimo del documento de la obligación de protesto, pero éste podrá optar por efectuarlo "
       . "a su arbitrio. En el evento del protesto el suscriptor se obliga a pagar los gastos e impuestos que dicha diligencia devengue, "
       . "en conformidad a la ley. <br><br>"

       . "Para todos los efectos legales, judiciales o extrajudiciales de este pagaré, prorrogo expresamente la competencia de los "
       . "tribunales de justicia con asiento en la comuna y ciudad de Santiago, prórroga que será obligatoria para el suscriptor. "

       . "Sin perjuicio de esta prórroga de competencia, declaro solo para efectos de emplazamiento judicial, que mi domicilio y "
       . "residencia son los indicados en el cuerpo de este título, comprometiéndome a dar aviso por escrito a la Universidad, en el "
       . "evento que realice cualquier cambio de domicilio en el futuro. <br><br>"

       . "Autorizo a la Corporación Universidad Miguel de Cervantes, para que, en caso de simple retardo, mora o incumplimiento de las "
       . "obligaciones contraídas en el presente Pagaré, mis datos personales y los demás derivados del presente pagaré puedan ser "
       . "ingresados, procesados, tratados y comunicados a terceros sin restricciones, en la base de datos o sistema de información "
       . "comercial que defina la UMC. <br><br>"

       . "En la comuna de ________________________, a ___/___/______ <br><br><br><br>";

$FIRMAS = "<table width='100%'>".$LF
        . "  <tr>".$LF
        . "    <td align='center' valign='top' width='60%'>&nbsp;</td>".$LF
        . "    <td align='center' valign='top' width='40%'><hr noshade size='1'><b>Firma y Huella digital del/la Suscriptor(a)</b></td>".$LF
        . "  </tr>".$LF
        . "</table>".$LF
        . "<table width='100%' cellpadding='2' border='0.5'>".$LF
        . "  <tr>".$LF
        . "    <td valign='top' width='15%' nowrap>Representante Legal: </td><td valign='top' width='70%'> &nbsp; </td>".$LF
        . "    <td valign='top' width='5%' nowrap>RUT:</td>                <td valign='top' width='10%'> &nbsp; </td>".$LF
        . "  </tr>".$LF
        . "  <tr>".$LF
        . "    <td valign='top' width='15%' nowrap>Nombre o Razón Social: </td><td valign='top' width='70%'> &nbsp; </td>".$LF
        . "    <td valign='top' width='5%' nowrap>RUT:</td>                  <td valign='top' width='10%'> &nbsp; </td>".$LF
        . "  </tr>".$LF
        . "  <tr>".$LF
        . "    <td valign='top' widht='15%' nowrap>Domicilio: </td><td valign='top' width='85%' colspan='3'> &nbsp; </td>".$LF
        . "  </tr>".$LF
        . "</table>".$LF;

$TEXTO = $titulo . $TEXTO . $FIRMAS;

?>

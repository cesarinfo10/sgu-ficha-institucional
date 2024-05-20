<?php


$titulo = "<table width='100%'>"
        . "  <tr>"
        . "    <td align='left'><img src='../img/logoumc_apaisado.jpg'></td>"
        . "    <td align='right'>Nº $id_contrato</td>"
        . "  </tr>"
        . "</table>"
        . "<b>ANEXO<br>CONTRATO DE PRESTACIÓN DE SERVICIOS EDUCACIONALES<br>IMPLEMENTACIÓN LEY 21.369</b><br>";

$TEXTO = "Yo, don(ña) $nombre_al, RUT $rut_al, de nacionalidad $nacionalidad_al, domicilio en $domicilio_al:<br><br>"

       . "Declaro conocer y aceptar la normativa vigente en documentos de la UMC, en el marco de la Ley 21.369, política, procedimientos y protocolo en "
       . "materia de acoso sexual, de violencia sexual y discriminación de género, además del Código de Ética y Buena "
       . "Convivencia, entre otros. Asimismo me comprometo a respetar lo dispuesto en estos textos, todos los cuales están "
       . "disponibles en www.umcervantes.cl.<br><br>"

//       . "<!-- PAGE BREAK -->"
       . "<br><br><br><br><br><br>"
       . "<table align='center'>"
       . "  <tr>"
       . "    <td width='150' align='center'><hr size='1' noshade>Estudiante</td>"
       . "  </tr>"
       . "</table><br><br>"
       . "En Santiago de Chile, a $fecha_contrato";

$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <title>UMC - SGU - Anexo de Contrato de Servicios Educacionales Ley 21.369</title>".$LF
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

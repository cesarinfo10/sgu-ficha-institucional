<?php

$fecha_hora = strftime("%x %X");

$titulo = "<u>UNIVERSIDAD MIGUEL DE CERVANTES</u><br>"
        . "<sup>Vicerrectoría Académica<br>"
        . "Unidad de Registro Académico<br>"
        . "Sistema de Gestión Universitaria (SGU)<br></sup>"
        . "<b>Acta de Curso</b>";
$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <title>UMC - SGU - Acta de Curso: $asignatura</title>".$LF
      . "    <style>".$LF
      . "      td { font-size: 12px; font-family: sans,arial,helvetica; }".$LF
      . "      @media print {".$LF
      . "        @page {page-break-after: always; size: 21.5cm 25cm; }".$LF
      . "        td { font-size: 12px; font-family: sans,arial,helvetica; }".$LF
      . "      }".$LF
      . "    </style>".$LF
      . "  </head>".$LF
      . "  <body>".$LF
      . "    <table width='100%'>".$LF
      . "      <tr>".$LF
      . "        <td>".$LF
      . "          <table width='100%'><tr><td align='center'>$titulo</td><td align='right' valign='top'><img src='http://sgu.umc.cl/sgu/php-barcode/barcode.php?code=$id_curso&scale=1'><br></td></tr></table><br>".$LF
      . "          <table width='100%'>".$LF
      . "            <tr><td valign='middle' width='100%'>$IDENTIFICACION_CURSO</td></tr>".$LF
      . "          </table>".$LF
      . "        </td>".$LF
      . "      </tr>".$LF
      . "      <tr>".$LF
      . "        <td>$LISTA_DE_CURSO</td>".$LF
      . "      </tr>".$LF
      . "    </table><br><br><br><br><br>".$LF
      . "    <table width='100%'>".$LF
      . "      <tr>".$LF
      . "        <td>$FIRMAS</td>".$LF
      . "      </tr>".$LF
      . "    </table>".$LF
      . "  </body>".$LF
      . "</html>".$LF;

?>

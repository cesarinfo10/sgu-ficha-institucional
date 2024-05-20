<?php

$fecha_hora = strftime("%x %X");

$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <meta content='text/html; charset=UTF-8' http-equiv='content-type'>".$LF
      . "    <title>UMC - SGU - Comprobante de Inscripción de Asignaturas</title>".$LF
      . "    <style>td { font-size: 12px; font-family: sans,arial,helvetica }</style>".$LF
      . "  </head>".$LF
      . "  <body topmargin='0' leftmargin='0' rightmargin='0'>".$LF
      . "    <table width='100%'>".$LF
      . "      <tr>".$LF
      . "        <td>".$LF
      . "          <table width='100%'><tr><td><b>UMC - SGU - Comprobante de Inscripción de Asignaturas (Toma de Ramos)</b></td><td align='right'>$fecha_hora</td></tr></table><br>".$LF
      . "          <center><img src='img/logoUMC.gif'></center><br><br>"
      . "          $IDENTIFICACION_ALUMNO".$LF
      . "        </td>".$LF
      . "      </tr>".$LF
      . "      <tr>".$LF
      . "        <td>$LISTA_DE_CURSOS</td>".$LF
      . "      </tr>".$LF
      . "      <tr>".$LF
      . "        <td>$HORARIO</td>".$LF
      . "      </tr>".$LF
      . "      <tr>".$LF
      . "        <td>$FIRMAS</td>".$LF
      . "      </tr>".$LF
      . "    </table>".$LF
      . "  </body>".$LF
      . "</html>".$LF;

?>

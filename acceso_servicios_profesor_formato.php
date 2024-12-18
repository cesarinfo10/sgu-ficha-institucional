<?php

$titulo = "<table width='100%'>"
        . "  <tr>"
        . "    <td align='center'>"
        . "      <img src='img/logo_UMC_DIC.jpg'>"
        . "      <b>Acceso a Servicios de Internet</b>"
        . "    </td>"
        . "  </tr>"
        . "</table><br>";

$TEXTO = "$vocativo $nombre<br><br>"

       . "La Universidad le ha asignado una cuenta de usuario de Acceso a Servicios Internet, con el "
       . "fin de acceder a:<br>"
       . "<ol>"
       . "  <li><b>Correo Electrónico:</b> Sistema estándar de mensajería por Internet, que se accede a "
       . "      través de WebMail. Así mismo, su casilla electrónica es $email. A través de esta casilla "
       . "      podrá estar en permanente contacto con sus alumnos y autoridades de nuestra Universidad y "
       . "      recibir información importante para su desempeño académico. Este es el medio oficial de "
       . "      comunicación entre la Universidad y usted."
       . "  </li><br>"
       . "  <li><b>SGU:</b> El Sistema de Gestión Universitaria, es un software que entre algunas de sus "
       . "      funciones, la principal probablemente, es gestionar y registrar las calificaciones de los "
       . "      alumnos. Es su deber registrar todas las calificaciones que resulten de la aplicación de las "
       . "      distintas pruebas solemnes y controles parciales. Al ingresar a su correo electrónico, "
       . "      encontrará un instructivo para llevar a cabo el registro de calificaciones."
       . "  </li>"
       . "</ol><br>"
       
       . "Para entrar a estos Servicios Internet, debe ingresar a nuestro portal en "
       . "<a href='http://www.umcervantes.cl/'>http://www.umcervantes.cl/</a> "
       . "con los datos que más abajo se especifican, en la sección Acceso a Servicios Internet.<br><br>"
       
       . "Para cambiar su contraseña, debe acceder al WebMail, luego pinchando en “Opciones” y finalmente "
       . "en &laquo;Cambiar Contraseña&raquo;.<br><br>"       
       
       . "<table cellpadding='2' cellspacing='0' border='1' align='center' width='90%'>"
       . "  <tr>"
       . "    <td align='center'>"
       . "      <table>"
       . "        <tr>"
       . "          <td align='right'>"
       . "            Nombre de Usuario:<br>"
       . "            Contraseña:<br>"
       . "            Perfil:"
       . "          </td>"
       . "          <td>"
       . "            $nombre_usuario<br>"
       . "            $clave<br>"
       . "            Profesor"
       . "          </td>"
       . "        </tr>"
       . "      </table>"
       . "    </td>"
       . "  </tr>"
       . "</table><br><br>"
       . "En Santiago de Chile, a $fecha";

$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <title>UMC - SGU - Acceso a Servicios Internet</title>".$LF
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

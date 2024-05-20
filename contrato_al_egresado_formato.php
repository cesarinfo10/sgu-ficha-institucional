<?php

if (empty($tipo_contrato)) { $tipo_contrato = "Anual"; }

$titulo = "<table width='100%'>"
        . "  <tr>"
        . "    <td align='left'><img src='img/logo_umc_apaisado.jpg'></td>"
        . "    <td align='right'>Nº $id_contrato</td>"
        . "  </tr>"
        . "</table>"
        . "<b>CONTRATO DE PRESTACIÓN DE SERVICIOS EDUCACIONALES</b><br>";

$TEXTO = "Entre la Universidad Miguel de Cervantes, Corporación de Derecho Privado de Educación Superior, "
       . "Rut: 73.124.400-6, representada por su $REPRESENTANTE_LEGAL, ambos domiciliados "
       . "calle Mac Iver N° 370, de esta ciudad, en adelante «la Universidad» y/o «UMC», por una parte, y "
       . "por la otra, don(ña) $nombre_al, de nacionalidad $nacionalidad_al, domicilio en $domicilio_al, en adelante "
       . "el alumno se ha convenido el siguiente contrato de prestación de servicios educacionales:<br><br>"
       . "<b>PRIMERO</b><br>"
       . "El (compareciente don(ña) $nombre_al) es egresado en proceso de titulación se matricula como "
       . "alumno regular de la Universidad Miguel de Cervantes, en la carrera de $carrera_al.<br><br>"
       . "<b>SEGUNDO</b><br>"
       . "El alumno(a) en su calidad de Egresado(a) en proceso de titulación tendrá derecho por el plazo del "
       . "año calendario 2013 a:<br><ol>"
       . "<li>Inscribir hasta dos asignaturas por semestre, sin derecho a rendir evaluaciones ni opción de aprobar dichos cursos.</li>"
       . "<li>Usar los servicios informáticos de la Universidad.</li>"
       . "<li>Acceder a los servicios de Biblioteca.</li>"
       . "<li>Acogerse a los convenios vigentes que la Universidad haya celebrado con entidades externas en beneficio "
       . "de sus alumnos cuando corresponda.</li></ol><br><br>"
       . "<b>TERCERO</b><br>"
       . "Por su parte el alumno declara conocer el Estatuto y todos los reglamentos de la Universidad que regulan "
       . "las actividades académicas y formativas para obtener el respectivo título y/o grado, y los derechos y "
       . "obligaciones del alumno. Además se deja constancia que la Información pertinente, Estatutos y Reglamentos "
       . "aplicables a este contrato están publicados en el portal «www.umcervantes.cl» hecho del cual el estudiante "
       . "certifica conocer. El alumno en este acto se obliga a conocer y a respetar la reglamentación interna de la "
       . "Universidad en lo que le sea aplicable, y en general toda otra norma que emane de la institucionalidad "
       . "universitaria. El incumplimiento por parte del alumno de dicha reglamentación dará derecho a la UMC a "
       . "poner término al presente contrato, sin necesidad de declaración judicial.<br><br>"
       . "Las actividades académicas correspondientes a la carrera en la que se matriculó el alumno tendrán lugar "
       . "en la sede central de la Universidad, o en otra instalación según lo disponga la UMC. El alumno declara "
       . "conocer la infraestructura de la universidad  y manifiestan su plena satisfacción con esta.<br><br>"
       . "<b>CUARTO</b><br>"
       . "Los cursos y/o asignaturas que la Universidad pone a disposición del alumno son todos los que durante "
       . "el periodo académico 2013 se dicten para las distintas carreras. Cada uno de estos cursos se impartirá "
       . "sólo una vez al año, en el semestre académico correspondiente, determinado por el plan de estudios de las "
       . "distintas carreras.<br><br>"
       . "<b>QUINTO</b><br>"
       . "Cuando el número de alumnos por curso así lo permita, la Vicerrectoría Académica podrá determinar que "
       . "dicho curso y/o asignatura sea dictado bajo el sistema tutorial o personalizado. Asimismo, los horarios "
       . "y jornadas de los cursos serán determinados semestralmente por la Dirección de la Escuela respectiva en "
       . "función de las necesidades de la estructura curricular. En todo caso, los cursos y/o asignaturas podrán "
       . "dictarse de lunes a sábado cualquiera sea la jornada que curse el alumno.<br><br>"
       
       . "<!-- PAGE BREAK -->"

       . "<b>SEXTO</b><br>"
       . "El valor del derecho de matrícula anual corresponden al total de la prestación contratada para dicho "
       . "período. La Universidad fijará anualmente estos derechos.<br><br>"
       . "Se deja establecido que aún cuando el alumno, por cualquier causa o motivo, no hiciere uso de este "
       . "servicio educacional deberá dar cumplimiento a la totalidad de la obligación contraída en este contrato.<br><br>"
       . "El alumno manifiesta su conformidad en que a partir de esta fecha, toda información y comunicación oficial de "
       . "la Universidad sea enviada a través del correo electrónico, a la dirección personal que para estos efectos se "
       . "ha creado a cada alumno. Su dirección de e-mail es: la correspondiente a su primer nombre y su apellido paterno "
       . "más la expresión @al.umcervantes.cl. Sin perjuicio de lo anterior, la Universidad podrá enviar comunicaciones al "
       . "domicilio que el alumno haya entregado a la Universidad. En todo caso será responsabilidad del alumno informar "
       . "del cambio de domicilio.<br><br>"

       . "<b>SÉPTIMO</b><br>"
       . "El alumno, en este acto expresa su consentimiento y conformidad con la facultad de la Universidad de extender "
       . "o denegar certificaciones académicas o de cualquier otro orden, ante el incumplimiento de las obligaciones "
       . "contraídas en virtud del presente contrato, renunciando desde ya al ejercicio de acciones legales de cualquier "
       . "naturaleza por este motivo. Los alumnos que a través de este documento hayan reprogramado deuda anterior "
       . "quedarán sujetos a igual restricción y renuncia de acciones.<br><br>"
       . "El alumno deberá observar las normas reglamentarias internas para inscribir sus cursos o actividades.<br><br>"
       . "También se deja constancia que el proceso de titulación constituye una actividad académica regular del programa "
       . "o carrera, por lo tanto requiere que el estudiante esté matriculado para el período académico respectivo, y que "
       . "cancele los aranceles correspondientes a su proceso de titulación.<br><br>"

       . "<b>OCTAVO</b><br>"
       . "En caso que el estudiante ocasionare daños materiales al patrimonio de la Universidad, deberá pagar solidariamente "
       . "la reparación o reposición de los daños causados, sin perjuicio de las sanciones reglamentarias y/o legales que "
       . "correspondan.<br><br>"
       . "Las partes dejan constancia que la Universidad no será responsable por los perjuicios producidos por la pérdida "
       . "o sustracción de efectos personales de propiedad del estudiante que se introduzcan o mantengan en los recintos "
       . "universitarios; por consiguiente, el estudiante declara conocer su obligación de mantener el debido resguardo "
       . "sobre dichos objetos.<br><br>" 

       //. "<!-- PAGE BREAK -->"

       . "<b>NOVENO</b><br>"
       . "La individualización del alumno y apoderado de la comparecencia y los datos de los servicios académicos "
       . "contratados son:<br><br>"
       . "<table cellpadding='2' cellspacing='0' border='1' align='center' width='90%'>"
       . "  <tr><td width='20%'>Alumna/alumno</td> <td width='80%'>$nombre_al</td></tr>"
       . "  <tr><td width='20%'>RUT Alumna/o</td>  <td width='80%'>$rut_al</td></tr>"
       . "  <tr><td width='20%'>Domicilio</td>     <td width='80%'>$domicilio_al</td></tr>"
       . "  <tr><td width='20%'>Fono Fijo</td>     <td width='80%'>$telefono_al</td></tr>"
       . "  <tr><td width='20%'>Fono Celular</td>  <td width='80%'>$tel_movil_al</td></tr>"
       . "</table><br>"
       . "<table cellpadding='2' cellspacing='0' border='1' align='center' width='90%'>"
       . "  <tr><td width='20%'>Carrera</td> <td width='80%'>$carrera_al</td></tr>"
       . "  <tr><td width='20%'>Jornada</td> <td width='80%'>$jornada_al</td></tr>"
       . "</table><br><br>"       
       . "El Derecho de Matrícula Anual $tipo_contrato convenido es de \$ $monto_matricula ($monto_matricula_palabras pesos), que "
       . "se paga al contado, en este acto.<br><br>"

       . "<b>DÉCIMO</b><br>"
       . "Las partes dejan constancia de los siguiente:<br>"
       . "El alumno tendrá derecho a Retirarse formalmente, pero sin devolución del derecho de Matricula pagado en este acto.<br><br>"
 
       . "<!-- PAGE BREAK -->"

       . "<b>UNDÉCIMO</b><br>"
       . "El estudiante declara haber leído detalladamente el presente contrato y estar de acuerdo con todas y cada una de sus "
       . "estipulaciones, y toman debida nota de que el contenido de este documento es el único que regula la relación "
       . "contractual entre la UMC y el estudiante.<br><br>"
       . "La vigencia del presente contrato es exclusivamente por el período académico señalado en este instrumento. La "
       . "celebración de un nuevo contrato entre las partes está condicionada al estricto cumplimiento por parte del "
       . "estudiante de lo establecido en los Reglamentos que regulan sus estudios.<br><br>"
       . "El presente contrato es firmado por parte de la Universidad bajo el supuesto que todo lo declarado por el "
       . "Estudiante, así como la documentación requerida y proporcionada, son fidedignos. En el evento que ello no "
       . "fuere efectivo, el presente contrato terminará de pleno derecho, no correspondiendo a la Universidad efectuar "
       . "devolución alguna de las sumas ya pagadas o documentadas de la matrícula, el arancel, o cualquiera de las sumas "
       . "o cuotas pactadas. Para todos los efectos legales derivados del presente contrato, las partes fijan domicilio en "
       . "la ciudad de Santiago y se someten a la jurisdicción de los tribunales de justicia. El presente contrato se firma "
       . "en tres ejemplares del mismo tenor, quedando uno en poder del alumno(a), y dos en poder de la UMC.<br><br>"
       . "<br><br><br><br><br><br><br><br><br>"
       . "<table align='center'>"
       . "  <tr><td><hr size='1' noshade>p.p. Universidad Miguel de Cervantes</td></tr>"
       . "</table><br><br><br><br><br><br>"
       . "<table align='center'>"
       . "  <tr><td width='150' align='center'><hr size='1' noshade>Alumno</td></tr>"
       . "</table><br>"
       . "En Santiago de Chile, a $fecha_contrato";

$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <title>UMC - SGU - Contrato de Servicios Educacionales</title>".$LF
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

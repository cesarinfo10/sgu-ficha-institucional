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
       . "por la otra, don(ña) <u>$nombre_al</u>, de nacionalidad <u>$nacionalidad_al</u>, domicilio en <u>$domicilio_al</u>, "
       . "profesión <u>$profesion_al</u>, estado civil <u>$est_civil_al</u>, RUT <u>$rut_al</u>, en adelante «el alumno», "
       . "en conformidad a las disposiciones legales y al Código Civil se ha convenido el "
       . "siguiente contrato de prestación de servicios educacionales:<br><br>"
       . "<b>PRIMERO</b><br>"
       . "Por el presente acto el compareciente don(ña) <u>$nombre_al</u>, se matricula como alumno(a) de la Universidad Miguel "
       . "de Cervantes, en el programa de <u>$carrera_al</u>, en adelante «el Diplomado», una vez que haya cumplido con todos "
       . "los requisitos y haya hecho entrega a las instancias correspondientes de la Universidad de toda la documentación "
       . "obligatoria para el ingreso a esta casa de estudios, todo lo cual se encuentra especificado en la información "
       . "proporcionada por la UMC y que el(la) alumno(a) compareciente declara conocer y aceptar.<br><br>"
       . "<b>SEGUNDO</b><br>"
       . "En virtud del presente contrato, la Universidad se obliga a mantener el cupo correspondiente, para "
       . "el alumno regular en los servicios docentes que le sean pertinentes y que prestará durante la duración establecida "
       . "para este Diplomado y se deja constancia que podrá realizar los ajustes administrativos y/o académicos que estime "
       . "convenientes para el buen desarrollo de este Programa. El alumno declara conocer la reglamentación de la Universidad "
       . "y los derechos y obligaciones que de ahí emanan respecto a su condición de alumno, información que se encuentra "
       . "disponible y que el(la) alumno(a) declara conocer. El alumno declara conocer que el Diplomado se realizará solo "
       . "si en este y en el plazo indicado en la Convocatoria, se matriculan un mínimo de personas.<br><br>"
       . "Las actividades académicas correspondientes al Diplomado en donde se matriculó el alumno(a) tendrán lugar en la sede "
       . "central de la Universidad, o en otra instalación de similar característica.<br><br>"
       . "<b>TERCERO</b><br>"
       . "Los cursos y/o asignaturas que la Universidad imparte corresponden, al plan de estudios del Diplomado, "
       . "el que se impartirá en el periodo estipulado en la convocatoria, en el semestre académico correspondiente, "
       . "determinado por el plan de estudios del Diplomado en el que se matriculó el(la) alumno(a).<br><br>"
       . "Los cursos y/o asignaturas están establecidos en el plan de estudios del Diplomado, según  el programa "
       . "establecido en la convocatoria y en conformidad con la reglamentación vigente en la Universidad.<br><br>"
       . "<b>CUARTO</b><br>"
       . "El valor del arancel corresponde al total de la prestación contratada, es decir, para cursar el Diplomado "
       . "ya individualizado. Una vez aprobado este programa el alumno podrá solicitar la certificación correspondiente "
       . "de la Universidad, sólo una vez que se encuentren totalmente pagadas la matrícula y arancel antes indicado. "
       . "Los pagos mensuales, cuando corresponda, se efectuarán en las oficinas de la Universidad, o por medio de la "
       . "Institución financiera a la cual ella hubiere encomendado su recaudación y/o cobro, no estando obligadas ni la "
       . "Universidad ni la Institución financiera a notificar en cada oportunidad las fechas de vencimiento del compromiso "
       . "del pago. El alumno autoriza expresamente a la Universidad para informar a DICOM en el caso de no cumplir el pago "
       . "señalado en la cláusula siguiente.<br><br>"
       . "<!-- PAGE BREAK -->"
       . "<b>QUINTO</b><br>"
       . "La individualización del alumno(a) de la comparecencia y los datos de los servicios académicos "
       . "contratados son:<br><br>"
       . "<table cellpadding='2' cellspacing='0' border='1' align='center' width='90%'>"
       . "  <tr><td width='20%'>Alumno(a)</td> <td width='80%'>$nombre_al</td></tr>"
       . "  <tr><td width='20%'>RUT Alumno(a)</td>  <td width='80%'>$rut_al</td></tr>"
       . "  <tr><td width='20%'>Domicilio</td>     <td width='80%'>$domicilio_al</td></tr>"
       . "  <tr><td width='20%'>Fono Fijo</td>     <td width='80%'>$telefono_al</td></tr>"
       . "  <tr><td width='20%'>Fono Celular</td>  <td width='80%'>$tel_movil_al</td></tr>"
       . "  <tr><td width='20%'>Programa</td> <td width='80%'>$carrera_al</td></tr>"
       . "</table><br>"
       . "El Arancel convenido es de \$ $monto_arancel ($monto_arancel_palabras pesos) y este bajo ninguna circunstancia estará "
       . "sujeto a devolución.<br><br>"
       . "Se paga de la siguiente forma:"
       . "<table cellpadding='2' align='center'>"
       . "  <tr>"
       . "    <td nowrap>&raquo; Con el otorgamiento de una beca de Procedencia por la suma de</td>"
       . "    <td align='right'>\$ $monto_convenio</td>"
       . "  </tr>"
       . "  <tr>"
       . "    <td nowrap>&raquo; Con el otorgamiento de una beca de excelencia por la suma de</td>"
       . "    <td align='right'>\$ $monto_beca_arancel_excelencia</td>"
       . "  </tr>"
       . "  <tr>"
       . "    <td nowrap>&raquo; Con el otorgamiento de la beca UMC por la suma de</td>"
       . "    <td align='right'>\$ $monto_beca_arancel_umc</td>"
       . "  </tr>"
       . "  <tr>"
       . "    <td nowrap>&raquo; Al contado, en este acto, la suma de</td>"
       . "    <td align='right'>\$ $arancel_efectivo</td>"
       . "  </tr>"
       . "  <tr>"
       . "    <td align='right'>Saldo</td>"
       . "    <td align='right'>\$ $arancel_saldo</td>"
       . "  </tr>"
       . "</table><br>"       
       . $tabla_pagare_cheques
       . "<b>SEXTO</b><br>"
       . "Declaro conocer y aceptar la normativa vigente en documentos de la UMC, en el marco de la Ley 21.369, política, procedimientos y protocolo en "
       . "materia de acoso sexual, de violencia sexual y discriminación de género, además del Código de Ética y Buena "
       . "Convivencia, entre otros. Asimismo me comprometo a respetar lo dispuesto en estos textos, todos los cuales están "
       . "disponibles en www.umcervantes.cl.<br><br>"
       
       . "Para todos los efectos legales derivados del "
       . "presente contrato, las partes fijan domicilio en la ciudad de Santiago y se someten a la jurisdicción "
       . "de los tribunales de justicia. El presente contrato se firma en tres ejemplares del mismo tenor, "
       . "quedando uno en poder del alumno(a), otro en poder de Vicerrectoría de Administración y Finanzas y otro "
       . "en poder de Vicerrectoría Extensión y Comunicaciones.<br><br>"
       . "<br><br><br><br><br><br>"
       . "<table align='center'>"
       . "  <tr><td><hr size='1' noshade>p.p. Universidad Miguel de Cervantes</td></tr>"
       . "</table><br><br><br><br><br><br>"
       . "<table align='center'>"
       . "  <tr>"
       . "    <td width='150' align='center'><hr size='1' noshade>Alumno</td>"
       . "  </tr>"
       . "</table><br><br>"
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

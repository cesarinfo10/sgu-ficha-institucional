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
        . "<b>ALUMNOS ANTIGUOS</b><br>"
        . "<b>$titulo_periodo</b><br>";

$TEXTO = "Entre la Universidad Miguel de Cervantes, Corporación de Derecho Privado de Educación Superior, "
       . "Rut: 73.124.400-6, representada por su $REPRESENTANTE_LEGAL, ambos domiciliados "
       . "calle Mac Iver N° 370, de esta ciudad, en adelante «la Universidad» y/o «UMC», por una parte, y "
       . "por la otra, don(ña) $nombre_al, RUT $rut_al, de nacionalidad $nacionalidad_al, domicilio en $domicilio_al, en adelante "
       . "el alumno, y don(ña) $nombre_rf, RUT $rut_rf, de nacionalidad $nacionalidad_rf, profesión $profesion_rf, "
       . "estado civil $est_civil_rf, domicilio en $domicilio_rf, en adelante "
       . "el apoderado se ha convenido el siguiente contrato de prestación de servicios educacionales:<br><br>"
       
       . "<b>PRIMERO: </b>"
       . "Por el presente acto, el compareciente (don(ña) $nombre_al) se entenderá matriculado como alumno regular "
       . "de la Universidad Miguel de Cervantes, en la carrera de $carrera_al, $texto_periodo y en la jornada $jornada_al, "
       . "una vez que haya cumplido con todos los requisitos y entregados a las instancias correspondientes de la "
       . "Universidad toda la documentación obligatoria para el ingreso a esta casa de estudios, todo lo cual "
       . "se encuentra especificado en la información proporcionada por la UMC y que tanto el alumno como el "
       . "apoderado comparecientes declaran conocer y aceptar. Asimismo, el estudiante se obliga a contestar "
       . "las distintas encuestas que se apliquen por la UMC, en especial las encuestas de satisfacción, de "
       . "evaluación docente, de informantes claves en procesos de acreditación y de SINERGIA entre otras.<br><br>"

       . "<b>SEGUNDO: </b>"
       . "En virtud del presente contrato, la Universidad se obliga a mantener el cupo correspondiente, para el "
       . "alumno regular en los servicios docentes que le sean pertinentes y que prestará durante los periodos "
       . "académicos en que se matricula. Para estos efectos, se considerará como parte integrante de este contrato "
       . "el plan de estudio de la carrera en la que se matricula el alumno. Esto, sin perjuicio del derecho de la UMC "
       . "de modificar ese Plan de Estudios en atención a razones académicas, exigencias, por fuerza mayor o recomendaciones "
       . "de la autoridad pública competente y/o de los organismos responsables de realizar los procesos de acreditación "
       . "institucional y/o de carreras. Por su parte el alumno y su apoderado declaran conocer el Estatuto y todos los "
       . "reglamentos de la Universidad que regulan las actividades académicas y formativas para obtener el respectivo "
       . "título y/o grado, y los derechos y obligaciones del alumno. Además, se deja constancia que la información "
       . "pertinente, Estatutos y Reglamentos aplicables a este contrato están publicados en el portal «www.umcervantes.cl» "
       . "hecho del cual el alumno y su apoderado certifican conocer. El alumno en este acto se obliga a conocer y a respetar "
       . "la reglamentación interna de la Universidad, y en general toda otra norma que emane de la institucionalidad "
       . "universitaria. El incumplimiento por parte del alumno de dicha reglamentación dará derecho a la UMC a poner "
       . "término al presente contrato, sin necesidad de declaración judicial.<br>"
       . "Las actividades académicas correspondientes a la carrera en la que se matriculó el alumno tendrán lugar en la "
       . "sede central de la Universidad, o en otra instalación según lo disponga la UMC y se podrá realizar parte de estas "
       . "actividades académicas por sistemas especiales de educación a distancia las que podrán representar hasta un treinta "
       . "y nueve por ciento del plan de estudio de cada semestre. De igual modo por razones de fuerza mayor, "
       . "caso fortuito, salud pública tipo pandemia o similares, o por existir dificultades para realizar clases presenciales, "
       . "sea por impedimento o dificultades de acceso al local de la UMC, por limitaciones en el transporte público o limitaciones "
       . "horarias dictadas por la autoridad, se podrán utilizar sistemas especiales de educación a distancia, en la totalidad de "
       . "las actividades académicas, por el tiempo que la UMC decida.<br>"
       . "Presente en este acto el apoderado antes individualizado quien declara que se constituye en fiador y codeudor solidario "
       . "de todas y cada una de las obligaciones que para con la Universidad Miguel de Cervantes ha asumido el alumno contratante "
       . "en virtud de este instrumento, renunciando expresamente al beneficio de excusión. El alumno y su apoderado declaran "
       . "contar con la implementación necesaria para acceder electrónicamente vía internet a la educación a distancia, esto es "
       . "conectividad, accesibilidad, y, equipos adecuados para participar en clases virtuales sincrónicas y asincrónicas, y "
       . "además, declaran conocer la infraestructura de la Universidad manifestando su plena satisfacción con esta.<br>"
       . "El apoderado y el alumno declaran conocer que este contrato puede requerir ser modificado, en las siguientes situaciones "
       . "que afecten a alumno: cambio de jornada de estudios, incorporación o eliminación de becas de la UMC o de terceros, o "
       . "beneficios o crédito con aval del estado, cambio de datos personales del alumno u otras modificaciones objetivas "
       . "similares. El apoderado y codeudor solidario acepta expresamente  que este contrato sea modificado por las partes "
       . "(alumno contratante y UMC) en las situaciones antes descritas, mediante anexos suscritos por el alumno y la UMC, "
       . "dichas modificaciones le serán aplicables, sin necesidad de concurrir a la firma del respectivo anexo, todo ello "
       . "en cuanto no implique para el apoderado y codeudor solidario una obligación económica más gravosa que la originalmente "
       . "asumida a la firma de este contrato y su eventual renovación para el semestre siguiente. El apoderado y codeudor solidario "
       . "da a las obligaciones contraídas el carácter de indivisibles, pudiendo, en consecuencia, exigirse el pago total a "
       . "cualquiera de sus herederos, de conformidad con el artículo 1.528 del Código Civil.<br><br>"

       . "<b>TERCERO: </b>"
       . "Los cursos y/o asignaturas que la Universidad imparte corresponden, al plan de estudios de la respectiva carrera. "
       . "Cada uno de estos cursos se impartirá sólo una vez al año, en el semestre académico correspondiente, determinado "
       . "por el plan de estudios de la carrera en la que se matriculó el alumno.<br>"
       . "Aquel alumno que haya realizado retiro, suspensión de estudios o abandono y posteriormente decida solicitar "
       . "formalmente su reincorporación deberá adscribirse al plan de estudios vigente en su carrera. La Universidad "
       . "impartirá cursos y/o asignaturas de carácter transversal, del mismo plan de estudios o que tengan el mismo programa, "
       . "esto es, cursos y/o asignaturas en los que participen alumnos de cualquier carrera y/o jornada, y estos se impartirán "
       . "en la jornada vespertina y el día sábado. De igual modo, aquellas asignaturas de la jornada diurna que no reúnan un "
       . "mínimo de diez estudiantes regulares válidamente inscritos para el respectivo período y nivel, podrán ser fusionadas "
       . "con las asignaturas iguales de la jornada vespertina, abriendo para ello un solo curso en horario vespertino y además "
       . "el día sábado.<br>"
       . "Los cursos y/o asignaturas que el alumno podrá inscribir en cada semestre dependerán del cumplimiento de los "
       . "prerrequisitos académicos y de admisión establecidos en el plan de estudios de la respectiva carrera, según la "
       . "jornada en que se matriculó y en conformidad con la reglamentación vigente en la Universidad.<br>"
       . "A los alumnos que no inscriban la carga curricular correspondiente a su Plan de Estudios o no efectúen la inscripción "
       . "de asignaturas en tiempo, oportunidad y forma establecida por la UMC, no se les hará devolución arancelaria alguna, "
       . "debiendo dar cumplimiento a la totalidad de la obligación contraída. Teniendo en consideración la existencia virtual "
       . "y física de textos en la Biblioteca Institucional, no forma parte de este contrato la entrega de estos, para el "
       . "cumplimiento de las exigencias académicas del alumno.<br>"
       . "No será válida la inscripción de asignaturas que no cumplan secuencialmente los prerrequisitos académicos, salvo las "
       . "autorizadas por las Vicerrectoría Académica de la UMC.<br><br>"
//       . "<!-- PAGE BREAK -->"

       . "<b>CUARTO: </b>"
       . "Cuando el número de alumnos por curso así lo permita, la Vicerrectoría Académica podrá determinar que dicho curso y/o "
       . "asignatura sea dictado bajo el sistema tutorial o personalizado. Asimismo, los horarios y jornadas de los cursos serán "
       . "determinados semestralmente por la Dirección de la Escuela respectiva en función de las necesidades de la estructura "
       . "curricular. En todo caso, los cursos y/o asignaturas podrán dictarse de lunes a sábado cualquiera sea la jornada que "
       . "curse el alumno.<br><br>"

       . "<b>QUINTO: </b>"
       . "El valor del derecho de matrícula y el valor del arancel semestral corresponden al total de la prestación contratada "
       . "para dicho período. La Universidad fijará anualmente el valor del derecho de matrícula y el valor del arancel, y a "
       . "partir de este el que corresponda al contrato semestral.<br>"
       . "Se deja establecido que aun cuando el alumno, por cualquier causa o motivo, no hiciere uso de este servicio educacional "
       . "deberá dar cumplimiento a la totalidad de la obligación contraída en este contrato y en los pagarés que sustentan dicha "
       . "obligación, salvo que haya efectuado el trámite de retiro en conformidad a la ley, a la reglamentación de la UMC o a los "
       . "términos de este instrumento.<br>"
       . "Los pagos correspondientes, se efectuarán en las oficinas de la Universidad o mediante transferencia electrónica "
       . "debidamente informada a la UMC al correo electrónico «transferencias@corp.umc.cl» indicando el nombre y el RUT del "
       . "alumno, o por medio de la Institución financiera a la cual la UMC hubiere encomendado su recaudación y/o cobro, no "
       . "estando obligadas ni la Universidad ni la Institución financiera a notificar en cada oportunidad las fechas de "
       . "vencimiento del compromiso de pago. En el caso de pago en cuotas, el incumplimiento o mora en el pago de alguna de "
       . "ellas, dará derecho a la Universidad a exigir el pago total de lo adeudado. Transcurridos diez días hábiles desde la "
       . "fecha de pago y estando moroso el pago, lo adeudado se transformará automáticamente en el equivalente en unidades de "
       . "fomento, las que a su vez se convertirán en pesos a la fecha en que dicho pago se realice.<br>"
       . "El alumno manifiesta su conformidad en que, a partir de esta fecha, toda información y comunicación oficial de la "
       . "Universidad pueda ser enviada a través de su dirección personal, al correo electrónico institucional que para estos "
       . "efectos se ha creado a cada alumno. Su dirección de e-mail es la correspondiente al primer nombre seguido de un punto "
       . "y seguido de su apellido paterno más la expresión «@alumni.umc.cl». En caso de existir una dirección electrónica "
       . "idéntica a otra anteriormente asignada a un alumno antiguo de la UMC, el Departamento de Informática asignará una "
       . "dirección electrónica distinta para el alumno, según establecen los procedimientos respectivos. El apoderado deberá "
       . "entregar su correo electrónico personal y su domicilio para los efectos de recibir comunicaciones oficiales de la "
       . "Universidad y tendrá derecho a solicitar la información referida a pagos y calificaciones del alumno. Sin perjuicio "
       . "de lo anterior, la UMC podrá enviar comunicaciones al domicilio y/o al correo electrónico personal que el alumno y el "
       . "apoderado hayan entregado a la UMC. En todo caso será responsabilidad del alumno y del apoderado informar del cambio de "
       . "domicilio y/o de sus correos electrónicos personales. El alumno deberá validar anualmente su domicilio, teléfono móvil y "
       . "su correo electrónico personal, como requisito de matrícula y a su vez deberá revalidarlo cuando así se lo solicite la "
       . "UMC.<br><br>"

       . "<b>SEXTO: </b>"
       . "La UMC podrá poner término al presente contrato, si el alumno no hubiere cumplido con los requisitos de admisión e "
       . "ingreso. El alumno y su apoderado, en este acto expresan su consentimiento y conformidad con la facultad de la UMC de "
       . "extender o denegar certificaciones académicas o de cualquier otro orden, ante el incumplimiento de las obligaciones "
       . "contraídas en virtud del presente contrato, renunciando desde ya al ejercicio de acciones legales de cualquier naturaleza "
       . "por este motivo. Los alumnos que a través de este documento hayan reprogramado deuda anterior quedarán sujetos a igual "
       . "restricción y renuncia de acciones. El alumno deberá observar las normas reglamentarias internas para inscribir sus "
       . "cursos o actividades; por consiguiente, si el alumno debe repetir uno o más cursos alterándose el orden o número de "
       . "cursos que debe inscribir, ello será de su exclusiva responsabilidad, es decir, la Universidad no estará obligada a "
       . "otorgar facilidades, horarios especiales u otras medidas equivalentes.<br>"
       . "También se deja constancia que cuando el proceso de titulación constituye una actividad académica regular del programa "
       . "o carrera, se requiere que el alumno esté matriculado para el período académico respectivo, y que además cancele los "
       . "aranceles correspondientes a su proceso de titulación.<br><br>"

       . "<b>SÉPTIMO: </b>"
       . "De todas las obligaciones económicas y financieras estipuladas en el presente contrato o que se deriven de él, serán "
       . "solidaria e indivisiblemente responsables, el alumno y su apoderado. Por este instrumento, el alumno y su apoderado "
       . "autorizan expresamente y en conformidad a la Ley, a la Universidad Miguel de Cervantes para que, separada e "
       . "individualmente, en caso de simple retardo, mora o incumplimiento total o parcial de las obligaciones contraídas "
       . "en este contrato, sus datos personales y los demás derivados de éste (del alumno y/o su apoderado) puedan ser "
       . "tratados y/o comunicados a terceros sin restricciones, a efecto de la cobranza.<br>"
       . "En el caso que el alumno presente formalmente su solicitud de suspensión de estudios o que haya sido eliminado "
       . "por rendimiento académico, abandono o por sanción disciplinaria, en conformidad a la reglamentación vigente de "
       . "la UMC, deberá pagar la totalidad de la deuda contraída en el presente contrato. Los plazos para presentar solicitud "
       . "de retiro o de suspensión de estudios estarán establecidos en el calendario académico definido por la Vicerrectoría "
       . "Académica y comunicado a través del sitio Web institucional.<br>"
       . "El alumno deberá estar al día en todas sus obligaciones económicas con la Universidad para poder incorporarse a las "
       . "actividades académicas del período correspondiente, tener derecho a matricularse e inscribir asignaturas en los "
       . "períodos académicos sucesivos, a obtener certificaciones, hacer uso en plenitud de la biblioteca y laboratorios "
       . "de la Universidad y a presentarse y rendir evaluaciones solemnes y/o exámenes. Si no lo estuviere, quedará suspendido "
       . "de todos sus derechos académicos, económicos y administrativos mientras persista la mora. Las fechas de evaluaciones "
       . "solemnes y/o de exámenes no rendidos por esa causa no serán recuperables.<br>"
       . "Así mismo los beneficios de becas, créditos o cualquier otro que le haya otorgado la Universidad con recursos propios "
       . "a un alumno, se perderán y por tanto no se extenderán al año o semestre siguiente según corresponda, si este cae en "
       . "mora por falta de pago de sus obligaciones pactadas con la Universidad.<br><br>"

       //. "<!-- PAGE BREAK -->"

       . "<b>OCTAVO: </b>"
       . "En el caso que el estudiante hubiere cumplido con los aspectos académicos necesarios y este al día en el cumplimiento "
       . "de sus obligaciones financieras, se entenderá matriculado para el Segundo Semestre de Primavera, al pagar la matrícula "
       . "de ese semestre y pagar o documentar el pago del arancel del mismo.<br><br>"
       
       . "<b>NOVENO: </b>"
       . "Si el alumno ocasionare daños materiales al patrimonio de la Universidad, él y su apoderado deberán pagar solidariamente "
       . "la reparación o reposición de los daños causados, sin perjuicio de las sanciones reglamentarias y/o legales que correspondan."
       . "Las partes dejan constancia que la Universidad no será responsable por los perjuicios producidos por la pérdida o sustracción "
       . "de efectos personales de propiedad del alumno que se introduzcan o mantengan en los recintos universitarios; por consiguiente, "
       . "el alumno declara conocer su obligación de mantener el debido resguardo sobre dichos objetos.<br><br>"
       
       . "<b>DÉCIMO: </b>"
       . "La individualización del alumno y apoderado de la comparecencia y los datos de los servicios educacionales "
       . "contratados son:<br><br>"

//       . "<!-- PAGE BREAK -->"

       . "<table cellpadding='2' cellspacing='0' border='1' align='center' width='90%'>"
       . "  <tr><td width='20%'>Alumna/alumno</td> <td width='80%'>$nombre_al</td></tr>"
       . "  <tr><td width='20%'>RUT Alumna/o</td>  <td width='80%'>$rut_al</td></tr>"
       . "  <tr><td width='20%'>Domicilio</td>     <td width='80%'>$domicilio_al</td></tr>"
       . "  <tr><td width='20%'>Fono Fijo</td>     <td width='80%'>$telefono_al</td></tr>"
       . "  <tr><td width='20%'>Fono Celular</td>  <td width='80%'>$tel_movil_al</td></tr>"
       . "  <tr><td width='20%'>e-Mail Personal</td>  <td width='80%'>$email_al</td></tr>"
       . "</table><br>"
       . "<table cellpadding='2' cellspacing='0' border='1' align='center' width='90%'>"
       . "  <tr><td width='20%'>Carrera</td> <td width='80%'>$carrera_al</td></tr>"
       . "  <tr><td width='20%'>Jornada</td> <td width='80%'>$jornada_al</td></tr>"
       . "</table><br>"

       . "<!-- PAGE BREAK -->"

       . "<table cellpadding='2' cellspacing='0' border='1' align='center' width='90%'>"
       . "  <tr><td width='20%'>Apoderado</td>     <td width='80%'>$nombre_rf</td></tr>"
       . "  <tr><td width='20%'>RUT Apoderado</td> <td width='80%'>$rut_rf</td></tr>"
       . "  <tr><td width='20%'>Domicilio</td>     <td width='80%'>$domicilio_rf</td></tr>"
       . "  <tr><td width='20%'>Fono Fijo</td>     <td width='80%'>$telefono_rf</td></tr>"
       . "  <tr><td width='20%'>Fono Celular</td>  <td width='80%'>$tel_movil_rf</td></tr>"
       . "  <tr><td width='20%'>e-Mail Personal</td>  <td width='80%'>$email_rf</td></tr>"
       . "</table><br>"
                    
       . "El Derecho de Matrícula ".mb_strtolower($titulo_periodo)." es de \$ $monto_matricula ($monto_matricula_palabras pesos), que "
       . "se pagará de la siguiente forma:<br><br>"
       . $HTML_mat ."<br>"

//       . "<!-- PAGE BREAK -->"
       . "El Derecho de Colegiatura ".mb_strtolower($titulo_periodo)." convenido es de \$ $monto_arancel ($monto_arancel_palabras pesos) que se "
       . "paga de la siguiente forma:<br><br>"       
       . $HTML_arancel
       . "<br>"     
       . $tabla_pagare_cheques

       . $texto_2do_sem

       . "Los Derechos de Matrícula $tipo_contrato a los que se hace mención en esta cláusula, bajo ninguna circunstancia estarán sujetos "
       . "a devolución, atendido que la UMC ha debido incurrir en gastos para cumplir con la prestación de servicios pactada.<br><br>"      
       
       . "Derecho de Titulación (Examen de Grado, tesis, tesina o práctica) establecido y que se acepta y conviene por las partes es de "
       . "12 UF. Y el derecho por Título Técnico de Nivel Superior convenido es de 7 UF, que se aplica sólo en el caso que la carrera, en "
       . "la que se matricula, otorgue dicha salida intermedia y el alumno desee obtenerlo. Las partes declaran conocer que anualmente la "
       . "UMC dicta una Resolución que fija los Aranceles de las certificaciones extraordinarias.<br><br>"
       
       . "<b>DÉCIMO PRIMERO: </b>"
       . "Se deja constancia la existencia en la UMC del Crédito Solidario, que consiste en la expresión y práctica de uno de los "
       . "valores organizacionales prioritarios de la Institución, este es el de la Solidaridad. El crédito que se otorga obliga al "
       . "receptor de este, además de su obligación legal, a un compromiso ético del pago del mismo, pues en la UMC no sólo se recibe "
       . "un crédito por solidaridad, sino que también se paga dicho crédito por parte de los egresados que lo recibieron, como "
       . "reciprocidad de esa solidaridad, lo que permite el otorgamiento del mismo beneficio, a quienes postulen a este al "
       . "ingresar a la Universidad, en conformidad con el reglamento correspondiente.<br><br>"
       
       . "<b>DÉCIMO SEGUNDO: </b>"
       . "Las partes dejan constancia de lo siguiente:<br>"
       . "Que el incumplimiento o mora en el pago del servicio que por este acto se suscribe, facultará a la Universidad para suspender "
       . "al alumno la prestación del servicio educacional contratado; y de persistir la morosidad, para poner término a la calidad de "
       . "alumno regular. El simple retardo o mora en el pago de alguna de las cuotas dará derecho a la UMC para exigir el pago del "
       . "total de la deuda como si fuere de plazo vencido. Al décimo día corrido de la fecha de vencimiento, la deuda en pesos se "
       . "convertirá en la equivalencia correspondiente a Unidades de Fomento. Habida consideración de los principios de la UMC, la "
       . "mora o atraso en el pago generará un interés mensual que no superará el 50% de la tasa máxima convencional. Asimismo, la UMC "
       . "podrá también, exigir el pago, por vía judicial o extrajudicial, de las sumas devengadas e impagas, correspondientes al "
       . "arancel pactado y/o externalizar la cobranza de la deuda en mora. El deudor moroso será responsable de los gastos de "
       . "protesto, cobranza extrajudicial y judicial y demás que genere su incumplimiento.<br>"
       . "Que en base a lo establecido en la Ley de Protección de los Derechos de los Consumidores y en la normativa de la "
       . "Superintendencia de Bancos e Instituciones Financieras, existen recargos por concepto de cobranza extrajudicial de los "
       . "créditos o cuotas impagas detallados en la cláusula anterior, incluyendo honorarios a cargo del deudor según los plazos "
       . "que se indican más adelante, los cuales serán cobrados directamente por la UMC o por la empresa de cobranza que la UMC "
       . "designe al efecto, en su caso, la que actuará en nombre y en representación e interés de la Universidad Miguel de Cervantes "
       . "en las gestiones de cobranza extrajudicial y/o judicial. Lo anterior es sin perjuicio de que la empresa señalada puede ser "
       . "cambiada por decisión unilateral de la UMC. Esta cobranza extrajudicial será realizada conforme a la ley, en días hábiles y "
       . "en horario de 09.00 a 20.00 horas.<br>"
       . "El alumno y su apoderado por este instrumento, declaran conocer y aceptar que, de acuerdo a lo establecido en la ley No 19.628 "
       . "sobre Protección de Datos de Carácter Personal, y para que la empresa de cobranza que la UMC designe al efecto pueda realizar "
       . "la respectiva cobranza extrajudicial y/o judicial, la Universidad Miguel de Cervantes suministrará a dicha empresa antecedentes, "
       . "tanto respecto de los créditos morosos de sus deudores y otros que no estando en dicha condición estén asociados a éstos, como de "
       . "los antecedentes comerciales de los deudores, tales como nombres y apellidos, cédula nacional de identidad, rol único tributario, "
       . "domicilios, direcciones y teléfonos, etc.<br>"
       . "Las tarifas de honorarios por concepto de la cobranza extrajudicial, y en conformidad a la ley de protección del consumidor, "
       . "ascenderán a los porcentajes aplicados sobre el total de la deuda o la cuota vencida, según el caso, conforme a la siguiente "
       . "escala progresiva: a) Obligaciones hasta 10 Unidades de Fomento 9%, b) por la parte que exceda de 10 UF y hasta 50 UF 6% y c) "
       . "por la parte que exceda de 50 UF 3%. El plazo para la aplicación de honorarios será de 15 días corridos de atraso (mora) desde "
       . "el día de vencimiento de la obligación.<br>"
       . "Los gastos judiciales corresponderán a las costas procesales y honorarios profesionales del abogado o empresa de cobranza externa "
       . "a quien se le encargue dicha gestión.<br>"
       . "El alumno declara no haber sido condenado por crimen o delito que cause inhabilidad para el ejercicio profesional y no haber sido "
       . "expulsado de otra Universidad, por sanciones disciplinarias. El incumplimiento por parte del alumno de las previsiones contenidas "
       . "en la Ley 20.393 y en este instrumento, será causal para la terminación del mismo, sin perjuicio de ejercer las acciones legales a "
       . "las que hubiere lugar ante las autoridades competentes.<br>"
       . "La UMC se reserva el derecho de admitir y/o cancelar la matrícula de un alumno, en el caso eventual, de haber sido sancionado por "
       . "infracciones disciplinarias por una Institución de Educación Superior o haber sido condenado por un crimen o simple delito que cause "
       . "inhabilidad para el ejercicio profesional, o en el caso eventual de haber sido sancionado por infracciones disciplinarias por una "
       . "Institución de Educación Superior a protocolos referidos al acoso o agresión sexual.<br>"
       . "El alumno y su apoderado compareciente, declaran su conocimiento y conformidad con lo establecido en los párrafos anteriores de esta "
       . "cláusula.<br><br>"

       . "<b>DÉCIMO TERCERO: </b>"
       . "El alumno y su apoderado declaran haber leído detalladamente el presente contrato y estar de acuerdo con todas y cada una de sus "
       . "estipulaciones, y toman debida nota que el contenido de este documento es el único que regula la relación contractual entre la UMC "
       . "y el alumno y su apoderado.<br>"
       . "La vigencia del presente contrato es exclusivamente por el período académico señalado en este instrumento. La celebración de un "
       . "nuevo contrato entre las partes está condicionada al estricto cumplimiento por parte del alumno de lo establecido en los Reglamentos "
       . "que regulan sus estudios.<br>"
       . "La solicitud para renovar la prestación de los servicios educacionales por un nuevo periodo académico, deberá ser realizada "
       . "oportunamente, dentro de los plazos establecidos por la UMC.<br>"
       . "El presente contrato es firmado por parte de la Universidad bajo el supuesto que la documentación requerida y proporcionada por "
       . "el alumno y su apoderado, son fidedignos y legalmente válidos. En el evento que ello no fuere efectivo, el presente contrato "
       . "terminará de pleno derecho, no correspondiendo a la Universidad efectuar devolución alguna de las sumas ya pagadas o documentadas "
       . "de la matrícula, el arancel, o cualquiera de las sumas o cuotas pactadas.<br><br>"
//       . "<!-- PAGE BREAK -->"
       
       . "<b>DÉCIMO CUARTO: </b>"
       . "Declaro conocer y aceptar la normativa vigente en documentos de la UMC, en el marco de la Ley 21.369, política, "
       . "procedimientos y protocolo en materia de acoso sexual, de violencia sexual y discriminación de género, además del "
       . "Código de Ética y Buena Convivencia, entre otros. Asimismo me comprometo a respetar lo dispuesto en estos textos, "
       . "todos los cuales están disponibles en www.umcervantes.cl.<br><br>"

       . "Para todos los efectos legales derivados del presente contrato, las partes fijan domicilio en la ciudad de Santiago y se someten a "
       . "la jurisdicción de sus Tribunales de Justicia. "
       . "El presente contrato se firma en tres ejemplares del mismo tenor, quedando uno en poder del alumno(a), y dos en poder de la UMC."
       
       . "<br><br><br><br><br>"
       . "<table align='center'>"
       . "  <tr><td><hr size='1' noshade>p.p. Universidad Miguel de Cervantes</td></tr>"
       . "</table><br>"
       . "<table align='center'>"
       . "  <tr>"
       . "    <td width='150' align='center'><hr size='1' noshade>Apoderado</td>"
       . "    <td width='200'>&nbsp;</td>"
       . "    <td width='150' align='center'><hr size='1' noshade>Alumno</td>"
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
      . "          <table>".$LF
      . "            <tr><td valign='top' align='justify'>$TEXTO</td></tr>".$LF
      . "          </table>".$LF
      . "        </td>".$LF
      . "      </tr>".$LF
      . "    </table>".$LF
      . "  </body>".$LF
      . "</html>".$LF;

?>

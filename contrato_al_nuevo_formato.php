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
        . "<b>ALUMNOS NUEVOS</b><br>"
        . "<b>$titulo_periodo</b><br>";

$TEXTO = "Entre la Universidad Miguel de Cervantes, Corporación de Derecho Privado de Educación Superior, "
       . "Rut: 73.124.400-6, representada por su $REPRESENTANTE_LEGAL, ambos domiciliados "
       . "calle Mac Iver N° 370, de esta ciudad, en adelante «la Universidad» y/o «UMC», por una parte, y "
       . "por la otra, don(ña) $nombre_al, RUT $rut_al, de nacionalidad $nacionalidad_al, domicilio en $domicilio_al, en adelante "
       . "«el alumno», y don(ña) $nombre_rf, RUT $rut_rf, de nacionalidad $nacionalidad_rf, profesión $profesion_rf, "
       . "estado civil $est_civil_rf, domicilio en $domicilio_rf, en adelante "
       . "«el apoderado», en conformidad a las disposiciones legales y al Código Civil se ha convenido el "
       . "siguiente contrato de prestación de servicios educacionales:<br><br>"
       
       . "<b>PRIMERO: </b>"
       . "Por el presente acto el (compareciente don(ña) $nombre_al) entenderá matriculado como alumno regular de la "
       . "Universidad Miguel de Cervantes, en la carrera de $carrera_al, $texto_periodo y en la jornada $jornada_al,"
       . "una vez que haya cumplido con todos los requisitos y entregado a las instancias correspondientes de la "
       . "Universidad  toda la documentación obligatoria para el ingreso a esta casa de estudios, lo cual se encuentra "
       . "especificado en la información proporcionada por la UMC y que tanto el alumno como el apoderado comparecientes "
       . "declaran conocer y aceptar. Mientras no se completen los antecedentes señalados, el compareciente ya "
       . "individualizado tendrá la condición de Postulante.<br>"
       . "Asimismo, el alumno se obliga, al inicio del período de clase, cualquiera sea la carrera en la que se haya "
       . "matriculado a: 1. Someterse a una evaluación diagnostica establecida por la Universidad. 2. Incorporarse al "
       . "proceso de «acompañamiento académico», sin costo adicional, de las asignaturas del «Plan Sinergia» y a los "
       . "cursos de inducción que la Universidad establezca. 3. Responder las encuestas que la Universidad le solicite "
       . "en el proceso de Matrícula. Se excluye de estas obligaciones, salvo la referida a las encuestas, a los "
       . "estudiantes que se matriculen en programas de continuidad de estudios, debido a que ya poseen un título "
       . "profesional.<br>"
       . "El alumno y apoderado, quien más adelante comparece en el presente contrato como codeudor solidario de las "
       . "obligaciones financieras del alumno, declaran, que la universidad, previo a la suscripción del contrato, ha "
       . "puesto a su disposición, en forma oportuna la información relativa a los servicios educacionales que se "
       . "contratan, declarando ambos, que han tomado conocimiento previo a través de la página web oficial "
       . "www.umcervantes.cl y por medio de los canales de atención de admisión, que la Universidad ha puesto a "
       . "disposición de alumnos y apoderados.<br><br>"
       
       . "<b>SEGUNDO: </b>"
       . "En virtud del presente contrato, la Universidad se obliga a mantener el cupo correspondiente, para el "
       . "alumno regular en los servicios docentes que le sean pertinentes y que prestará durante los periodos "
       . "académicos en que se matricula. Para estos efectos, se considerará como parte integrante de este contrato "
       . "el plan de estudios de la carrera en la que se matricula el alumno. Esto, sin perjuicio del derecho de la UMC "
       . "de modificar ese plan de estudios en atención a razones académicas, exigencias, por fuerza mayor o "
       . "recomendaciones de la autoridad pública competente y/o de los organismos responsables de realizar los "
       . "procesos de acreditación institucional y/o de carreras. Por su parte el alumno y su apoderado declaran conocer "
       . "el Estatuto y todos los reglamentos de la Universidad que regulan las actividades académicas y formativas para "
       . "obtener el respectivo título y/o grado, y los derechos y obligaciones del alumno. Además, se deja constancia que "
       . "la información pertinente, estatutos y reglamentos aplicables a este contrato están publicados en el portal "
       . "«www.umcervantes.cl» hecho del cual el alumno y su apoderado certifican conocer. El alumno en este acto se obliga "
       . "a conocer y a respetar la reglamentación interna de la Universidad, y en general toda otra norma que emane de la "
       . "institucionalidad universitaria. El incumplimiento por parte del alumno de dicha reglamentación dará derecho a la "
       . "UMC a poner término al presente contrato, sin necesidad de declaración judicial.<br>"
       . "Las actividades académicas correspondientes a la carrera en la que se matriculó el alumno tendrán lugar en la sede "
       . "central de la Universidad, o en otra instalación según lo disponga la UMC, y se podrá realizar una parte de estas "
       . "actividades académicas por sistemas especiales de educación a distancia las que podrán representar hasta un treinta "
       . "y nueve por ciento del plan de estudio de cada semestre. De igual modo por razones de fuerza mayor, "
       . "caso fortuito, de salud pública, tipo pandemia o similares, o por existir dificultades para realizar clases "
       . "presenciales, sea por impedimento o dificultades de acceso al local de la UMC, por limitaciones en el transporte "
       . "público o limitaciones horarias dictadas por la autoridad, también se podrán utilizar sistemas especiales de "
       . "educación a distancia en la totalidad de las actividades académicas, por el tiempo que la UMC decida. El "
       . "alumno y su apoderado declaran contar con la implementación necesaria para acceder electrónicamente vía internet a la "
       . "educación a distancia, esto es conectividad, accesibilidad, y, equipos adecuados para participar en clases "
       . "virtuales sincrónicas y asincrónicas, y además, declaran conocer la infraestructura de la Universidad, manifestando "
       . "su plena satisfacción con esta.<br>"
       . "Presente en este acto, el apoderado, antes individualizado, quien declara que se constituye en fiador y en codeudor "
       . "solidario de todas y cada una de las obligaciones que para con la Universidad Miguel de Cervantes ha asumido el "
       . "Alumno contratante en virtud de este instrumento, renunciando expresamente al beneficio de excusión.<br>"
       . "El apoderado y el alumno declaran conocer que este contrato puede requerir ser modificado en las siguientes "
       . "situaciones que afecten al Alumno Contratante:  cambio de jornada de estudios; incorporación o eliminación de "
       . "becas de la UMC o de terceros o beneficios o Crédito con Aval del Estado; cambio de datos personales del Alumno "
       . "Contratante; u otras modificaciones objetivas similares. El apoderado y codeudor solidario acepta expresamente "
       . "que este contrato sea modificado por las partes en las situaciones antes mencionadas, mediante anexos suscritos "
       . "por el Alumno Contratante y la UMC, y que dichas modificaciones le serán aplicables, sin necesidad de concurrir "
       . "a la firma del respectivo anexo, todo ello en cuanto no implique para el apoderado y codeudor solicidario una "
       . "obligación económica más gravosa que la originalmente asumida a la firma del contrato.<br>"
       . "Los alumnos que sean preseleccionados con una beca estatal o con un Crédito con Aval del Estado, deberán presentar "
       . "la información necesaria ante la autoridad y la Universidad para efectuar la acreditación socioeconómica requerida "
       . "por el Ministerio de Educación, teniendo presente que no entregar los antecedentes en los plazos establecidos por el "
       . "Mineduc, implica ser excluidos de dichos beneficios estatales. Se establece que la sola matrícula en la Universidad o "
       . "la acreditación socioeconómica correspondiente no garantiza necesariamente la obtención de una beca estatal o crédito, "
       . "ya que la etapa de selección forma parte de un proceso a cargo del Ministerio de Educación o de las autoridades "
       . "pertinentes, según requisitos y normas dispuestas por aquellos.<br>"
       . "La no obtención del CAE - Crédito con Aval del Estado (Ley N° 20.027) en ningún caso deja sin efecto el Contrato de "
       . "Prestación de Servicios Educacionales, que se suscribe con la Universidad. Por lo tanto, no es deber de la UMC "
       . "proceder a la devolución de pagos efectuados, ni de documentos que respalden pagos pendientes por concepto de "
       . "arancel.<br>"
       . "El apoderado y codeudor solidario da a las obligaciones contraídas el carácter de indivisibles, pudiendo, en "
       . "consecuencia, exigirse el pago total de ellas a cualquiera de sus herederos, de conformidad a lo previsto en el "
       . "artículo 1.528 del Código Civil.<br><br>"
       
       . "<b>TERCERO: </b>"
       . "Los cursos y/o asignaturas que la Universidad imparte corresponden, al plan de estudios de la respectiva carrera. "
       . "Cada uno de estos cursos se impartirá sólo una vez al año, en el semestre académico correspondiente, determinado "
       . "por el plan de estudios de la carrera en la que se matriculó el alumno.<br>"
       . "Aquel alumno que haya realizado retiro, suspensión de estudios o abandono y posteriormente decida solicitar "
       . "formalmente su reincorporación deberá adscribirse al plan de estudios vigente en su carrera.<br>"
       . "La Universidad impartirá cursos y/o asignaturas de carácter transversal, del mismo plan de estudios o que "
       . "tengan el mismo programa, esto es, cursos y/o asignaturas en los que participen alumnos de cualquier carrera "
       . "y/o jornada, y estos se impartirán en la jornada vespertina y el día sábado. De igual modo, aquellas asignaturas "
       . "de la jornada diurna que no reúnan un mínimo de diez estudiantes regulares válidamente inscritos para el respectivo "
       . "período y nivel, podrán ser fusionadas con las asignaturas iguales de la jornada vespertina, abriendo para ello un "
       . "sólo curso en horario vespertino y/o además el día sábado.<br>"
       . "Los cursos y/o asignaturas que el alumno podrá inscribir en cada semestre dependerán del cumplimiento de los "
       . "prerrequisitos académicos y de admisión establecidos en el plan de estudios de la respectiva carrera, según la "
       . "jornada en que se matriculó y en conformidad con la reglamentación vigente en la Universidad.<br>"
       . "A los alumnos que no inscriban la carga curricular correspondiente a su Plan de Estudios o no efectúen la "
       . "inscripción de asignaturas en tiempo, oportunidad y forma establecida por la UMC, no se les hará devolución "
       . "arancelaria alguna, debiendo dar cumplimiento a la totalidad de la obligación contraída. Teniendo en consideración "
       . "la existencia virtual y física de textos en la Biblioteca Institucional, no forma parte de este contrato la entrega "
       . "de estos, para el cumplimiento de las exigencias académicas del alumno. No será válida la inscripción de asignaturas "
       . "que no cumplan secuencialmente los prerrequisitos académicos, salvo las autorizadas por la Vicerrectoría Académica de "
       . "la UMC.<br><br>"
       
       . "<b>CUARTO: </b>"
       . "Si no se completare un número mínimo de 45 alumnos en primer nivel de la carrera, lo que el alumno y su apoderado "
       . "declaran conocer, la UMC podrá optar por no impartir dicha carrera en la jornada respectiva. Dada esta situación, "
       . "la Universidad podrá ofrecer otras alternativas al alumno o, si éste lo solicita, se procederá a dejar sin efecto "
       . "el contrato firmado, reintegrando los eventuales pagos efectuados y la documentación entregada por el alumno y "
       . "recibida por la UMC.<br>"
       . "Cuando el número de alumnos por curso así lo permita, la Vicerrectoría Académica podrá determinar que dicho curso "
       . "y/o asignatura sea dictado bajo el sistema tutorial o personalizado. Asimismo, los horarios y jornadas de los cursos "
       . "serán determinados semestralmente por la Dirección de la Escuela respectiva en función de las necesidades de la "
       . "estructura curricular. En todo caso, los cursos y/o asignaturas podrán dictarse de lunes a sábado cualquiera sea "
       . "la jornada que curse el alumno.<br><br>"
       
       . "<b>QUINTO: </b>"
       . "El valor del derecho de matrícula y el valor del arancel semestral corresponden al total de la prestación contratada "
       . "para dicho período. La Universidad fijará anualmente el valor del derecho de matrícula y el valor del arancel, y a "
       . "partir de aquel el que corresponda al contrato semestral. Se deja establecido que aun cuando el alumno, por cualquier "
       . "causa o motivo, no hiciere uso de este servicio educacional deberá dar cumplimiento a la totalidad de la obligación "
       . "contraída en este contrato y en los pagarés que sustentan dicha obligación, salvo que haya efectuado el trámite de "
       . "retiro en conformidad a la ley, a la reglamentación de la UMC o a los términos de este instrumento.<br>"
       . "Los pagos correspondientes, se efectuarán en las oficinas de la Universidad o mediante transferencia electrónica "
       . "debidamente informada a la UMC al correo electrónico «transferencias@corp.umc.cl» indicando el nombre y el RUT del "
       . "alumno, o por medio de la Institución financiera a la cual la UMC hubiere encomendado su recaudación y/o cobro, no "
       . "estando obligadas ni la Universidad ni la Institución financiera a notificar en cada oportunidad las fechas de "
       . "vencimiento del compromiso de pago.<br>"
       . "En el caso de pago en cuotas, el incumplimiento o mora en el pago de alguna de ellas, dará derecho a la Universidad "
       . "a exigir el pago total de lo adeudado. Transcurridos diez días hábiles desde la fecha de pago y estando moroso el "
       . "pago, lo adeudado se transformará automáticamente en el equivalente en unidades de fomento, las que a su pago se "
       . "convertirán en pesos a la fecha en que dicho pago se realice.<br>"
       . "El alumno manifiesta su conformidad en que, a partir de esta fecha, toda información y comunicación oficial de la "
       . "UMC pueda ser enviada a través de la dirección personal de email, al correo electrónico institucional, que para "
       . "estos efectos se ha creado a cada alumno. Su dirección de e-mail es la correspondiente al primer nombre seguido "
       . "de un punto y seguido de su apellido paterno más la expresión «@alumni.umc.cl.». En caso de existir una dirección "
       . "electrónica idéntica a otra anteriormente asignada a un alumno antiguo de la UMC, el Departamento de Informática "
       . "asignará una dirección electrónica distinta para el alumno, según establecen los procedimientos respectivos. El "
       . "apoderado deberá entregar su correo electrónico personal y su domicilio para los efectos de recibir comunicaciones "
       . "oficiales de la UMC y tendrá derecho a solicitar la información referida a pagos y calificaciones del alumno. Sin "
       . "perjuicio de lo anterior, la UMC podrá enviar comunicaciones al domicilio y/o al correo electrónico personal que "
       . "el alumno y el apoderado hayan entregado a la UMC. En todo caso será responsabilidad del alumno y del apoderado "
       . "informar del cambio de domicilio y/o de sus correos electrónicos personales. El alumno deberá validar al momento "
       . "de la matrícula semestral respectiva, su domicilio, teléfono móvil y su correo electrónico personal, como requisito "
       . "de matrícula y a su vez deberá revalidarlo cuando así se lo solicite la UMC.<br><br>"

       . "<b>SEXTO: </b>"
       . "En el caso de alumnos nuevos, incorporados como postulantes, esto es, aquellos que ingresan por primera vez a la "
       . "Universidad, la UMC podrá poner término al presente contrato, si el alumno no hubiere cumplido con los requisitos "
       . "de admisión e ingreso. En este caso, junto a la rescisión del presente contrato, la Universidad devolverá los pagos "
       . "efectuados por el alumno por los servicios inicialmente convenidos, a excepción del derecho de matrícula, así como "
       . "la documentación entregada por el alumno y recibida por la UMC.<br>"
       . "El alumno y su apoderado, en este acto expresan su consentimiento y conformidad con la facultad de la Universidad de "
       . "extender o denegar certificaciones académicas o de cualquier otro orden, ante el incumplimiento de las obligaciones "
       . "contraídas en virtud del presente contrato, renunciando desde ya al ejercicio de acciones legales de cualquier "
       . "naturaleza por este motivo. El alumno deberá observar las normas reglamentarias internas para inscribir sus cursos "
       . "o actividades; por consiguiente, si el alumno debe repetir uno o más cursos alterándose el orden o número de cursos "
       . "que debe inscribir, ello será de su exclusiva responsabilidad, es decir, la Universidad no estará obligada a otorgar "
       . "facilidades, horarios especiales u otras medidas equivalentes.<br>"
       . "También se deja constancia que cuando el proceso de titulación constituye una actividad académica regular del programa "
       . "o carrera, se requiere que el alumno esté matriculado para el período académico respectivo, y que además cancele los "
       . "aranceles correspondientes a su proceso de titulación.<br><br>"
       
       . "<b>SÉPTIMO: </b>"
       . "De todas las obligaciones económicas y financieras estipuladas en el presente contrato o que se deriven de él, serán "
       . "solidaria e indivisiblemente responsables, el alumno y su apoderado. Por este instrumento, el alumno y su apoderado "
       . "autorizan expresamente y en conformidad a la ley, a la UMC para que, separada e individualmente, en caso de simple "
       . "retardo, mora o incumplimiento total o parcial de las obligaciones contraídas en este contrato, sus datos personales "
       . "y los demás derivadas de éste (del alumno y/o su apoderado) puedan ser tratados y/o comunicados a terceros sin "
       . "restricciones, a efectos de su cobranza.<br>"
       . "En el caso que el alumno presente formalmente su solicitud de retiro y/o de suspensión de estudios y esta fuere "
       . "aprobada, deberá pagar el 50% de lo no pagado o no vencido a la fecha de presentación de dicha solicitud, en el "
       . "caso que haya sido eliminado por rendimiento académico, abandono o por sanción disciplinaria, en conformidad a la "
       . "reglamentación vigente de la UMC, deberá pagar la totalidad de la deuda contraída en el presente contrato. Los "
       . "plazos para presentar solicitud de retiro o de suspensión de estudios estarán establecidos en el calendario "
       . "académico definido por la Vicerrectoría Académica e informado a través del sitio Web institucional.<br>"
       . "El alumno deberá estar al día en todas sus obligaciones económicas con la Universidad para poder incorporarse a "
       . "las actividades académicas del primer semestre del primer año, tener derecho a matricularse e inscribir asignaturas "
       . "en los períodos académicos sucesivos, a obtener certificaciones, hacer uso en plenitud de la biblioteca y "
       . "laboratorios de la Universidad y a presentarse y rendir evaluaciones solemnes y/o exámenes. Si no lo estuviere, "
       . "quedará suspendido de todos sus derechos académicos, económicos y administrativos mientras persista la mora. Las "
       . "fechas de evaluaciones solemnes y/o exámenes no rendidos por esta causa no serán recuperables.<br>"
       . "Asimismo, los beneficios de becas, créditos o cualquier otro que le haya otorgado la Universidad con recursos "
       . "propios a un alumno, se perderán y por tanto no se extenderán al año o semestre siguiente según corresponda, si "
       . "este cae en mora por falta de pago de sus obligaciones pactadas con la Universidad.<br><br>"
       
       . "<b>OCTAVO: </b>"
       . "En el caso que el estudiante hubiere cumplido con los aspectos académicos necesarios y este al día en el "
       . "cumplimiento de sus obligaciones financieras, se entenderá matriculado para el segundo semestre de Primavera, "
       . "al pagar la matrícula de ese semestre y pagar o documentar el pago del arancel del mismo, de acuerdo a los "
       . "montos que se indican en la clausula Décimo Primera.<br><br>"
       
       . "<b>NOVENO: </b>"
       . "En caso que el alumno ocasionare daños materiales al patrimonio de la Universidad, él y su apoderado deberán "
       . "pagar solidariamente la reparación o reposición de los daños causados, sin perjuicio de las sanciones "
       . "reglamentarias y/o legales que correspondan. Las partes dejan constancia que la Universidad no será "
       . "responsable por los perjuicios producidos por la pérdida o sustracción de efectos personales de propiedad "
       . "del alumno que se introduzcan o mantengan en los recintos universitarios; por consiguiente, el alumno declara "
       . "conocer su obligación de mantener el debido resguardo sobre dichos objetos.<br><br>"
       
       . "<b>DÉCIMO:</b>"
       . "Los alumnos que ingresan al primer semestre de la Universidad podrán, dentro del plazo de 10 días contados "
       . "desde aquél en que se complete la primera publicación de los resultados de las postulaciones a las Universidades "
       . "pertenecientes al Consejo de Rectores de las Universidades Chilenas, dejar sin efecto el presente contrato en "
       . "arreglo a los requisitos establecidos por la ley. Sin pago alguno por los servicios educacionales no prestados. "
       . "En todo caso, por concepto de costo de administración, la Universidad cobrará el monto equivalente al 1% del "
       . "arancel anual de la carrera. (Art. 3° de la Ley 19.496). Vencido dicho plazo, el pago de las sumas señaladas "
       . "en la cláusula décima, en tiempo oportuno, constituye una obligación de la esencia del presente contrato, "
       . "obligación que recaerá en el apoderado y en el alumno regular, ya individualizados, solidariamente, durante "
       . "el periodo académico convenido, aunque el alumno no hiciere uso del servicio educacional materia de este contrato, "
       . "toda vez que la Universidad no puede prescindir de continuar otorgándolo con los gastos consiguientes. Sin perjuicio "
       . "de lo anterior, el alumno podrá hasta el primer día de inicio de clases, solicitar por escrito resolver o dejar sin "
       . "efecto este contrato fundado en fuerza mayor, caso fortuito, causales reglamentarias o situaciones debidamente "
       . "justificadas que le impidan cumplir sus obligaciones financieras y/o de asistencia, en caso que esta solicitud "
       . "sea aprobada, se procederá a devolver los pagos de arancel eventualmente realizado, en todo caso no será devuelto "
       . "el derecho pagado por concepto de matrícula. Aprobada la solicitud, ambas partes quedan liberadas de las obligaciones "
       . "asumidas por este contrato.<br><br>"
       
//       . "<!-- PAGE BREAK -->"
  
       . "<b>DÉCIMO PRIMERO: </b>"
       . "La individualización del alumno y apoderado de la comparecencia y los datos de los servicios educacionales "
       . "contratados son:<br><br>"
       . "<table cellpadding='2' cellspacing='0' border='0.5' align='center' width='90%'>"
       . "  <tr><td width='20%'>Alumna/alumno</td> <td width='80%' colspan='3'>$nombre_al</td></tr>"
       . "  <tr><td width='20%'>RUT Alumna/o</td>  <td width='80%' colspan='3'>$rut_al</td></tr>"
       . "  <tr><td width='20%'>Domicilio</td>     <td width='80%' colspan='3'>$domicilio_al</td></tr>"
       . "  <tr><td width='20%'>Fono Fijo</td>     <td width='30%'>$telefono_al</td><td width='20%'>Fono Celular</td>  <td width='30%'>$tel_movil_al</td></tr>"
       . "  <tr><td width='20%'>e-Mail Personal</td>  <td width='80%' colspan='3'>$email_al</td></tr>"
       . "</table><br>"
       . "<table cellpadding='2' cellspacing='0' border='0.5' align='center' width='90%'>"
       . "  <tr><td width='20%'>Carrera</td> <td width='80%'>$carrera_al</td></tr>"
       . "  <tr><td width='20%'>Jornada</td> <td width='80%'>$jornada_al</td></tr>"
       . "</table><br>"
//       . "<!-- PAGE BREAK -->"

       . "<table cellpadding='2' cellspacing='0' border='0.5' align='center' width='90%'>"
       . "  <tr><td width='20%'>Apoderado</td>     <td width='80%' colspan='3'>$nombre_rf</td></tr>"
       . "  <tr><td width='20%'>RUT Apoderado</td> <td width='80%' colspan='3'>$rut_rf</td></tr>"
       . "  <tr><td width='20%'>Domicilio</td>     <td width='80%' colspan='3'>$domicilio_rf</td></tr>"
       . "  <tr><td width='20%'>Fono Fijo</td>     <td width='30%'>$telefono_rf</td><td width='20%'>Fono Celular</td>  <td width='30%'>$tel_movil_rf</td></tr>"
       . "  <tr><td width='20%'>e-Mail Personal</td>  <td width='80%' colspan='3'>$email_rf</td></tr>"
       . "</table><br>"

       . "<!-- PAGE BREAK -->"
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
       . "12 UF, con excepción de los programas de Licenciatura que tendrán un valor de 5 UF."
       . "Y el derecho por Título Técnico de Nivel Superior convenido es de 7 UF, que se aplica sólo en el caso que la carrera, en "
       . "la que se matricula, otorgue dicha salida intermedia y el alumno desee obtenerlo. Las partes declaran conocer que anualmente la "
       . "UMC dicta una Resolución que fija los Aranceles de las certificaciones extraordinarias.<br><br>"       

       . "<b>DÉCIMO SEGUNDO: </b>"
       . "Se deja constancia la existencia en la UMC del Crédito Solidario, que consiste en la expresión y práctica de uno de los valores "
       . "organizacionales prioritarios de la Institución, este es el de la Solidaridad. El crédito que se otorga obliga al receptor de "
       . "este, además de su obligación legal, a un compromiso ético del pago del mismo, pues en la UMC no sólo se recibe un crédito por "
       . "solidaridad, sino que también se paga dicho crédito por parte de los egresados que lo recibieron, como reciprocidad de esa "
       . "solidaridad, lo que permite el otorgamiento del mismo beneficio, a quienes postulen a este al ingresar a la Universidad, en "
       . "conformidad con el reglamento correspondiente.<br><br>"
       
       . "<b>DÉCIMO TERCERO: </b>"
       . "Las partes dejan constancia de lo siguiente:<br>"
       . "Que el incumplimiento o mora en el pago del servicio que por este acto se suscribe, facultará a la Universidad para suspender "
       . "al alumno la prestación del servicio educacional contratado; y de persistir la morosidad, para poner término a la calidad de "
       . "alumno regular. El simple retardo o mora en el pago de alguna de las cuotas dará derecho a la UMC para exigir el pago del "
       . "total de la deuda como si fuere de plazo vencido. Al décimo día corrido de la fecha de vencimiento, la deuda en pesos se "
       . "convertirá en la equivalencia correspondiente a Unidades de Fomento. Habida consideración de los principios de la UMC, "
       . "la mora o atraso en el pago generará un interés mensual que no superará el 50% de la tasa máxima convencional. Asimismo, "
       . "la UMC podrá también, exigir el pago, por vía judicial o extrajudicial, de las sumas devengadas e impagas, correspondientes "
       . "al arancel pactado y/o externalizar la cobranza de la deuda en mora. El deudor moroso será responsable de los gastos de "
       . "protesto, cobranza extrajudicial y judicial y demás que genere su incumplimiento.<br>"
       . "Que sobre la base de lo establecido en la Ley de Protección de los derechos de los Consumidores y en la normativa de la "
       . "Superintendencia de Bancos e Instituciones Financieras, existen recargos por concepto de cobranza extrajudicial de los "
       . "créditos o cuotas impagas detallados en la cláusula anterior, incluyendo honorarios a cargo del deudor según los plazos "
       . "que se indican más adelante, los cuales serán cobrados directamente por la UMC o por la empresa de cobranza que la UMC "
       . "designe al efecto, en su caso, la que actuará en nombre y en representación e interés de la Universidad Miguel de "
       . "Cervantes en las gestiones de cobranza extrajudicial. Lo anterior es sin perjuicio que la empresa señalada puede "
       . "ser cambiada por decisión unilateral de la UMC. Esta cobranza extrajudicial será realizada conforme a la ley, en "
       . "días hábiles y en horario de 09.00 a 20.00 horas.<br>"
       . "El alumno y su apoderado por este instrumento, declaran conocer y aceptar que, de acuerdo a lo establecido en la ley "
       . "No 19.628 sobre Protección de Datos de Carácter Personal, y para que la empresa de cobranza que la UMC haya designado "
       . "al efecto pueda realizar la respectiva cobranza extrajudicial y /o judicial, la Universidad Miguel de Cervantes "
       . "suministrará a dicha empresa antecedentes, tanto respecto de los créditos morosos de sus deudores y otros que no "
       . "estando en dicha condición estén asociados a éstos, como de los antecedentes comerciales de los deudores, tales como "
       . "nombres y apellidos, cédula nacional de identidad, rol único tributario, domicilios, direcciones y teléfonos, etc. Las "
       . "tarifas de honorarios por concepto de la cobranza extrajudicial, y en conformidad a la ley de protección del consumidor, "
       . "ascenderán a los porcentajes aplicados sobre el total de la deuda o la cuota vencida, según el caso, conforme a la "
       . "siguiente escala progresiva: a) obligaciones hasta 10 Unidades de Fomento 9%, b) por la parte que exceda de 10 UF y "
       . "hasta 50 UF 6% y c) por la parte que exceda de 50 UF 3%. El plazo para la aplicación de honorarios será de 15 días "
       . "corridos de atraso (mora) desde el día de vencimiento de la obligación. Los gastos judiciales corresponderán a las "
       . "costas procesales y honorarios profesionales del abogado o empresa de cobranza externa a quien se le encargue dicha "
       . "gestión.<br>"
       . "El alumno declara no haber sido condenado por crimen o delito que cause inhabilidad para el ejercicio profesional y "
       . "no haber sido expulsado de otra Universidad, por sanciones disciplinarias. El incumplimiento por parte del alumno de "
       . "las previsiones contenidas en la Ley 20.393 y en este instrumento, será causal para la terminación del mismo, sin "
       . "perjuicio de ejercer las acciones legales a las que hubiere lugar ante las autoridades competentes.<br>"
       . "La UMC se reserva el derecho de admitir y/o cancelar la matrícula de un alumno, en el caso eventual, de haber sido "
       . "sancionado por infracciones disciplinarias por una Institución de Educación Superior o haber sido condenado por un "
       . "crimen o simple delito que cause inhabilidad para el ejercicio profesional, o en el caso eventual de haber sido "
       . "sancionado por  infracciones disciplinarias por la UMC o una Institución de Educación Superior o por sanciones "
       . "establecidas en  protocolos referidos al acoso o agresión sexual, al bullying o a restricción de acercamiento "
       . "por medidas cautelares.<br>"
       . "El alumno y su apoderado compareciente, declaran su conocimiento y conformidad con lo establecido en los párrafos "
       . "anteriores de esta cláusula.<br><br>"
       
       . "<b>DÉCIMO CUARTO: </b>"
       . "El alumno y su apoderado declaran haber leído detalladamente el presente contrato y estar de acuerdo con todas y cada "
       . "una de sus estipulaciones, y toman debida nota que el contenido de este documento es el único que regula la relación "
       . "contractual entre la UMC y el alumno y su apoderado.<br>"
       . "La vigencia del presente contrato es exclusivamente por el período académico señalado en este instrumento. La "
       . "celebración de un nuevo contrato entre las partes está condicionada al estricto cumplimiento por parte del alumno "
       . "de lo establecido en los Reglamentos que regulan sus estudios.<br>"
       . "La solicitud para renovar la prestación de los servicios educacionales por un nuevo periodo académico, deberá ser "
       . "realizada oportunamente, dentro de los plazos establecidos por la UMC.<br>"
       . "El presente contrato es firmado por parte de la Universidad bajo el supuesto que la documentación requerida y "
       . "proporcionada por el alumno y su apoderado, son fidedignos y legalmente válidos. En el evento que ello no fuere "
       . "efectivo, el presente contrato terminará de pleno derecho, no correspondiendo a la Universidad efectuar devolución "
       . "alguna de las sumas ya pagadas o documentadas de la matrícula, el arancel, o cualquiera de las sumas o cuotas "
       . "pactadas.<br><br>"

       . "<b>DÉCIMO QUINTO: </b>"
       . "Declaro conocer y aceptar la normativa vigente en documentos de la UMC, en el marco de la Ley 21.369, política, "
       . "procedimientos y protocolo en materia de acoso sexual, de violencia sexual y discriminación de género, además del "
       . "Código de Ética y Buena Convivencia, entre otros. Asimismo me comprometo a respetar lo dispuesto en estos textos, "
       . "todos los cuales están disponibles en www.umcervantes.cl.<br><br>"
       
       . "Para todos los efectos legales derivados del presente contrato, las partes fijan domicilio en la ciudad de Santiago "
       . "y se someten a la jurisdicción de los tribunales de justicia. El presente contrato se firma en tres ejemplares del "
       . "mismo tenor, quedando uno en poder del alumno(a), y dos en poder de la UMC.<br><br>"

       . "<br><br><br>"

       . "<table width='100%' align='center'>"
       . "  <tr>"
       . "    <td width='30%' align='center'><br><br><hr size='1' noshade>Apoderado</td>"
       . "    <td width='40%' align='center'><hr size='1' noshade>p.p. Universidad Miguel de Cervantes</td>"
       . "    <td width='30%' align='center'><br><br><hr size='1' noshade>Alumno</td>"
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
      . "          <table width='100%'>".$LF
      . "            <tr><td valign='top' align='justify'>$TEXTO</td></tr>".$LF
      . "          </table>".$LF
      . "        </td>".$LF
      . "      </tr>".$LF
      . "    </table>".$LF
      . "  </body>".$LF
      . "</html>".$LF;

?>
<!-- <iframe style='margin: 10px' align="right" width="560" height="315" src="https://www.youtube.com/embed/PnMZvyUoMz4?autoplay=1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> -->
<!-- <img src="img/admision.png" align='right' style='margin: 10px'> -->
<a href='https://youtu.be/C7Dfq5OH-MM' target='_blank'><img src="img/UNDEHPA.jpg" width='35%' align='right' style='margin: 10px'></a>
<div class="texto" align="justify">
  <b>Publicaciones UMC</b>
  <blockquote>
    <ul>
      <li><a href='/sgu/archivos/guia_autocuidado_umc_vol_1.pdf'>Guía de Autocuidado UMC Vol I</a></li>
      <li><a href='/sgu/archivos/tips_para_la_escritura_en_foros_y_textos_breves_dpi-umc.pdf'>Tips para la Escritura en Foros y Textos breves</a></li>
    </ul>
  </blockquote>  
<?php
	if ($_SESSION['tipo'] == 1 || $_SESSION['tipo'] == 2 ) {
		$ANO_Encuesta      = 2023;
		$SEMESTRE_Encuesta = 2;
		$enl_encuestas = "https://sgu.umc.cl/sgu/encuestas/?modulo=encuestas_escuela&id_evaluador={$_SESSION['id_usuario']}&ano=$ANO_Encuesta&semestre=$SEMESTRE_Encuesta";
?>
Lunes 11 de diciembre de 2023: <blink><b>Encuesta de Evaluación Docente Directores 2do semestre 2023</b></blink>
  <blockquote style="background: #FFFF00">
    Se encuentran disponibles las encuestas para ser respondidas en línea. Para ello debe pinchar en el siguiente botón:
    <a href="<?php echo($enl_encuestas); ?>" class="boton">
      Encuestas de Evaluación Docente
    </a>
  </blockquote>

<?php } ?>  
Lunes 22 de Abril de 2020: <b>Libro de Clases</b>
<blockquote style="background: #FFFF00">
	Tutorial del Libro de Clases
	<a href="https://www.youtube.com/watch?v=R66_a1eNVJg" class="boton">ver aquí</a>
</blockquote>
Lunes 16 de Marzo de 2020: <b>Classroom</b>
<blockquote style="background: #FFFF00">
	Tutorial de Classroom para docentes
	<a href="http://online.fliphtml5.com/rmbt/dvjg/#p=1" class="boton">ver aquí</a>
</blockquote>
<?php if ($_SESSION['tipo'] <> 3 && $_SESSION['tipo'] <> 10) { ?>
  <?php echo(ucfirst(strftime("%A %d de %B de %Y", time()))); ?>:
  <b>Avance de Estados de Alumnos de Pregrado Matriculados (para el periodo <?php echo ("$SEMESTRE-$ANO"); ?>): </b>
  <blockquote>
    <?php include("contenido_finazas.php"); ?>
  </blockquote><br>
  <!--
  <?php //echo(ucfirst(strftime("%A %d de %B de %Y", time()))); ?>:
  <b>Avance de Estados de Alumnos de Pregrado a Distancia Matriculados (para el periodo <?php echo ("$SEMESTRE-$ANO"); ?>): </b>
  <blockquote>
    <?php //include("contenido_finanzas_pregrado_distancia.php"); ?>
  </blockquote><br>
  <?php echo(ucfirst(strftime("%A %d de %B de %Y", time()))); ?>:
  <b>Avance de Estados de Alumnos de Postgrado a Distancia Matriculados (para el periodo <?php echo ("$SEMESTRE-$ANO"); ?>): </b>
  <blockquote>
    <?php //include("contenido_finanzas_postgrado_distancia.php"); ?>
  </blockquote><br> -->
  <?php } ?>


<?php if ($_SESSION['tipo'] <= 3) { ?>
<!--Acreditación Institucional 2017: <b>Presentación Estudiantes</b>
  <blockquote>
    Para descargar la presentación, pinche <a href='archivos/acreditacion2017.pdf'>aquí</a>
  </blockquote>-->
<?php } ?>


</div>

<?php if ($_SESSION['tipo'] == 3 ) { ?>

<!--
<div class="texto" style="">
  <b>Presentación Metodologías Activo - Participativas UMC</b><br>
  Adrián Pereira Director Magister y Postgrados<br>
  <iframe src="https://player.vimeo.com/video/334694330" width="640" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
  <br>
  Una vez que haya terminado de ver el video, le solitamos conteste esta 
  <a href="http://encuestas.umc.cl/limesurvey/index.php/123456?lang=es" target="_blank">encuesta</a> 
  de evaluación del material.
  -->
<!-- 
  <b>Resultados Evaluación Docente</b>
  <blockquote>
    Se encuentra disponible los Resultados de los Procesos de Evaluación Docente realizados por los estudiantes que
    estuvieron en su(s) asignatura(s), los Directores de Escuelas (evaluación directiva) y usted (autoevaluación).
    Para verlos pinche en el siguiente botón:
    <a href="<?php echo("$enlbase=gestion_ev_docente_profes"); ?>" class='boton'>Resultados Ev. Docente</a>
  </blockquote>
-->
</div><br>
<div class="texto" style="">
<!--
  <b>Presentación sobre Rúbricas de Evaluación UMC</b><br>
  Dr. Luis Venegas<br>
  <iframe src="https://player.vimeo.com/video/349522522" width="640" height="480" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
  <br>
  -->
  <b>Resultados Evaluación Docente</b>
  <blockquote>
    Se encuentra disponible los Resultados de los Procesos de Evaluación Docente realizados por los estudiantes que
    estuvieron en su(s) asignatura(s), los Directores de Escuelas (evaluación directiva) y usted (autoevaluación).
    Para verlos pinche en el siguiente botón:
    <a href="<?php echo("$enlbase=gestion_ev_docente_profes"); ?>" class='boton'>Resultados Ev. Docente</a>
  </blockquote>
</div><br>

<?php } ?>

<!--
<div class="texto" style="">
  Lunes 4 de abril de 2011: <b>Se encuentra disponible el módulo de Calendarización de asignaturas</b>
  <blockquote>
    La CALENDARIZACIÓN ACADÉMICA es una herramienta fundamental de la gestión docente para el logro de los aprendizajes
    programados de los alumnos de la asignatura que usted dicta en la Universidad, la cual se debe realizar a comienzo de semestre
    y entregar a los Directores/as de Escuela, los que deberán enviar los formularios a la Dirección de Docencia.<br>
    <br>
    Para facilitar su elaboración se ha informatizado el procedimiento en el SGU y se ha preparado el siguiente
    <a href='instructivo_calendarizacion.pdf' target='_blank'><b>instructivo</b></a><br>
    <br>
</div>
-->

<!--
<div class="texto" style="color: #7D7D7D">
  Lunes 15 de Septiembre de 2008: <b>Se encuentra disponible el ingreso de Calificaciones Parciales</b>
  <blockquote>
    A diferencia del semestre anterior, en que se encontraba predefinida la cantidad de notas parciales (dos notas)
    que un profesor debía aplicar, en este semestre cada docente podrá definir para cada curso este parámetro. El
    rango es de mínimo una a un máximo de siete notas parciales que se aplicarán e ingresarán durante el semestre. Así
    mismo, el SGU cuando detecte que no se ha definido tal parámetro, preguntará al intentar entrar a un curso para
    ser calificado. Finalmente, este parámetro podrá ser cambiado por el profesor las veces que estime conveniente, no
    obstante, en la medida que lo disminuya, la orden que le estará dando al SGU es de eliminar todas aquellas notas
    parciales fuera del rango. Para cualquier duda, puede consultar con su coordinador de carrera.
  </blockquote><br>
  Lunes 25 de Agosto de 2008: <b>Se encuentra habilitado los cupos de cursos</b>
  <blockquote>
    Como una necesidad natural de la gestión académico administrativa, se ha habilitado los cupos de cursos
    que funcionan como tope al momento de realizar inscripciones de asignaturas. Algunos cursos (que se solicitaron
    informalmente) ya tienen sus cupos establecidos, mientras que para el resto de los cursos del periodo 2-2008 no lo
    tienen, por tanto, no hay límite de alumnos a inscribir. Prontamente, en el módulo de edición de cursos, aparecerá
    la opción para establecer el cupo de un curso.
  </blockquote><br>
  Viernes 18 de Julio de 2008: <b>Se encuentra disponible el cálculo de Promedio de Notas Parciales</b>
  <blockquote>
    Originalmente se pensó en un cálculo centralizado de Nota de cátedra (vale decir, promedio de Notar Parciales) y
    la Nota Final, pero era poco práctico. Entonces, para calcular este promedio, se debe entrar al modulo de control
    de Notas Parciales y pinchar en el último botón (Calcular Nota de Cátedra). Este paso será necesario para obtener el
    listado de alumnos con derecho u obligación de rendir prueba recuperativa (a excepción de las asignaturas que se
    encuentran en proceso de Examinación por parte del CSE).
  </blockquote><br>
<!--
Viernes 20 de octubre de 2006: <b>&Uacute;ltima hora!!!</b>
<blockquote>
Nuestro Vicerrector Acad&eacute;mico autoriz&oacute; la nueva nomenclatura para los m&oacute;dulos de horarios:
<table border="1" class="texto" cellspacing="0" cellpadding="2">
  <tr><th align="center">Nombre del<br>M&oacute;dulo</th><th align="center">intervalo</th></tr>
  <tr valign="top"><td align="center">A</td><td align="left">08:30 - 10:00</td></tr>
  <tr valign="top"><td align="center">B</td><td align="left">10:15 - 11:45</td></tr>
  <tr valign="top"><td align="center">C</td><td align="left">12:00 - 13:30</td></tr>
  <tr valign="top"><td align="center">D</td><td align="left">13:45 - 15:15</td></tr>
  <tr valign="top"><td align="center">E</td><td align="left">15:30 - 17:00</td></tr>
  <tr valign="top"><td align="center">F</td><td align="left">17:15 - 18:45</td></tr>
  <tr valign="top"><td align="center">G</td><td align="left">19:00 - 20:30</td></tr>
  <tr valign="top"><td align="center">H</td><td align="left">20:45 - 22:15</td></tr>
</table>
</blockquote>
Mi&eacute;rcoles 16 de agosto de 2006: <b>Apunto de la liberación 1.0</b><br>
<blockquote>
Luego de ya largos 6 meses de desarrollo y m&aacute;s dos meses de dise&ntilde;o,
SGU (Sistema de Gesti&oacute;n Universitaria), en su primera liberaci&oacute;n
siendo la candidata a la versi&oacute;n estable 1.0, est&aacute; apunto de ver la luz.<br>
<br>
En un inicio, este proyecto fue un prototipo para un trabajo de taller universitario,
pero luego de un arduo dise&ntilde;o de datos interno (con la colaboraci&oacute;n del DESIM y
la Vicerector&iacute;a Acad&eacute;mica), y la filosof&iacute;a del software libre han permitido
sacar adelante este software, que ayudar&aacute; notablemente a la gesti&oacute;n de nuestra
Universidad Miguel de Cervantes.
</blockquote>
</div>
-->

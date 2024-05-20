<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$ids_carreras = $_SESSION['ids_carreras'];
 
include("validar_modulo.php");
include("libchart/classes/libchart.php");

$_id_escuela = $_REQUEST['id_escuela'];
$_ano        = $_REQUEST['ano'];
$_semestre   = $_REQUEST['semestre'];
$_jornada    = $_REQUEST['jornada'];
$_regimen    = $_REQUEST['regimen'];

if ($_REQUEST['ano'] == "") { $_ano = $ANO; }
if ($_REQUEST['semestre'] == "") { $_semestre = $SEMESTRE; }
if (empty($_REQUEST['regimen'])) { $_regimen = 'PRE'; }

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }
$escuelas = consulta_sql("SELECT id,nombre FROM escuelas ORDER BY nombre;");

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

//include("iga_calendarizaciones.php");
//include("iga_asignaturas_criticas.php");
//include("iga_grado_acad_docentes.php");
//include("iga_rematricula.php");
//include("iga_inscripcion_asignaturas.php");
include("iga_reincorporados.php");
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style="margin-top: 5px">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Año:<br>
          <select class='filtro' name="ano" onChange="submitform();">
            <option value="0">Todos</option>
            <?php echo(select($anos,$_ano)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Semestre:<br>
          <select class='filtro' name="semestre" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($semestres,$_semestre)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Escuela/Departamento:<br>
          <select class='filtro' name="id_escuela" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($escuelas,$_id_escuela)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Jornada:<br>
          <select class='filtro' name="jornada" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($JORNADAS,$_jornada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Régimen:</div>
          <select class="filtro" name="regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$_regimen)); ?>
          </select>
        </td>
      </tr>
    </table>
    <br>
    <div style='height: 600px; overflow: auto'>
      <?php //echo(cuadro_Calendarizaciones());?>
      <?php //echo(cuadro_AsignaturasCriticas());?>
      <?php //echo(cuadro_GradoAcadDocentes());?>
      <?php //echo(cuadro_Rematricula());?>
      <?php //echo(cuadro_Rematricula_Carreras());?>
      <?php //echo(cuadro_Inscripcion_Asignaturas());?>
      <?php echo(cuadro_Reincorporados());?>
    </div>
</div>

</form>
<!-- Fin: <?php echo($modulo); ?> -->


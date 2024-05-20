<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
include("validar_modulo_uid_no_cero.php");

$id_alumno  = $_REQUEST['id_alumno'];

$SQL_alumno = "SELECT va.id,va.nombre,va.rut,trim(va.carrera) AS alias_carrera,va.malla_actual,
                      coalesce(va.id_malla_actual,0) AS id_malla_actual,va.id_carrera,a.jornada,a.admision,
                      c.nombre AS carrera,a.cohorte,a.semestre_cohorte,a.mes_cohorte,a.cohorte_reinc,a.semestre_cohorte_reinc,a.mes_cohorte_reinc
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               WHERE va.id=$id_alumno;";
$alumno     = consulta_sql($SQL_alumno);

extract($alumno[0]);

if ($_REQUEST['guardar'] == "Guardar") {
	if ($_REQUEST['malla_actual'] > 0) {
		$malla = consulta_sql("SELECT id_carrera,alias_carrera||'/'||ano AS nombre_malla FROM vista_mallas WHERE id={$_REQUEST['malla_actual']}");
		$_REQUEST['carrera_actual'] = $id_carrera;
		if ($malla[0]['id_carrera'] <> $id_carrera) {
			echo(msje_js("Ha escogido un Plan de estudios perteneciente a una carrera distinta "
			            ."a la que el alumno cursa actualmente.\\n"
			            ."Entonces junto con modificar la malla actual para este alumno, "
			            ."se modificará la carrera."));
			$_REQUEST['carrera_actual'] = $malla[0]['id_carrera'];
		}
		$aCampos = array("carrera_actual","malla_actual","admision","jornada","cohorte_reinc","mes_cohorte_reinc","semestre_cohorte_reinc");
		$SQL_update = "UPDATE alumnos SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_alumno";
		$alumno = consulta_dml($SQL_update);
		if ($alumno == 1) {
			echo(msje_js("Se han guardado existosamente los datos"));
			echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		}
	} else {
		echo(msje_js("No ha seleccionado un Plan de estudios"));
	}
}

$SQL_mallas = "(SELECT id,alias_carrera||'/'||ano AS nombre FROM vista_mallas WHERE id_carrera=$id_carrera ORDER BY alias_carrera,ano DESC)
               UNION ALL
               (SELECT 0,'-- Planes de otras carreras --')
               UNION ALL
               (SELECT id,alias_carrera||'/'||ano AS nombre FROM vista_mallas WHERE id_carrera<>$id_carrera ORDER BY alias_carrera,ano DESC)";

$mallas     = consulta_sql($SQL_mallas);

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>"Primer"),
                            array("id"=>2,"nombre"=>"Segundo"));
$COHORTES_REINC = array();
for ($ano=$cohorte;$ano<=$ANO+1;$ano++) { $COHORTES_REINC[] = array("id"=>$ano,"nombre"=>$ano); }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="guardar" value="Guardar"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="history.back();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr" style="text-align: center" colspan="4">Antecedentes del Alumno</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">ID:</td>
    <td class="celdaValorAttr"><?php echo($id); ?></td>
    <td class="celdaNombreAttr">RUT:</td>
    <td class="celdaValorAttr"><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr"><?php echo($carrera); ?></td>
    <td class="celdaNombreAttr"><u>Jornada:</u></td>
    <td class="celdaValorAttr">
      <select name='jornada' class='filtro'>
        <?php echo(select($JORNADAS,$jornada)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Cohorte Inicial:</td>
    <td class="celdaValorAttr"><?php echo("$semestre_cohorte-$cohorte (".$meses_palabra[$mes_cohorte-1]['nombre'].")"); ?></td>
    <td class="celdaNombreAttr">Cohorte<br>Reincorporación:</td>
    <td class="celdaValorAttr">
      <table class='tabla'>
        <tr>
          <td class="celdaValorAttr">
			Semestre:<br>
            <select class='filtro' name='semestre_cohorte_reinc'>
              <option value=''>-- Seleccione --</option>
              <?php echo(select($SEMESTRES_COHORTES,$semestre_cohorte_reinc)); ?>
            </select>
          </td>
          <td class="celdaValorAttr">
            Año:<br>
            <select class='filtro' name='cohorte_reinc'>
              <option value=''>-- Seleccione --</option>
              <?php echo(select($COHORTES_REINC,$cohorte_reinc)); ?>
            </select>
          </td>
          <td class="celdaValorAttr">
            Mes:<br>
            <select class='filtro' name='mes_cohorte_reinc'>
              <option value=''>-- Seleccione --</option>
              <?php echo(select($meses_fn,$mes_cohorte_reinc)); ?>
            </select>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Admisión:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="admision" class='filtro'>
        <?php echo(select($ADMISION,$alumno[0]['admision']));?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr" style="text-align: center" colspan="4">Plan de Estudios del Alumno</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Actual:</td>
    <td class="celdaValorAttr">
      <a href="<?php echo("$enlbase=ver_malla&id_malla=$id_malla"); ?>">      
        <?php echo($alias_carrera."/".$malla_actual); ?>
      </a>
    </td>
    <td class="celdaNombreAttr"><u>Nuevo:</u></td>
    <td class="celdaValorAttr">
      <select name="malla_actual" class='filtro'>
        <?php echo(select($mallas,$id_malla_actual)); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->


	<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");
$bdcon = pg_connect("dbname=regacad" . $authbd);

if ($_REQUEST['ano'] == "") {
	$_REQUEST['ano'] = strftime("%Y") + 1;
};	

$ano         = $_REQUEST['ano'];
$id_malla    = $_REQUEST['id_malla'];
$id_carrera  = $_REQUEST['id_carrera'];
$comentarios = $_REQUEST['comentarios'];
 
if ($_REQUEST['crear'] <> "") {
	$SQLtxt = "SELECT * FROM mallas WHERE ano=$ano and id_carrera=$id_carrera;";
	$resultado = pg_query($bdcon, $SQLtxt);
	$filas = pg_numrows($resultado);
	if ($filas > 0) {
		$mensaje  = "Intenta crear una Malla para un año y carrera ya existente en la base de datos.\\n"
		          . "A continuación puede editar nuevamente los datos del ingreso.";
		echo(msje_js($mensaje));
	} else {
		//Copiar encabezado de malla
		$SQLinsert0 = "INSERT INTO mallas (ano,id_carrera,niveles,cant_asig_oblig,cant_asig_elect,cant_asig_efp,comentarios) 
		                    SELECT $ano AS ano,$id_carrera AS id_carrera,niveles,cant_asig_oblig,
		                           cant_asig_elect,cant_asig_efp,'$comentarios' AS comentarios
		                    FROM mallas WHERE id=$id_malla;";
		$resultado0 = pg_query($bdcon, $SQLinsert0);
		$filas0 = pg_affected_rows($resultado0);
		if ($filas0 > 0) {
			//en caso de exito, obtengo el id de la nueva malla
			$SQLtxt0 = "SELECT currval('mallas_id_seq') AS \"id_malla_nueva\";";
			$id_malla_nueva = pg_fetch_result(pg_query($bdcon, $SQLtxt0),0,'id_malla_nueva');			
			
			//copio las asignaturas de la malla antigua a la nueva
			$SQLinsert1 = "INSERT INTO detalle_mallas (id_malla,id_prog_asig,nivel,caracter,linea_tematica) 
			                    SELECT $id_malla_nueva AS id_malla,id_prog_asig,nivel,caracter,linea_tematica
			                    FROM detalle_mallas WHERE id_malla=$id_malla ORDER BY id;";
			$resultado1 = pg_query($bdcon, $SQLinsert1);
			$filas1 = pg_affected_rows($resultado1);
			if ($filas1 > 0) {
				//comienzo a copiar los pre/post requisitos de la malla antigua a la nueva
				//Se comienza creando una tabla de homolagacion de los id de detalle de malla nuevos con los antiguos
				$SQLtxt2 = "SELECT dm_original.id AS id_dm_original, dm_copia.id AS id_dm_copia
				            INTO TEMP copia_malla 
				            FROM (SELECT * from detalle_mallas where id_malla=$id_malla) AS dm_original
				            JOIN (SELECT * from detalle_mallas where id_malla=$id_malla_nueva) AS dm_copia 
				              ON dm_copia.id_prog_asig=dm_original.id_prog_asig
				            ORDER BY dm_original.id;";
				$resultado2 = pg_query($bdcon, $SQLtxt2);
				//luego genero la tabla de requisitos para la nueva malla, con los id nuevos correspondientes
				$SQLtxt3 = "SELECT cm1.id_dm_copia AS id_dm, cm2.id_dm_copia AS id_dm_req, req.tipo AS tipo
				            INTO TEMP req_malla_copia
				            FROM requisitos_malla AS req
				            JOIN copia_malla AS cm1 ON cm1.id_dm_original=req.id_dm
				            JOIN copia_malla AS cm2 ON cm2.id_dm_original=req.id_dm_req;";
				$resultado3 = pg_query($bdcon, $SQLtxt3);
				//luego los inserto en la tabla de los requisitos. Aqui termina la copia de malla.
				$SQLtxt4 = "INSERT INTO requisitos_malla SELECT * from req_malla_copia;"; 
				$resultado4 = pg_query($bdcon, $SQLtxt4);
				$filas4 = pg_affected_rows($resultado4);
				if ($filas4 > 0) {
					$mensaje  = "Se ha creado una copia de Malla con los parámetros ingresados\\n";
					echo(msje_js($mensaje));
					echo(js("window.location='principal.php?modulo=ver_malla&id_malla=$id_malla_nueva'"));
				};
			};
		};
	};
};

$SQLtxt_0 = "SELECT id,nombre FROM carreras ORDER BY nombre;";
$resultado_0 = pg_query($bdcon, $SQLtxt_0);
$filas_0 = pg_numrows($resultado_0);
if ($filas_0 > 0) {
	$carreras = pg_fetch_all($resultado_0);
};

if ($id_carrera <> "") {
	$SQLtxt_1 = "SELECT id,ano AS nombre FROM mallas WHERE id_carrera=$id_carrera ORDER BY ano;";
	$resultado_1 = pg_query($bdcon, $SQLtxt_1);
	$filas_1 = pg_numrows($resultado_1);
	if ($filas_1 > 0) {
		$anos_mallas = pg_fetch_all($resultado_1);
	};
};

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('ano','id_carrera','id_malla');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="crear" value="Crear copia"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="cancelar_guardar();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr">
      <select name="id_carrera" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($carreras,$_REQUEST['id_carrera'])); ?>
      </select>
    </td>
  </tr>
<?php
	if ($filas_1 > 0) {
?>
  <tr>
    <td class="celdaNombreAttr">A&ntilde;o desde que se copiar&aacute;:</td>
    <td class="celdaValorAttr">
      <select name="id_malla" onChange="cambiado();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($anos_mallas,$_REQUEST['id_malla'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">A&ntilde;o de la nueva malla:</td>
    <td class="celdaValorAttr">
      <input type="text" name="ano" value="<?php echo($_REQUEST['ano']); ?>" maxlength="4" size="4" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Comentarios para<br>la nueva malla:</td>
    <td class="celdaValorAttr">
      <textarea name='comentarios'><?php echo($_REQUEST['comentarios']); ?></textarea>
    </td>
  </tr>
<?php
	} else {
		if ($id_carrera <> "") {
			echo(msje_js("Esta carrera no tiene mallas"));
			echo(js("window.location='principal.php?modulo=$modulo'"));
		};
	};
?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->


<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

if ($_SESSION['id_escuela'] <> "") {
	$ids_carreras = $_SESSION['ids_carreras'];
}

$id_escuela_u = $_REQUEST['id_escuela_u'];
$jornada    = $_REQUEST['jornada'];
if ($jornada <> "D" && $jornada <> "V") { $jornada = ""; }

$fecha = $_REQUEST['fecha'];
if ($fecha == "") {
	$fecha = strftime("%Y-%m-%d");
} else {
	list($dia,$mes,$ano)=explode("-",$fecha);
	$fecha = "$ano-$mes-$dia"; 
	$fecha = strftime("%Y-%m-%d",strtotime($fecha));
}

include("validar_modulo.php");

$fecha_prop = strftime("%d-%m-%Y",strtotime($fecha));

if ($_REQUEST['aceptar'] == "Aceptar") {
	$problemas = false;
	
	if ($_SESSION['id_escuela'] == "" && $id_escuela_u == "") {
		echo(msje_js("Usted no tiene una escuela definida y debe seleccionar obligatoriamente una escuela a la cual va ingresar"));
		$problemas = true;
	}
	
	if (!strtotime($fecha)) {
		echo(msje_js("Ha ingresado una fecha inválida. Corrigala y vuelva a intentarlo. No olvide usar el formato DD-MM-AAAA"));
		$problemas = true;
	}
	
	if (strtotime($fecha) > time()+60*60*24) {
		echo(msje_js("Ha ingresado una fecha inválida. No se permite una fecha superior a la de mañana.\\n"
		            ."Corrigala y vuelva a intentarlo. No olvide usar el formato DD-MM-AAAA"));
		$problemas = true;
	}
	if (strtotime($fecha) < $Fec_Ini_Sem1) {
		echo(msje_js("Ha ingresado una fecha inválida. No se permite una fecha inferior a la del inicio del semestre.\\n"
		            ."Corrigala y vuelva a intentarlo. No olvide usar el formato DD-MM-AAAA"));
		$problemas = true;
	}
		
	if (!$problemas) { 
		$fecha = strftime("%Y%m%d",strtotime($fecha));
		echo(js("window.location='$enlbase=profesores_control_asistencia&fecha=$fecha&jornada=$jornada&id_escuela_u=$id_escuela_u';"));
		exit;
	}
}

$escuelas = consulta_sql("SELECT id,nombre FROM escuelas ORDER BY nombre");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

<table width="100%">
  <tr>
    <td>
      <input type="submit" name="aceptar" value="Aceptar">
    </td>
  </tr>
</table>

<div class="texto">
</div><br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
<?php if ($_SESSION['id_escuela'] == "") { ?>
  <tr>
    <td class='celdaNombreAttr'>Escuela:</td>
    <td class='celdaValorAttr'>
      <select name="id_escuela_u">
        <option value="">-- Seleccione --</option>
        <?php echo(select($escuelas,$id_escuela_u)); ?>
      </select>
    </td>
  </tr>
<?php } ?>
  <tr>
    <td class='celdaNombreAttr'>Jornada:</td>
    <td class='celdaValorAttr'>
      <select name="jornada">
        <option value="">Ambas</option>
        <?php echo(select($JORNADAS,$jornada)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'>
      <input type="text" size="10" name="fecha" value="<?php echo($fecha_prop); ?>"><br>
      <sup>Formato DD-MM-AAAA</sup>      
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<!--
 ALTER TABLE asist_profesores ALTER id_coordinador DROP not null;
-->

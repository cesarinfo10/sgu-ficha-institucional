<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

$ids_carreras = $_SESSION['ids_carreras'];
 
include("validar_modulo.php");

$cant_reg = 30;

$reg_inicio = $_REQUEST['reg_inicio'];
if ($reg_inicio=="") {
	$reg_inicio = 0;
};

$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$id_carrera   = $_REQUEST['id_carrera'];
$ano          = $_REQUEST['ano'];
$semestre     = $_REQUEST['semestre'];

$condiciones = "WHERE ";

if ($ids_carreras <> "") {
	if ($texto_buscar <> "" &&  $buscar == "Buscar") {
		$texto_buscar_regexp = sql_regexp($texto_buscar);
		$id_carrera = "";
		$ano = "";
		$semestre = "";
		$condiciones .= "(asignatura ~* '$texto_buscar_regexp' OR
	                     cod_asignatura ~* '$texto_buscar_regexp' OR
	  	                  id ~* '$texto_buscar_regexp' OR
	     	               profesor ~* '$texto_buscar_regexp')
	     	               AND id_carrera IN ($ids_carreras)";
		} else {
			$texto_buscar = "";
		};
	};
	

if ($id_carrera <> "") {
	$condiciones = "WHERE id_carrera=$id_carrera";
};

if ($condiciones <> "") {
	if ($ano <> "" && $semestre <> "") {
		$condiciones .= " AND semestre=$semestre AND ano=$ano";
	};
	if ($ano <> "" && $semestre == "") {
		$condiciones .= " AND ano=$ano";
	};
	if ($ano == "" && $semestre <> "") {
		$condiciones .= " AND semestre=$semestre";
	};
} else {
	if ($ano <> "" && $semestre <> "") {
		$condiciones = "WHERE semestre=$semestre AND ano=$ano";
	};
	if ($ano <> "" && $semestre == "") {
		$condiciones = "WHERE ano=$ano";
	};
	if ($ano == "" && $semestre <> "") {
		$condiciones = "WHERE semestre=$semestre";
	};

};
/*if ($ids_carreras <> "" && $condiciones <> "") {
	$condiciones .= " AND id_carrera IN ($ids_carreras) ";
} else {
	$condiciones = " WHERE id_carrera IN ($ids_carreras) ";
};*/

$bdcon = pg_connect("dbname=regacad" . $authbd);

$SQLtxt = "SELECT id,cod_asignatura||'-'||seccion AS \"código\",asignatura,semestre||'-'||ano AS periodo,
                  profesor AS \"profesor cátedra\"
           FROM vista_cursos $condiciones 
           ORDER BY ano DESC, semestre DESC, cod_asignatura
           LIMIT $cant_reg
           OFFSET $reg_inicio;";
          
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
$cant_campos = pg_num_fields($resultado);
$tot_reg = 0;
if ($filas > 0) {
	$cursos = utf2html(pg_fetch_all($resultado));
	$SQLtxt0 = "SELECT count(id) FROM vista_cursos $condiciones;";
	$resultado0 = pg_query($bdcon, $SQLtxt0);
	$tot_reg = pg_fetch_row($resultado0, 0);
	$tot_reg = $tot_reg[0];
	$reg_ini_sgte = $reg_inicio + $cant_reg;
	$reg_ini_ante = $reg_inicio - $cant_reg;
	if ($reg_ini_ante < 0) {
		$reg_ini_ante = 0;
	};
	if ($reg_ini_sgte >= $tot_reg) {
		$reg_ini_sgte = 0;
	};
};

if ($_SESSION['ids_carreras'] <> "") {
	$SQLtxt2 = "SELECT id,nombre FROM carreras WHERE id IN (".$_SESSION['ids_carreras'].") ORDER BY nombre;";
} else {
	$SQLtxt2 = "SELECT id,nombre FROM carreras ORDER BY nombre;";
};
$resultado2 = pg_query($bdcon, $SQLtxt2);
$filas2 = pg_numrows($resultado2);
if ($filas2 > 0) {
	$carreras = utf2html(pg_fetch_all($resultado2));
};

$_SESSION['enlace_volver'] = "$enlbase=$modulo&id_carrera=$id_carrera&texto_buscar=$texto_buscar&buscar=$buscar&reg_inicio=$reg_inicio";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="2" border="0" cellspacing="0" width="auto">
      <tr valign="top">
        <td class="texto" width="auto">
          Filtrar por a&ntilde;o
          <select name="ano" onChange="submitform();">
            <option value="">Todas</option>
				<?php
					$anos = array();
					for($ano_x=1998;$ano_x<=date("Y");$ano_x++) {
						$anos = array_merge($anos,array($ano_x => array("id" => $ano_x,"nombre" => $ano_x)));
					};
					echo(select($anos,$ano));
				?>
          </select>
          y/o semestre:
          <select name="semestre" onChange="submitform();">
            <option value="">Todos</option>
				<?php
					$semestres = array(array("id" => 1,"nombre" => 1),
					                   array("id" => 2,"nombre" => 2));
					echo(select($semestres,$semestre));
				?>
          </select>
        </td>
      </tr>
      <tr valign="top">
        <td class="texto">
          Mostrar cursos de la carrera:<br>
          <select name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>    
          </select>
        </td>
      </tr>
      <tr valign="top">
        <td class="texto" width="auto">
          Buscar por C&oacute;digo (de asignatura), n&uacute;mero de acta, asignatura o profesor:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="20">
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	};
          ?>
        </td>
      </tr>
    </table>
  </form>
  <?php
  	$enlace_nav = "$enlbase=$modulo&id_carrera=$id_carrera&texto_buscar=$texto_buscar&ano=$ano&semestre=$semestre&buscar=$buscar&reg_inicio";
  ?>
  Mostrando <b><?php echo($tot_reg); ?></b> curso(s) en total, en p&aacute;gina(s) de <?php echo($cant_reg); ?> filas<br>
  <a class="enlaces" href="<?php echo("$enlace_nav=$reg_ini_ante"); ?>">Anterior</a> | 
  <?php
  	for($pag=1;$pag<=ceil($tot_reg/$cant_reg);$pag++) {
  		if ($cant_reg*($pag-1)== $reg_inicio) {
  			echo(" <b>$pag</b> |");
  		} else {
  			$reg_ini_pag = ($pag - 1) * $cant_reg;
  			echo("<a class='enlaces' href='$enlace_nav=$reg_ini_pag'> $pag</a> |");
  		};  			
  	};
  ?>
  <a class="enlaces" href="<?php echo("$enlace_nav=$reg_ini_sgte"); ?>">Siguiente</a>
</div>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
	<?php
		for ($y=1;$y<$cant_campos;$y++) {
			$nombre_campo = ucfirst(pg_field_name($resultado, $y));
			echo("<td class='tituloTabla'>$nombre_campo</td>\n");
		};
	?>
  </tr>
<?php
	if ($filas > 0) {
		for ($x=0; $x<$filas; $x++) {
			$enl = "$enlbase=ver_curso&id_curso=" . $cursos[$x]['id'];
			$enlace = "<a class='enlitem' href='$enl'>";
			echo("  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n");
			for ($z=1;$z<$cant_campos;$z++) {
				$alinear="";
				if (strncmp(pg_field_type($resultado,$z),"int",3) == 0 || pg_field_type($resultado,$z) == "date") {
					$alinear=" align='right'";
				};			 
				echo("    <td class='textoTabla'$alinear>&nbsp;$enlace");
				echo($cursos[$x][pg_field_name($resultado,$z)] . "</a></td>\n");
			};
		};
	} else {
		echo("<td class='textoTabla' colspan='$cant_campos'>No hay registros para los criterios de b&uacute;squeda/selecci&oacute;n</td>\n");
	};
?>
</table><br>
<div class="texto">
  <a class="enlaces" href="<?php echo("$enlace_nav=$reg_ini_ante"); ?>">Anterior</a> | 
  <?php
  	for($pag=1;$pag<=ceil($tot_reg/$cant_reg);$pag++) {
  		if ($cant_reg*($pag-1)== $reg_inicio) {
  			echo(" <b>$pag</b> |");
  		} else {
  			$reg_ini_pag = ($pag - 1) * $cant_reg;
  			echo("<a class='enlaces' href='$enlace_nav=$reg_ini_pag'> $pag</a> |");
  		};  			
  	};
  ?>
  <a class="enlaces" href="<?php echo("$enlace_nav=$reg_ini_sgte"); ?>">Siguiente</a>
</div>
<!-- Fin: <?php echo($modulo); ?> -->


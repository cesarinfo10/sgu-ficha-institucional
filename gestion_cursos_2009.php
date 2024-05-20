<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

$ids_carreras = $_SESSION['ids_carreras'];
 
include("validar_modulo.php");

$cant_reg = 30;

$reg_inicio = $_REQUEST['reg_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$id_carrera   = $_REQUEST['id_carrera'];
$ano          = $_REQUEST['ano'];
$semestre     = $_REQUEST['semestre'];
$seccion      = $_REQUEST['seccion'];
$tipo         = $_REQUEST['tipo'];
$sala         = $_REQUEST['sala'];

if ($_REQUEST['ano'] == "") { $ano = $ANO; }
if ($_REQUEST['semestre'] == "") { $semestre = $SEMESTRE; }

if (!empty($id_carrera) || !empty($ano) || !empty($semestre) || !empty($seccion) || !empty($sala)) {
	
	$condiciones = "WHERE true ";
	
	if (!empty($id_carrera)) { $condiciones .= "AND vc.id_carrera=$id_carrera "; }
	
	if (!empty($ano))        { $condiciones .= "AND vc.ano=$ano "; }
	
	if (!empty($semestre))   { $condiciones .= "AND vc.semestre=$semestre "; }
	
	if (!empty($seccion))    { $condiciones .= "AND vc.seccion=$seccion "; }
	
	if (!empty($tipo))       { $condiciones .= "AND c.tipo='$tipo' "; }

	if (!empty($sala))       { $condiciones .= "AND $sala IN (vc.sala1,vc.sala2,vc.sala3) "; }
	
	if (!empty($dia))        { $condiciones .= "AND $dia IN (vc.dia1,vc.dia2,vc.dia3) "; }
	
	if (!empty($horario))    { $condiciones .= "AND $horario IN (vc.horario1,vc.horario2,vc.horario3) "; }
	
}

if ($texto_buscar <> "" &&  $buscar == "Buscar") {
        $texto_buscar_regexp = sql_regexp($texto_buscar);
        $textos_buscar = explode(" ",$texto_buscar_regexp);
        $condiciones = "WHERE ";
        for ($x=0;$x<count($textos_buscar);$x++) {
                $cadena_buscada = strtolower($textos_buscar[$x]);

	        $condiciones .= "(lower(asignatura) ~* '$cadena_buscada' OR "
		             .  " cod_asignatura ~* '$cadena_buscada' OR "
		             .  " id ~* '$cadena_buscada' OR "
		             .  " lower(profesor) ~* '$cadena_buscada') AND ";
        }
        $condiciones = substr($condiciones,0,strlen($condiciones)-4);
	$id_carrera = $ano = $semestre = null;
} else {
	$texto_buscar = "";
};


if ($condiciones <> "") {
	if ($ids_carreras <> "") {
		$condiciones .= " AND id_carrera IN ($ids_carreras) ";
	}
} else {
	if ($ids_carreras <> "") {
		$condiciones = "WHERE id_carrera IN ($ids_carreras)";
	}
}

$SQL_cant_s1 = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso=vc.id AND solemne1 IS NOT NULL";
$SQL_cant_nc = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso=vc.id AND nota_catedra IS NOT NULL";
$SQL_cant_s2 = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso=vc.id AND solemne2 IS NOT NULL";
$SQL_cant_nf = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso=vc.id AND nota_final IS NOT NULL AND (id_estado IS NULL OR id_estado NOT IN (6,10,11))";

$SQL_cursos = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre||'-'||vc.ano AS periodo,
                      vc.profesor,coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                      cantidad_alumnos(vc.id) AS cant_alumnos,
                      CASE WHEN cantidad_alumnos(vc.id) BETWEEN 1 AND ($SQL_cant_s1) THEN 'Si' ELSE 'No' END AS s1,
                      CASE WHEN cantidad_alumnos(vc.id) BETWEEN 1 AND ($SQL_cant_nc) THEN 'Si' ELSE 'No' END AS nc,
                      CASE WHEN cantidad_alumnos(vc.id) BETWEEN 1 AND ($SQL_cant_s2) THEN 'Si' ELSE 'No' END AS s2,
                      CASE WHEN cantidad_alumnos(vc.id) BETWEEN 1 AND ($SQL_cant_nf) THEN 'Si' ELSE 'No' END AS nf  
               FROM vista_cursos AS vc
               LEFT JOIN cursos AS c ON c.id=vc.id
               $condiciones 
               ORDER BY vc.ano DESC, vc.semestre DESC, vc.cod_asignatura
               LIMIT $cant_reg
               OFFSET $reg_inicio;";
//echo($SQL_cursos);
$cursos = consulta_sql($SQL_cursos);
if (count($cursos) > 0) {
	$SQL_cursos2 = "SELECT count(id) AS cant_cursos FROM vista_cursos AS vc $condiciones;";
	$cursos2 = consulta_sql($SQL_cursos2);
	$tot_reg = $cursos2[0]['cant_cursos'];
	$reg_ini_sgte = $reg_inicio + $cant_reg;
	$reg_ini_ante = $reg_inicio - $cant_reg;
	if ($reg_ini_ante < 0) {
		$reg_ini_ante = 0;
	};
	if ($reg_ini_sgte >= $tot_reg) {
		$reg_ini_sgte = 0;
	};
};

if ($ids_carreras <> "") {
	$SQL_carreras = "SELECT id,nombre FROM carreras WHERE id IN ($ids_carreras) ORDER BY nombre;";
} else {
	$SQL_carreras = "SELECT id,nombre FROM carreras ORDER BY nombre;";
}
$carreras = consulta_sql($SQL_carreras);

$SECCIONES = array();
for ($x=1;$x<10;$x++) { $SECCIONES = array_merge($SECCIONES,array(array("id"=>$x,"nombre"=>$x))); }

$TIPOS = array(array("id" => "r", "nombre" => "Regular"),
               array("id" => "t", "nombre" => "Tutorial"),
               array("id" => "m", "nombre" => "Modular"));
               
$salas = consulta_sql("SELECT trim(codigo) AS id,nombre FROM salas ORDER BY codigo;");

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
          Año:
          <select name="ano" onChange="submitform();">
            <option value="">Todos</option>
				<?php
					echo(select($anos,$ano));
				?>
          </select>
          Semestre:
          <select name="semestre" onChange="submitform();">
            <option value="-1">Todos</option>
				<?php
					echo(select($semestres,$semestre));
				?>
          </select>
          Sección:
          <select name="seccion" onChange="submitform();">
            <option value="">Todas</option>
				<?php
					echo(select($SECCIONES,$seccion));
				?>
          </select>
          Tipo:
          <select name="tipo" onChange="submitform();">
            <option value="">Todos</option>
				<?php
					echo(select($TIPOS,$tipo));
				?>
          </select>
        </td>
      </tr>
      <tr valign="top">
        <td class="texto">
          Sala:
          <select name="sala" onChange="submitform();">
            <option value="">Todas</option>
				<?php
					echo(select($salas,$sala));
				?>
          </select>
          Día:
          <select name="dia" onChange="submitform();">
            <option value="">Todos</option>
				<?php
					echo(select($dias_palabra,$dia));
				?>
          </select>
          Horario:
          <select name="horario" onChange="submitform();">
            <option value="">Todos</option>
				<?php
					echo(select($horarios,$horario));
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
          Buscar por Código o nombre de asignatura, número de acta o nombre del profesor:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="30">
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	}
          ?>
        </td>
      </tr>
    </table>
  </form>
  <?php
  	$enlace_nav = "$enlbase=$modulo&id_carrera=$id_carrera&texto_buscar=$texto_buscar&ano=$ano&semestre=$semestre&buscar=$buscar&reg_inicio";
  ?>
  Mostrando <b><?php echo($tot_reg); ?></b> curso(s) en total, en página(s) de <?php echo($cant_reg); ?> filas<br>
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
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>A.I.</td>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>NC</td>
    <td class='tituloTabla'>S2</td>
    <td class='tituloTabla'>NF</td>
    <td class='tituloTabla'>Horario</td>
  </tr>
<?php
	if (count($cursos) > 0) {
		$_verde = "color: #009900; text-align: center";
		$_rojo  = "color: #ff0000; text-align: center";

		for ($x=0; $x<count($cursos); $x++) {
			extract($cursos[$x]);
		
			$est_s1 = $est_nc = $est_s2 = $est_nf = "color: #000000";
			
			if (strlen($asignatura)>30) { $asignatura = mb_substr($asignatura,0,30)."...";}
			if (strlen($profesor)>20)   { $profesor   = mb_substr($profesor,0,20)."...";}
			
			if ($s1=="Si") { $est_s1 = $_verde; } else { $est_s1 = $_rojo; }   
			if ($nc=="Si") { $est_nc = $_verde; } else { $est_nc = $_rojo; }   
			if ($s2=="Si") { $est_s2 = $_verde; } else { $est_s2 = $_rojo; }   
			if ($nf=="Si") { $est_nf = $_verde; } else { $est_nf = $_rojo; }   
			
			$enl = "$enlbase=ver_curso&id_curso=$id";
			$enlace = "<a class='enlitem' href='$enl'>";
			echo("  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n");
			echo("    <td class='textoTabla'>$id</td>");
			echo("    <td class='textoTabla'>$asignatura</td>");
			echo("    <td class='textoTabla' style='text-align: right'>$cant_alumnos</td>");
			echo("    <td class='textoTabla'>$periodo</td>");
			echo("    <td class='textoTabla'>$profesor</td>");
			echo("    <td class='textoTabla' style='$est_s1'>$s1</td>");
			echo("    <td class='textoTabla' style='$est_nc'>$nc</td>");
			echo("    <td class='textoTabla' style='$est_s2'>$s2</td>");
			echo("    <td class='textoTabla' style='$est_nf'>$nf</td>");
			echo("    <td class='textoTabla'>$horario</td>");
			echo("  </tr>");
		};
	} else {
		echo("<td class='textoTabla' colspan='9'>No hay registros para los criterios de b&uacute;squeda/selecci&oacute;n</td>\n");
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


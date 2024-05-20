<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_prod_acad = $_REQUEST['id_prod_acad'];

$aCampos = array('nombre','id_usuario_reg','ano','fecha_inicio','fecha_termino','id_estado',
                 'palabras_clave','id_tipo','alcance','id_medio_public','public_formato',
                 'revista_nombre','revista_numero','revista_editorial','revista_ciudad','revista_pais','revista_factor_impacto','revista_enlace',
                 'libro_nombre','libro_editorial','libro_ciudad','libro_pais','libro_enlace',
                 'proyecto_organismo','proyecto_id_invest_princ',
                 'informe_organismo',
                 'ponencia_nombre_congreso','ponencia_modalidad','ponencia_ciudad','ponencia_pais'                 
                );

if ($_REQUEST['guardar'] == "💾 Guardar") {

	$SQL_upd = "UPDATE dpii.productos_acad SET ".arr2sqlupdate($_REQUEST,$aCampos)." WHERE id=$id_prod_acad";
	if (consulta_dml($SQL_upd) > 0) {
        consulta_dml("INSERT INTO dpii.productos_acad_audit VALUES ($id_prod_acad,{$_SESSION['id_usuario']},'Modificación',default)");
		echo(msje_js("Se han guardado exitosamente los datos."));
        echo(js("location.href='$enlbase_sm=productos_acad_ver&id_prod_acad=$id_prod_acad';"));
		//echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

$SQL_autores = "SELECT char_comma_sum(aut.apellidos||aut.nombres) AS autores
                FROM dpii.autores_prod AS aut
				WHERE id_prod_acad=pa.id";

$SQL_asig = "SELECT char_comma_sum(asig.codigo||' '||asig.nombre) AS asignaturas
             FROM dpii.prod_acad_asignaturas AS paa
			 LEFT JOIN prog_asig AS pa ON pa.id=paa.id_prog_asig
			 LEFT JOIN asignaturas AS asig ON asig.codigo=pa.cod_asignatura
			 WHERE paa.id_prod_acad=pa.id";


$SQL_prod = "SELECT *,($SQL_autores) AS autores,($SQL_asig) AS asignaturas
             FROM dpii.productos_acad AS pa
             WHERE id=$id_prod_acad";
$prod = consulta_sql($SQL_prod);
if (count($prod) > 0) {
    $_REQUEST = array_merge($prod[0],$_REQUEST);
    if ($_REQUEST['autores'] == "") { $_REQUEST['autores'] = "** Sin autor(es) ingresado(s) **"; }
    if ($_REQUEST['asignaturas'] == "") { $_REQUEST['asignaturas'] = "** Sin asignatura(s) ingresada(s) **"; }
}

?>
<!-- Fin: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<?php include("productos_acad_formulario.php"); ?>
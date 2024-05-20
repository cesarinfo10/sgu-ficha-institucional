<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}
setlocale(LC_MONETARY,"es_CL.UTF8");
setlocale(LC,"es_CL.UTF8");  
include("validar_modulo.php");

$query =
"SELECT 
	c.id as id_carrera, 
	c.nombre as carreras, 
	c.regimen, 
	c.id_malla_actual,
	m.ano,
	m.niveles,
	e.nombre as escuela 
FROM carreras as c 
	left join escuelas as e on e.id=c.id_escuela
	left join mallas as m on m.id=c.id_malla_actual
WHERE regimen in ('PRE') and 
	c.activa and 
	c.admision order by e.nombre;";

$query1 =
"SELECT 
	c.id as id_carrera, 
	c.nombre as carreras, 
	c.regimen, 
	c.id_malla_actual,
	m.ano,
	m.niveles,
	e.nombre as escuela 
FROM carreras as c 
	left join escuelas as e on e.id=c.id_escuela
	left join mallas as m on m.id=c.id_malla_actual
WHERE regimen in ('PRE-D') and 
	c.activa and 
	c.admision order by e.nombre;";

    $Carrera_malla = consulta_sql($query);
    $Carrera_malla1 = consulta_sql($query1);
   //var_dump($Carrera_malla); 
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
<?php echo($nombre_modulo); ?>

</div><br>

<h2>Tabla de datos Carreras activas PRESENCIAL - DISTANCIA </h2>
<table  bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
    <tr class='filaTituloTabla'>
        <th class='tituloTabla'>ID CARRERA</th>
        <th class='tituloTabla'>CARRERA</th>
        <th class='tituloTabla'>REGIMEN</th>
        <th class='tituloTabla'>ID MALLA ACTUAL</th>
        <th class='tituloTabla'>AÑO</th>
        <th class='tituloTabla'>NIVELES</th>
        <th class='tituloTabla'>ESCUELA</th>
        <th class='tituloTabla'>INGRESE SEMESTRE</th>
        <th class='tituloTabla'>INGRESE AÑO</th>
    </tr>
    <?php foreach ($Carrera_malla as $row): ?>
    <tr>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['id_carrera']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['carreras']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['regimen']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['id_malla_actual']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['ano']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['niveles']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['escuela']; ?></td>
       <td align="center" class="textoTabla" style="vertical-align: middle;"><select name="opciones">
    	<option value="1">Semestre 1</option>
    	<option value="2">Semestre 2</option>
		</select></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><select name="años">
    <?php
    // Obtener el año actual
    $ano_actual = date("Y");

    // Calcular el año siguiente
    $ano_siguiente = $ano_actual + 1;

    // Imprimir las opciones de la lista desplegable
    for ($ano = $ano_actual; $ano <= $ano_siguiente; $ano++) {
        echo "<option value=\"$ano\">$ano</option>";
    }
    ?>
</select></td>
    </tr>
    </tr>
    <?php endforeach; ?>
</table>
<BR>
<table  bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
    <tr class='filaTituloTabla'>
        <th class='tituloTabla'>ID CARRERA</th>
        <th class='tituloTabla'>CARRERA</th>
        <th class='tituloTabla'>REGIMEN</th>
        <th class='tituloTabla'>ID MALLA ACTUAL</th>
        <th class='tituloTabla'>AÑO</th>
        <th class='tituloTabla'>NIVELES</th>
        <th class='tituloTabla'>ESCUELA</th>
		<th class='tituloTabla'>INGRESE SEMESTRE</th>
        <th class='tituloTabla'>INGRESE AÑO</th>
    </tr>
    <?php foreach ($Carrera_malla1 as $row): ?>
    <tr>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['id_carrera']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['carreras']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['regimen']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['id_malla_actual']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['ano']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['niveles']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><?php echo $row['escuela']; ?></td>
        <td align="center" class="textoTabla" style="vertical-align: middle;"><select name="opciones">
    	<option value="1">Semestre 1</option>
    	<option value="2">Semestre 2</option>
		</select></td>
		<td align="center" class="textoTabla" style="vertical-align: middle;"><select name="años">
    <?php
    // Obtener el año actual
    $ano_actual = date("Y");

    // Calcular el año siguiente
    $ano_siguiente = $ano_actual + 1;

    // Imprimir las opciones de la lista desplegable
    for ($ano = $ano_actual; $ano <= $ano_siguiente; $ano++) {
        echo "<option value=\"$ano\">$ano</option>";
    }
    ?>
</select></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->





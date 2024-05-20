<?php

$SQL_postulante = "SELECT vp.id,vp.nombre,rut,colegio,ano_egreso_col,promedio_col,
                          ano_psu,puntaje_psu,ies,carr_ies_pro,prom_nt_ies_pro
                   FROM vista_pap AS vp
                   LEFT JOIN carreras AS c1 ON c1.id=vp.carrera1_post
                   LEFT JOIN carreras AS c2 ON c2.id=vp.carrera2_post
                   LEFT JOIN carreras AS c3 ON c3.id=vp.carrera3_post
                   WHERE vp.id=$id_pap;";                   
$postulante     = consulta_sql($SQL_postulante);
if (count($postulante) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}

?>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Postulante</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID Postulante:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['nombre']); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Escolares del Postulante</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Colegio EM:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['colegio']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año de Egreso EM:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['ano_egreso_col']); ?></td>
    <td class='celdaNombreAttr'>Promedio EM:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['promedio_col']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año PAA/PSU:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['ano_psu']); ?></td>
    <td class='celdaNombreAttr'>Puntaje PAA/PSU:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['puntaje_psu']); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Estudios Superiores anteriores del Postulante</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Inst. de Ed. Sup.:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['ies']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['carr_ies_pro']); ?></td>
    <td class='celdaNombreAttr'>Promedio de Notas:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['prom_nt_ies_pro']); ?></td>
  </tr>
</table>


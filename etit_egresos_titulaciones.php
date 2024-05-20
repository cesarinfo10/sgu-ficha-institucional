<?php 
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};
include("validar_modulo.php");
?>
    <div class="tituloModulo">
			<?php echo($nombre_modulo); ?>
			
		</div><br>
		</br>

    
    <div class="texto">EGRESOS : <b><?php echo $mini_glosa ?></b></div><br>
    <!--
    <div class="texto"> - <b><a href="https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/etit_egresos_ano" style="text-align:left">x Año</a></b></div><br>
    <div class="texto"> - <b><a href="https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/etit_egresos_cohorte" style="text-align: left">x Cohorte</a></b></div><br>
    <div class="texto"> - <b><a href="https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/etit_egresos_tasa" style="text-align: left">x Tasa</a></b></div><br>
-->
    <div class="texto"> - <b><a href="<?php echo($enlbase); ?>=etit_egresos_ano" style="text-align:left">x Año</a></b></div><br>
    <div class="texto"> - <b><a href="<?php echo($enlbase); ?>=etit_egresos_cohorte" style="text-align: left">x Cohorte</a></b></div><br>
    <div class="texto"> - <b><a href="<?php echo($enlbase); ?>=etit_egresos_tasa" style="text-align: left">x Tasa</a></b></div><br>

    <br>
    
    <div class="texto">TITULACIONES : <b><?php echo $mini_glosa ?></b></div><br>
    <!--
    <div class="texto"> - <b><a href="https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/etit_titulaciones_ano" style="text-align: left">x Año</a></b></div><br>
    <div class="texto"> - <b><a href="https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/etit_titulaciones_cohorte" style="text-align: left">x Cohorte</a></b></div><br>
    <div class="texto"> - <b><a href="https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/etit_titulaciones_tasa" style="text-align: left">x Tasa</a></b></div><br>
    <div class="texto"> - <b><a href="https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/etit_tiempo_titulacion_cohorte" style="text-align: left">Tiempo de Titulación x Cohorte</a></b></div><br>
    <div class="texto"> - <b><a href="https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/etit_tasa_titulacion_oportuna" style="text-align: left">Tasa de Titulación Oportuna</a></b></div><br>
-->
    <div class="texto"> - <b><a href="<?php echo($enlbase); ?>=etit_titulaciones_ano" style="text-align: left">x Año</a></b></div><br>
    <div class="texto"> - <b><a href="<?php echo($enlbase); ?>=etit_titulaciones_cohorte" style="text-align: left">x Cohorte</a></b></div><br>
    <div class="texto"> - <b><a href="<?php echo($enlbase); ?>=etit_titulaciones_tasa" style="text-align: left">x Tasa</a></b></div><br>
    <div class="texto"> - <b><a href="<?php echo($enlbase); ?>=etit_tiempo_titulacion_cohorte" style="text-align: left">Tiempo de Titulación x Cohorte</a></b></div><br>
    <div class="texto"> - <b><a href="<?php echo($enlbase); ?>=etit_tasa_titulacion_oportuna" style="text-align: left">Tasa de Titulación Oportuna</a></b></div><br>

<?php

?>
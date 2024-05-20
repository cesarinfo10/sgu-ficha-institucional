<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

$tipos_usuario = tipos_usuario($_SESSION['tipo']);
$nombre_real_usuario = nombre_real_usuario($_SESSION['usuario'],$_SESSION['tipo'])
?>

<!-- mensaje -->
<table width="100%" bgcolor="#ffffff" class="tabla" cellspacing='0'>
  <tr style='background-image: linear-gradient(#E5F4FF, #ffffff)'>
    <td width="50%" class="texto" style='vertical-align: middle'>
        Bienvenido <b><?php echo($nombre_real_usuario) ?></b>
        (<?php echo($tipos_usuario['nombre']); ?>)
        <!-- <?php echo(" id_escuela=" . $_SESSION['id_escuela'] . " ids_carreras=" . $_SESSION['ids_carreras']); ?> -->
      </font>
<?php if ($_SESSION['tipo'] <> 3) { // usuarios no profesores, se redirigen a login ?>      
      <a href='salida.php' class='boton'>Salir</a>
<?php } else { //si es profesor, se redirige a la web institucional ?>
      <a href='https://www.umcervantes.cl' class='boton'>Salir</a>
<?php } ?>
    </td>
    <td width="50%" class="texto" style='vertical-align: middle'>
		Periodo de trabajo: <b><?php echo($SEMESTRE.'-'.$ANO); ?></b>
    </td>
    <td class="texto" style='vertical-align: middle' nowrap>
      <font size="1" color="#4D4D4D"><?php echo(strftime("%A %d de %B de %Y", time())); ?></font>
    </td>
  </tr>
</table>
<!-- mensaje -->


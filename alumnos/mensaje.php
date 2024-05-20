<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

$nombre_real_usuario = $_SESSION['nombre'];
?>

<!-- mensaje -->
<table width="100%" bgcolor="#ffffff" class="tabla">
  <tr>
    <td width="100%" class="textoTabla">
        Bienvenido <b><?php echo($_SESSION['nombre_alumno']) ?></b>
        (<?php echo($_SESSION['carrera']); ?>)
        <!-- <?php echo(" id_escuela=" . $_SESSION['id_escuela'] . " ids_carreras=" . $_SESSION['ids_carreras']); ?> -->
      </font>
      <a href="salida.php" class="enlaces">SALIR</a></font>
    </td>
    <td class="textoTabla" nowrap>
      <font size="1" color="#4D4D4D"><?php echo(strftime("%A %d de %B de %Y", time())); ?></font>
    </td>
  </tr>
</table>
<!-- mensaje -->


<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php"); 

$bdcon = pg_connect("dbname=regacad" . $authbd);
$modo  = $_REQUEST['modo'];
$id_alumno=$_REQUEST['id_alumno'];
//echo("<br>id_alumno=$id_alumno");
/*
if ($modo="ELIMINAR") {
  //verificacmos que el nombre clasificacino no exista!!..
//  $SQL = "select count(*) as cuenta from tipo_motivo_clasif_proretencion where upper(clasificacion) = upper('$nombre_clasificacion')";
//  $fPending = consulta_sql($SQL);	
//  $cuenta = $fPending[0]['cuenta'];

//  if ($cuenta == 0) {
    $SQL = "delete from tipo_motivo_clasif_proretencion
    where id = $id_tabla";

    //echo("<br>$SQL<br>");
  
    if (consulta_dml($SQL) == 1) {
      echo(msje_js("Clasificación Eliminada."));
      echo(js("location='$enlbase=crud_clasificaciones_proretencion';"));
    } else {
      echo(msje_js("Error al eliminar."));
    } 
  
//  } else {
//    echo(msje_js("Esta Clasificación ya existe."));
//  }

}
*/
if ($_REQUEST['crear'] <> "") {
  /*
	$nombre_usuario = $_REQUEST['nombre_usuario'];
	$tipo = $_REQUEST['tipo'];
	$SQLtxt = "SELECT id FROM usuarios WHERE nombre_usuario='$nombre_usuario' AND tipo=$tipo;";
	$resultado1 = pg_query($bdcon, $SQLtxt);
	if (pg_numrows($resultado1) > 0) {
		$mensaje  = "Esta intentando crear un usuario que al parecer ya exite en la base de datos.\\n"
		          . "Esto puede estar ocurriendo debido a que para un tipo o perfil de usuario, "
		          . "ya existe el nombre de usuario que esta intentando crear";
		echo(msje_js($mensaje));
	} else {
		$aCampos = array("nombre_usuario","nombre","apellido","sexo","tipo","grado_academico","id_escuela","activo");
		$SQLinsert = "INSERT INTO usuarios " . arr2sqlinsert($_REQUEST,$aCampos);
		$resultado = pg_query($bdcon, $SQLinsert);
		if (!$resultado) {
			echo(msje(pg_last_error()));
		} else {
			$filas = pg_affected_rows($resultado);
		};
		if ($filas > 0) {
			$tipo_usuario = tipos_usuario($_REQUEST['tipo']);
			$asunto = "Nuevo usuario de SGU";
			$cuerpo = "Debes crear el usuario $nombre_usuario en " . $tipo_usuario['servidor'];
			mail("jeugenio@umcervantes.cl",$asunto,$cuerpo);
			$mensaje  = "Se ha creado un nuevo usuario con los datos ingresados.\\n"
			          . "También se ha enviado una petición de creación de casilla de correo al "
			          . "Administrador de Redes y Sistemas, para que genere la cuenta de este nuevo usuario.\\n"
			          . "ATENCIÓN: Este nuevo usuario no podrá ingresar al sistema sino hasta que tenga "
			          . "su casilla de correo creada.\\n\\n"
			          . "Desea añadir a otro usuario?";
			$url_si = "$enlbase=$modulo";
			$url_no = "$enlbase=gestion_usuarios";
			echo(confirma_js($mensaje,$url_si,$url_no));
			exit;
		};
	};
  */
};

$SQL_escuelas = "SELECT id,nombre FROM escuelas;";
$escuelas = consulta_sql($SQL_escuelas);








$SQL_resueltos = "
select 
id, 
glosa_periodo_eval, 
to_char(periodo_desde,'dd/mm/yyyy') periodo_desde, 
to_char(periodo_hasta, 'dd/mm/yyyy') periodo_hasta,
activo ,
mini_glosa ano_periodo
from 
periodo_eval 
order by periodo_desde desc";
$resueltos = consulta_sql($SQL_resueltos);
$HTML_resueltos = "";
for ($x=0;$x<count($resueltos);$x++) {
	extract($resueltos[$x]);
//echo("<br>que ssss : $id");
  //$compromiso_color = (strtotime($fecha_comp) > time()) ? "green" : "red";

/*
  if ($activo=='t') {
        $resueltoFinal = "<span style='color: green'>Sí</span>";
  } else {
    $resueltoFinal = "<span style='color: red'>No</span>";
  }
  if ($respuesta_contacto=='t') {
      $respuesta_contactoFinal = "<span style='color: green'>Contactado</span>";
      
  } else {
    $respuesta_contactoFinal = "<span style='color: red'>Sin Respuesta</span>";
  }
  */
  if ($activo=='t') {
    $strActivo = "Activo";
  } else {
    $strActivo = "";
  }


	$HTML_resueltos .= "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$id</td>\n"
      . "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$strActivo</td>\n"
      . "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$ano_periodo</td>\n"
     . "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
href='$enlbase=evdem_periodo_evaluacion_insert_update
&id_tabla=$id
&id_activar=$strActivo
&id_alumno=$id_alumno
&glosa_periodo=$glosa_periodo_eval
&ano_vigente=$ano_periodo
&modo=EDIT'
>$glosa_periodo_eval</a>
</td>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$periodo_desde</td>\n"
. "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$periodo_hasta</td>\n"

. "</tr>\n"; 
}

if (count($resueltos) == 0) {
	$HTML_resueltos = "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;' colspan='10'>\n"
		  . "    ** No hay Periodos evaluación registrados **"
		  . "  </td>\n"
		  . "</tr>\n";
}

?>



<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre_usuario','nombre','apellido','grado_academico','tipo');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla" style="margin-top: 5px">
  <tr>
	  <td class='celdaFiltro'>
        Acciones:<br>
        <a id='sgu_fancybox' 
     href='<?php echo($enlbase); ?>=evdem_periodo_evaluacion_insert_update
&modo=NUEVO
' class='boton'>Crear Nuevo</a>      
<!--<input type="button" name="cancelar" value="Volver" onclick="window.location='principal.php?modulo=crud_motivos_proretencion';"> -->
    </td>
  </tr>
</table>


<!--
<table class="tabla">
  <tr>
    <td class='textoTabla' style='vertical-align: middle'>
    <a id='sgu_fancybox' 
     href='<?php echo($enlbase); ?>=crud_clasificaciones_proretencion_insert_update
&id_tabla=
&id_alumno=<?php echo($id_alumno); ?>
&nombre_clasificacion=
&modo=NUEVO
' class='boton'>Crear Nuevo</a>      
    </td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Volver" onclick="window.location='principal.php?modulo=crud_motivos_proretencion';"></td> 
  </tr>
</table>
-->
<br>
<!--
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Nombre de usuario:</td>
    <td class="celdaValorAttr">
      <input type="text" name="nombre_usuario" value="<?php echo($_REQUEST['nombre_usuario']); ?>" size="20">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombres:</td>
    <td class="celdaValorAttr">
      <input type="text" name="nombre" value="<?php echo($_REQUEST['nombre']); ?>" size="40">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Apellidos:</td>
    <td class="celdaValorAttr">
      <input type="text" name="apellido" value="<?php echo($_REQUEST['apellido']); ?>" size="40">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">G&eacute;nero:</td>
    <td class="celdaValorAttr">
      <select name="sexo">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($generos,$_REQUEST['sexo'])); ?>
      </select>
    </td>
  </tr>
  <tr>  
    <td class="celdaNombreAttr">Tipo:</td>
    <td class="celdaValorAttr">
      <select name="tipo">
      <option value=''>-- Seleccione --</option>
      <?php echo(select(tipos_usuario(null),$_REQUEST['tipo'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Grado Acad&eacute;mico:</td>
    <td class="celdaValorAttr">
      <select name="grado_academico">
        <option value="">-- Seleccione --</option>
        <?php echo(select(grados_academicos(null),$_REQUEST['grado_academico'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr">
      <select name="id_escuela" onChange="cambiado();">
        <option value="">Sin escuela</option>
        <?php echo(select($escuelas,$usuario[0]['id_escuela'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Activo?</td>
    <td class="celdaValorAttr">
      <select name="activo">
        <?php echo(select($sino,$_REQUEST['activo'])); ?>
      </select>
    </td>
  </tr>
</table>
-->
<div class="texto">
  Listado Periodos Evaluación :
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Año</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Periodo Desde</td>
    <td class='tituloTabla'>Período Hasta</td>
  </tr>
  <?php echo($HTML_resueltos); ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

<script>
  /*
  function fEliminar(id) {
	//	alert("id_curso = " + id_curso);
		var txt;
		var r = confirm("Seguro(a) de eliminar esta clasificación?");
		if (r == true) {
			  var pSaltar = "/sgu/principal.php?modulo=crud_clasificaciones_proretencion&modo=ELIMINAR&id_tabla="+id;
	          pSaltar = "http://" + window.location.hostname + ":" + window.location.port + pSaltar;
	          window.location.href = pSaltar;
		} else {
	//	txt = "You pressed Cancel!";
		}	
	}
  */
</script>
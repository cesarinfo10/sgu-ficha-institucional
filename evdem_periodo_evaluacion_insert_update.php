<?php 
  
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php"); 
$id_tabla  = $_REQUEST['id_tabla'];
$id_ano  = $_REQUEST['id_ano'];

$glosa_periodo  = $_REQUEST['glosa_periodo'];
$modo  = $_REQUEST['modo'];
$accion  = $_REQUEST['accion'];
//$id_alumno= $_REQUEST['id_alumno'];
$id_activar= $_REQUEST['id_activar'];
$ano_vigente= $_REQUEST['ano_vigente'];


//$SQL = "select mini_glosa ano_min_db from periodo_eval where activo = 't';"; 
//$anitos = consulta_sql($SQL);
//extract($anitos[0]);

//if ($ano_min_db == "") {
//  $ano_min_db = $ano_vigente;
//} 

if (($ano_vigente=="")) {
  $SQL = "select max(to_number(mini_glosa,'9999')) ano_vigente  from periodo_eval"; 
  $anos_vigentes = consulta_sql($SQL);
  extract($anos_vigentes[0]);
  $ano_vigente++;
}

if ($ano_vigente == "") {
  $ano_vigente = $ANO;
}

if ($modo == "EDIT") {
  $nombreBoton = "Actualizar";
}
if ($modo == "NUEVO") {
  $nombreBoton = "Crear";
}

$bdcon = pg_connect("dbname=regacad" . $authbd);



if ($accion == "Actualizar") {
//verificacmos que el nombre clasificacino no exista!!..
//  $SQL = "select count(*) as cuenta from periodo_eval where upper(glosa_periodo_eval) = upper('$glosa_periodo')";
//  $fPending = consulta_sql($SQL);	
//  $cuenta = $fPending[0]['cuenta'];

//  if ($cuenta == 0) { 
    $puedeSeguir = true;
    if ($id_activar <> "") {
      //$puedeSeguir = false;
      $SQL = "update periodo_eval
      set activo = 'f'";
      //echo("<br>$SQL<br>");
      consulta_dml($SQL);
      //if (consulta_dml($SQL) == 1) {
              $SQL = "update periodo_eval
              set activo = 't'
              where id = $id_tabla";
            
              //echo("<br>$SQL<br>");
              consulta_dml($SQL);
//              if (consulta_dml($SQL) == 1) {
//                $puedeSeguir = true;

//              } else {
//                echo(msje_js("Error al actualizar."));
//              } 
          
      //} else {
      //  echo(msje_js("Error al actualizar*."));
      //  $puedeSeguir=false;
      //} 
     
    }
    if ($puedeSeguir==true) {
      $SQL = "update periodo_eval
      set glosa_periodo_eval = upper('$glosa_periodo')
      where id = $id_tabla";
    
      //echo("<br>$SQL<br>");
      
      if (consulta_dml($SQL) == 1) {
        //echo(js("location='history.back();"));
        //echo(msje_js("Clasificación Actualizada."));
       // echo(js("location='$enlbase=crud_clasificaciones_proretencion';"));
       echo(msje_js("Clasificación Actualizada."));
       echo(js("location='$enlbase=evdem_periodo_evaluacion';"));
        
        
        
      } else {
        echo(msje_js("Error al actualizar."));
      } 
  
    }

    
//  } else {
//    echo(msje_js("Esta glosa ya existe."));
//  }



     
}


if ($accion == "Crear") {
  $SQL = "select count(*) as cuenta from periodo_eval where upper(glosa_periodo_eval) = upper('$glosa_periodo')";
  $fPending = consulta_sql($SQL);	
  $cuenta = $fPending[0]['cuenta'];

  if ($cuenta == 0) {
    $SQL = "    insert into 
    periodo_eval(
      id,
      glosa_periodo_eval, 
      periodo_desde, 
      periodo_hasta, 
      mini_glosa, 
      activo) 
    values ( 
      (select max(id)+1 from periodo_eval),
      upper('$glosa_periodo'),
      '$id_ano-01-01',
      '$id_ano-12-31',
      '$id_ano',
      'f' 
    );
    
    ";
  
    //echo("<br>$SQL<br>");
    
    if (consulta_dml($SQL) == 1) {
      echo(msje_js("Nuevo periodo creado."));
      echo(js("location='$enlbase=evdem_periodo_evaluacion';"));
      
      
    } else {
      echo(msje_js("Error al insertar."));
    } 
    
  } else {
    echo(msje_js("Esta glosa ya existe."));
  }

};
/*
if ($id_activar <> "") {
  $SQL = "update periodo_eval
  set activo = 'f'";

  if (consulta_dml($SQL) == 1) {
          $SQL = "update periodo_eval
          set activo = 't'
          where id = $id_tabla";
        
          echo("<br>$SQL<br>");
          
          if (consulta_dml($SQL) == 1) {
          echo(msje_js("Periodo Activado."));
          //echo(js("location='$enlbase=evdem_periodo_evaluacion';"));
            
            
            
          } else {
            echo(msje_js("Error al actualizar."));
          } 
      
  } else {
    echo(msje_js("Error al actualizar."));
  } 




 

};
*/


if ($id_activar<>"") {
  $chk_selected = "checked";
} else {
  $chk_selected = "";
}




//$SQL_escuelas = "SELECT id,nombre FROM escuelas;";
//$escuelas = consulta_sql($SQL_escuelas);





/*


$SQL_resueltos = "select id, clasificacion from tipo_motivo_clasif_proretencion";
$resueltos = consulta_sql($SQL_resueltos);
$HTML_resueltos = "";
for ($x=0;$x<count($resueltos);$x++) {
	extract($resueltos[$x]);
//echo("<br>que ssss : $id");
  //$compromiso_color = (strtotime($fecha_comp) > time()) ? "green" : "red";

  if ($resuelto=='t') {
        $resueltoFinal = "<span style='color: green'>Sí</span>";
  } else {
    $resueltoFinal = "<span style='color: red'>No</span>";
  }
  if ($respuesta_contacto=='t') {
      $respuesta_contactoFinal = "<span style='color: green'>Contactado</span>";
      
  } else {
    $respuesta_contactoFinal = "<span style='color: red'>Sin Respuesta</span>";
  }
  
	$HTML_resueltos .= "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$id</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: left;'><small>$clasificacion</small></td>\n"

     . "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
     href='$enlbase=registro_atenciones_agregar_new_derivado
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada	
&modo_ver=SI
&comentarios_derivado=$comentarios_derivado
' class='boton'>Editar</a></td>\n"
. "</tr>\n";
}

if (count($resueltos) == 0) {
	$HTML_resueltos = "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;' colspan='10'>\n"
		  . "    ** No hay clasificaciones registradas **"
		  . "  </td>\n"
		  . "</tr>\n";
}
*/
?>



<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre_usuario','nombre','apellido','grado_academico','tipo');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type='hidden' name='id_tabla' value='<?php echo($id_tabla); ?>'>
<!--<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>-->
<input type='hidden' name='glosa_periodo' value='<?php echo($glosa_periodo); ?>'>
<input type='hidden' name='modo' value='<?php echo($modo); ?>'>
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla" style="margin-top: 5px">
  <tr>
	<td class='celdaFiltro'>
	  Acciones:<br>
    <input type="submit" name="accion" value="<?php echo($nombreBoton);  ?>">
    <input type="button" name="volver" value="Volver" onClick="history.back();">
    </td>
  </tr>
</table>
<!--
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="accion" value="<?php echo($nombreBoton);  ?>"></td>
<td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar***malo" onclick="window.location='principal.php?modulo=crud_clasificaciones_proretencion';"></td> 
    <td class="tituloTabla"><input type="button" name="volver" value="Volver" onClick="history.back();"></td>
  </tr>
</table>
-->
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
<tr>
    <td class="celdaNombreAttr"></td>
    <td class="celdaValorAttr">
      <?php if ($modo=="EDIT") {?>
            <input type='checkbox' id='id_activar' name='id_activar'  <?php echo($chk_selected); ?>> Periodo Activo
    <?php } ?>
    </td>
  </tr>

  <tr>
    <td class="celdaNombreAttr">Periodo:</td>
    <td class="celdaFiltro">
            <select name='id_ano' id='id_ano' onChange="submitform();">
              <?php 
                      $ss = "";
                      for ($x=$ano_vigente;$x<=($ano_vigente);$x++) {
                        if ($x == $ano_vigente+1) {
                          $ss = "selected";
                        } else {
                          $ss = "";
                        }
                        echo("<option value=$x $ss>$x</option>");
                      }
              ?>
            </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Glosa Periodo:</td>
    <td class="celdaValorAttr">
      <input type="text" name="glosa_periodo" value="<?php echo($_REQUEST['glosa_periodo']); ?>" size="40">
    </td>
  </tr>

  
  <!--
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
-->
</table>
<!--
<div class="texto">
  Listafo clasificaciones :
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style="color: gray">ID</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Acción</td>
  </tr>
  <?php echo($HTML_resueltos); ?>
</table>
  -->

</form>
<!-- Fin: <?php echo($modulo); ?> -->


<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

//$ids_carreras = $_SESSION['ids_carreras'];


$bdcon = pg_connect("dbname=regacad" . $authbd);
//$ano_aux          = $_REQUEST['ano_aux'];
$ano          = $_REQUEST['ano'];
$id_cohorte     = $_REQUEST['id_cohorte'];
$p_ano          = $_REQUEST['p_ano'];
$p_cohorte     = $_REQUEST['p_cohorte'];


$crear = $_REQUEST['crear'];
$grabar = $_REQUEST['grabar'];

$id_id = $_REQUEST['id_id'];
$id_moodlenombre = $_REQUEST['id_moodlenombre'];
$id_moodleurl = $_REQUEST['id_moodleurl'];
$id_moodletoken = $_REQUEST['id_moodletoken'];
$id_moodle_servicio = $_REQUEST['id_moodle_servicio'];
$accion = $_REQUEST['accion'];

//if ($ano == "") {
//  $ano = $ANO;
//}
//echo("<br>ano=".$ano);
//echo("<br>cohorte=".$id_cohorte);

/*
echo("<br>accion = ".$accion);
echo("<br>id_moodle_servicio = ".$id_moodle_servicio);
echo("<br>grabar = ".$grabar);
echo("<br>id_id = ".$id_id);
*/
/*
$SQL = "
select 
id, 
nombre, 
url_servicio, 
token 
from moodle_servicios
order by nombre;
";
*/

if ($accion=="EDITAR") {
  $ano  = $p_ano;
  $id_cohorte = $p_cohorte;
}

$condicion = "";
//POR MIENSTRAS QUEDA COMENTADO PARA NO TENER FILTROS
/*
if (($ano <> "") || ($id_cohorte <> "")){
  $condicion = " where ";
  $colocaAnd = false;
  if ($ano <> "") {
    $condicion .= " ano = $ano";    
    $colocaAnd = true;
  }

  if ($id_cohorte <> "") {
    if ($colocaAnd) {
      $condicion .= " and ";
    }
    $condicion .= " cohorte = $id_cohorte";    
  }
}
*/


//echo("<br>condicion = ".$condicion);

$SQL = "
select 
m.id as id, 
m.ano as ano_db,
m.cohorte as cohorte_db,
m.nombre as nombre, 
m.url_servicio as url_servicio, 
m.token as token,
(
		select 
		 count(*)
		from cursos c
		where c.id_moddle_servicio = m.id
) puede_eliminar	
from moodle_servicios m
$condicion
order by m.ano desc, m.cohorte desc, m.nombre;
";
//echo($SQL);
//echo("<br>filtro : ano = ".$ano);
//echo("<br>filtro : id_cohorte = ".$id_cohorte);

$fLista = consulta_sql($SQL);

if ($accion=="ELIMINAR") {
    $SQL_borrar = "delete from moodle_servicios where id = $id_moodle_servicio;";
    if (consulta_dml($SQL_borrar) == 1) {
      echo(msje_js("Registro eliminado correctamente."));
      echo(js("location='$enlbase=crear_moodle_servicio';"));
      
    }        

}
if ($accion=="EDITAR") {
  $verificaOnchange = "";
  //$ano_aux = $ano;

  $SQL = "select id, nombre, url_servicio, token, ano, cohorte from moodle_servicios where id = $id_moodle_servicio;";
  $ff = consulta_sql($SQL);	
  $id_id = $ff[0]['id'];  
  $id_moodlenombre = $ff[0]['nombre'];
  $id_moodleurl = $ff[0]['url_servicio'];
  $id_moodletoken = $ff[0]['token'];
  $ano = $ff[0]['ano'];
  $id_cohorte = $ff[0]['cohorte'];
  
} else {
  $verificaOnchange = ""; //"onChange='submitform();'";
}
if ($grabar<>"") { //UPDATE
  $SQL_actualizar = "update moodle_servicios
                  set 
                  nombre = upper('$id_moodlenombre'),
                  url_servicio = '$id_moodleurl',
                  token = '$id_moodletoken',
                  ano = $ano,
                  cohorte = $id_cohorte
                  where id = $id_id;
                ";  
                echo($SQL_actualizar);
                
  if (consulta_dml($SQL_actualizar) == 1) {    
    echo(msje_js("Servicio Moodle actualizado correctamente."));
  } else {
    echo(msje_js("Hubo un error al actualizar.".$SQL_actualizar));
  }
  echo(js("location='$enlbase=crear_moodle_servicio';")); 

}
if ($crear<>"") {
  $SQL = "
  select count(*) as cuenta from moodle_servicios where upper(nombre) = upper('$id_moodlenombre');
  ";
  $ff = consulta_sql($SQL);	
  $cuenta = $ff[0]['cuenta'];
  if ($cuenta > 0) {
    echo(msje_js("ERROR : El nombre de este servicio moodle ya existe!."));
    //echo(js("location='$enlbase=crear_curso_masivo';"));
  } else {
          $SQL = "
          select count(*) as cuenta from moodle_servicios where upper(url_servicio) = upper('$id_moodleurl');
          ";
          $ff = consulta_sql($SQL);	
          $cuenta = $ff[0]['cuenta'];
          if ($cuenta > 0) {
            echo(msje_js("ERROR : La URL de este servicio moodle ya existe!."));
            //echo(js("location='$enlbase=crear_curso_masivo';"));
          } else {
                  $SQL = "
                  select count(*) as cuenta from moodle_servicios where upper(token) = upper('$id_moodletoken');
                  ";
                  $ff = consulta_sql($SQL);	
                  $cuenta = $ff[0]['cuenta'];
                  if ($cuenta > 0) {
                          echo(msje_js("ERROR : el TOKEN de este servicio moodle ya existe!."));
                  } else {

                    $SQL = "
                    select count(*) as cuenta from moodle_servicios where ano = $ano and cohorte = $id_cohorte and upper(nombre) = upper($id_moodlenombre);
                    ";
                    $ff = consulta_sql($SQL);	
                    $cuenta = $ff[0]['cuenta'];
//echo(msje_js($SQL));
                    if ($cuenta > 0) {
                            echo(msje_js("ERROR : el Año y cohorte ya existen!."));
                    } else {
                            $SQL = "
                          insert into moodle_servicios(id, nombre, url_servicio,token,ano, cohorte) values (
                            default,
                            upper('$id_moodlenombre'),
                            '$id_moodleurl',
                            '$id_moodletoken',
                            $ano,
                            $id_cohorte);
                          ";
  
                          if (consulta_dml($SQL) > 0) {
                                  echo(msje_js("Servicio Moodle creado correctamente."));
                                  $id_moodlenombre = "";
                                  $id_moodleurl = "";
                                  $id_moodletoken = "";
                                  $crear = "";
                                  echo(js("location='$enlbase=crear_moodle_servicio';"));
                          } else {
                                  echo(msje_js("Error : sus cursos no fueron creados."));          
                          }                  

                    }


                  }              
          }
  
  }

}
?>
                <form name="formulario" action="principal.php" method="get">
                <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
                <div class="tituloModulo">
                    <?php echo($nombre_modulo); ?>
                </div><br>
                <table class="tabla">
                  <tr>
                    <?php if ($accion=="EDITAR") { ?>    
                        <td class="tituloTabla"><input type="submit" name="grabar" id="grabar" value="Grabar" onClick="return validarCampos();"></td>
                    <?php } else {?>
                        <td class="tituloTabla"><input type="submit" name="crear" id="crear" value="Crear" onClick="return validarCampos();"></td>
                    <?php } ?>
                    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="javascript:fnCancelar();"></td>
                  </tr>
                </table>
<br>
        
                <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
                <tr>
                    <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                    <?php 
                      if ($accion=="EDITAR") {
                        echo("Moodle : Modo edición.");
                      } else {
                        echo("Moodle : Ingrese Nuevo Servicio.");
                      }
                    ?>
                      
                    </td>
                  </tr>

                <tr>
                        <td class="celdaNombreAttr">A&ntilde;o:</td>
                

                        <td class="celdaValorAttr">

                          <select name="ano" id="id_ano"  <?php echo($verificaOnchange); ?>>
                                <option value="">-- Seleccione --</option>
                          <?php
                                  $ss = "";
                                  for ($x=2019;$x<=($ANO+2);$x++) {
                                    if ($x == $ano) {
                                      $ss = "selected";
                                    } else {
                                      $ss = "";
                                    }

                                    echo("<option value=$x $ss>$x</option>");
                                  }
                          ?>
                          </select>
                          <!--<input type="text" name="ano_aux" id="ano_aux" value="<?php //echo($ano_aux); ?>">-->
                        </td>
                        <td class="celdaNombreAttr">Cohorte:</td>
                        <td class="celdaValorAttr">
                          <select name="id_cohorte" id="id_cohorte" <?php echo($verificaOnchange); ?>>
                            <option value="">-- Seleccione --</option>
                            <?php
                            for ($x=1;$x<=12;$x++) {
                              if ($x==$id_cohorte) {
                                $ss = "selected";
                              } else {
                                $ss = "";
                              }
                              $mes_nombre = substr($meses_palabra[$x-1]['nombre'],0,3);
                              echo("<option value='$x' $ss>$mes_nombre</option>");
                            }
                            
                            ?>
                          </select>
                        </td>
                  </tr>





                    <tr>
                          <td class="celdaNombreAttr">Id:</td>
                                  <td class="celdaValorAttr" colspan=3>
                                  <input readonly type="text" size="10" style="border: none" maxlength="20" name="id_id" id="id_id" value="<?php echo($id_id); ?>">
                          </td>                  
                  </tr> 

                  <tr>
                          <td class="celdaNombreAttr">Nombre:</td>
                                  <td class="celdaValorAttr" colspan=3>
                                  <input type="text" size="70" style="border: none" maxlength="250" name="id_moodlenombre" id="id_moodlenombre" value="<?php echo($id_moodlenombre); ?>">
                          </td>                  
                  </tr> 
                  <tr>
                          <td class="celdaNombreAttr">URL Servicio:</td>
                                  <td class="celdaValorAttr" colspan=3>
                                  <input type="text" size="70" style="border: none" maxlength="250" name="id_moodleurl" id="id_moodleurl" value="<?php echo($id_moodleurl); ?>">
                          </td>                  
                  </tr> 
                  <tr>
                          <td class="celdaNombreAttr">Token:</td>
                                  <td class="celdaValorAttr" colspan=3>
                                  <input type="text" size="70" style="border: none" maxlength="250" name="id_moodletoken" id="id_moodletoken" value="<?php echo($id_moodletoken); ?>">
                          </td>                  
                  </tr> 

                </table>
<br>
                  <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
                  <tr>
                    <td class="celdaNombreAttr" colspan=8 style="text-align:center">
                      Servicios Moodle Creados
                    </td>
                  </tr>
                  <tr>
                          <td class="celdaNombreAttr" style="text-align:center; display:none;">Id</td>
                          <td class="celdaNombreAttr" style="text-align:center">Año</td>                  
                          <td class="celdaNombreAttr" style="text-align:center">Cohorte</td>                  
                          <td class="celdaNombreAttr" style="text-align:center">Nombre</td>                  
                          <td class="celdaNombreAttr" style="text-align:center">URL Servicio</td>                  
                          <td class="celdaNombreAttr" style="text-align:center">Token</td>                  
                          <td class="celdaNombreAttr" style="text-align:center">Editar</td>
                          <td class="celdaNombreAttr" style="text-align:center">Eliminar</td>
                  </tr>
                  <?php
                    for ($x=0;$x<count($fLista);$x++) {
                      $id = $fLista[$x]['id'];		

                      $ano_db = $fLista[$x]['ano_db'];		
                      $cohorte_db = $fLista[$x]['cohorte_db'];		
                      $nombreCohorte = substr($meses_palabra[$cohorte_db-1]['nombre'],0,3);
                      $nombre = $fLista[$x]['nombre'];		
                      $url_servicio = $fLista[$x]['url_servicio'];		
                      $token = $fLista[$x]['token'];	
                      $puede_eliminar  = $fLista[$x]['puede_eliminar'];	
                      echo("<tr>");
                      echo("<td class='textoTabla' style='text-align:center; display:none;'>$id</td>");
                      echo("<td class='textoTabla'>$ano_db</td>");                  
                      echo("<td class='textoTabla'>$nombreCohorte</td>");                  
                      echo("<td class='textoTabla'>$nombre</td>");                  
                      echo("<td class='textoTabla'>$url_servicio</td>");
                      echo("<td class='textoTabla'>$token</td>"); 
                      echo("<td class='tituloTabla'>");
                      echo("  <a href='$enlbase=crear_moodle_servicio&id_moodle_servicio=$id&accion=EDITAR&p_ano=$ano&p_cohorte=$id_cohorte' class='boton' onClick='return grabarYcontinuar();'><span style='color: blue'><b> ✓ </b></span></a>");
                      
                      echo("</td>");
                      echo("<td class='tituloTabla'>");
                      if ($puede_eliminar == 0) {
                        echo("  <a href='$enlbase=crear_moodle_servicio&id_moodle_servicio=$id&accion=ELIMINAR' class='boton' onClick='return eliminarYcontinuar();'><span style='color: red'><b> ✗ </b></span></a>");
                      }                       
                      //$asistencia = "<span style='color: green'><b> ✓ </b>$tasa_presentes%</span><br><span style='color: red'><b> ✗ </b>$tasa_ausentes%</span>";
                      //$asistencia = "<span style='color: red'><b> ✗ </b></span>";
                      echo("</td>");

                      echo("</tr>");
                    }

                  ?>



                  


                </table>




                </form>
<script>
    function saltar_crear_curso_masivo() {
    var pSaltar = "/sgu/principal.php?modulo=crear_moodle_servicio";
          pSaltar = "http://" + window.location.hostname + ":" + window.location.port + pSaltar;
          window.location.href = pSaltar;

  }
  function fnCancelar() {
    saltar_crear_curso_masivo();
  }
  /*
  function enviarValoresBorrar(){
		var f = document.createElement('form');
		f.action='?modulo=crear_moodle_servicio';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("id_eliminar", "id_eliminar");
		f.appendChild(i);

		document.body.appendChild(f);
		f.submit();
}
*/
  function eliminarYcontinuar() {
          var bb = false;
          var r = confirm("Está seguro(a) de realizar esta acción?");
          if (r == true) {
            //enviarValoresBorrar();
            bb = true;
          } else {
            bb = false;
          }
          return bb;
  }



  function validarCampos() {
    //var myRegimen = $( "#id_regimen option:selected" ).text();
    var b = true;
    var myMoodleNombre = $("#id_moodlenombre").val();

    var myMoodleUrl = $("#id_moodleurl" ).val();
    var myMoodleToken = $("#id_moodletoken").val();
    var myAno = $("select#id_ano option:checked" ).val();
    var myCohorte = $("select#id_cohorte option:checked" ).val();
    if (b == true) {
      if (myAno == "") {
        b = false;
        console.log('verificacion 0.1/3');
        campoFaltante = "Año";
        $("#id_ano").focus();
      }
    }

    if (b == true) {
      if (myCohorte == "") {
        b = false;
        console.log('verificacion 0.2/3');
        campoFaltante = "Cohorte";
        $("#id_cohorte").focus();
      }
    }
    if (b == true) {
      if (myMoodleNombre == "") {
        b = false;
        console.log('verificacion 1/3');
        campoFaltante = "Nombre Moodle";
        $("#id_moodlenombre").focus();
      }
    }
    if (b == true) {
      if (myMoodleUrl == "") {
              b = false;
              console.log('verificacion 2/3');
              campoFaltante = "URL Servicio";
              $("#id_moodleurl").focus();
      }
    }
    if (b == true) {
      if (myMoodleToken == "") {
              b = false;
              console.log('verificacion 3/3');
              campoFaltante = "Token";
              $("#id_moodletoken").focus();
      }
    }
    if (b == false) {
      alert("Debe completar todos los campos, falta : " + campoFaltante);
    }
    console.log("HA PASADO VALIDACION b="+b);
    return b;
  }

  $( document ).ready(function() {
    //alert('Ready!');
    //checkTodosLosCursosPropuestos();
    
  });
</script>  


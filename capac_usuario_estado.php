<?php
  function sacaEstadoCapacitacion($id_capacitacion) {

    $ss = "
      select id_asiscapac_estado from asiscapac_capacitaciones
      where id = $id_capacitacion
    ";
    $sql     = consulta_sql($ss);

    //echo("<br>".$ss);


    extract($sql[0]);
    return $id_asiscapac_estado;
}
 
function existenRegistros($ano, 
                      //$id_asiscapac_origen, 
                      $id_capacitacion, 
                      $id_usuario) {
  
  try {
        $ss = "
          select count(*) as cuenta from asiscapac_capacitaciones_funcionarios
          where
          ano = $ano 
          and id_asiscapac_capacitaciones = $id_capacitacion
          and id_usuario = $id_usuario
        "; 


    
        $sqlCuenta     = consulta_sql($ss);

        //echo("<br>".$ss);


        extract($sqlCuenta[0]);
  } catch (Exception $e) {
    $cuenta = 0;
  }

      return $cuenta;
  
}
  
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
//$modulo_destino = "ver_alumno";
$id_asiscapac_capacitaciones = $_REQUEST['id_asiscapac_capacitaciones'];
$id_observacion = $_REQUEST['id_observacion'];
$campo_id_check = $_REQUEST['campo_id_check'];
$ano            = $_REQUEST['ano'];
//$id_capacitacion     = $_REQUEST['id_capacitacion'];
//$id_usuario = $_REQUEST['id_usuario'];
$id_usuario = $_SESSION['id_usuario'];



$estado_actividad = "";
$strActividad = "";
if ($id_capacitacion<>"") {
  $estado_actividad = sacaEstadoCapacitacion($id_capacitacion);
  if ($estado_actividad == 1) {
    $strActividad = "PROGRAMADA";
  }
  if ($estado_actividad == 2) {
    $strActividad = "EJECUTADA";
  }
  if ($estado_actividad == 3) {
    $strActividad = "CERRADA";
  }
  if ($estado_actividad == 4) {
    $strActividad = "SUSPENDIDA";
  }

}

//echo("plop campo_id_check = ".$campo_id_check."<br>");
if ($campo_id_check == "") {
  $campo_id_check = "2"; //PRESENTE
}
//echo("campo_id_check = ".$campo_id_check);

$modo = "NUEVO"; //$_REQUEST['modo'];
$grabar      = $_REQUEST['grabar'];
$id_usuario_parametro = $_REQUEST['id_usuario_parametro'];

$id_usuario = $_SESSION['id_usuario'];

if ($id_usuario_parametro <> "") {
  $id_usuario = $id_usuario_parametro;
}


/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */

if ($grabar == "grabar") {
  if ($modo=="NUEVO") {
    //se procede a almacenar registro.
    //verificaciones de los campos
    $puedeSeguir = true;
    if ($puedeSeguir) {
      if ($id_observacion == "") {
        //echo(msje_js("Falta Ingresar Observación"));
        //$puedeSeguir = false;
        $campo_observacion = "null";
      }  else {
        $campo_observacion = "'$id_observacion'";
      }
    }
    if ($puedeSeguir) {

      $cuenta = existenRegistros($ano, 
                                    //$id_asiscapac_origen, 
                                    $id_asiscapac_capacitaciones, 
                                    $id_usuario);
      echo("<br>cuenta = $cuenta");
      if ($cuenta==0) {

              //$fecha = date("Y-m-d");
              $SQL = "
                insert into asiscapac_capacitaciones_funcionarios
                (ano, 
                id_asiscapac_capacitaciones, 
                id_usuario, 
                id_asiscapac_actividades_funcionarios_check,
                observacion
                ) 
                values (
                  $ano, 
                  $id_asiscapac_capacitaciones,
                  $id_usuario,
                  $campo_id_check,
                 $campo_observacion
            )
              ;";
        echo("<br>$SQL");
              if (consulta_dml($SQL) > 0) {

                echo(msje_js("Registro almacenado con éxito"));
               
                echo(js("location='$enlbase=capac_convocar&ano=$ano&id_origen=1&id_campo_capacitaciones=$id_asiscapac_capacitaciones';"));
//     echo(js("location='$enlbase=asiscapac_actividades_nuevo';"));
              } else {
                      echo(msje_js("Error : al momento de grabar."));          
              }                  
      } else {
//        echo(msje_js("Registro existente."));   
          $SQL = "
          update asiscapac_capacitaciones_funcionarios
          set ano = $ano, 
          id_asiscapac_capacitaciones = $id_asiscapac_capacitaciones,
          id_usuario = $id_usuario,
          id_asiscapac_actividades_funcionarios_check = $campo_id_check,
          observacion = $campo_observacion
          where id = $id_asiscapac_capacitaciones
          ;"; 
        //  echo("<br>$SQL");
          consulta_dml($SQL);      
          echo(msje_js("Registro almacenado con éxito*"));
          echo(js("location='$enlbase=capac_convocar&ano=$ano&id_origen=1&id_campo_capacitaciones=$id_asiscapac_capacitaciones';"));      }


    }
  }
}



/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */







$sql_funcionarios_check = "select id id_check, glosa nombre_check from asiscapac_actividades_funcionarios_check where id not in (0,1) order by id";
$funcionarios_checks = consulta_sql($sql_funcionarios_check);


?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

    <input type="hidden" name="ano" id="ano" value="<?php echo($ano); ?>">
    <!--<input type="hidden" name="id_capacitacion" id="id_capacitacion" value="<?php echo($id_capacitacion); ?>"> -->
    <input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo($id_usuario); ?>">
    
    <input type="hidden" name="id_asiscapac_capacitaciones" id="id_asiscapac_capacitaciones" value="<?php echo($id_asiscapac_capacitaciones); ?>">
    <input type="hidden" name="campo_id_check" id="campo_id_check" value="<?php echo($campo_id_check); ?>">


<table cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="texto">
      Esta actividad se encuentra : <?php echo($strActividad); ?> <br>
    </td>
  </tr>
</table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          <?php 
            //echo("maximo = ".count($funcionarios_checks));
            echo("<br>");
            $max = count($funcionarios_checks);
            echo("<fieldset>");
            for ($x=0;$x<=($max-1);$x++) {
              extract($funcionarios_checks[$x]);
              if ($id_check == $campo_id_check) {
                $chk = "checked";
              } else {
                $chk = "";
              }
              echo("<input type='radio' id='id_radio_$id_check' name='funcionario_check' onclick=presionaCheck($id_check) $chk>$nombre_check <br>");
            }
            echo("</fieldset>");
          ?>
        </td>

      </tr>
    </table>
    <input type="hidden" name="modo" id="modo" value="<?php echo($modo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
    <tr>
        <td class="celdaFiltro">
          Observación :<br>
          <textarea id="id_observacion" name="id_observacion" rows="4" cols="50"><?php echo($id_observacion); ?></textarea>          
        </td>
      </tr>
      <td class="celdaFiltro">
          Acción:<br>
          <?php if ($strActividad == "CERRADA") { ?>
              <!--NADA-->
          
          <?php }  else { ?>
          <input type='submit' name='grabar' value='grabar' style='font-size: 9pt'>
          <?php } ?>
          
          <input type="button" name="cancelar" value="Cancelar"  style='font-size: 9pt' onClick="history.back();">
        </td>



    </table>
</div>

  </form>

<!-- Fin: <?php echo($modulo); ?> -->


<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: true,
		'titleShow'         : false,
		'titlePosition'     : 'inside',
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 1000,
		'height'			: 550,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: true,
		'titleShow'         : false,
		'titlePosition'     : 'inside',
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 600,
		'height'			: 550,
		'maxHeight'			: 550,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});

  /*
  $("#id_origen").change(function(){
  
    alert($(this).val());
  
      if ($(this).val()==1) {
          //capacitacion
          //$('#id_tipo').prop('disabled', true);
          //DESAHILITAR
          //$('#id_tipo').attr('disabled', 'disabled');
          $('#id_tipo').prop('disabled', true);
          alert("inhabilitado");
      } else {
        //$('#id_tipo').prop('disabled', false);
        //HABILITAR
        $('#id_tipo').removeAttr('disabled');
        alert("habiliatado");
      }

  });
*/

});
function presionaCheck(id_check) {
  /*
          var maxNiveles = $("#maxNiveles").val();
          var idCheckBox = "";
          var nivelSelected = "";
          var ss = "";

          for (let i = 1; i <= maxNiveles; i++) {
                  idCheckBox = "id_checkbox_"+i;
                  nivelSelected = document.getElementById(idCheckBox);
                  if (nivelSelected.checked == true){
                    ss = ss + i + ",";                  
                  }
          }
          ss = ss.substr(0,ss.length - 1); 
          $("#id_nivelesSeleccionados").val(ss);
*/
          
          //var dd = "#id_radio_"+id_check;
          var dd = id_check;
          $("#campo_id_check").val(dd);
          
  } 											  
</script>

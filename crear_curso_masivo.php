<?php
function sacaCommaFinal($s) {
  $ss = $s;
  $ult = "";
  $ult = substr($ss,strlen($ss)-1,1); 
  //echo("ultimo = ".$ult."<br>");
  //echo substr($ss,0,strlen($ss)-1);
  if ($ult == ",") {
    $ss = substr($ss,0,strlen($ss)-1);
  }
  return $ss;
}
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

//$ids_carreras = $_SESSION['ids_carreras'];


$bdcon = pg_connect("dbname=regacad" . $authbd);

$campo_id_check = $_REQUEST['campo_id_check'];

if ($campo_id_check == "") {
  $campo_id_check = 2;
}

$id_borrar_y_comenzar = $_REQUEST['id_borrar_y_comenzar'];
$id_profesores_seleccionados = $_REQUEST['id_profesores_seleccionados'];
$sql_actualizar_curso_tmp = $_REQUEST['sql_actualizar_curso_tmp'];
//$sql_eliminar_curso_tmp = $_REQUEST['sql_eliminar_curso_tmp'];
//$sql_crear_curso = $_REQUEST['sql_crear_curso'];
/*
echo("id_profesores_seleccionados = ".$id_profesores_seleccionados);
echo("<br>");
echo("sql = ".$sql_actualizar_curso_tmp);
echo("<br>");
echo("sql = ".$sql_eliminar_curso_tmp);
echo("<br>");
echo("sql = ".$sql_crear_curso);
*/


$id_regimen   = $_REQUEST['id_regimen'];
$id_carrera   = $_REQUEST['id_carrera'];
$id_malla     = $_REQUEST['id_malla'];
$id_malla_new     = $_REQUEST['id_malla_new'];

if ($id_regimen <> "") {
  //echo("HE ENTRADO");
//  echo(js("location='$enlbase=crear_curso_masivo';"));
  //echo("HE SALIDO");
}

/*SACAR MALLA-CARRERA!!*/
//echo("<br>id_malla_new = ".$id_malla_new);
if ($id_borrar_y_comenzar <> "") {
        $SQL_borrar = "delete from curso_tmp where id_usuario = {$_SESSION['id_usuario']}";
        if (consulta_dml($SQL_borrar) == 1) {
          echo(js("location='$enlbase=crear_curso_masivo';"));
        }        
}
if ($id_malla_new <> "") {
        $SQL = "
        select id_carrera as id_carrera from mallas where id = ".$id_malla_new;
        $fff = consulta_sql($SQL);	
        $id_carrera = $fff[0]['id_carrera'];
        $id_malla = $id_malla_new;
//        echo("<br>id_malla = ".$id_malla);
//        echo("<br>id_carrera = ".$id_carrera);
        
}
/*FIN MALLA-CARRERA!!*/



//$id_nivel     = $_REQUEST['id_nivel'];
//$id_checkbox   = $_REQUEST['id_checkbox'];
//$maxNiveles = $_REQUEST['maxNiveles'];


$id_prog_asig = $_REQUEST['id_prog_asig'];
//$id_profesor  = $_REQUEST['id_profesor'];
$ano          = $_REQUEST['ano'];
$semestre     = $_REQUEST['semestre'];
//$seccion      = $_REQUEST['seccion'];
//$dia1         = $_REQUEST['dia1'];
//$horario1     = $_REQUEST['horario1'];
//$dia2         = $_REQUEST['dia2'];
//$horario2     = $_REQUEST['horario2'];
//$dia3         = $_REQUEST['dia3'];
//$horario3     = $_REQUEST['horario3'];
$id_cohorte     = $_REQUEST['id_cohorte'];
$id_seccion     = $_REQUEST['id_seccion'];
$ejecutar = $_REQUEST['ejecutar'];
$crear    = $_REQUEST['crear'];
if ($crear <> "") {
  $ejecutar = "ejecuta!";
}
$checkReadSelect = "";
$checkReadText = "";

$id_nivelesSeleccionados = $_REQUEST['id_nivelesSeleccionados'];
/*  if ($ejecutar <> "") {

    echo("<div class='texto'>");
    echo("CURSO CREADO EXITOSAMENTE");
    echo("</div>");
    $ejecutar = "";
  }
*/

//if ($ejecutar != "") {
//  $crear = "";
//  $id_carrera = "";
//  $id_malla = "";
//} 
//echo("<br> registros = $existenPendientes");
if ($id_profesores_seleccionados != "") {
  //echo("<br>999");
  //echo("PROFESORES : ".$id_profesores_seleccionados);
  //AQUI DEBE EJECUTAR EL UPDATE!
  
  $SQL_actualizar = $sql_actualizar_curso_tmp;
  if (consulta_dml($SQL_actualizar) == 1) {              
    /*
        $SQL_crear_curso = "
              insert into curso_tmp2 (id_prog_asig, seccion, semestre, ano, id_profesor, cupo)
              (select id_prog_asig, seccion, semestre, ano, id_profesor, 70 as cupo
              from curso_tmp where id_usuario = {$_SESSION['id_usuario']} and id_profesor is not null)";
*/
              
              $SQL_crear_curso = "
              insert into cursos (id_prog_asig, seccion, semestre, ano, id_profesor, cupo)
              (select id_prog_asig, seccion, semestre, ano, id_profesor, 70 as cupo
              from curso_tmp where id_usuario = {$_SESSION['id_usuario']} and id_profesor is not null)
              ";              

        //echo($SQL_crear_curso);

        /*
        consulta_dml($SQL_crear_curso);

        $SQL_delete_tmp = "delete from curso_tmp where id_usuario = {$_SESSION['id_usuario']}";
        consulta_dml($SQL_delete_tmp);
        echo(msje_js("Curso generado correctamente."));
        */
        $SQL_delete_tmp = "delete from curso_tmp where id_usuario = {$_SESSION['id_usuario']}";
        if (consulta_dml($SQL_crear_curso) > 0) {
                
                
                        //                echo("CURSO CREADO CORRECTAMENTE");
                        echo(msje_js("Curso generado correctamente."));
                        
                //} else {
                //              
                //}
//                consulta_dml($SQL_delete_tmp);
//                echo(msje_js("Curso generado correctamente."));
        } else {
                echo(msje_js("Error : sus cursos no fueron creados."));          
        }
        if (consulta_dml($SQL_delete_tmp) == 0) {
          echo(msje_js("Hubo error interno, favor comunicar a depto. de informática."));
        }
        
  } else {
    //echo("CURSO TMP ERROR");
    echo(msje_js("(*) Error al crear curso"));
  }
  echo(js("location='$enlbase=crear_curso_masivo';"));  
} else {
        /*BUSCAMOS SI EXISTEN REGISTOR EN curso_tmp, pendientes */
        //echo("<br>111");
        $SQL = "
        SELECT 
        count(*) as cuenta
        FROM curso_tmp cur
        where id_usuario = {$_SESSION['id_usuario']}";
        //echo($SQL);


        
        $fPending = consulta_sql($SQL);	
        $existenPendientes = $fPending[0]['cuenta'];

        if ($existenPendientes > 0) {
          //echo("HAY REGISTROS PENDIENTES");
          //echo("<br>222");
          ?>
        
          <form name="formulario" action="principal.php" method="get">
                  <div class="tituloModulo">
                          <?php echo($nombre_modulo); ?>
                  </div><br>
        
                  <table class="tabla">
                          <tr>
                            <input type='hidden' id='id_profesores_seleccionados' name='id_profesores_seleccionados'>
                            <input type='hidden' id='sql_actualizar_curso_tmp' name='sql_actualizar_curso_tmp'>
                            <!--<input type='text' id='sql_eliminar_curso_tmp' name='sql_eliminar_curso_tmp'>-->
                            <!--<input type='text' id='sql_crear_curso_tmp' name='sql_crear_curso_tmp'>-->
                            
                            <!--
                              <td class="tituloTabla"><input type="submit" name="grabar" id="grabar" value="Grabar y finalizar" onClick="return grabarYcontinuar();"></td>
                            -->
                            <td class="tituloTabla"><input type="button" name="grabar" id="grabar" value="Grabar y finalizar" onClick="return grabarYcontinuar();"></td>
                            <td class="tituloTabla"><input type="button" name="comenzar" id="comenzar" value="Borrar y comenzar" onClick="return borrarYcomenzar();"></td>
                            <td class="tituloTabla"> 
                              <a href='<?php echo("$enlbase=crear_profesor&id_prog_curso=$id"); ?>' class='boton'>Agregar un Profesor(a)</a>
                            </td>
                            <!--<td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="javascript:fnCancelar();"></td> -->
                          </tr>
                  </table>
                  <?php
                  /*listado profesores para asignar */
                  $SQL = "
                          select 
                          usu.id id_usuario, 
                          usu.nombre nombre ,
                          usu.apellido apellido 
                          from usuarios usu
                          where 
                          usu.tipo = 3
                          and usu.activo = 't' 
                          order by usu.nombre, usu.apellido;";
                          $fListaProfesor = consulta_sql($SQL);
                          if (count($fListaProfesor) > 0) {
                            //          echo("<select name='lista_profesor' id='lista_profesor'>");
                          //          echo("<option value=''>-- Seleccione --</option>");
                                    $values = array();
                                    for ($x=0;$x<count($fListaProfesor);$x++) {
                                      $pId_usuario = $fListaProfesor[$x]['id_usuario'];		
                                      $pNombre = $fListaProfesor[$x]['nombre'];		
                                      $pApellido = $fListaProfesor[$x]['apellido'];		
                          //            echo("<option value='$pId_usuario'>$pNombre $pApellido</option>");
                                      $profesor = [
                                        'id_usuario' => $pId_usuario,
                                        'nombre_completo' => $pNombre." ".$pApellido,
                                      ];
        
                                      array_push($values, $profesor);
                                      
                                    }
                                    /*
                                    echo('<br>');
                                    foreach($values as $valores) {
                                    echo('<br>'.$valores['id_usuario'].' '.$valores['nombre_completo']);
                                    }
                                    */
                                    
                          //          echo("</select>");
                          }
        
                    /*        
                    $SQL = "
        
                    SELECT 
                    tmp.id id, 
                    tmp.id_prog_asig id_prog_asig, 
                    tmp.ano ano,
                    tmp.semestre semestre,
                    concat(pasig.cod_asignatura|| '-'|| tmp.seccion) casig, 
                    carr.nombre nombre_carrera,
                    asig.nombre nombre_asignatura, 
                    tmp.id_profesor id_profesor 
                    FROM curso_tmp tmp, prog_asig pasig, asignaturas asig, carreras carr
                    where 
                    tmp.id_usuario = {$_SESSION['id_usuario']}
                    and tmp.cerrado = 'f'
                    and pasig.id = tmp.id_prog_asig
                    and asig.codigo = pasig.cod_asignatura
                    and carr.id = asig.id_carrera
                    order by nombre_carrera, casig;                    
                    ";
                    */                    
        
                    $SQL = "
                    SELECT 
                    tmp.id id, 
                    tmp.id_prog_asig id_prog_asig, 
                    tmp.ano ano,
                    tmp.semestre semestre,
                    concat(pasig.cod_asignatura|| '-'|| tmp.seccion) casig, 
                    carr.nombre nombre_carrera,
                    carr.alias||'/'||m.ano malla,
                    asig.nombre nombre_asignatura, 
                    tmp.id_profesor id_profesor 
                    FROM curso_tmp tmp, prog_asig pasig, asignaturas asig, carreras carr
                    ,detalle_mallas dm, mallas m
                    where 
                    tmp.id_usuario = {$_SESSION['id_usuario']}        
                    and tmp.cerrado = 'f'
                    and tmp.id_malla = m.id
                    and pasig.id = tmp.id_prog_asig
                    and asig.codigo = pasig.cod_asignatura
                    and carr.id = asig.id_carrera
                    and dm.id_prog_asig = pasig.id
                    and m.id = dm.id_malla
                    order by nombre_carrera, casig;
                    ";
                  $fAsignaProfesor = consulta_sql($SQL);
                  if (count($fAsignaProfesor) > 0) {
                    echo("<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' id='id_tabla_profesores'>");
                    echo("<tr>");
                    echo("<td class='celdaNombreAttr' style='text-align:center'>id</td>");
                    echo("<td class='celdaNombreAttr' style='text-align:center'>Periodo</td>");
                    echo("<td class='celdaNombreAttr' style='text-align:center'>Malla</td>");
                    echo("<td class='celdaNombreAttr' style='text-align:center'>Asignatura</td>");
                    echo("<td class='celdaNombreAttr' style='text-align:center'>Incluir</td>");
                    echo("<td class='celdaNombreAttr' style='text-align:center'>Profesor</td>");
                    echo("</tr>");
                    $indice = 1;
                    $aux = "";
                    $colocaBlancos = false;
                    for ($x=0;$x<count($fAsignaProfesor);$x++) {
                      $pId = $fAsignaProfesor[$x]['id'];		
                      $pano = $fAsignaProfesor[$x]['ano'];		
                      $psemestre = $fAsignaProfesor[$x]['semestre'];		
                      $pcasig = $fAsignaProfesor[$x]['casig'];		
                      $pnombreCarrera = $fAsignaProfesor[$x]['nombre_carrera'];		
                      $pmalla = $fAsignaProfesor[$x]['malla'];		
                      $pnombre_asignatura = $fAsignaProfesor[$x]['nombre_asignatura'];		
                      if ($aux=="") {
                        $aux = $pnombre_asignatura;
                      } else {
                        if ($aux != $pnombre_asignatura) {
                          $aux = $pnombre_asignatura;
                          $colocaBlancos = true;
                          
                        }
                      }
                      if ($colocaBlancos) {
                              $sss = "<td class='textoTabla'>&nbsp;</td><td class='textoTabla'>&nbsp;</td><td class='textoTabla'>&nbsp;</td><td class='textoTabla'>&nbsp;</td><td class='textoTabla'>&nbsp;</td><td class='textoTabla'>&nbsp;</td>";
                              echo("<tr>$sss</tr>");
                              $colocaBlancos = false;
                      }
                      echo("<tr id='id_fila_$indice'>");
                      echo("<td class='textoTabla' id='id_curso_tmp_$indice' >$pId</td>");
                      echo("<td class='textoTabla' >$psemestre-$pano</td>");
                      echo("<td class='textoTabla' >$pmalla</td>");
                      echo("<td class='textoTabla' ><div>$pcasig </div><div>$pnombre_asignatura</div></td>");              
                  
                      //INCLUIR
                      echo("<td class='textoTabla' >"); 
                      //echo("<input type='checkbox' id='id_incluir_$indice' name='id_incluir_$indice' onclick=presionaCheckIncluir($indice)> &nbsp;&nbsp;");
                      echo("<input type='checkbox' id='id_incluir_$indice' name='id_incluir_$indice' onclick=armarQuerys()> &nbsp;&nbsp;");
                      echo("</td>");              
                      //FIN INCLUIR

                      echo("<td class='textoTabla'>");
                              $idSeleccionado = "lista_profesor_".$indice;
                              echo("<select name='lista_profesor' id='lista_profesor_$indice' onchange='javaScript:traspasaValoresListas($indice);'>");                      
                              echo("<option value=''>-- Seleccione --</option>");
                                      foreach($values as $valores) {
                                        echo("<option value='{$valores['id_usuario']}'>{$valores['nombre_completo']}</option>");
                                      }
                              echo("</select>");
                      echo("</td>");
                      echo("</tr>");
                      $indice++;
                    }
                    echo("</table>");
                  }
        
        
        ?>
          </form>  
        <?php   
        } else {
                //echo("<br>333");
              //if ($ejecutar == "") {
                if ($crear <> "") {
                  //echo("<br>444");
                  $checkReadSelect = "disabled='true'";
                  $checkReadText = "readonly";

                  /*
                  echo("</br>");
                  echo("01 PRESIONADOS : id_regimen = ".$id_regimen."</br>");
                  echo("02 PRESIONADOS : id_carrera = ".$id_carrera."</br>");
                  echo("03 PRESIONADOS : id_malla = ".$id_malla."</br>");
                  echo("06 PRESIONADOS : id_prog_asig = ".$id_prog_asig."</br>");
                  echo("07 PRESIONADOS : ano = ".$ano."</br>");
                  echo("08 PRESIONADOS : semestre = ".$semestre."</br>");
                  echo("09 PRESIONADOS : id_cohorte = ".$id_cohorte."</br>");
                  echo("10 PRESIONADOS : id_seccion = ".$id_seccion."</br>");
                  echo("11 PRESIONADOS : id_nivelesSeleccionados = ".$id_nivelesSeleccionados."</br>");
                  */
                  
                        $mySeccion = "";
                        $mySeccion2 = "";
                        for ($x=$id_cohorte;$x<=$id_cohorte;$x++) {
                                for ($y=1;$y<=$id_seccion;$y++) {
                                  //$sSeccion = str_pad($y,  2, "0", STR_PAD_LEFT);
                                  //echo("</br>".$x.$sSeccion);
                                  //$mySeccion .= $x.$sSeccion.",";
                                  $mySeccion .= $x.$y.",";
                                  $vcero = "";
                                  //if ($y < 10) {
                                    $vcero = "0";
                                  //}
                                  $mySeccion2 .= $x.$vcero.$y.",";
                                }
                        }
//echo("<br>HOLA");
                        $SQL = "select id_prog_asig as id_prog_asig from detalle_mallas where id_malla = $id_malla and nivel in ($id_nivelesSeleccionados) and ofertable;";
                        $prog_asig = consulta_sql($SQL);
                        $list_prog_asig = "";                
                        $ag = "";
                        if (count($prog_asig) > 0) {
                          for ($x=0;$x<count($prog_asig);$x++) {
                                  $list_prog_asig .= $prog_asig[$x]['id_prog_asig'].",";		
                                  //$list_prog_asig_niveles .= $prog_asig[$x]['id_prog_asig']."-".$prog_asig[$x]['nivel'].",";		                    
                          }
                          
                        }
                        $mySeccion = sacaCommaFinal($mySeccion);
                        $mySeccion2 = sacaCommaFinal($mySeccion2);

                        if ($campo_id_check=="1") {
                          //echo("<br>DECENAS : ".$mySeccion);
                        } 
                        if ($campo_id_check=="2") {
                          //echo("<br>CENTENAS : ".$mySeccion2);
                         $mySeccion = $mySeccion2;
                        }

//echo("<br>HOLA2");
                        $list_prog_asig = sacaCommaFinal($list_prog_asig);

if (strlen($list_prog_asig) <= 1) {
  echo(msje_js("Malla sin asignaturas registradas."));
  echo(js("location='$enlbase=crear_curso_masivo';"));
}
                        /*VERIFICA SI PUEDE O NO PROCEDER!*/
/*        
                        $SQL = "
                              SELECT 
                              count(*) as cuenta
                              FROM cursos cur
                              where cur.id_prog_asig in ($list_prog_asig)
                              and cur.seccion in ($mySeccion)
                              and cur.semestre = $semestre
                              and cur.ano = $ano";
*/

                              $SQL = "
                              SELECT 
                              count(*) as cuenta
                              FROM cursos cur, vista_detalle_malla v
                              where cur.id_prog_asig in ($list_prog_asig)
                              and cur.seccion in ($mySeccion)
                              and cur.semestre = $semestre
                              and cur.ano = $ano
                              and v.id_prog_asig = cur.id_prog_asig
                              and v.caracter <> 'Obligatoria'
                              ";                              
                          //    echo("<br>$SQL");
/*
                              $SQL2 = "
                              SELECT 
                              count(*) as cuenta
                              FROM cursos cur
                              where cur.id_prog_asig in ($list_prog_asig)
                              and cur.seccion in ($mySeccion2)
                              and cur.semestre = $semestre
                              and cur.ano = $ano";
                              echo("<br>$SQL2");
*/
//ELIMINAR ESTA LINEA//                              
//$SQL_LOADER_DELETE = "delete from loader_tmp;";
//consulta_dml($SQL_LOADER_DELETE);

//$SQL_LOADER = "insert into loader_tmp(text) values ('$SQL')";
//consulta_dml($SQL_LOADER);
//FIN

                        $cuenta = consulta_sql($SQL);	
                        $puedeCrear = $cuenta[0]['cuenta'];
//ELIMINAR ESTA LINEA//                        
//$SQL_LOADER = "insert into loader_tmp(text) values ('puedeCrear = $puedeCrear')";
//consulta_dml($SQL_LOADER);
//FIN                        
                        if ((int)$puedeCrear > 0) {

                          //echo("NO PUEDE CREAR CURSO");
                          //ELIMINAR ESTA LINEA//                        
                          //hhh
                          //$SQL_LOADER = "insert into loader_tmp(text) values ('NO PUEDE CREAR CURSO')";
                          //consulta_dml($SQL_LOADER);
                          //FIN                        
                          echo("<br>");  
                          $crear = "";
                          $id_regimen="";
                          $id_carrera="";
                          $id_malla="";
                          $ejecutar = "";

//7echo("$SQL");
//echo("<br>Ya existen cursos, no se pued continuar.");
//echo("<br>Listado de cursos Existentes.");
//eecho("<br><br>");



                          echo(msje_js("ERROR* : Ud.ha seleccionado unos parámetros que coinciden con cursos ya existentes. No se puede continuar."));
                          //echo(js("location='$enlbase=crear_curso_masivo';"));
  
                        } else {
                          //ELIMINAR ESTA LINEA//                        
                          //$SQL_LOADER = "insert into loader_tmp(text) values ('PERMITIDO')";
                          //consulta_dml($SQL_LOADER);
                          //FIN                        

                          //echo("PERMITIDO");
                            $porcionesProgAsig = explode(",", $list_prog_asig);
                            //$porcionesProgAsigNiveles = explode(",", $list_prog_asig_niveles);
                            foreach ($porcionesProgAsig as $my_prog_asig) {
                                  //foreach ($porcionesProgAsigNiveles as $my_prog_asig_niveles) { 
                                  //$porcionesProgAsigNiveles = explode(",", $my_prog_asig_niveles);
                                    $porcionesSeccion = explode(",", $mySeccion);
                                    foreach ($porcionesSeccion as $my_seccion) {
                                            $sql_insert .= "                  
                                            insert into curso_tmp(
                                            id_prog_asig, 
                                            seccion,
                                            semestre,
                                            ano,
                                            id_usuario,
                                            id_malla,
                                            id_cohorte
                                            ) values (
                                            $my_prog_asig,
                                            $my_seccion,
                                            $semestre,
                                            $ano	,
                                            {$_SESSION['id_usuario']},
                                            $id_malla,
                                            $id_cohorte
                                            );                  ";
                                            //echo("<br>$insert");
                                    }          
                              }
                              //pirigui
                              $_SESSION["sql_crea_curso_tmp"]=$sql_insert;
                        }
        
                }
        
  //              if ($ids_carreras <> "") {
  //                      $condicion_carreras = "WHERE id IN ($ids_carreras)";
  //              }
                if ($id_regimen <> "") {  
//                        $sql_carreras ="SELECT id,nombre FROM carreras $condicion_carreras
//                        where regimen = '".$id_regimen."' and activa = true ORDER BY nombre;";
                        $sql_carreras ="SELECT id,nombre FROM carreras
                        where regimen = '".$id_regimen."' and activa = true ORDER BY nombre;";

                        $sql_mallas_new_vigentes ="
                        select 
                        m.id id,
                        concat((select nombre from carreras cc where id = m.id_carrera) || '/' || ano) nombre
                        from mallas m left join carreras c on m.id=c.id_malla_actual 
                        where 
                        c.id_malla_actual is not null
                        and (select regimen from carreras cc where id = m.id_carrera) = '".$id_regimen."'
                        order by ano desc;
                        ";
                        $mallas_vigentes = consulta_sql($sql_mallas_new_vigentes);
                        $sql_mallas_new_no_vigentes ="
                        select 
                        m.id id,
                        concat((select nombre from carreras cc where id = m.id_carrera) || '/' || ano) nombre
                        from mallas m left join carreras c on m.id=c.id_malla_actual 
                        where 
                        c.id_malla_actual is null
                        and (select regimen from carreras cc where id = m.id_carrera) = '".$id_regimen."'
                        order by ano desc;
                        ";
                        $mallas_no_vigentes = consulta_sql($sql_mallas_new_no_vigentes);

                        //echo("<br>".$sql_carreras);
                        $carreras = consulta_sql($sql_carreras);


                }
                $regimenes = consulta_sql("SELECT id,nombre FROM regimenes;");
        
        
                if ($id_carrera <> "") {
                  //echo("<br>555");
                  $id_malla_actual = consulta_sql("select id_malla_actual from carreras where id=$id_carrera");
                  $id_malla = $id_malla_actual[0]['id_malla_actual'];  
        
        
                  //	$mallas = consulta_sql("SELECT id,ano AS nombre FROM mallas WHERE id_carrera='$id_carrera' order by ano desc;");	
                  $sql_mallas = "select m.id,
                                        ano||case when c.id_malla_actual IS NULL then ' No vigente' ELSE ' Vigente' end as nombre 
                                from mallas m 
                                left join carreras c on m.id=c.id_malla_actual 
                                where 
                                id_carrera =$id_carrera 
                                order by ano desc;";
        
                  // echo("<br>$sql_mallas");
        //echo("<br>".$sql_mallas);
                  $mallas = consulta_sql($sql_mallas);
                  
        
        
                  if ($id_malla <> "") {
                    //echo("<br>666");
                    $ano_malla = consulta_sql("SELECT ano from mallas where id = $id_malla;");	
                    $ano_malla = $ano_malla[0]['ano'];
        
        
        
        
                    $niveles = consulta_sql("select niveles from mallas where id_carrera = $id_carrera and ano = $ano_malla;");
                    $niveles = $niveles[0]['niveles'];
        
        
                    $SQL_prog_asigs = "SELECT id_prog_asig AS id,cod_asignatura||' '||asignatura AS nombre
                                      FROM vista_detalle_malla
                                      WHERE id_malla='$id_malla'
                                      ORDER BY cod_asignatura;";
                    $prog_asigs = consulta_sql($SQL_prog_asigs);
                    if ($id_prog_asig <> "") {
                      //echo(nose"<br>777");
                      $id_profesor = consulta_sql("SELECT id_profesor FROM vista_detalle_malla WHERE id_prog_asig='$id_prog_asig';");
                      $id_profesor = $id_profesor[0]['id_profesor'];
                      $profesores = consulta_sql("SELECT id,nombre FROM profesores ORDER BY nombre;");
                      $horarios = consulta_sql("SELECT id,id||'=> '||intervalo AS nombre FROM vista_horarios ORDER BY id;");
                    };
                  };
                };
        
                if ($semestre == "") { $semestre = $SEMESTRE; }
                if ($ano == "") { 
                    $ano = $ANO; 
                    $anoProx = $ano+1;
                }	
                //if ($seccion == "") { $seccion = 1; }	
        
        
                ?>
        
                <!-- Inicio: <?php echo($modulo); ?> -->
                <form name="formulario" action="principal.php" method="get">
                <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
                <div class="tituloModulo">
                    <?php echo($nombre_modulo); ?>
                </div><br>
        
                <?php 
                  if ($ejecutar <> "") { 
                    //echo("<br>888");
                    //echo("</br>");
        
                    //echo($_SESSION["sql_crea_cursos"]);
                    $SQL_insert = $_SESSION["sql_crea_curso_tmp"];  
                    if (consulta_dml($SQL_insert) == 1) {
                      //echo("<div class='texto'>CURSO GENERADO EXITOSAMENTE!.</div>");
                      echo(js("location='$enlbase=crear_curso_masivo';"));
        
                      //QUERY PARA LOS PROFESORES
                      /*
                      SELECT 
                      cur.id id, 
                      cur.id_prog_asig, 
                      concat(pasig.cod_asignatura|| '-'|| cur.seccion) casig, 
                      asig.nombre nombre_asignatura, cur.id_profesor id_profesor 
                      FROM cursos cur, prog_asig pasig, asignaturas asig 
                      where cur.id_prog_asig in (1213,1156,1169,1180,1189,764,1216,1199,1159,1211,1182,1181) 
                      and cur.seccion NOT in (101,102,103,104) 
                      and cur.semestre = 1 
                      and cur.ano = 2004
                      and pasig.id = cur.id_prog_asig
                      and asig.codigo = pasig.cod_asignatura
                      order by nombre_asignatura
                      */              
        
                    } else {
//                      echo("<div class='texto'>Error al grabar curso!.</div>");
//                      echo("sql = ".$SQL_insert." FIN SQL");
                      $huboError = 1;
                    }
        
        
                    
                    /*
                        if ($puedeCrear == 0) {
                          echo("<div class='texto'>CURSO GENERADO EXITOSAMENTE!.</div>");
                        } else {
                          echo("<div class='texto'>NO SE PUEDE CREAR CURSO YA QUE SE ENCUENTRAN GENERADOS!.</div>");
                        }
                        */
                  } 
                ?>              
        
        
                <table class="tabla">
                  <tr>
                    <?php if ($crear == "") { ?>    
                        <td class="tituloTabla"><input type="submit" name="crear" id="crear" value="Crear" onClick="return validarCampos();"></td>
                        <!--<td class="tituloTabla"><input type="submit" name="ejecutar" id="ejecutar" value="Crear" onClick="return validarCampos();"></td>-->
                    <?php } ?>
                    <!--<td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="history.back();"></td> -->
                    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="javascript:fnCancelar();"></td>
                  </tr>
                </table>
        
                <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
                  <tr>
                    <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                      Antecedentes de los cursos por crear
                    </td>
                  <tr>
                    
                    <td class="celdaNombreAttr">Regimen:</td>
                    <td class="celdaValorAttr" colspan=3>
                      
                      <select name="id_regimen" id="id_regimen" onChange="submitform(); " <?php echo($checkReadSelect); ?>>
                        <option value="">-- Seleccione --</option>
                        <?php echo(select($regimenes,$id_regimen)); ?>
                      </select>
                    </td>
                  
                  </tr> 
                  <?php if ($id_regimen <> "") { ?> 
                    <tr>
                            <td class="celdaNombreAttr">Carrera/Malla:</td>
                            <td class="celdaValorAttr" colspan=3>
                                    <select class="filtro" name="id_malla_new" id="id_malla_new" onChange="submitform();" <?php echo($checkReadSelect); ?>>
                                    <option value="">-- Seleccione --</option>
                                    <optgroup label='Vigentes'>
                                    <?php echo(select($mallas_vigentes,$id_malla_new)); ?>
                                    </optgroup>
                                    <optgroup label='No vigentes'>
                                    <?php echo(select($mallas_no_vigentes,$id_malla_new)); ?>
                                    </optgroup>
                                  </select>
                            </td>
                    </tr>

                  <?php };?>  
                  <?php //if ($id_regimen <> "") { ?> 
                    <!--
                          <tr>
                            <td class="celdaNombreAttr">Carrera:</td>
                            <td class="celdaValorAttr" colspan=3>
                              <select name="id_carrera" id="id_carrera" onChange="submitform();" <?php echo($checkReadSelect); ?>>
                                <option value="">-- Seleccione --</option>
                                <?php //echo(select($carreras,$id_carrera)); ?>
                              </select>
                            </td>
                          </tr>
                  -->
                  <?php //};?>
                <?php if ($id_carrera <> "") { ?>
                          <!--
                          <tr>
                            <td class="celdaNombreAttr">Malla:</td>
                            <td class="celdaValorAttr" colspan=3>
                              <select name="id_malla" id="id_malla" onChange="submitform();" <?php echo($checkReadSelect); ?>>
                                <option value="">-- Seleccione --</option>
                                <?php 
                                        //echo(select($mallas,$id_malla)); 
                                ?>
                              </select>
                            </td>
                          </tr>
                -->
                          <?php if ($id_malla <> "") { ?>
                            <tr>
                                            <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                                            Periodo en que se dictarán
                                            </td>
                                          <tr>
        
                                          <tr>
                                            <td class="celdaNombreAttr">Semestre:</td>
                                            <td class="celdaValorAttr">
                                              <select name="semestre" id="id_semestre" <?php echo($checkReadSelect); ?>>
                                                <?php echo(select($semestres,$semestre)); ?>
                                              </select>
                                            </td>
                                            <td class="celdaNombreAttr">A&ntilde;o:</td>
                                            <td class="celdaValorAttr">
                                              
                                              <select name="ano" id="id_ano" <?php echo($checkReadSelect); ?>>
                                              <?php
                                                      $ss = "";
                                                      for ($x=$ANO-5;$x<=($ANO+1);$x++) {
                                                        if ($x == $ano) {
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
                                  <td class="celdaNombreAttr">Nivel:</td>
                                  <td class="celdaValorAttr" colspan=3>
                                      <?php
                                      if ($crear == "") {
                                              echo("<input type='hidden' id='id_nivelesSeleccionados' name='id_nivelesSeleccionados'>");
                                              echo("<input type='hidden' id='maxNiveles' value=$niveles>");
                                              for ($x=1;$x<=$niveles;$x++) {
                                                echo("<input type='checkbox' id='id_checkbox_$x' name='id_checkbox_$x' onclick=presionaCheck($x)>$x &nbsp;&nbsp;");
                                              }  
                                      } else {
                                        echo($id_nivelesSeleccionados);
                                      }
                                      ?>
                                  </td>
                                </tr>
                                <tr>
                                  <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                                    Secciones
                                  </td>
                                <tr>
        
                                <tr>
                                  <td class="celdaNombreAttr">Cohorte:</td>
                                  <td class="celdaValorAttr">
                                    <select name="id_cohorte" id="id_cohorte" <?php echo($checkReadSelect); ?>>
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
                                  <td class="celdaNombreAttr">Cantidad Grupo:</td>
                                  <td class="celdaValorAttr">
                                    <select name="id_seccion" id="id_seccion" <?php echo($checkReadSelect); ?>>
                                      <option value="">-- Seleccione --</option>
                                      <?php
                                      for ($x=1;$x<=10;$x++) {
                                        if ($x==$id_seccion) {
                                          $ss = "selected";
                                        } else {
                                          $ss = "";
                                        }
                                        echo("<option value='$x' $ss>$x</option>");
                                      }
                                      ?>
                                    </select>
                                  </td>
        
                                </tr>
<!--                                
                                          <tr>
                                            <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                                            Periodo en que se dictarán
                                            </td>
                                          <tr>
        
                                          <tr>
                                            <td class="celdaNombreAttr">Semestre:</td>
                                            <td class="celdaValorAttr">
                                              <select name="semestre" id="id_semestre" <?php //echo($checkReadSelect); ?>>
                                                <?php //echo(select($semestres,$semestre)); ?>
                                              </select>
                                            </td>
                                            <td class="celdaNombreAttr">A&ntilde;o:</td>
                                            <td class="celdaValorAttr">
                                              <input type="text" size="4" maxlength="4" name="ano" id = "id_ano" value="<?php //echo($ano); ?>" <?php //echo($checkReadText); ?>>
                                            </td>
        
                                          </tr>
-->                                 

                                  <tr>

                                  <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                                    &nbsp
                                  </td>
                                    </tr>
                                    <tr>
                                    <input type="hidden" name="campo_id_check" id="campo_id_check" value="<?php echo($campo_id_check); ?>">
                                  <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                                    Opción de creación sección : &nbsp
                                    <input type='radio' id='id_radio_1' name='id_radio' onclick=presionaCheck2(1)>decena
                                    <input type='radio' id='id_radio_2' name='id_radio' onclick=presionaCheck2(2) checked>centena
                                  </td>
                                    </tr>
        

                          <?php };?>
                <?php };?>
                </table>
               <!--hhh-->
                <?php if ($crear <> "") { 
                        echo("<br>");                          
                        if ($puedeCrear > 0) {
                          echo(msje_js("ERROR : OBSOLETO Ud.ha seleccionado unos parámetros que coinciden con cursos ya existentes. No se puede continuar."));
                          echo(js("location='$enlbase=crear_curso_masivo';"));
                        } else {
                          echo("<input type='submit' name='ejecutar' value='Ejecutar Creaci&oacute;n'>");
                        }
                      } ?>
                  <!-- <input type="submit" name="ejecutar" value="Ejecutar Creaci&oacute;n"> -->
                </form>
              <?php //} ?>
        
        
        <?php } /*existenPendientes*/?>
  
<?php } /*id_profesores_seleccionados*/?>



<script>
  function saltar_crear_curso_masivo() {
    var pSaltar = "/sgu/principal.php?modulo=crear_curso_masivo";
          pSaltar = "http://" + window.location.hostname + ":" + window.location.port + pSaltar;
          window.location.href = pSaltar;

  }
  function fnCancelar() {
    saltar_crear_curso_masivo();
  }
  function cambiaColorFondoRow(indiceSeleccionado, check) {
    console.log("*** tr , indiceSeleccionado = "+indiceSeleccionado);
          $("tr").each(function() {
                  //var i = $(this).attr("id");
                  //console.log("*** tr i = "+i);
                  id_fila = "#id_fila_"+indiceSeleccionado;
                  console.log("*** td, id_fila = " +  id_fila);
                  if (check) {
                    $(id_fila).css("background-color", "white");
                  } else {
                    $(id_fila).css("background-color", "gray");
                    //$('#contribution_status_id').val("2");
                    $("#lista_profesor_"+indiceSeleccionado).val("");
                  }
                      
          });  
  }
  function presionaCheck(nivel) {
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
          
  }  
  function selectElement(id, valueToSelect) {    
    let element = document.getElementById(id);
    //console.log("is select buscar : " + id + ", cambiar valor por " + valueToSelect);


//    d = document.getElementById(valueToSelect).value;
//    element.value = d; //valueToSelect;

    try {
      d = document.getElementById(valueToSelect).value;
      element.value = d; //valueToSelect;
    } catch (error) {
      //console.error(error);
    }

  }
  function armarQuerys() {
    var profSeleccionados = "";
    var sql_actualizar_curso_tmp = "";
    //var sql_eliminar_curso_tmp = "";
    //var sql_crear_curso_tmp = "";

    $("#id_profesores_seleccionados").val(profSeleccionados);
    $("#sql_actualizar_curso_tmp").val(sql_actualizar_curso_tmp);
    //$("#sql_eliminar_curso_tmp").val(sql_eliminar_curso_tmp);
    //$("#sql_crear_curso_tmp").val(sql_crear_curso_tmp);
    maxFilas = sacaMaxFilas();
    for (let i = 1; i <= maxFilas; i++) {
            try {
                  idCheckBox = "id_incluir_"+i;
                  cursoSelected = document.getElementById(idCheckBox);
                  if (cursoSelected.checked == true){
                    cambiaColorFondoRow(i, true);
                          id_curso_tmp	 = "id_curso_tmp_"+i;
                          idSeleccionar = "lista_profesor_"+i;
                          //console.log(idSeleccionar);
                          listSeleccionado = "#"+idSeleccionar + " option:selected";
                          //console.log("he pasado");
                          ////////////////////////////////////////////////////////////
                          valor_id_curso_tmp = $("#"+id_curso_tmp).text();
                          //console.log("valor_id_curso_tmp = " + valor_id_curso_tmp);
                          ////////////////////////////////////////////////////////////
                          valorSeleccionado = $( listSeleccionado ).val();

                          if (valorSeleccionado != "") {
                                  if (valor_id_curso_tmp.length > 0) {
                                        //valorSeleccionado = $("#lista_profesor_1  option:selected").val();
                                        //console.log("listSeleccionado = " + listSeleccionado + ", valor = " + valorSeleccionado);
                                        sql_actualizar_curso_tmp = sql_actualizar_curso_tmp + "update curso_tmp set id_profesor = " + valorSeleccionado + " where id = " + valor_id_curso_tmp + "; ";
                                        //sql_eliminar_curso_tmp = sql_eliminar_curso_tmp + "delete from curso_tmp where id = " + valor_id_curso_tmp + "; ";
                                        //sql_crear_curso_tmp = sql_crear_curso_tmp + "insert into curso( id_prog_asig, seccion, semestre, ano, id_profesor) values (id_prog_asig";
                                        profSeleccionados = profSeleccionados + valor_id_curso_tmp + "-" + valorSeleccionado + ",";
                                        
                                  }
                          }
                          

                  } else {
                    console.log("debe cambiar color FONDO, inactivo");
                    cambiaColorFondoRow(i, false);
                  }
          } catch (error) {
              //SE HIZO PO>R LOS BLANCOS
              //console.error(error);
          }

    }
    
    var ss = profSeleccionados;
    if (ss.length > 1) {
      ss = ss.substr(0,ss.length - 1); 
      console.log(profSeleccionados);
      console.log(sql_actualizar_curso_tmp);
      $("#id_profesores_seleccionados").val(ss);
      $("#sql_actualizar_curso_tmp").val(sql_actualizar_curso_tmp);
      //$("#sql_eliminar_curso_tmp").val(sql_eliminar_curso_tmp);
      //$("#sql_crear_curso_tmp").val(sql_crear_curso_tmp);

    } else {
      $("#id_profesores_seleccionados").val("");
    }

  }
  function sacaMaxFilas() {
    var maxFilas = 0;
    $("#id_tabla_profesores tr").each(function (index) {
        if (!index) return;
        i = 1;
        $(this).find("td").each(function () {
//            if (i == 1) {
                //primera fila
//            }
            //var id = $(this).text().toLowerCase().trim();
            //console.log("id="+id);
            maxFilas++;
        });
    });
    console.log("*****regs totales : " + maxFilas);
    maxFilas = maxFilas / 6; //MAX-ROWS 
    console.log("*****maxFilas : " + maxFilas);
    return maxFilas;
  }

  function sacaMaxFilas_x_asignatura() {
    //csm
    var ss2 = "";
    var ss = "<td class='textoTabla'>&nbsp;</td><td class='textoTabla'>&nbsp;</td><td class='textoTabla'>&nbsp;</td><td class='textoTabla'>&nbsp;</td><td class='textoTabla'>&nbsp;</td><td class='textoTabla'>&nbsp;</td>";
    ss = ss.replaceAll("'", "\"");
    console.log("ss = "+ss);
    i = 0;
    $("#id_tabla_profesores tr").each(function (index) {
        if (!index) return;
        i++;
        //console.log("* * * * * * * "+$(this).html());
        ss2 = $(this).html();
        //ss2.replace("'", "");
        
        if (ss2.trim() == ss.trim()) {
          console.log("TOKEN");
          i--;
          return false; //break
        }
        //console.log("ss2 = "+ss2);
        //console.log($(this).html());
    });

    console.log("*****regs faltantes x asignatura  : " + i);
    return i;    
  }
  function traspasaValoresListas(filaSeleccionada)
   {
     //csm
    //maxFilas = sacaMaxFilas();
    maxFilas = sacaMaxFilas_x_asignatura();
    //console.log("* * * maxFilas x asignatura = " + maxFilas);
    idSeleccionado = "lista_profesor_"+filaSeleccionada;
    //console.log("* * * idSeleccionado = " + idSeleccionado);
    //console.log("* * * filaSeleccionada = " + filaSeleccionada);
//    if (filaSeleccionada < maxFilas) {


  //DEBE DESAPARECER
/*
            hasta = maxFilas+filaSeleccionada;
            console.log("* * * hasta  = " + hasta);
            for (let i = filaSeleccionada + 1; i <= (hasta-1); i++) {
                    idSeleccionar = "lista_profesor_"+i;
                    selectElement(idSeleccionar, idSeleccionado);
            }
  */
  //FIN DEBE DESAPARECER
  //NUEVA PARTE
            ii = filaSeleccionada;
            //xx = ii / maxFilas;
///////////////////////////////////////////////////////////////////////////////////
            xx = ii/maxFilas;
            //console.log("HHH="+ans.toFixed(2));
            /*
            if (xx.toFixed(2) == Math.trunc(3/4)) {
              console.log("ES ENTERO");
            } else {
              console.log("NO ES ENTERO");
            }
            */
            ggg = 1000;
            
            while (xx.toFixed(2) != Math.trunc(ii/maxFilas)) {
              
                  idSeleccionar = "lista_profesor_"+ii;
                  selectElement(idSeleccionar, idSeleccionado);
                  ii++;
                  xx = ii / maxFilas;
                  if (ii>=ggg) {
                    alert("algo salio mal");
                    return false;
                  }
                  
            }
            idSeleccionar = "lista_profesor_"+ii;
            selectElement(idSeleccionar, idSeleccionado);
            
///////////////////////////////////////////////////////////////////////////////////
/*
            while (xx <> int(xx)) {
                  idSeleccionar = "lista_profesor_"+ii;
                  selectElement(idSeleccionar, idSeleccionado);
                  ii++;
                  xx = ii / maxFilas;
            }
*/            
/*
            let ans = 3/4;
            //console.log("HHH="+ans.toFixed(2));
            if (ans.toFixed(2) == Math.trunc(3/4)) {
              console.log("ES ENTERO");
            } else {
              console.log("NO ES ENTERO");
            }
*/
  //FIN NUEVA PARTE
 //   }

    armarQuerys();
/*    
    console.log("************");
    for (let i = 1; i <= maxFilas; i++) {
            try {
                  idCheckBox = "id_incluir_"+i;                  
                  cursoSelected = document.getElementById(idCheckBox);                  
                  if (cursoSelected.checked == true){
                    console.log("idCheckBox = " + idCheckBox);
                    console.log("cursoSelected = " + cursoSelected);
                    console.log("debe incljuir");
                  }
            } catch (error) {
              //SE HIZO PO>R LOS BLANCOS
              //console.error(error);
            }

    }
*/

   }
   
  /*
  function copiaLista(listaDestino) {
    $x = 1;
    $("#lista_profesor option").each(function (i) {
                texto = $(this).text();
                codigo = $(this).val();
                $id_lista = listaDestino + "_" + x + codigo ;
                $("#"+idListaDestino).append($('<option>', {
                            text: texto,
                            value: codigo
                }));                
                x = x + 1;
            });
  }
  */
  function todosMarcados(maxFilas) {
    var bSalida = true;
    var huboAlgunoSeleccionado = false;
    for (let i = 1; i <= maxFilas; i++) {
          try {
                  idCheckBox = "id_incluir_"+i;
                  cursoSelected = document.getElementById(idCheckBox);
                  if (cursoSelected.checked == true){
                          huboAlgunoSeleccionado = true;
                          idSeleccionar = "lista_profesor_"+i;
                          listSeleccionado = "#"+idSeleccionar + " option:selected";
                          valorSeleccionado = $( listSeleccionado ).val();

                          if (valorSeleccionado == "") {
                            bSalida = false;
                            break;
                          }

                  }

          } catch (error) {
              //SE HIZO PO>R LOS BLANCOS
              //console.error(error);
          }            
    }
    if (bSalida) {
      if (huboAlgunoSeleccionado === false) {
        bSalida = false;
      }
    }
    return bSalida;

  }
  function almacenaVariable(nombreVariable, valor) {
	var i=document.createElement('input');
		i.type='hidden';
		i.name=nombreVariable;
		i.value=valor;
	return i;
}
function enviarValoresEvaluar(id_profesores_seleccionados, sql_actualizar_curso_tmp){
		var f = document.createElement('form');
		f.action='?modulo=crear_curso_masivo';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("id_profesores_seleccionados", id_profesores_seleccionados);
		f.appendChild(i);

		i = almacenaVariable("sql_actualizar_curso_tmp", sql_actualizar_curso_tmp);
		f.appendChild(i);



		document.body.appendChild(f);
		f.submit();
}
function enviarValoresBorrar(){
		var f = document.createElement('form');
		f.action='?modulo=crear_curso_masivo';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("id_borrar_y_comenzar", "id_borrar_y_comenzar");
		f.appendChild(i);

		document.body.appendChild(f);
		f.submit();
}

  function borrarYcomenzar() {
          var bb = false;
          var r = confirm("Está seguro(a) de realizar esta acción?");
          if (r == true) {
            enviarValoresBorrar();
            bb = true;
          } else {
            bb = false;
          }
          return bb;
  }
  function grabarYcontinuar() {
    maxFilas = sacaMaxFilas();
    //bSalida = false;
    if (todosMarcados(maxFilas)) {
      armarQuerys();
      //submitform();
      id_profesores_seleccionados = $("#id_profesores_seleccionados").val();
      sql_actualizar_curso_tmp = $("#sql_actualizar_curso_tmp").val();
      //sql_eliminar_curso_tmp = $("#sql_eliminar_curso_tmp").val();
      //sql_crear_curso = $("#sql_crear_curso").val();
      console.log("grabarYcontinuar -> id_profesores_seleccionados => " + id_profesores_seleccionados);
      //enviarValoresEvaluar(id_profesores_seleccionados, sql_actualizar_curso_tmp, sql_eliminar_curso_tmp, sql_crear_curso);
      //enviarValoresEvaluar(id_profesores_seleccionados, sql_actualizar_curso_tmp, sql_crear_curso);
      enviarValoresEvaluar(id_profesores_seleccionados, sql_actualizar_curso_tmp);
      //bSalida = true;
    } else {
      alert("Faltan profesores por asignar")
    }

    //return bSalida;
  }
  function validarCampos() {
    //var myRegimen = $( "#id_regimen option:selected" ).text();
    var b = true;
    var myRegimen = $("select#id_regimen option:checked" ).val();
    var myCarrera = $("select#id_carrera option:checked" ).val();
    var myMalla = $("select#id_malla option:checked" ).val();
    var myNivelesSeleccionados = $("#id_nivelesSeleccionados").val();

    var myCohorte = $("select#id_cohorte option:checked" ).val();
    var mySeccion = $("select#id_seccion option:checked" ).val();
    var mySemestre = $("select#id_semestre option:checked" ).val();
    var myAno = $("#id_ano").val();
    var campoFaltante = "";
    //if (myNivelesSeleccionados === undefined) {
    //  alert("chemp!!");
    //}
    console.log("valor de myRegimen=" + myRegimen);
    if (myRegimen == "") {
      b = false;
      console.log('verificacion 1/8');
    }
    if (b == true) {
      if (myCarrera == "") {
              b = false;
              console.log('verificacion 2/8');
              campoFaltante = "CARRERA";
      }
    }
    if (b == true) {
      if (myMalla == "") {
              b = false;
              console.log('verificacion 3/8');
              campoFaltante = "MALLA";
      }
    }
    if (b == true) {
      if (myNivelesSeleccionados.length == 0) {
              b = false;
              console.log('verificacion 4/8');
              campoFaltante = "NIVEL";
      }
    }
//
    if (b == true) {
      if (myCohorte == "") {
              b = false;
              console.log('verificacion 5/8');
              campoFaltante = "COHORTE";
      }
    }


    if (b == true) {
      if (mySeccion == "") {
              b = false;
              console.log('verificacion 6/8');
              campoFaltante = "GRUPO";
      }
    }
    if (b == true) {
      if (mySemestre == "") {
              b = false;
              console.log('verificacion 7/8');
              campoFaltante = "SEMESTRE";
      }
    }
    if (b == true) {
      if (myAno.length == 0) {
              b = false;
              console.log('verificacion 8/8');
              campoFaltante = "AÑO";
      }
    }
//

    if (b == false) {
      alert("Debe completar todos los campos, falta : " + campoFaltante);
    }
    console.log("HA PASADO VALIDACION");
    return b;
  }
  function checkTodosLosCursosPropuestos() {
    maxFilas = sacaMaxFilas();
    for (let i = 1; i <= maxFilas; i++) {
      $("#id_incluir_"+i).prop('checked', true);
    }
    
  }
  function presionaCheck2(id_check) {
          var dd = id_check;
          $("#campo_id_check").val(dd);
          
  } 					

  $( document ).ready(function() {
    //alert('Ready!');
    checkTodosLosCursosPropuestos();
    
  });
</script>  


<?php
function sacaCommaFinal($s) {
  $ss = $s;
  $ult = "";
  $ult = substr($ss,strlen($ss)-1,1); 
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


$bdcon = pg_connect("dbname=regacad" . $authbd);
$id_borrar_y_comenzar = $_REQUEST['id_borrar_y_comenzar'];
$id_profesores_seleccionados = $_REQUEST['id_profesores_seleccionados'];
$sql_actualizar_curso_tmp = $_REQUEST['sql_actualizar_curso_tmp'];

$id_regimen   = $_REQUEST['id_regimen'];
$id_carrera   = $_REQUEST['id_carrera'];
$id_malla     = $_REQUEST['id_malla'];
$id_malla_new     = $_REQUEST['id_malla_new'];


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
}
$id_prog_asig = $_REQUEST['id_prog_asig'];
$ano          = $_REQUEST['ano'];
$semestre     = $_REQUEST['semestre'];

//NEW RAMO
$ano2          = $_REQUEST['ano2'];
$semestre2     = $_REQUEST['semestre2'];
$listado_cursos     = $_REQUEST['listado_cursos'];
$id_crear_ramos = $_REQUEST['id_crear_ramos'];
$datos_traspaso = $_REQUEST['datos_traspaso'];
//echo('listado_cursos='.$listado_cursos);
//FIN NEW RAMO

$id_cohorte     = $_REQUEST['id_cohorte'];
$id_seccion     = $_REQUEST['id_seccion'];
$ejecutar = $_REQUEST['ejecutar'];
$crear    = $_REQUEST['crear'];


///////////////////////////////////////////////////////////////////////////////////////////////////
if ($datos_traspaso <>"") {

  $datos_traspaso = sacaCommaFinal($datos_traspaso);
  //echo("<br>".$datos_traspaso);

  $porcionCursos = explode("#", $datos_traspaso);
  //hhh
  foreach ($porcionCursos as $my_porcionCursos) {
        //echo("<br> my_porcionCursos = ".$my_porcionCursos);
        $porcionInterna = explode("*", $my_porcionCursos);        
        $j = 1;
        foreach ($porcionInterna as $my_porcionInterna) {
          if ($j == 1) {
            $id_curso_ = $my_porcionInterna;
          }  
          if ($j == 2) {
            $id_cantidadprerequisitos_ = $my_porcionInterna;
          }  
          if ($j == 3) {
            $id_cursosprocesar_ = $my_porcionInterna;
          }            

          $j++;
        }
        //zzz
        //echo("<br>----------id_curso_ = ".$id_curso_);
        //echo("<br>----------id_cantidadprerequisitos_ = ".$id_cantidadprerequisitos_);
        //echo("<br>----------id_cursosprocesar_ = ".$id_cursosprocesar_);
        //hacer insert por cada curso!!
        if ($id_curso_ != "") {
            if ($id_cursosprocesar_ <> "") {
                    $SQL_creaRamos = "
                    insert into cargas_academicas(id_curso,id_alumno, id_usuario)
                    (
                    SELECT $id_curso_ as id_curso, 
                          id_alumno, 
                          {$_SESSION['id_usuario']} as id_usuario
                    FROM   cargas_academicas
                    WHERE  id_estado = 1
                          AND id_curso IN ( $id_cursosprocesar_ )
                    GROUP  BY id_alumno
                    HAVING Count(id_alumno) = $id_cantidadprerequisitos_
                    );";
//echo("<br>".$SQL_creaRamos);
                    if (consulta_dml($SQL_creaRamos) > 0) {
            //          console.log("Ramos generados correctamente");
                    } else {
                      //console.log("ERROR Ramos generados correctamente ");
                    }                  
                    //usleep(500000)); //medio segundo  


                    //NUEVA PARTE
                    $SQL_inscripcionesCursos = "
                    insert into inscripciones_cursos(id_curso,id_alumno)
                    (
                    SELECT $id_curso_ as id_curso, 
                          id_alumno                          
                    FROM   cargas_academicas
                    WHERE  id_estado = 1
                          AND id_curso IN ( $id_cursosprocesar_ )
                    GROUP  BY id_alumno
                    HAVING Count(id_alumno) = $id_cantidadprerequisitos_
                    );";                    


            }
        }

  }          
  echo(msje_js("Ramos generados correctamente.")); 


}
if ($id_crear_ramos <>"") {
  echo(msje_js("Ramos generados correctamente."));
  echo(js("location='$enlbase=crear_ramos_masivo';"));
}

if ($crear <> "") {
  $ejecutar = "ejecuta!";
}
$checkReadSelect = "";
$checkReadText = "";

$id_nivelesSeleccionados = $_REQUEST['id_nivelesSeleccionados'];

if ($id_profesores_seleccionados != "") {
  
  $SQL_actualizar = $sql_actualizar_curso_tmp;
  if (consulta_dml($SQL_actualizar) == 1) {              
              $SQL_crear_curso = "
              insert into cursos (id_prog_asig, seccion, semestre, ano, id_profesor, cupo)
              (select id_prog_asig, seccion, semestre, ano, id_profesor, 70 as cupo
              from curso_tmp where id_usuario = {$_SESSION['id_usuario']} and id_profesor is not null)
              ";              

        $SQL_delete_tmp = "delete from curso_tmp where id_usuario = {$_SESSION['id_usuario']}";
        if (consulta_dml($SQL_crear_curso) > 0) {
                        echo(msje_js("Curso generado correctamente."));
                        
        } else {
                echo(msje_js("Error : sus cursos no fueron creados."));          
        }
        if (consulta_dml($SQL_delete_tmp) == 0) {
          echo(msje_js("Hubo error interno, favor comunicar a depto. de informática."));
        }
        
  } else {
    echo(msje_js("(*) Error al crear curso"));
  }
  echo(js("location='$enlbase=crear_curso_masivo';"));  
} else {
        $SQL = "
        SELECT 
        count(*) as cuenta
        FROM curso_tmp cur
        where id_usuario = {$_SESSION['id_usuario']}";
        $fPending = consulta_sql($SQL);	
        $existenPendientes = $fPending[0]['cuenta'];

        if ($existenPendientes > 0) {
          ?>
        
          <form name="formulario" action="principal.php" method="get">
                  <div class="tituloModulo">
                          <?php echo($nombre_modulo); ?>
                  </div><br>
        
                  <table class="tabla">
                          <tr>
                            <input type='hidden' id='id_profesores_seleccionados' name='id_profesores_seleccionados'>
                            <input type='hidden' id='sql_actualizar_curso_tmp' name='sql_actualizar_curso_tmp'>
                            <td class="tituloTabla"><input type="button" name="grabar" id="grabar" value="Grabar y finalizar" onClick="return grabarYcontinuar();"></td>
                            <td class="tituloTabla"><input type="button" name="comenzar" id="comenzar" value="Borrar y comenzar" onClick="return borrarYcomenzar();"></td>
                            <td class="tituloTabla"> 
                              <a href='<?php echo("$enlbase=crear_profesor&id_prog_curso=$id"); ?>' class='boton'>Agregar un Profesor(a)</a>
                            </td>
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
                                    $values = array();
                                    for ($x=0;$x<count($fListaProfesor);$x++) {
                                      $pId_usuario = $fListaProfesor[$x]['id_usuario'];		
                                      $pNombre = $fListaProfesor[$x]['nombre'];		
                                      $pApellido = $fListaProfesor[$x]['apellido'];		
                                      $profesor = [
                                        'id_usuario' => $pId_usuario,
                                        'nombre_completo' => $pNombre." ".$pApellido,
                                      ];
        
                                      array_push($values, $profesor);
                                      
                                    }
                          }
        
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
                  
                      echo("<td class='textoTabla' >"); 
                      echo("<input type='checkbox' id='id_incluir_$indice' name='id_incluir_$indice' onclick=armarQuerys()> &nbsp;&nbsp;");
                      echo("</td>");              

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
                if ($crear <> "") {
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
                        for ($x=$id_cohorte;$x<=$id_cohorte;$x++) {
                                for ($y=1;$y<=$id_seccion;$y++) {
                                  $sSeccion = str_pad($y,  2, "0", STR_PAD_LEFT);
                                  $mySeccion .= $x.$sSeccion.",";
                                }
                        }
                        $SQL = "select id_prog_asig as id_prog_asig from detalle_mallas where id_malla = $id_malla and nivel in ($id_nivelesSeleccionados) and ofertable;";
                        $prog_asig = consulta_sql($SQL);
                        $list_prog_asig = "";                
                        $ag = "";
                        if (count($prog_asig) > 0) {
                          for ($x=0;$x<count($prog_asig);$x++) {
                                  $list_prog_asig .= $prog_asig[$x]['id_prog_asig'].",";		
                          }
                          
                        }
                        $mySeccion = sacaCommaFinal($mySeccion);
                        $list_prog_asig = sacaCommaFinal($list_prog_asig);

                        if (strlen($list_prog_asig) <= 1) {
                          echo(msje_js("Malla sin asignaturas registradas."));
                          echo(js("location='$enlbase=crear_curso_masivo';"));
                        }
                        $SQL = "
                              SELECT 
                              count(*) as cuenta
                              FROM cursos cur
                              where cur.id_prog_asig in ($list_prog_asig)
                              and cur.seccion in ($mySeccion)
                              and cur.semestre = $semestre
                              and cur.ano = $ano";
                        $cuenta = consulta_sql($SQL);	
                        $puedeCrear = $cuenta[0]['cuenta'];
                        if ((int)$puedeCrear > 0) {
                          echo("<br>");  
                          $crear = "";
                          $id_regimen="";
                          $id_carrera="";
                          $id_malla="";
                          $ejecutar = "";
                          echo(msje_js("ERROR : Ud.ha seleccionado unos parámetros que coinciden con cursos ya existentes. No se puede continuar."));
                          echo(js("location='$enlbase=crear_curso_masivo';"));
  
                        } else {
                            $porcionesProgAsig = explode(",", $list_prog_asig);
                            foreach ($porcionesProgAsig as $my_prog_asig) {
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
                                    }          
                              }
                              $_SESSION["sql_crea_curso_tmp"]=$sql_insert;
                        }
        
                }
                if ($id_regimen <> "") {  
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

                        $carreras = consulta_sql($sql_carreras);


                }
                $regimenes = consulta_sql("SELECT id,nombre FROM regimenes;");
        
        
                if ($id_carrera <> "") {
                  $id_malla_actual = consulta_sql("select id_malla_actual from carreras where id=$id_carrera");
                  $id_malla = $id_malla_actual[0]['id_malla_actual'];  
        
        
                  $sql_mallas = "select m.id,
                                        ano||case when c.id_malla_actual IS NULL then ' No vigente' ELSE ' Vigente' end as nombre 
                                from mallas m 
                                left join carreras c on m.id=c.id_malla_actual 
                                where 
                                id_carrera =$id_carrera 
                                order by ano desc;";
        
                  $mallas = consulta_sql($sql_mallas);
                  
        
        
                  if ($id_malla <> "") {
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
        
                ?>
        
                <!-- Inicio: <?php echo($modulo); ?> -->
                <form name="formulario" action="principal.php" method="get">
                <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
                <div class="tituloModulo">
                    <?php echo($nombre_modulo); ?>
                </div><br>
        
                <?php 
                  if ($ejecutar <> "") { 
                    $SQL_insert = $_SESSION["sql_crea_curso_tmp"];  
                    if (consulta_dml($SQL_insert) == 1) {
                      echo(js("location='$enlbase=crear_curso_masivo';"));
        
                    } else {
                      $huboError = 1;
                    }
        
                  } 
                ?>              
        
        
                <table class="tabla">
                  <tr>
                    <?php if ($listado_cursos == "") { ?>    
                        <!--<td class="tituloTabla"><input type="submit" name="crear" id="crear" value="Crear" onClick="return validarCampos();"></td> -->
                        <td class="tituloTabla"><input type="submit" name="listado_cursos" id="listado_cursos" value="Listado Cursos" onClick="return validarCampos();"></td>
<!--<td class="tituloTabla"><input type="text" value="BORRAME" onClick="return validarCampos();"></td> -->                                               
                    <?php } else { ?>
                        <td class="tituloTabla"><input type="submit" name="id_crear_ramos" id="id_crear_ramos" value="Crear Ramos" onClick="return traspasarInformacion();"></td>                      


                    <?php
                    }
                    
                    ?>
                    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="javascript:fnCancelar();"></td>
                  </tr>
                </table>
<?php
//NEW CURSOS
if ($listado_cursos <> "") {
  $SQL = "
    SELECT 
    c.id as id,
    c.seccion as seccion,
    cod_asignatura||'-'||c.seccion AS cod_asignatura, 
    asignatura,
    c.semestre||'-'||c.ano AS periodo,
    vc.carrera as carrera,
    vc.id_prog_asig as id_prog_asig_origen
    FROM vista_gestion_cursos AS vc 
    LEFT JOIN cursos AS c USING (id) 
    LEFT JOIN carreras AS car ON car.id=vc.id_carrera     
    LEFT JOIN usuarios AS u ON u.id=vc.id_profesor 
    LEFT JOIN detalle_mallas AS dm on (dm.id_prog_asig = c.id_prog_asig and dm.id_malla = $id_malla)    
    WHERE id_fusion IS NULL 
    AND id_carrera=".$id_carrera. 
    " AND c.ano=".$ano.
    " AND c.semestre=".$semestre.
    " and dm.nivel > 1 ".
    "ORDER BY c.ano DESC, c.semestre DESC, cod_asignatura,c.seccion
    ;
        ";
//echo($SQL);        
    $ff = consulta_sql($SQL);
    if (count($ff) > 0) {
      $header = "";
      $header .= "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' id='id_tabla_cursos'>";
      $header .= "<tr>";
      $header .= "<td class='celdaNombreAttr' style='text-align:center'>id</td>";
      $header .= "<td class='celdaNombreAttr' style='text-align:center'>Ramos</td>";
      $header .= "<td class='celdaNombreAttr' style='text-align:center'>Asignatura</td>";
      $header .= "<td class='celdaNombreAttr' style='text-align:center'>Periodo</td>";
      $header .= "<td class='celdaNombreAttr' style='text-align:center'>Carrera</td>";
      $header .= "<td class='celdaNombreAttr' style='text-align:center'>Prerequisitos</td>";
      $header .= "<td class='celdaNombreAttr' style='text-align:center'>Alumnos por inscribir</td>";
      $header .= "<td class='celdaNombreAttr' style='text-align:center; display:none;'>cursosProcesar</td>";
      $header .= "<td class='celdaNombreAttr' style='text-align:center; display:none;'>cantidadPrerequisitos</td>";
      $header .= "</tr>";
      $indice = 0; //1;
      $aux = "";
      $colocaBlancos = false;
      $id_cursos = "";
      $totalAlumnos = 0;
      $variable = "";
      for ($x=0;$x<count($ff);$x++) {
          $pId = $ff[$x]['id'];		
/*
          $SQL_alumnos = "
          select count(*) as cuenta from cargas_academicas 
          where id_estado =  1
          and id_curso in ($pId)              
          ";
          $fAlumnos = consulta_sql($SQL_alumnos);	
          $cuentaAlumnos = $fAlumnos[0]['cuenta'];
*/
          //$cuentaAlumnos = $ff[$x]['cuenta_alumnos'];


          $id_prog_asig_origen = $ff[$x]['id_prog_asig_origen'];		
          $id_seccion_pre = $ff[$x]['seccion'];		
          $pCod_asignatura = $ff[$x]['cod_asignatura'];	






          $SQL_prerequisitos = "
          SELECT
          id_prog_asig
          ,cod_asignatura
          ,asignatura
          ,id_prog_asig_req
          ,cod_asignatura_req
          ,asignatura_req
          ,tipo
          ,id_dm
          ,id_dm_req 
                              FROM vista_requisitos_malla
                              WHERE id_dm IN (SELECT id FROM detalle_mallas WHERE id_malla=$id_malla)
          and tipo = 1
          and id_prog_asig = $id_prog_asig_origen
                    ";
//and cod_asignatura = concat('$pCod_asignatura','-','$id_seccion_pre')          


//echo("<br>SQL_prerequisitos=$SQL_prerequisitos");                          
          $cuentaAlumnos = "";

                    //echo("<br>".$SQL_prerequisitos);
          $fPrerequisitos = consulta_sql($SQL_prerequisitos);	
          $strPreRequisitos = "";
          if (count($fPrerequisitos) > 0) {
                $id_curso_procesar = "";
                for ($indPre=0;$indPre<count($fPrerequisitos);$indPre++) {

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                  $id_prog_asig_pre = $fPrerequisitos[$indPre]['id_prog_asig_req'];
                  $SQL_cursosProcesar = "
                              select id as id_curso_procesar
                              from cursos
                              where 
                              id_prog_asig = $id_prog_asig_pre
                              and semestre = $semestre2
                              and seccion = $id_seccion_pre
                              and ano = $ano2                  
                          ";
//echo("<br>SQL_cursosProcesar=$SQL_cursosProcesar");                          
                  $fcursosProcesar = consulta_sql($SQL_cursosProcesar);	
                  for ($z=0;$z<count($fcursosProcesar);$z++) {
                    $id_curso_procesar .= $fcursosProcesar[$z]['id_curso_procesar'].",";		
                  }
                  

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                  $strPreRequisitos = $strPreRequisitos."<div>".$fPrerequisitos[$indPre]['cod_asignatura_req']." - ".$fPrerequisitos[$indPre]['asignatura_req']."</div>";		                    

                }
                
                $id_curso_procesar = sacaCommaFinal($id_curso_procesar);
//echo("<br>id_curso_procesar=$id_curso_procesar");
                $SQL_cuentaAlumnos = "
                select count(id_alumno) as cuenta_alumnos from (
                  select id_alumno from cargas_academicas 
                  where id_estado =  1
                  and id_curso in (
                    $id_curso_procesar
                  )
                  group by id_alumno
                  having count(id_alumno) = ".count($fPrerequisitos).
                ") as a";         
                $fCuentaAlumnos = consulta_sql($SQL_cuentaAlumnos);	
                $cuentaAlumnos = $fCuentaAlumnos[0]['cuenta_alumnos'];		
//echo("<br>SQL_cuentaAlumnos=$SQL_cuentaAlumnos");                         
          }
          $cursosProcesar = $id_curso_procesar;
          $cantidadPrerequisitos = count($fPrerequisitos);
          //ACUMULADOR
          $id_cursos = $id_cursos.$pId.",";

          //$pCod_asignatura = $ff[$x]['cod_asignatura'];		
          $pAsignatura = $ff[$x]['asignatura'];		
          $pPeriodo = $ff[$x]['periodo'];		
          $pCarrera = $ff[$x]['carrera'];		
          $estado = "";
          if ($cuentaAlumnos==0) {
            $colorFondo="background-color:#FF0000"; //ROJO
          } else {
            //EXISTEN YA REGISTROS EN TABLA carga_academicas
            //chemp
            $SQL = "
            select count(*) as cuenta from cargas_academicas 
            where id_curso = $pId
            ";
            $fexiste = consulta_sql($SQL);	
            $existeRegs = $fexiste[0]['cuenta'];
            
            if ($existeRegs > 0) {
              $colorFondo="background-color:#00FFFF";
              $estado = "YA CREADOS";
            } else {
              //TODO NORMAL
              $colorFondo="background-color:#FFFFFF";
            }
            
            
          }
          $indice++;
          $variable .= "<tr id='id_fila_$indice'>";
          $variable .= "<td class='textoTabla' style='$colorFondo' id='id_curso_$indice'>$pId</td>";
          $variable .= "<td class='textoTabla' style='$colorFondo' id='id_curso_$indice'>$estado</td>";
          $variable .= "<td class='textoTabla' style='$colorFondo'><div>$pCod_asignatura </div><div>$pAsignatura</div></td>";
          $variable .= "<td class='textoTabla' style='$colorFondo'>$pPeriodo</td>";
          $variable .= "<td class='textoTabla' style='$colorFondo'>$pCarrera</td>";              


/*
              $SQL_alumnos = "
              select count(id_alumno) as cuenta from (
                select id_alumno from cargas_academicas 
                where id_estado =  1
                and id_curso in ($pId)
                group by id_alumno
                --having count(id_alumno) = 3
              ) as a
                ;
                ";
*/                
          $variable .= "<td class='textoTabla' style='$colorFondo;'>$strPreRequisitos</td>";      
          $variable .= "<td class='textoTabla' style='$colorFondo;'>$cuentaAlumnos</td>";     
          //$variable .= "<td class='textoTabla' style='$colorFondo;'>$cuentaAlumnos * $SQL_cuentaAlumnos</td>";     

          $variable .= "<td class='textoTabla' style='$colorFondo; display:none;' id='id_cursosprocesar_$indice'>$cursosProcesar</td>";      
          $variable .= "<td class='textoTabla' style='$colorFondo; display:none;' id='id_cantidadprerequisitos_$indice'>$cantidadPrerequisitos</td>";      
//$variable .= "<td class='textoTabla' style='$colorFondo; ' id='id_cursosprocesar_$indice'>$cursosProcesar</td>";      
//$variable .= "<td class='textoTabla' style='$colorFondo; ' id='id_cantidadprerequisitos_$indice'>$cantidadPrerequisitos</td>";      

              $totalAlumnos = $totalAlumnos + $cuentaAlumnos;        
              $variable .= "</tr>";
       
      }
      $variable3 = $header.$variable;
      echo($variable3);

      $id_cursos = sacaCommaFinal($id_cursos);

      echo("</table>");
    }
/*
echo("<br>");
echo($id_cursos);
echo("<br>");
echo("total cursos = ".$indice);
*/
echo("<br>");
/*
      $SQL = "
      select count(id_alumno) as cuenta from (
        select id_alumno from cargas_academicas 
        where id_estado =  1
        and id_curso in (
          SELECT 
          c.id
          FROM vista_gestion_cursos AS vc LEFT JOIN cursos AS c USING (id) 
          LEFT JOIN carreras AS car ON car.id=vc.id_carrera 
          LEFT JOIN usuarios AS u ON u.id=vc.id_profesor 
          WHERE id_fusion IS NULL 
          AND id_carrera=".$id_carrera. 
          " AND c.ano=".$ano.
          " AND c.semestre=".$semestre.
          " AND car.regimen = '$id_regimen'
          ORDER BY c.ano DESC, c.semestre DESC, cod_asignatura,c.seccion
        )
        group by id_alumno
        having count(id_alumno) = $indice
      ) as a
          ;
          ";
*/

/*
$SQL = "
select count(id_alumno) as cuenta from (
  select id_alumno from cargas_academicas 
  where id_estado =  1
  and id_curso in ($id_cursos)
  group by id_alumno
  having count(id_alumno) = $indice
) as a
    ;
    ";
*/
//          echo($SQL);
//        $fPending = consulta_sql($SQL);	
 //       $cuenta = $fPending[0]['cuenta'];
        echo("<br>");
        echo("<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>");
        echo("<tr>");
        echo("<td class='celdaNombreAttr' style='text-align:center'>Asignaturas</td>");
        echo("<td class='textoTabla' style='text-align:center'>$indice</td>");
        echo("</tr>");
        echo("<tr>");
        echo("<td class='celdaNombreAttr' style='text-align:center'>Cantidad Alumnos</td>");
        echo("<td class='textoTabla' style='text-align:center'>$totalAlumnos</td>");
        echo("</tr>");

        echo("</table>");

} else {


?>                


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
                <?php if ($id_carrera <> "") { ?>
                          <?php if ($id_malla <> "") { ?>
                            <tr>
                                            <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                                            Per&iacute;odo a procesar
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
                                                      for ($x=$ANO-2;$x<=($ANO);$x++) {
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
<!--NEW RAMO-->
                                          <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                                            Periodo para curso precedente
                                            </td>
                                          <tr>
        
                                          <tr>
                                            <td class="celdaNombreAttr">Semestre:</td>
                                            <td class="celdaValorAttr">
                                              <select name="semestre2" id="id_semestre2" <?php echo($checkReadSelect); ?>>
                                                <?php echo(select($semestres,$semestre2)); ?>
                                              </select>
                                            </td>
                                            <td class="celdaNombreAttr">A&ntilde;o:</td>
                                            <td class="celdaValorAttr">
                                              
                                              <select name="ano2" id="id_ano2" <?php echo($checkReadSelect); ?>>
                                              <?php
                                                      $ss = "";
                                                      for ($x=$ANO-2;$x<=($ANO);$x++) {
                                                        if ($x == $ano2) {
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

<!--FIN NEW RAMO-->
<!--
                                <tr>
                                  <td class="celdaNombreAttr">Nivel:</td>
                                  <td class="celdaValorAttr" colspan=3>
                                      <?php/*
                                      if ($crear == "") {
                                              echo("<input type='hidden' id='id_nivelesSeleccionados' name='id_nivelesSeleccionados'>");
                                              echo("<input type='hidden' id='maxNiveles' value=$niveles>");
                                              for ($x=1;$x<=$niveles;$x++) {
                                                echo("<input type='checkbox' id='id_checkbox_$x' name='id_checkbox_$x' onclick=presionaCheck($x)>$x &nbsp;&nbsp;");
                                              }  
                                      } else {
                                        echo($id_nivelesSeleccionados);
                                      }
                                      */
                                      ?>
                                  </td>
                                </tr>
                                <tr>
                                  <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                                    Secciones
                                  </td>
                                <tr>
-->        
<!--
                                <tr>
                                  <td class="celdaNombreAttr">Cohorte:</td>
                                  <td class="celdaValorAttr">
                                    <select name="id_cohorte" id="id_cohorte" <?php echo($checkReadSelect); ?>>
                                      <option value="">-- Seleccione --</option>
                                      <?php
                                      /*
                                      for ($x=1;$x<=12;$x++) {
                                        if ($x==$id_cohorte) {
                                          $ss = "selected";
                                        } else {
                                          $ss = "";
                                        } 
                                        $mes_nombre = substr($meses_palabra[$x-1]['nombre'],0,3);
                                        echo("<option value='$x' $ss>$mes_nombre</option>");
                                      }
                                      */
                                      ?>
                                    </select>
                                  </td>
                                  <td class="celdaNombreAttr">Cantidad Grupo:</td>
                                  <td class="celdaValorAttr">
                                    <select name="id_seccion" id="id_seccion" <?php echo($checkReadSelect); ?>>
                                      <option value="">-- Seleccione --</option>
                                      <?php
                                      /*
                                      for ($x=1;$x<=10;$x++) {
                                        if ($x==$id_seccion) {
                                          $ss = "selected";
                                        } else {
                                          $ss = "";
                                        }
                                        echo("<option value='$x' $ss>$x</option>");
                                      }
                                      */
                                      ?>
                                    </select>
                                  </td>        
                                </tr>
-->                                
                          <?php };?>
                <?php };?>
                </table>
                <?php if ($crear <> "") { 
                        echo("<br>");                          
                        if ($puedeCrear > 0) {
                          echo(msje_js("ERROR : OBSOLETO Ud.ha seleccionado unos parámetros que coinciden con cursos ya existentes. No se puede continuar."));
                          echo(js("location='$enlbase=crear_curso_masivo';"));
                        } else {
                          echo("<input type='submit' name='ejecutar' value='Ejecutar Creaci&oacute;n'>");
                        }
                      } ?>

<?php };?>  <!--listado_cursos-->

                </form>
              <?php //} ?>
        
        <?php } /*existenPendientes*/?>
  
<?php } /*id_profesores_seleccionados*/?>



<script>
  function saltar_crear_ramos_masivo() {
    var pSaltar = "/sgu/principal.php?modulo=crear_ramos_masivo";
          pSaltar = "http://" + window.location.hostname + ":" + window.location.port + pSaltar;
          window.location.href = pSaltar;

  }
  function fnCancelar() {
    saltar_crear_ramos_masivo();
  }
  function traspasarInformacion() {

    armarDatosTraspaso();
    return false;
  } 
  function armarDatosTraspaso() {
    //hhh
    console.log("estoy : armarDatosTraspaso")
    datosTraspaso = "";
    i = 1;

    $("#id_tabla_cursos tr").each(function (index) {
        if (!index) return;
        //console.log("ggg");
        
        datosCurso = "";
        
        //$(this).find("td").each(function () {
            id_curso_ = $("#id_curso_"+i).text();
            id_cantidadprerequisitos_ = $("#id_cantidadprerequisitos_"+i).text();
            id_cursosprocesar_ = $("#id_cursosprocesar_"+i).text();
            datosCurso = id_curso_ + "*" + id_cantidadprerequisitos_ + "*" + id_cursosprocesar_;
            datosTraspaso = datosTraspaso + datosCurso + "#";
            console.log(i+".-*** id_curso = " +  id_curso_);          
            console.log(i+".-*** id_cantidadprerequisitos_ = " +  id_cantidadprerequisitos_);          
            console.log(i+".-*** id_cursosprocesar_ = " +  id_cursosprocesar_);                    
            i++;
        //});
    });
    //console.log(datosTraspaso);
    enviarValoresEvaluar(datosTraspaso);

  }
  function cambiaColorFondoRow(indiceSeleccionado, check) {
    console.log("*** tr , indiceSeleccionado = "+indiceSeleccionado);
          $("tr").each(function() {
                  id_fila = "#id_fila_"+indiceSeleccionado;
                  console.log("*** td, id_fila = " +  id_fila);
                  if (check) {
                    $(id_fila).css("background-color", "white");
                  } else {
                    $(id_fila).css("background-color", "gray");
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

    try {
      d = document.getElementById(valueToSelect).value;
      element.value = d; //valueToSelect;
    } catch (error) {
    }

  }
  function armarQuerys() {
    var profSeleccionados = "";
    var sql_actualizar_curso_tmp = "";

    $("#id_profesores_seleccionados").val(profSeleccionados);
    $("#sql_actualizar_curso_tmp").val(sql_actualizar_curso_tmp);
    maxFilas = sacaMaxFilas();
    for (let i = 1; i <= maxFilas; i++) {
            try {
                  idCheckBox = "id_incluir_"+i;
                  cursoSelected = document.getElementById(idCheckBox);
                  if (cursoSelected.checked == true){
                    cambiaColorFondoRow(i, true);
                          id_curso_tmp	 = "id_curso_tmp_"+i;
                          idSeleccionar = "lista_profesor_"+i;
                          listSeleccionado = "#"+idSeleccionar + " option:selected";
                          valor_id_curso_tmp = $("#"+id_curso_tmp).text();
                          valorSeleccionado = $( listSeleccionado ).val();

                          if (valorSeleccionado != "") {
                                  if (valor_id_curso_tmp.length > 0) {
                                        sql_actualizar_curso_tmp = sql_actualizar_curso_tmp + "update curso_tmp set id_profesor = " + valorSeleccionado + " where id = " + valor_id_curso_tmp + "; ";
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
        ss2 = $(this).html();
        
        if (ss2.trim() == ss.trim()) {
          console.log("TOKEN");
          i--;
          return false; //break
        }
    });

    console.log("*****regs faltantes x asignatura  : " + i);
    return i;    
  }
  function traspasaValoresListas(filaSeleccionada)
   {
    maxFilas = sacaMaxFilas_x_asignatura();
    idSeleccionado = "lista_profesor_"+filaSeleccionada;

            ii = filaSeleccionada;
            xx = ii/maxFilas;
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
            
    armarQuerys();

   }
   
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
//hhh
function enviarValoresEvaluar(datosTraspaso){
		var f = document.createElement('form');
		f.action='?modulo=crear_ramos_masivo';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("datos_traspaso", datosTraspaso);
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
    if (todosMarcados(maxFilas)) {
      armarQuerys();
      id_profesores_seleccionados = $("#id_profesores_seleccionados").val();
      sql_actualizar_curso_tmp = $("#sql_actualizar_curso_tmp").val();
      console.log("grabarYcontinuar -> id_profesores_seleccionados => " + id_profesores_seleccionados);
      enviarValoresEvaluar(id_profesores_seleccionados, sql_actualizar_curso_tmp);
    } else {
      alert("Faltan profesores por asignar")
    }
  }
  function validarCampos() {
    var b = true;
    var myRegimen = $("select#id_regimen option:checked" ).val();
    var myCarrera = $("select#id_carrera option:checked" ).val();
    var myCarreraMalla = $("select#id_malla_new option:checked" ).val();
//    var myMalla = $("select#id_malla option:checked" ).val();
//    var myNivelesSeleccionados = $("#id_nivelesSeleccionados").val();

//    var myCohorte = $("select#id_cohorte option:checked" ).val();
//    var mySeccion = $("select#id_seccion option:checked" ).val();
    var mySemestre = $("select#id_semestre option:checked" ).val();
    var mySemestre2 = $("select#id_semestre2 option:checked" ).val();
    var myAno = $("#id_ano").val();
    var myAno2 = $("#id_ano2").val();
    var campoFaltante = "";
    console.log("valor de mySemestre=" + mySemestre);
    console.log("valor de mySemestre2=" + mySemestre2);
    console.log("valor de myAno=" + myAno);
    console.log("valor de myAno2=" + myAno2);
    
    if (myRegimen == "") {
      b = false;
      console.log('verificacion 1/8');
    }
    if (b == true) {
      if (myCarreraMalla == "") {
              b = false;
              console.log('verificacion 2/8');
              campoFaltante = "CARRERA/MALLA";
      }
    }
    /*
    if (b == true) {
      if (myMalla == "") {
              b = false;
              console.log('verificacion 3/8');
              campoFaltante = "MALLA";
      }
    }
    */
    /*
    if (b == true) {
      if (myNivelesSeleccionados.length == 0) {
              b = false;
              console.log('verificacion 4/8');
              campoFaltante = "NIVEL";
      }
    }
    if (b == true) {
      if (myCohorte == "") {
              b = false;
              console.log('verificacion 5/8');
              campoFaltante = "COHORTE";
      }
    }
*/
/*
    if (b == true) {
      if (mySeccion == "") {
              b = false;
              console.log('verificacion 6/8');
              campoFaltante = "GRUPO";
      }
    }
    */
    /*
    if (b == true) {
      if (mySemestre == "") {
              b = false;
              console.log('verificacion 7/8');
              campoFaltante = "SEMESTRE";
      }
    }
    if (b == true) {
      console.log('verificacion 8/8');
      if (myAno.length == 0) {
              b = false;
              
              campoFaltante = "AÑO";
      }
    }
*/
    if (b == true) {
      console.log('verificacion 9/8');
      if (myAno2  > myAno) {
              b = false;
              campoFaltante = "Año del periodo precedente no debe ser mayor al periodo a procesar";
      } else {
        if (myAno2  == myAno) {
          if (mySemestre2  > mySemestre) {
            b = false;
              campoFaltante = "Semestre del periodo precedente no debe ser mayor al semestre a procesar";
          } else {
            if (mySemestre2  == mySemestre) {
              b = false;
                campoFaltante = "Semestre del periodo precedente no debe ser igual al semestre a procesar";
              }

          }
        }
      }     
    }

    if (b == false) {
      alert("Debe completar todos los campos, falta : " + campoFaltante);
    }
    console.log("HA PASADO VALIDACION");
    //alert("wait");
    return b;
  }
  function checkTodosLosCursosPropuestos() {
    maxFilas = sacaMaxFilas();
    for (let i = 1; i <= maxFilas; i++) {
      $("#id_incluir_"+i).prop('checked', true);
    }
    
  }

  $( document ).ready(function() {
    checkTodosLosCursosPropuestos();
    
  });
</script>  


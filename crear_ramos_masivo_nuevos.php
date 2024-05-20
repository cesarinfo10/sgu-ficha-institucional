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
//echo("<br>00001");
$crear_ramos_masivo_nuevos = $_REQUEST['crear_ramos_masivo_nuevos'];
//echo("<br>00002 crear_ramos_masivo_nuevos = ".$crear_ramos_masivo_nuevos);
$ejecutar_ramos_masivo = $_REQUEST['ejecutar_ramos_masivo'];
//echo("<br>00003 ejecutar_ramos_masivo = ".$ejecutar_ramos_masivo);
//$ejecutar_proceso_ramos_masivo = $_REQUEST['ejecutar_proceso_ramos_masivo'];
//echo("<br>00004 ejecutar_proceso_ramos_masivo = ".$ejecutar_proceso_ramos_masivo);

$id_regimen   = $_REQUEST['id_regimen'];
$id_carrera   = $_REQUEST['id_carrera'];
$id_malla     = $_REQUEST['id_malla'];
$id_malla_new     = $_REQUEST['id_malla_new'];
$id_jornada   = $_REQUEST['id_jornada'];
//echo("<br>MODO EFIMERO 12");
if ($id_regimen <> "") {
  //echo("HE ENTRADO");
//  echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));
  //echo("HE SALIDO");
}

/*SACAR MALLA-CARRERA!!*/
//echo("<br>id_malla_new = ".$id_malla_new);
if ($id_borrar_y_comenzar <> "") {
        $SQL_borrar = "delete from curso_tmp where id_usuario = {$_SESSION['id_usuario']}";
        if (consulta_dml($SQL_borrar) == 1) {
          echo(js("location='$enlbase=crear_ramos_masivo_nuevo';"));
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
$seccion_buscar = "";
if ($id_cohorte == 1) { //ENERO
  $seccion_buscar = "((seccion between 11 and 19) or (seccion = 110))";
}
if ($id_cohorte == 2) { //FEBRERO
$seccion_buscar = "((seccion between 21 and 29) or (seccion = 210))";
}
if ($id_cohorte == 3) { //MARZO
$seccion_buscar = "((seccion between 31 and 39) or (seccion = 310))";
}
if ($id_cohorte == 4) { //ABRIL
$seccion_buscar = "((seccion between 41 and 49) or (seccion = 410))";
}
if ($id_cohorte == 5) { //MAYO
$seccion_buscar = "((seccion between 51 and 59) or (seccion = 510))";
}
if ($id_cohorte == 6) { //JUNIO
$seccion_buscar = "((seccion between 61 and 69) or (seccion = 610))";
}
if ($id_cohorte == 7) { //JULIO
$seccion_buscar = "((seccion between 71 and 79) or (seccion = 710))";
}
if ($id_cohorte == 8) { //AGOSTO
$seccion_buscar = "((seccion between 81 and 89) or (seccion = 810))";
}
if ($id_cohorte == 9) { //SEPTIEMBRE
$seccion_buscar = "((seccion between 91 and 99) or (seccion = 910))";
}
if ($id_cohorte == 10) { //OCTUBRE
$seccion_buscar = "((seccion between 101 and 109) or (seccion = 1010))";
}
if ($id_cohorte == 11) { //NOVIEMBRE
$seccion_buscar = "((seccion between 111 and 119) or (seccion = 1110))";
}
if ($id_cohorte == 12) { //DICIEMBRE
$seccion_buscar = "((seccion between 121 and 129) or (seccion = 1210))";
}


//echo("<!--seccion_buscar = ".$seccion_buscar."-->");
//echo("<br>id_cohorte = ".$id_cohorte);
//echo("<br>seccion_buscar = ".$seccion_buscar);
/*
function obtieneDatosCarreraCuenta() {
    $SQL = "select id_carrera as id_carrera from mallas where id = $id_malla";
    $fPending = consulta_sql($SQL);	
    $carrera_alumnos_nuevos = $fPending[0]['id_carrera'];
    //CANTIDAD ALUMNOS N UEVOS
    $SQL = "
      select count(*) as cuenta
      from alumnos
      where estado = 1
      and cohorte = $ano
      and semestre_cohorte = $semestre
      and jornada = '$id_jornada'
      and carrera_actual = $carrera_alumnos_nuevos
      and mes_cohorte = $id_cohorte
    ";
    echo("<br>SQL = ".$SQL ); 
    $fPending = consulta_sql($SQL);	
    $cuenta_alumnos_nuevos = $fPending[0]['cuenta'];

}
*/
if ($crear_ramos_masivo_nuevos <> "") {

        echo("<!--TAMOS LISTO!!-->");
        echo("<!--id_cohorte = ".$id_cohorte."-->");
        echo("<!--id_malla = ".$id_malla."-->" );
        echo("<!--ano = ".$ano."-->" );
        echo("<!--semestre = ".$semestre."-->" );
        echo("<!--Jornada = ".$id_jornada."-->" );
        echo("<!--ejecutar_ramos_masivo = ".$ejecutar_ramos_masivo."-->");

  //      obtieneDatosCarreraCuenta();
      
        $SQL = "select id_carrera as id_carrera from mallas where id = $id_malla";
        $fPending = consulta_sql($SQL);	
        $carrera_alumnos_nuevos = $fPending[0]['id_carrera'];
        //CANTIDAD ALUMNOS N UEVOS
        //CAMBIO REINCORPORADOS
        /*
        $SQL = "
          select count(*) as cuenta
          from alumnos
          where estado = 1
          and cohorte = $ano
          and semestre_cohorte = $semestre
          and jornada = '$id_jornada'
          and carrera_actual = $carrera_alumnos_nuevos
          and mes_cohorte = $id_cohorte
        ";
        */
        $SQL = "
        select sum(cuenta) as cuenta from (
          select count(*) as cuenta
          from alumnos
          where estado = 1
          and cohorte = $ano
          and semestre_cohorte = $semestre
          and jornada = '$id_jornada'
          and carrera_actual = $carrera_alumnos_nuevos
          and mes_cohorte = $id_cohorte
          union
          select count(*) as cuenta
          from alumnos
          where estado = 1
          and cohorte_reinc = $ano
          and semestre_cohorte_reinc = $semestre
          and jornada = '$id_jornada'
          and carrera_actual = $carrera_alumnos_nuevos
          and mes_cohorte_reinc = $id_cohorte
          ) as a        
        ";
        //echo("<br>SQL = ".$SQL ); 
        $fPending = consulta_sql($SQL);	
        $cuenta_alumnos_nuevos = $fPending[0]['cuenta'];

}
////////////////////////////////////////////////////////////////////////////////////////////////////////////
class SeccionesCupo
{
    public $seccion;
    public $desde;
    public $hasta;
}
class SeccionesCurso
{
    public $seccion;
    public $id_cursos;
}
$arr = array();
$arrSeccionCursos = array();

/*
$arr[0] = new SeccionesCupo();
$arr[0]->seccion = 61;
$arr[0]->desde = 0;
$arr[0]->hasta = 70;

$arr[1] = new SeccionesCupo();
$arr[0]->seccion = 62;
$arr[1]->desde = 71;
$arr[1]->hasta = 140;

foreach ($arr as $myArr) {
  echo("<br>ARR: seccion " . $myArr->seccion . " desde:" . $myArr->desde . " hasta:" . $myArr->hasta);
}
*/

////////////////////////////////////////////////////////////////////////////////////////////////////////////
function obtieneCursos_seccion($seccion, $arrSeccionCursos) {
  $id_cursos = "";
  foreach ($arrSeccionCursos as $myArr) {
    if ($seccion >= $myArr->seccion) {
      $id_cursos = $myArr->id_cursos;
    }
  }
  return $id_cursos;
}

function obtieneSeccion_rango($indice, $arr) {
        //echo("<br>me han llamado indice= ".$indice);
        $mySeccion = 0;
        foreach ($arr as $myArr) {
          //echo("<br>ARR: seccion " . $myArr->seccion . " desde:" . $myArr->desde . " hasta:" . $myArr->hasta);
          if (($indice >= $myArr->desde) && ($indice <= $myArr->hasta)) {
            //echo("<br>encuentra seccion = ".$myArr->seccion.", para indice = ".$indice);
            $mySeccion = $myArr->seccion;
          }
        }
        return $mySeccion;
}
if ($ejecutar_ramos_masivo <> "") {
//CUIDADO
//echo("<br>TAMOS LISTO2!!");
//echo("<br>id_cohorte = ".$id_cohorte);
//echo("<br>id_malla = ".$id_malla );
//echo("<br>ano = ".$ano );
//echo("<br>semestre = ".$semestre );
//echo("<br>Jornada = ".$id_jornada );
//echo("<br>ejecutar_ramos_masivo = ".$ejecutar_ramos_masivo);
//echo("<br>seccion_buscar = ".$seccion_buscar);
  




  //SE REPITE ESTE CODIGO
  //------------------------------------
    $SQL = "select id_carrera as id_carrera from mallas where id = $id_malla";
    $fPending = consulta_sql($SQL);	
    $carrera_alumnos_nuevos = $fPending[0]['id_carrera'];



    $SQL = "
    select 	
    seccion, 
    min(coalesce(cupo,0)) cupo
    from cursos
    where ano = $ano
    and semestre = $semestre
    and id_prog_asig in (select id_prog_asig from detalle_mallas where id_malla = $id_malla and nivel = 1)
    and ($seccion_buscar)
    group by seccion
    order by seccion
    ";
    echo("<!--ejecutar_ramos_masivo = $SQL -->");
    //echo("<br>".$SQL);
    $f_UNO = consulta_sql($SQL);
    $indice = 0;
    $desde = 0;
    $hasta = 0;
    if (count($f_UNO) > 0) {
      for ($x=0;$x<count($f_UNO);$x++) {
        $seccion_UNO = $f_UNO[$x]['seccion'];		
        $cupo_UNO = $f_UNO[$x]['cupo'];		
        //echo("<br>seccion_UNO=".$seccion_UNO.", cupo_UNO=".$cupo_UNO);
        if ($cupo_UNO > 0) {
          if ($indice == 0) {
            $desde = 0;
            $hasta = $cupo_UNO;
          } else {
            $desde = $hasta+1;
            $hasta = $cupo_UNO + $hasta;
  
          }        
          $arr[$indice] = new SeccionesCupo();
          $arr[$indice]->seccion = $seccion_UNO;
          $arr[$indice]->desde = $desde;
          $arr[$indice]->hasta = $hasta;
          $indice++;
  
        }
      }
    }
    $indice = 0;
    foreach ($arr as $myArr) {
        $mySeccion = $myArr->seccion;
        $SQL = " select 
                  id
                  from cursos where 
                  ano = $ano
                  and semestre = $semestre 
                  and id_prog_asig in (select id_prog_asig from detalle_mallas where id_malla = $id_malla and nivel = 1) 
                  and seccion = $mySeccion                  
                  ;";
                  //echo("<br>".$SQL);
        $f_DOS = consulta_sql($SQL);
        if (count($f_DOS) > 0) {
          for ($x=0;$x<count($f_DOS);$x++) {
            $id_c = $f_DOS[$x]['id'];		
            $id_curso .= $id_c.",";
            //echo("<br>---".$id_curso);
          }
        }
        $id_cursos = sacaCommaFinal($id_curso);
        //echo("<br>".$id_cursos);
        $arrSeccionCursos[$indice] = new SeccionesCurso();
        $arrSeccionCursos[$indice]->seccion = $mySeccion;
        $arrSeccionCursos[$indice]->id_cursos = $id_cursos;
        $indice++;
        $id_curso = "";
    }







    //    $seccionObtenida = obtieneSeccion_rango(72, $arr);
  //    echo("<br>seccion obtenida = ".$seccionObtenida);
 //CAMBIO REINCORPORADOS
  $SQL_creaRamos = "";
  /*
    $SQL_universo = "
      select a.id as id from alumnos a, pap p
      where 
        a.estado = 1 
      and a.cohorte = $ano
      and a.semestre_cohorte = $semestre
      and a.jornada = '$id_jornada'
      and a.carrera_actual = $carrera_alumnos_nuevos 
      and a.mes_cohorte = $id_cohorte
      and a.id_pap = p.id
      order by p.fecha_post
    ;";
  */    
    $SQL_universo = "
    select id from (
      select a.id as id, p.fecha_post from alumnos a, pap p
      where 
      a.estado = 1 
      and a.cohorte = $ano
      and a.semestre_cohorte = $semestre
      and a.jornada = '$id_jornada'
      and a.carrera_actual = $carrera_alumnos_nuevos 
      and a.mes_cohorte = $id_cohorte
      and a.id_pap = p.id
      UNION
      select a.id as id, p.fecha_post from alumnos a, pap p
      where 
      a.estado = 1 
      and a.cohorte_reinc = $ano
      and a.semestre_cohorte_reinc = $semestre
      and a.jornada = '$id_jornada'
      and a.carrera_actual = $carrera_alumnos_nuevos 
      and a.mes_cohorte_reinc = $id_cohorte
      and a.id_pap = p.id
      ) as a
      order by a.fecha_post      
    ";
//CUIDADO        
//echo("<br>SQL_universo = $SQL_universo"); 

    $f_universo = consulta_sql($SQL_universo);
    $cuentaBuenos = 0;
    if (count($f_universo) > 0) {
      $indice = 0;
      for ($u=0;$u<count($f_universo);$u++) {
        $indice = $u+1;
        $id_alumno = $f_universo[$u]['id'];		
        $seccionObtenida = obtieneSeccion_rango($indice, $arr);
        $id_cursos = obtieneCursos_seccion($seccionObtenida, $arrSeccionCursos);

        //echo("<br>".$indice." - id_alumno = ".$id_alumno.", seccion = ".$seccionObtenida.", id_cursos = ".$id_cursos);

        //RESTA LOS QUE EXISTEN
        $SQL_resta = "select id as id_curso_grabar from cursos where id in ($id_cursos) 
        except
        select id_curso as id_curso_grabar from cargas_academicas where 
        id_curso in ($id_cursos) 
        AND ID_ALUMNO = $id_alumno";
//CUIDADO        
//echo("<br>SQL_resta = $SQL_resta");                
        $id_curso_grabar = "";
        $f_resta = consulta_sql($SQL_resta);
        for ($r=0;$r<count($f_resta);$r++) {
          $id_c = $f_resta[$r]['id_curso_grabar'];		
          $id_curso_grabar .= $id_c.",";
        }
        $id_cursos_final = sacaCommaFinal($id_curso_grabar);
        $SQL_creaRamos = "
        insert into cargas_academicas(id_curso,id_alumno, id_usuario)
        (
          SELECT id as id_curso, $id_alumno as id_alumno, {$_SESSION['id_usuario']} as id_usuario 
          FROM cursos 
          WHERE id IN ( $id_cursos_final ) 
              
        );";

//NUEVA PARTE
        $SQL_inscripcionesCursos = "
        insert into inscripciones_cursos(id_curso,id_alumno)
        (
          SELECT id as id_curso, $id_alumno as id_alumno
          FROM cursos 
          WHERE id IN ( $id_cursos_final ) 
              
        );";

//CUIDADO
//echo("<br>SQL_creaRamos = $SQL_creaRamos");        
        /*
        $SQL_creaRamos = "
                          insert into cargas_academicas(id_curso,id_alumno, id_usuario)
                          (
                            SELECT id as id_curso, $id_alumno as id_alumno, {$_SESSION['id_usuario']} as id_usuario 
                            FROM cursos 
                            WHERE id IN ( $id_cursos ) 
                                
                          );";
        */              
//CUIDADO                    
        if (consulta_dml($SQL_creaRamos) == 1) {
          $cuentaBuenos++;
         // echo(msje_js("Proceso ejecutado exitosamente."));
         // echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));  
        }

        if (consulta_dml($SQL_inscripcionesCursos) == 1) {
          //$cuentaBuenos++;
         // echo(msje_js("Proceso ejecutado exitosamente."));
         // echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));  
        }

      }
    }

      //echo("<br>".$SQL_creaRamos);
/*      
        if (consulta_dml($SQL_creaRamos) == 1) {
          echo(msje_js("Proceso ejecutado exitosamente."));
          echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));  

        } else {
          echo(msje_js("Ha ocurrido un inconveniente, no se pudo registrar la información."));
          echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));  

        }                  
        //usleep(500000)); //medio segundo  
*/        
        //echo(msje_js("Proceso ejecutado exitosamente, se inscribieron $cuentaBuenos estudiantes."));
        
        echo(msje_js("Proceso ejecutado exitosamente."));
        //CUIDADO
        echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));  


}



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
  echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));  
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
                if ($crear <> "") {
                  $checkReadSelect = "disabled='true'";
                  $checkReadText = "readonly";

                        $mySeccion = "";
                        for ($x=$id_cohorte;$x<=$id_cohorte;$x++) {
                                for ($y=1;$y<=$id_seccion;$y++) {
                                  $mySeccion .= $x.$y.",";
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
  echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));
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
                          echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));
  
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
  $sql_misJornadas ="
  select 'D' id, 'Diurna' nombre 
  union
  select 'V' id, 'Vespertina' nombre 
";  
$misJornadas = consulta_sql($sql_misJornadas);

if ($crear_ramos_masivo_nuevos <> "") {
  $checkReadSelect = "disabled='true'";
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
                      echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));
                    } else {
                      $huboError = 1;
                    }
                  } 
                ?>              
                
<?php                    
//UNIVERSO SECCIONES CUPO (1er semestre siempre)
$SQL = "
select 	 
seccion, 
min(coalesce(cupo,0)) cupo
from cursos
where ano = $ano
and semestre = $semestre
and id_prog_asig in (select id_prog_asig from detalle_mallas where id_malla = $id_malla and nivel = 1)
and ($seccion_buscar)
group by seccion";

//echo($SQL);
echo("<!--QUERY CUPO-->");
echo("<!--$SQL-->");


$fUSC = consulta_sql($SQL);
$cupoTotal = 0;
if (count($fUSC) > 0) {
        for ($x=0;$x<count($fUSC);$x++) {
                $fuscCupo = $fUSC[$x]['cupo'];
                $cupoTotal = $cupoTotal + $fuscCupo;
        } 
}
?>                    



                <table class="tabla">

<input type='hidden' id='id_cohorte' name='id_cohorte' value='<?php echo($id_cohorte); ?>'>
<input type='hidden' id='id_malla' name='id_malla' value='<?php echo($id_malla); ?>'>
<input type='hidden' id='semestre' name='semestre' value='<?php echo($semestre); ?>'>
<input type='hidden' id='id_jornada' name='id_jornada' value='<?php echo($id_jornada); ?>'>

                  <tr>
                    <?php if ($crear_ramos_masivo_nuevos == "") { ?>    
                        <td class="tituloTabla"><input type="submit" name="crear_ramos_masivo_nuevos" id="crear_ramos_masivo_nuevos" value="Crear" onClick="return validarCampos();"></td>
                        <!--<td class="tituloTabla"><input type="submit" name="ejecutar" id="ejecutar" value="Crear" onClick="return validarCampos();"></td>-->
                    <?php } else { 
//if ($cuenta_alumnos_nuevos > 0) {
//echo("<br>verificacion : cuenta_alumnos_nuevos = ".$cuenta_alumnos_nuevos.", cupoTotal = ".$cupoTotal);
if ($cuenta_alumnos_nuevos <= $cupoTotal) {
  if ($cuenta_alumnos_nuevos > 0) {
                      ?>
                        <!--<td class="tituloTabla"><input type="submit" name="ejecutar_ramos_masivo" value="Ejecutar Proceso" onClick="return ejecutarProceso();"></td>-->
                        <td class="tituloTabla"><input type="button" name="ejecutar_ramos_masivo" value="Ejecutar Proceso" onClick="return ejecutarProceso('&ejecutar_ramos_masivo=ejecutar&id_regimen=<?php echo($id_regimen); ?>&id_malla_new=<?php echo($id_malla); ?>&id_jornada=<?php echo($id_jornada); ?>&semestre=<?php echo($semestre); ?>&ano=<?php echo($ano); ?>&id_cohorte=<?php echo($id_cohorte); ?>');"></td>
                    <?php 
    } 
}
                    };?>
                    <!--<td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="history.back();"></td> -->
                    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="javascript:fnCancelar();"></td>
                  </tr>
                </table>
                <?php //if ($crear_ramos_masivo_nuevos == "") { ?>
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
                                    <td class="celdaValorAttr">
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
                                    <td class="celdaNombreAttr">Jornada:</td>
                                    <td class="celdaValorAttr">
                                      <select name="id_jornada" id="id_jornada" <?php echo($checkReadSelect); ?>>
                                        <option value="">-- Seleccione --</option>
                                        
                                        <?php echo(select($misJornadas,$id_jornada)); ?>
                                        <!--<option value="D">Diurna</option>
                                        <option value="V">Vespertina</option>
                          -->
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
                            <!--
                                                    <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                                                    Periodo en que se dictarán
                                                    </td>
                                                  <tr>
                                  -->
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
                                          <!--
                                        <tr>
                                          <td class="celdaNombreAttr">Nivel:</td>
                                          <td class="celdaValorAttr" colspan=3>
                                              <?php
                                              /*
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
                                          <!--
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
                                            -->
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
                                  <?php };?>
                          <?php };?>
                        </table>
                <?php //} else { 
                ?>
              <?php //};?>

              <?php if ($crear_ramos_masivo_nuevos <> "") { ?>
                    <br>
                    <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
                        <tr>
                          <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                            Seleccionados
                          </td>
                        <tr>
                          
                          <td class="celdaNombreAttr">Alumnos Nuevos :</td>
                          <td class="celdaValorAttr" colspan=3>
                                  <?php echo($cuenta_alumnos_nuevos); ?> <a href='<?php echo("$enlbase=gestion_alumnos&mes_cohorte=$id_cohorte&semestre_cohorte=$semestre&cohorte=$ano&estado=1&id_carrera=$carrera_alumnos_nuevos&jornada=$id_jornada&regimen=$id_regimen"); ?> ' class='boton' target='_blank'>ver...</a>                            
                          </td>
                    </table>
                    <br>
                    <?php if ($cuenta_alumnos_nuevos == 0) { ?>
                            <div class="texto">
                            Proceso no puede continuar por que no hay alumnos nuevos.
                          </div>      
                    <?php };
                    ?> 
                    <br>
                    <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
                        <tr>
                          <td class="celdaNombreAttr" colspan=4 style="text-align:center">
                            Cursos - secciones a considerar
                          </td>
                        <tr>
                        <tr>
                          <td class="celdaNombreAttr" style="text-align:center">
                            Sección
                          </td>
                          <td class="celdaNombreAttr" style="text-align:center">
                            Cupo
                          </td>
                        <tr>

                    <?php                    
                            //UNIVERSO SECCIONES CUPO (1er semestre siempre)
                            $SQL = "
                            select 	 
                            seccion, 
                            min(coalesce(cupo,0)) cupo
                            from cursos
                            where ano = $ano
                            and semestre = $semestre
                            and id_prog_asig in (select id_prog_asig from detalle_mallas where id_malla = $id_malla and nivel = 1)
                            and ($seccion_buscar)
                            group by seccion";
//echo($SQL);
                            $fUSC = consulta_sql($SQL);
                            $cupoTotal = 0;
                            if (count($fUSC) > 0) {
                                    for ($x=0;$x<count($fUSC);$x++) {
                                            $fuscSeccion = $fUSC[$x]['seccion'];
                                            $fuscCupo = $fUSC[$x]['cupo'];
                                            $cupoTotal = $cupoTotal + $fuscCupo;
                                            ?>
                                            <tr>
                                              <td class="celdaNombreAttr" style="text-align:center">
                                                <?php echo($fuscSeccion); ?> <a href='<?php echo("$enlbase=gestion_cursos&ano=$ano&semestre=$semestre&id_carrera=$carrera_alumnos_nuevos&seccion=$fuscSeccion&regimen=$id_regimen"); ?>' class='boton' target='_blank'>ver...</a>
                                              </td>
                                              <td class="celdaNombreAttr" style="text-align:center">
                                                <?php echo($fuscCupo); ?>
                                              </td>
                                            <tr>


                    <?php                  
                                    } ?>
                                    <tr>
                                      <td class="celdaNombreAttr" style="text-align:center">
                                        
                                      </td>
                                      <td class="celdaNombreAttr" style="text-align:center">
                                        <?php echo($cupoTotal); ?>
                                      </td>
                                    <tr>
                                    
                            <?php }
                    ?>                    
                    </table>
                    <br>
                    <?php if ($cuenta_alumnos_nuevos >= $cupoTotal) { ?>
                            <div class="texto">
                            El número de alumnos nuevos (<?php echo($cuenta_alumnos_nuevos); ?>) excede al cupo total a considerar (<?php echo($cupoTotal); ?>).
                            <br>
                            Se sugiere que revise los cursos que integran estos grupos.
                          </div>      
                    <?php };?>

              <?php };?> 


               <!---->
                <?php 
                  /*if ($crear <> "") { 
                        echo("<br>");                          
                        if ($puedeCrear > 0) {
                          echo(msje_js("ERROR : OBSOLETO Ud.ha seleccionado unos parámetros que coinciden con cursos ya existentes. No se puede continuar."));
                          echo(js("location='$enlbase=crear_ramos_masivo_nuevos';"));
                        } else {
                          echo("<input type='submit' name='ejecutar' value='Ejecutar Creaci&oacute;n'>");
                        }
                      }
                      */ 
                      ?>
                  <!-- <input type="submit" name="ejecutar" value="Ejecutar Creaci&oacute;n"> -->
                </form>
              <?php //} ?>
        
        
        <?php } /*existenPendientes*/?>
  
<?php } /*id_profesores_seleccionados*/?>



<script>
  function saltar_crear_ramos_masivo_nuevos() {
    var pSaltar = "/sgu/principal.php?modulo=crear_ramos_masivo_nuevos";
          pSaltar = "http://" + window.location.hostname + ":" + window.location.port + pSaltar;
          window.location.href = pSaltar;

  }
  function fnCancelar() {
    saltar_crear_ramos_masivo_nuevos();
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
		f.action='?modulo=crear_ramos_masivo_nuevos';
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
		f.action='?modulo=crear_ramos_masivo_nuevos';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("id_borrar_y_comenzar", "id_borrar_y_comenzar");
		f.appendChild(i);

		document.body.appendChild(f);
		f.submit();
}

function enviarValoresEjecutarProceso(parametros){

/*  
		var f = document.createElement('form');
		f.action='?modulo=crear_ramos_masivo_nuevos';
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');
		i = almacenaVariable("ejecutar_proceso_ramos_masivo", "ejecutar_proceso_ramos_masivo");
		f.appendChild(i);

		var i=document.createElement('input');
		i = almacenaVariable("id_cohorte", "999");
		f.appendChild(i);

		document.body.appendChild(f);
		f.submit();
*/

//alert("CUIDADO!");
  var pSaltar = "/sgu/principal.php?modulo=crear_ramos_masivo_nuevos" + parametros;
  //var pSaltar = "/sgu/principal.php?modulo=../sgu_rc/EFIMERO/crear_ramos_masivo_nuevos" + parametros;
          pSaltar = "http://" + window.location.hostname + ":" + window.location.port + pSaltar;
          //alert(parametros);
          window.location.href = pSaltar;

}

function ejecutarProceso(parametros) {
          var bb = false;
          var r = confirm("Está a punto de ejecutar un proceso el cual generará ramos en forma masiva. Está seguro(a) de continuar?");
          if (r == true) {
            //bbb
                enviarValoresEjecutarProceso(parametros);
                bb = true;
          } else {
                bb = false;
          }
//          alert(bb);
//bbb
          return false; --bb;

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
    var myMalla = $("select#id_malla option:checked" ).val();
//    var myNivelesSeleccionados = $("#id_nivelesSeleccionados").val();

    var myCohorte = $("select#id_cohorte option:checked" ).val();
    var myJornada = $("select#id_jornada option:checked" ).val();
//    var mySeccion = $("select#id_seccion option:checked" ).val();
    var mySemestre = $("select#id_semestre option:checked" ).val();
    var myAno = $("#id_ano").val();
    var campoFaltante = "";
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
      if (myJornada == "") {
              b = false;
              console.log('verificacion x/x');
              campoFaltante = "JORNADA";
      }
    }

    if (b == true) {
      if (myMalla == "") {
              b = false;
              console.log('verificacion 3/8');
              campoFaltante = "MALLA";
      }
    }
/*    
    if (b == true) {
      if (myNivelesSeleccionados.length == 0) {
              b = false;
              console.log('verificacion 4/8');
              campoFaltante = "NIVEL";
      }
    }
*/    
    if (b == true) {
      if (myCohorte == "") {
              b = false;
              console.log('verificacion 5/8');
              campoFaltante = "COHORTE";
      }
    }

/*
    if (b == true) {
      if (mySeccion == "") {
              b = false;
              console.log('verificacion 6/8');
              campoFaltante = "GRUPO";
      }
    }
*/
    if (b == true) {
      if (mySemestre == "") {
              b = false;
              console.log('verificacion 7/8');
              campoFaltante = "SEMESTRE";
      }
    }
    if (b == true) {
      if (myAno == "") {
              b = false;
              console.log('verificacion 8/8');
              campoFaltante = "AÑO";
      }
    }
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

  $( document ).ready(function() {
    //alert('Ready!');
    checkTodosLosCursosPropuestos();
    
  });
</script>  


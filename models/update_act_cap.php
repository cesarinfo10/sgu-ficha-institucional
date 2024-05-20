<?php
include('conexion.php');

/*=============================================
ACTUALIZAR MINUTOS
=============================================*/
if (isset($_GET['postUpdateMinutos'])){
    $dbconn = db_connect();
 
  
        $minutos_asis = $_POST['minutos_asis']; //
        $id_usuario = $_POST['id_usuario']; //
        $id_asiscapac_actividades=$_POST['id_asiscapac_actividades']; //
  
        $sql = "UPDATE public.asiscapac_actividades_obligatorias_funcionarios SET minutos_asis = ".$minutos_asis." WHERE id_usuario= ".$id_usuario." AND id_asiscapac_actividades= ".$id_asiscapac_actividades."";
   
        
          // Ejecutamos la sentencia preparada
          $result = pg_query($dbconn, $sql);
        
          if($result){ 
        
            echo 1;
            while ($row = pg_fetch_row($result)) {
              echo $row[0];
            }
  
          } else {
              echo "<br>Hubo un problema y no se guardó el archivo. " . pg_last_error($dbconn) . "<br/>";
              echo 2;
          }
        //echo $result ;
         pg_close($dbconn);
       }
/*=============================================
ACTUALIZAR PRESENCIAL
=============================================*/
if (isset($_GET['UdatePresencialAc'])){
    $dbconn = db_connect();
 
  
    $campo = $_POST['col']; //
    $id_usuario = $_POST['id_usuario']; //
    $id_asiscapac_actividades=$_POST['id_asiscapac_actividades']; //
    $valor =$_POST['valor'];

    $sql = "UPDATE public.asiscapac_actividades_obligatorias_funcionarios SET ".$campo." = ".$valor." WHERE id_usuario= ".$id_usuario." AND id_asiscapac_actividades= ".$id_asiscapac_actividades."";

    
      // Ejecutamos la sentencia preparada
      $result = pg_query($dbconn, $sql);
    
      if($result){ 
    
        echo 1;
        while ($row = pg_fetch_row($result)) {
          echo $row[0];
        }

      } else {
          echo "<br>Hubo un problema y no se guardó el archivo. " . pg_last_error($dbconn) . "<br/>";
          echo 2;
      }
    //echo $result ;
     pg_close($dbconn);

    }
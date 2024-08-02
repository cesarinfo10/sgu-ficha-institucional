<?php
include('conexion.php');
//echo 'Hola';
/*=============================================
LLAMAR A Dotación de personal, por sede
=============================================*/
if (isset($_GET['getSede'])){
    $dbconn = db_connect();
  
    $query = "SELECT * FROM public.sede_ficha";

    $result = pg_query($dbconn, $query) or die('La consulta fallo: ' . pg_last_error());
     

    echo '<label for="sede">Sede:</label>
    <select class="shadow-lg p-1 bg-white form-control" id="tipoBeneficio" name="tipoBeneficio" style="visibility: visible;">
      <option value="0" selected>Seleccione Sede</option>';
      while ($row = pg_fetch_row($result)) {
      echo '<option value="'.$row[0].'">'.$row[1].'</option>';
      }
      echo'  </select>';
}

/*=============================================
INSERTAR DOTACION DE PERSONAL
=============================================*/
if (isset($_GET['insertDotacion'])){
  $dbconn = db_connect();
  $rut = $_POST['rut'];


      $sql = "INSERT INTO public.sede_ficha (rut) VALUES ('".$rut."')";    
      
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
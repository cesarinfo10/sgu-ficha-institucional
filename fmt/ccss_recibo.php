<?php 

$FIRMA = "Evelyn Vilches P.";

$texto_docto = "<img src='../img/logo_umc_nuevo_ls.png' width='300'><br><br>"
             . "<p align='right'>Santiago, $fecha_hoy</p>"
             . "<center>"
             . "<big><b>RECIBO CCSS N° $nro_comprobante</b></big>"
             . "<br>"
             . "</center>"
             . "<p align='justify'>"
             . "Con fecha $fecha, hemos recibido el pago de Cuotas Sociales "
             . "a nombre de la Universidad Miguel de Cervantes, correspondiente al "
             . "periodo $ano, de parte de $sociedad R.U.T.: $rut, representada por $socios por "
             . "la suma de $$monto_total mediante la siguiente forma de pago:<br><br>"
             . $HTML_medios_pago."<br>"
             . "Se extiende este Recibo para los fines que estime conveniente el comprometido."
             . "</p>"
             . "<br><br><br>"
             . "<center>"
             . $FIRMA
             . "<br>Dirección de Admnistración y Finanzas<br>Universidad Miguel de Cervantes"
             . "</center>";
        
?>

<?php

include("funciones.php");

$SQL_contratos_castigados = "SELECT c.id AS id_contrato,castigo_monto,castigo_fecha::date,coalesce(monto_condonacion,0) AS monto_condonacion 
                             FROM finanzas.contratos AS c
                             LEFT JOIN carreras AS car ON car.id=c.id_carrera
                             WHERE c.ano IN (2017,2018,2019,2020) AND regimen in ('POST-TD','POST-GD') AND c.estado IS NOT NULL AND castigo_monto IS NOT NULL
                             ORDER BY c.id";
$contratos_castigados = consulta_sql($SQL_contratos_castigados);

$SQL_upd = "";
for ($x=0;$x<count($contratos_castigados);$x++) {
    extract($contratos_castigados[$x]);
    $SQL_upd = "";
    if ($monto_condonacion < $castigo_monto) {
        $castigo_monto -= $monto_condonacion;
        //$SQL_upd .= "Monto castigo: $castigo_monto Monto Condonacion: $monto_condonacion \n";
        $cobros = consulta_sql("SELECT id,monto,castigo_monto FROM finanzas.cobros WHERE id_contrato=$id_contrato ORDER BY fecha_venc DESC");
        $castigo_aplicado = 0;
        for($y=0;$y<count($cobros);$y++) {            
            $monto_castigo = 0;
            if ($cobros[$y]['castigo_monto'] == "" && $castigo_aplicado < $castigo_monto) {
                if ($castigo_monto - $castigo_aplicado < $cobros[$y]['monto']) {
                    $monto_castigo = $castigo_monto - $castigo_aplicado;
                } else {
                    $monto_castigo = $cobros[$y]['monto'];
                }
                $SQL_upd .= "UPDATE finanzas.cobros SET castigo=true, castigo_monto=$monto_castigo, castigo_fecha='$castigo_fecha'::date WHERE id={$cobros[$y]['id']} AND id_contrato=$id_contrato;\n";
                $castigo_aplicado += $monto_castigo;
            }
        }    
        consulta_dml($SQL_upd);
    }
}
//echo($SQL_upd);

?>

<?php

/* Se hace un comprobante diario, por tipo de pago. Se suman los tipos de pago. Esto por cron
 * 
 * SELECT sum(efectivo) as efectivo,
 *        sum(cheque) as cheque,
 *        sum(transferencia) as transferencia,
 *        sum(tarj_credito) as tarj_credito,
 *        sum(tarj_debito) as tarj_debito
 * FROM finanzas.pagos
 * WHERE fecha=now()
 * */
$client = new http\Client;
$request = new http\Client\Request;
$request->setRequestUrl('https://api.manager.cl/ventas/Api/Comprobante');
$request->setRequestMethod('POST');
$body = new http\Message\Body;
$body->append('{
  "rutEmpresa": "73124400-6",
  "tokenEmpresa": "gD7G3UkDK1E2woKQazzsG19ks39zdU6K43q9u62g8M1kyCZcq3",
  "conNumReg": 0,
  "coTipo": "I",
  "coGlosa": "Pago BOV",
  "tipoDocComp": 6, // Efectivo
  "tipoDocComp": 2, // Cheque
  "tipoDocComp": 7, // Transferencia
  "tipoDocComp": 1, // Deposito
  "tipoDocComp": 8, // Tarjeta Debito
  "tipoDocComp": 9, // Tarjeta Crédito  
  "montoDocPago":6000 , // suma tipo pago
  "numDocPago":1010, // generar correlativo de comprobantes
  "cuentaContable": 110201, // cuenta contable del tipo de pago
  "documentos":"3361, 3362"  
}
');
$request->setBody($body);
$request->setOptions(array());
$request->setHeaders(array(
  'Content-Type' => 'application/json'
));
$client->enqueue($request)->send();
$response = $client->getResponse();
echo $response->getBody();


$body->append('{
  "rutEmpresa": "73124400-6",
  "tokenEmpresa": "gD7G3UkDK1E2woKQazzsG19ks39zdU6K43q9u62g8M1kyCZcq3",
  "conNumReg": 0,
  "coTipo": "I",
  "coGlosa": "Pago BOV",
  "tipoDocComp1": 2, // Cheque
  "montoDocPago1":6000 , // cheque individual
  "numDocPago1":1010, // numero del cheque
  "fecha_venc1":'YYYY-MM-DD'// fecha_vencimiento del cheque
  "cuentaContable1": 110201, // cuenta contable del tipo de pago
  "documentos1":"3361, 3362"  // boletas asociados a los cheques
}
?>

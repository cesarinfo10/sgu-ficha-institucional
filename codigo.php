<?

$c = $_REQUEST['c'];

$numeros = array("0","1","2","3","4","5","6","7","8","9");
$letras  = array("a","c","e","g","i","k","m","o","q","s");

$cod1 = str_replace($numeros,$letras,$c);
$cod2 = base64_encode($c);

echo("<center>");
echo("<img src='http://sgu.umcervantes.cl/sgu/php-barcode/barcode.php?code=$c&scale=1'><br>$c<br>");

echo("<img src='http://sgu.umcervantes.cl/sgu/php-barcode/barcode.php?code=$cod1&scale=1'><br>$cod1<br>");
echo("<img src='http://sgu.umcervantes.cl/sgu/php-barcode/barcode.php?code=$cod2&scale=1'><br>$cod2<br>");

echo("<img src='http://sgu.umcervantes.cl/sgu/php-barcode/barcode.php?code=$cod1&scale=2'><br>$cod1<br>");
echo("<img src='http://sgu.umcervantes.cl/sgu/php-barcode/barcode.php?code=$cod2&scale=2'><br>$cod2");
echo("</center>");

?>

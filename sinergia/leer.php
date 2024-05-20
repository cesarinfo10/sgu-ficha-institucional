<?php
setlocale(LC_ALL,"es_ES.UTF8");

$test = file('af5.csv',FILE_IGNORE_NEW_LINES);
$aKeys_test = explode(',',$test[0]);
$aTest = array();
for ($x=1;$x<count($test);$x++) {
	$aTest[$x-1] = array_combine($aKeys_test,explode(',',$test[$x]));
}
//var_dump($aTest);
?>

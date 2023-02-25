<?php
/**
* CURD modul kreálása
*/
if ((count($argv) < 2) | (!file_exists('includes/controllers/demo.php'))) {
	echo 'use:   
	php tools/createCURD.php name'."\n";
	exit();
}
$name = strtolower($argv[1]);

// controller
$lines = file('includes/controllers/demo.php');
$str = implode("",$lines);
$str = str_replace('demo',$name,$str);
$str = str_replace('Demo',ucfirst($name),$str);
$fp = fopen('includes/controllers/'.$name.'.php','w+');
fwrite($fp,$str);
fclose($fp);
echo 'controller created'."\n";

// model
$lines = file('includes/models/demomodel.php');
$str = implode("",$lines);
$str = str_replace('demo',$name,$str);
$str = str_replace('Demo',ucfirst($name),$str);
$fp = fopen('includes/models/'.$name.'model.php','w+');
fwrite($fp,$str);
fclose($fp);
echo 'model created'."\n";

// viewerek
$lines = file('includes/views/demobrowser.html');
$str = implode("",$lines);
$str = str_replace('demo',$name,$str);
$str = str_replace('Demo',ucfirst($name),$str);
$str = str_replace('DEMO',strtoupper($name),$str);
$fp = fopen('includes/views/'.$name.'browser.html','w+');
fwrite($fp,$str);
fclose($fp);
echo 'browser viewer created'."\n";

$lines = file('includes/views/demoform.html');
$str = implode("",$lines);
$str = str_replace('demo',$name,$str);
$str = str_replace('Demo',ucfirst($name),$str);
$str = str_replace('DEMO',strtoupper($name),$str);
$fp = fopen('includes/views/'.$name.'form.html','w+');
fwrite($fp,$str);
fclose($fp);
echo 'form viewer created'."\n";

// languages
$lines = file('languages/hu.js');
$str = implode("",$lines);
$str = str_replace('"END":"Vége"',
'/* '.$name.' */'."\n".
'    "'.strtoupper($name).'":"'.$name.'",'."\n".
'    "'.strtoupper($name.'s').'":"'.$name.'s",'."\n".
''."\n".
'    "END":"Vége"',$str);
$fp = fopen('languages/hu.js','w+');
fwrite($fp,$str);
fclose($fp);
echo 'languages file updated, check it!'."\n";


exit;
?>


<?php
$file = fopen("data.json","w") or die("error file!");
$txt=$_POST['val'];
fwrite($file,$txt);
fclose($file);
echo $txt;
?>

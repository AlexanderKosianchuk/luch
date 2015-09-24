<?php

$protectExecFile = @SITE_ROOT_DIR."/"."protect"."/"."LuchProtect.exe" . " " . 1;
$protectOutFile = @SITE_ROOT_DIR."/"."protect"."/"."answ_hash.key";

$process = popen($protectExecFile, "r");
if (is_resource($process)) {
	pclose($process);
}

//exec($protectExecFile);
//shell_exec($protectExecFile);

$contents = 0;
if(file_exists($protectOutFile)){
	$handle = fopen($protectOutFile, "r");
	$contents = fread($handle, 1);
	fclose($handle);
	unlink($protectOutFile);
}

if($contents != 1){
	die("Copyright rules were brocken. Please contact Luch service privider.");
}

?>

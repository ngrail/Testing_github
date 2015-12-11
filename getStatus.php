<?php
	require_once('class.pnrapi.php');
	$pnr = "0123456789"; //10-digit PNR number here or use GET/POST variable here
	$handle = new PNRAPI($pnr);
	$pStatus = $handle->getPassengerStatus();
	$cStatus = $handle->getChartStatus();
	print_r($pStatus);
	print_r($cStatus);
	echo $pnr;
?>

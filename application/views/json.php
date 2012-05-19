<?php
	header("Content-Type: application/json");
	header("Access-Control-Allow-Origin: *");
?>
<?= json_encode($data) ?>
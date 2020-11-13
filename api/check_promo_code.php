<?php
require_once("../functions/db_functions.php");
header('Content-Type: application/json');
$object = new DbQueries();
$code = $_POST['code'];
$origin = $_POST['origin'];
$destination = $_POST['destination'];
$check_promo_code = $object->check_promo_code($code, $origin, $destination);
?>
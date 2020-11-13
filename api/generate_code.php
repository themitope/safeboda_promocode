<?php
require_once("../functions/db_functions.php");
header('Content-Type: application/json');
$object = new DbQueries();
$event_venue = $_POST['event_venue'];
$amount = $_POST['amount'];
$radius = $_POST['radius'];
$code_validity_duration = $_POST['code_validity_duration'];
$object->generate_promo_code($event_venue, $amount, $radius, $code_validity_duration);
?>
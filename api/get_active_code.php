<?php
require_once("../functions/db_functions.php");
header('Content-Type: application/json');
$object = new DbQueries();
$object->get_active_code();
?>
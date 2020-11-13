<?php
require_once("../functions/db_functions.php");
header('Content-Type: application/json');
$object = new DbQueries();
$code = $_POST['code'];
$object->deactivate_code($code);
?>
<?php
require("../config.php");
require("../functions/db.php");
session_start();

if(intval($_GET[id] == 0)) { die(); }
$id = intval($_GET[id]);
$_SESSION[lastvisited] = $id;
?>

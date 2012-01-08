<?php
require("../config.php");
require("../functions/db.php");
require("../functions/common.php");
session_start();

if(unbase36($_GET[id]) == 0) { die(); }
$_SESSION[lastvisited] = $_GET[id];
?>

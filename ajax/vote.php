<?php
require("../config.php");
require("../functions/db.php");
require("../functions/common.php");
session_start();
$_SESSION[id] = intval($_SESSION[id]);
checkLogin($_SESSION[id]);
if($_SESSION[id] == 0) { die("You must be logged in to vote"); }
print(vote($_SESSION[id],unbase36($_POST[vote][0]),$_POST[vote][2],$_POST[vote][1]));

?>

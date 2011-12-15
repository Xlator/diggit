<?php
require("config.php");
require("functions/db.php");
session_start();
if($_SESSION[id] == 0) { die("You must be logged in to vote"); }
$subjectid = intval($_POST[vote][0]);
$vote = intval($_POST[vote][1]);
$type = $_POST[vote][2];
$userid = $_SESSION[id];
//print_r($_POST);
print(vote($userid,$subjectid,$type,$vote)); 

?>

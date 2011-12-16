<?php
require("config.php");
require("functions/db.php");
session_start();

if($_SESSION[id] == 0) { die("You must be logged in to vote"); }

print(vote($userid,$subjectid,$type,$vote));

?>

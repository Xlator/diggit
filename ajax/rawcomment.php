<?php
require("../config.php");
require("../functions/db.php");
session_start();

if(intval($_SESSION[id]) == 0) { die(); }
print rawComment(intval($_GET[id]));
?>

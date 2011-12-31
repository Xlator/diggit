<?php
require("../config.php");
require("../functions/db.php");
session_start();

checkLogin($_SESSION[id]);
if(intval($_SESSION[id]) == 0) { die(); }
nsfw(intval($_GET[id]));
?>

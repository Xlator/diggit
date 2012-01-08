<?php
require("../config.php");
require("../functions/db.php");
require("../functions/common.php");
session_start();

checkLogin($_SESSION[id]);
if(intval($_SESSION[id]) == 0) { die(); }
nsfw(unbase36($_GET[id]));
?>

<?php
require("../config.php");
require("../functions/db.php");
session_start();

if($_SESSION[id] == 0) { die(); }
nsfw(intval($_GET[id]));
?>

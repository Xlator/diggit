<?php
require("../config.php");
require("../functions/db.php");
session_start();

if($_SESSION[id] == 0) { die(); }
if($_GET[type] == "comment") {
	var_dump(deleteComment(intval($_GET[id])));
}
if($_GET[type] == "link") {
	var_dump(deleteLink(intval($_GET[id])));
}
?>

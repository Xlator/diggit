<?php
require("../config.php");
require("../functions/db.php");
session_start();
checkLogin($_SESSION[id]);

if(intval($_SESSION[id]) == 0) { die(); }
if($_GET[type] == "comment") {
	var_dump(deleteComment(intval($_GET[id])));
}
if($_GET[type] == "link") {
	var_dump(deleteLink(intval($_GET[id])));
}
?>

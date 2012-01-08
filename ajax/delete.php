<?php
require("../config.php");
require("../functions/db.php");
require("../functions/common.php");
session_start();
checkLogin($_SESSION[id]);

if(intval($_SESSION[id]) == 0) { die(); }
if($_GET[type] == "comment") {
	var_dump(deleteComment(intval($_GET[id])));
}
if($_GET[type] == "link") {
	deleteLink(unbase36($_GET[id]));
}
?>

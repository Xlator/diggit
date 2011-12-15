<?php
session_start();
if($_GET[logout]==1) { session_destroy(); header("Location: ./");}
if(!isset($_SESSION[id])) { $_SESSION[id] = 0; }
//print_r($_SESSION);
//$_SESSION[id] = 1;
//$_SESSION[username] = "Xlator";
require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/common.php");
printHeader();
print "<div class=linklist><ul>";

$links = getLinks();

if(!empty($links)) {
	foreach(getLinks() as $link) {
	print(printLink($link));
	}
}

?>

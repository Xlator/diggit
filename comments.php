<?php
session_start();
if(!isset($_SESSION[id])) { $_SESSION[id] = 0; }
$linkid = intval($_GET[linkid]);
require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/common.php");
require("functions/comments.php");
require("functions/forms.php");
if($_POST) {
	$commentid = sendComment($_POST);
	header("Location: comments.php?linkid=$_GET[linkid]#$commentid");
}
printHeader();
print "<div class=linklist><ul>";
$link = getLink($linkid);
print "<ul class=comments>";
print(printLink($link));
print "<li style=margin-bottom:1em;>";
print(commentform(0));
print "</li>";
$comments = getComments($_GET[linkid]);
commentTree($comments);



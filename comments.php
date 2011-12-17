<?php
session_start();
if(!isset($_SESSION[id])) { $_SESSION[id] = 0; }
$linkid = intval($_GET[linkid]);
require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/common.php");
require("functions/comments.php");

printHeader();
print "<div class=linklist><ul>";
$link = getLink($linkid);
print "</div>";

print(printLink($link));
$comments = getComments($_GET[linkid]);
commentTree($comments);

//print "<div class=linklist><ul>";


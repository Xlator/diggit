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

printHeader();
print "<div class=linklist><ul>";
$link = getLink($linkid);
print "<ul class=comments>";

print(printLink($link));
include "forms/comment.html";
$comments = getComments($_GET[linkid]);
commentTree($comments);

//print "<div class=linklist><ul>";


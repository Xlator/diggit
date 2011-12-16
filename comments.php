<?php
session_start();
if(!isset($_SESSION[id])) { $_SESSION[id] = 0; }

require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/common.php");
require("functions/comments.php");

printHeader();
print "<pre>";
$comments = getComments($_GET[linkid]);
commentTree($comments);

//print "<div class=linklist><ul>";


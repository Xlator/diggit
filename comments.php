<?php
session_start();
$_SESSION[id] = intval($_SESSION[id]);
if(!isset($_SESSION[id])) { $_SESSION[id] = 0; }

require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/common.php");
require("functions/comments.php");
require("functions/forms.php");

$linkid = unbase36($_GET[linkid]);
if(!linkIdExists($linkid)) { header("Location: ./"); } // If the link id is invalid, return to index

checkLogin($_SESSION[id]);
if($_POST) { // Post a comment
	$commentid = sendComment($_POST);
	header("Location: comments.php?linkid=$_GET[linkid]#$commentid");
}

$link = getLink($linkid);

// Category name in the header
if($link[category] != "main") { $_GET[category] = $link[category]; }

printHeader();
print "<div class=linklist><ul>";
print "<ul class=comments>";
print(printLink($link)); // Print the link
print "<li style=margin-bottom:1em;>";
print(commentform(0)); // Top comment form
print "</li>";
$comments = getComments($linkid);
commentTree($comments[0]);
print "</ul></ul></div>";

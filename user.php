<?php
session_start();
$_SESSION[id] = intval($_SESSION[id]);
if(!isset($_SESSION[id])) { $_SESSION[id] = 0; }
require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/common.php");
require("functions/comments.php");
require("functions/user.php");
require("functions/forms.php");
if(isset($_GET[name])) { $_GET[id] = getUserId($_GET[name]); }
if(intval($_GET[id]) == 0) { header("Location:".PREFIX); }
$linkid = intval($_GET[linkid]);

checkLogin($_SESSION[id]);

printHeader();
$user = getUser($_GET[id]);
printUser($user);

print "<div class=linklist>";
if($_GET[type] == "comments") {
	$comments = getComments(0,$_GET[id],$_GET[page],LINKS_PER_PAGE);
	$pagination = pagination($comments[page],$comments[totalpages],"links");
	print($pagination);
	print "<ul class=comments>";
	
	if(!empty($comments[0])) {	
		foreach($comments[0] as $comment) {
			print(printComment($comment,0));
		}
	}
}

else {
	$links = getLinks($_GET[page],LINKS_PER_PAGE,"",$_GET[id]);
	$pagination = pagination($links[page],$links[totalpages],"links");

	print($pagination);
	print "<ul>";
	
	if(!empty($links[0])) {	
		foreach($links[0] as $link) {
		print(printLink($link));
		}
	}
}

print "</ul>";
print($pagination);
print "</div>";

/*
print("<pre>");
print_r($_SERVER);
print_r(parse_url($_SERVER[REQUEST_URI]));
print("</pre>");
*/

<?php
session_start();
if(!isset($_SESSION[id])) { $_SESSION[id] = 0; }
if(intval($_GET[id]) == 0) { header("Location: ./"); }
$linkid = intval($_GET[linkid]);
require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/common.php");
require("functions/comments.php");
require("functions/forms.php");
printHeader();
print "<header class=userprofile>";
$user = getUser($_GET[id]);
print "<h2>$user[username]</h2>";
print " <span class=regtime>registered " . timeSince($user[registered]) . " - $user[points] points</span><br />";
print "<nav>";
print "<ul class=userinfo>";
print "<li><a href=user.php?id=$_GET[id]>$user[links] links</a></li>";
print "<li><a href=user.php?id=$_GET[id]&type=comments>$user[comments] comments</a></li>";
print "</ul>";
print "</nav>";
print "</header>";

print "<div class=linklist>";
if($_GET[type] == "comments") {
	$comments = getComments(0,$_GET[id],$_GET[page],LINKS_PER_PAGE);
	$pagination = pagination($comments[page],$comments[totalpages],"links");
	print($pagination);
	print "<ul class=comments>";
	
	foreach($comments[0] as $comment) {
		print(printComment($comment,0));
	}
}

else {
$links = getLinks($_GET[page],LINKS_PER_PAGE,"",$_GET[id]);
$pagination = pagination($links[page],$links[totalpages],"links");

print($pagination);
print "<ul>";

	foreach($links[0] as $link) {
	print(printLink($link));
	}

}
print "</ul>";
print($pagination);
print "</div>";



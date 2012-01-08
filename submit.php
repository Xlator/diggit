<?php 
session_start();
$_SESSION[id] = intval($_SESSION[id]);
require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/forms.php");
require("functions/common.php");
if($_SESSION[id] == 0) { header("Location: ".PREFIX); } // Redirect to index if we aren't logged in.
checkLogin($_SESSION[id]);
printHeader();

if($_POST) {
	$input = cleanLink($_POST); // Sanitize the input
		
	if(!linkErrors($input)) { // Post/edit the link and redirect to the comment page
		$linkid = sendLink($input);
		if($_POST[edit]) { $linkid = intval($_POST[edit]); }
		if(REWRITE == 'on') { header("Location: ".PREFIX."category/$input[cat]/comments/$linkid"); }
		else { header("Location: ".PREFIX."comments.php?category=$input[cat]&linkid=$linkid"); }
	}	
	$errors = linkErrors($input); // Output error messages
}
print "<section class=loginform>";
print(linkform($errors,$input));

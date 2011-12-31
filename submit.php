<?php 
session_start();
$_SESSION[id] = intval($_SESSION[id]);
if($_SESSION[id] == 0) { header("Location: ./"); } // Redirect to index if we aren't logged in.
require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/forms.php");
require("functions/common.php");
printHeader();

if($_POST) {
	$input = cleanLink($_POST); // Sanitize the input
		
	if(!linkErrors($input)) { // Post/edit the lik and redirect to the comment page
		$linkid = sendLink($input);
		if($_POST[edit]) { $linkid = intval($_POST[edit]); }
		header("Location: comments.php?linkid=$linkid");
	}	
	$errors = linkErrors($input); // Output error messages
}
print "<section class=loginform>";
print(linkform($errors,$input));

<?php 
session_start();
require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/forms.php");
require("functions/common.php");
printHeader();

if($_POST) {
	$input = cleanLink($_POST); // Sanitize the input
		
	if(!linkErrors($input)) {
		$linkid = sendLink($input);
		if($_POST[edit]) { $linkid = intval($_POST[edit]); }
		header("Location: comments.php?linkid=$linkid");
	}	
	$errors = linkErrors($input);
}

print(linkform($errors,$input));

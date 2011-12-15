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
		header("Location: comments.php?link=$linkid");
	}	
	$errors = linkErrors($input);
}

linkform($errors,$input);

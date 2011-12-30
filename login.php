<?php
session_start();
require("config.php");
require("functions/db.php");
require("functions/user.php");
require("functions/links.php");
require("functions/forms.php");
require("functions/common.php");
printHeader();
if($_POST[action] == "register") {
	if(!registrationErrors($_POST)) {
		$userid = registerUser($_POST);
		$_SESSION[id] = $userid;
		$_SESSION[username] = $_POST[username];
		header("Location: ./");
	}
	$errors = registrationErrors($_POST);
	$input = cleanLink($_POST);
}

if($_POST[action] == "login") {
	if(!loginErrors($_POST)) {
		$userid = getUserid($_POST[username]);
		$_SESSION[id] = $userid;
		$_SESSION[username] = $_POST[username];
		header("Location: ./");
	}
	$errors = loginErrors($_POST);
	$input = cleanLink($_POST);
}

loginform($errors,$input);
regform($errors,$input);

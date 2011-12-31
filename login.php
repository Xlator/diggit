<?php
session_start();
$_SESSION[id] = intval($_SESSION[id]);
require("config.php");
require("functions/db.php");
require("functions/user.php");
require("functions/links.php");
require("functions/forms.php");
require("functions/common.php");

printHeader();

if($_POST[action] == "register") { // Register
	if(!registrationErrors($_POST)) {
		$userid = registerUser($_POST);
		// Log in and redirect to index after registering
		$_SESSION[id] = $userid;
		session_regenerate_id();
		login(session_id());
		header("Location: ./");
	}
	$errors = registrationErrors($_POST);
	$input = cleanLink($_POST);
}

if($_POST[action] == "login") { // Login
	if(!loginErrors($_POST)) {
		$userid = getUserid($_POST[username]);
		$_SESSION[id] = $userid;
		session_regenerate_id();
		login(session_id());
		header("Location: ./");
	}
	$errors = loginErrors($_POST);
	$input = cleanLink($_POST);
}

// Print login and registration forms
loginform($errors,$input);
regform($errors,$input);

<?php

/* Password hashing functions */

// Generate a hash salt
function generateSalt($length = 128) {	
	// List characters
	$characters = "abcdef0123456789";
	
	for($i=0;$i < $length;$i++) {
		//Pick a random character from the string and add it to the salt
		$salt .= $characters{mt_rand(0,strlen($characters)-1)};
	}
	return $salt;	 
}

// Hash a password
function hashPassword($password,$salt) {
	$hash = hash("sha512",$salt . $password);
	$saltpass = $salt . $hash; // Prepend the salt to the hash for storage
	return($saltpass);
}

// Check an entered password against a stored hash
function validatePassword($password,$storedhash) {
	$salt = substr($storedhash,0,128);
	$hash = hashPassword($password,$salt);
	if ($hash  == $storedhash) { return(true); }
	else { return(false); }
}

// Validate registration data, return array of errors (if any), else return empty array
function registrationErrors($input) {
	
	$re = "/^[A-Za-z0-9\-_]{3,16}$/";
	if(!preg_match($re,trim($input[username]))) { $error[username] = "Invalid username"; }
	elseif(userExists("username",trim($input[username]))) { $error[username] = "That username is already registered"; }
		
	$valid = "!@#$%*\^()_\w\d"; // Valid characters for passwords
	$re = "/^[$valid]*(?=.{8,})(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[$valid]*$/";
	if(!preg_match($re,$input[password])) { $error[password] = "Invalid password"; }
	elseif($input[password] != $input[confirm_password]) { $error[password] = "Password and confirmation do not match"; }
		
	if(trim($input[email]) != "" && !filter_var(trim($input[email]),FILTER_VALIDATE_EMAIL)) { 
		$error[email] = "Invalid email address";
	}
	
	elseif(userExists("email",trim($input[email]))) { $error[email] = "That email address is already registered"; }

	if(isset($error)) { return($error); }
	else { return(false); }

}

function loginErrors($input) {
	if(!userExists('username',$input[username])) { $error[username] = "No such user"; }
	else { 
		$storedhash = getPassword($input[username]);
		if(!validatePassword($input[password],$storedhash)) { $error[password] = "Incorrect password"; }
	}
	if(isset($error)) { return($error); }
	else { return(false); }
}	

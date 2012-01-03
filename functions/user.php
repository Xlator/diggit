<?php

/* --------- Password hashing and comparison functions --------- */

function generateSalt($length = 128) { // Generate a hash salt
	// List characters
	$characters = "abcdef0123456789";
	
	for($i=0;$i < $length;$i++) {
		//Pick a random character from the string and add it to the salt
		$salt .= $characters{mt_rand(0,strlen($characters)-1)};
	}
	return $salt;	 
}

function hashPassword($password,$salt) { // Hash a password
	$hash = hash("sha512",$salt . $password);
	$saltpass = $salt . $hash; // Prepend the salt to the hash for storage
	return($saltpass);
}

function validatePassword($password,$storedhash) { // Check an entered password against a stored hash
	$salt = substr($storedhash,0,128);
	$hash = hashPassword($password,$salt);
	if ($hash  == $storedhash) { return(true); }
	else { return(false); }
}

/* --------- Validation functions --------- */

function registrationErrors($input) { // Validate registration data, return array of errors (if any), else return false
	
	$username_re = "/^[A-Za-z0-9\-_]{3,16}$/"; // Letters, numbers, hyphens and underscores only, 3-16 characters
	if(!preg_match($username_re,trim($input[username]))) { $error[username] = "Invalid username"; }
	elseif(userExists("username",trim($input[username]))) { $error[username] = "That username is already registered"; }
		
	$valid = "!@#$%*\^()_\w\d"; // Valid characters for passwords
	$password_re = "/^[$valid]*(?=.{8,})(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[$valid]*$/"; // At least 8 characters, at least one number, one lowercase letter and one capital
	if(!preg_match($password_re,$input[password])) { $error[password] = "Invalid password"; }
	elseif($input[password] != $input[confirm_password]) { $error[password] = "Password and confirmation do not match"; }
		
	if(trim($input[email]) != "") { // E-mail address is not compulsory, but must be valid and unique if entered
		if(!filter_var(trim($input[email]),FILTER_VALIDATE_EMAIL)) { $error[email] = "Invalid e-mail address"; }
		if(userExists("email",trim($input[email]))) { $error[email] = "That e-mail address is already registered"; }
	}
	
	if(isset($error)) { return($error); }
	else { return(false); }

}

function loginErrors($input) { // Check for errors in the login form
	if(!userExists('username',$input[username])) { $error[username] = "No such user"; }
	
	else { 
		$storedhash = getPassword($input[username]);
		if(!validatePassword($input[password],$storedhash)) { $error[password] = "Incorrect password"; }
	}
	
	if(isset($error)) { return($error); }
	else { return(false); }
}	

/* --------- User info output functions --------- */

function printUser($user) { // Output user information
	$template = file_get_contents("templates/userinfo.html");

	$user[links] .= ($user[links] == 1) ? ' link' : ' links';
	$user[comments] .= ($user[comments] == 1) ? ' comment' : ' comments';
	$user[points] .= ($user[points] == 1 || $user[points] == -1) ? ' point' : ' points';

	$placeholders = array(
				"USERID" => $_GET[id],
				"USERNAME" => $user[username],
				"REGTIME" => timeSince($user[registered]),
				"LINKS" => buildLink("user.php?id=$user[id]",$user[links]),
				"COMMENTS" => buildLink("user.php?id=$user[id]&type=comments",$user[comments]),
				"POINTS" => $user[points]
			);

	foreach($placeholders as $p => $value) {
		$template = str_replace("{".$p."}",$value,$template);
	}
	print($template);
}

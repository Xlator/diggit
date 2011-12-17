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

/* Validation functions */

function registrationErrors($input) { // Validate registration data, return array of errors (if any), else return empty array
	
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
	
	/*$privkey = recaptchaKey('private'); // Get the API key from the database
	$result = recaptcha_check_answer($privkey, $_SERVER[REMOTE_ADDR], $_POST[recaptcha_challenge_field], $_POST[recaptcha_response_field]);
	if(!$result->is_valid) { $error[recaptcha] = $result->error; }
	
	//$error[recaptcha] = recaptchaValidate($input[recaptcha_challenge_field],$input[recaptcha_response_field]);
	*/
	if(isset($error)) { return($error); }
	else { return(false); }
}	

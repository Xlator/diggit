<?php

/* --------- Form output functions --------- */

function linkform($errors=array(),$input=array()) { // Outputs the link submission form with error messages and input from previous attempt (if any)
	$form = file_get_contents("forms/link.html");
	if(!empty($errors)) { // Replace placeholders with error messages and post back input
		
		foreach($errors as $err => $str) {
			$placeholder = "{" . strtoupper($err) . "-ERR}";
			$form = str_replace($placeholder,$str,$form);
		}	

	}
	
	if(!empty($input)) { 
		foreach($input as $field => $data) {
			$placeholder = "{" . strtoupper($field) . "}";
			$form = str_replace($placeholder,$data,$form);
		}
	}
	
	if(!isset($input[nsfw]) || $input[nsfw] == 0) { // Make sure the NSFW checkbox is correct (off by default)
		$form = str_replace("nsfw checked","nsfw",$form);
	}

	if($categories = getCategories()) {
		foreach($categories as $cat) {
			$cats .= "<a href='#' onClick=getElementById('catbox').value=('$cat[name]');>$cat[name]</a> ";
		}

		$form = str_replace("{CATS}",$cats,$form);

	}	
		
	// remove placeholders where there are no errors or input 
	$form = preg_replace("/\{[A-Z-]+\}/","",$form);
	return($form);	
	//return($form);
}

function loginform($errors=array(),$input=array()) { // Outputs the login form with error messages and input from previous attempt (if any)
	$form = file_get_contents("forms/login.html");
	if($input[action] == "login") { 
		foreach($errors as $err => $str) {
			$placeholder = "{" . strtoupper($err) . "-ERR}";
			$form = str_replace($placeholder,$str,$form);
		}
		$form = str_replace("{USERNAME}",$input[username],$form);
	}

	$form = preg_replace("/\{[A-Z-]+\}/","",$form);
	
	print($form);
}

function regform($errors=array(),$input=array()) { // Outputs the registration form with error messages and input from previous attempts (if any)
	$form = file_get_contents("forms/register.html");
	if($input[action] == "register") {
		foreach($errors as $err => $str) {
			$placeholder = "{" . strtoupper($err) . "-ERR}";
			$form = str_replace($placeholder,$str,$form);
		}

		foreach($input as $field => $data) {
			$placeholder = "{" . strtoupper($field) . "}";
			$form = str_replace($placeholder,$data,$form);
		}
	}

	$form = preg_replace("/\{[A-Z-]+\}/","",$form);

	print($form);
}

function commentform($id=0,$hide=0) {
	$form = file_get_contents("forms/comment.html");
	if($_SESSION[id] != 0) { // Make sure we're logged in
		$form = str_replace("{ID}",$id,$form);
		$form = str_replace("{LINKID}",$_GET[linkid],$form);
		if($hide == 1) { $form = str_replace("{HIDE}","style=display:none;",$form); }
		$form = preg_replace("/\{[A-Z-]+\}/","",$form);
		return($form);
	}
}


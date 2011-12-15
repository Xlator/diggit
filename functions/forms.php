<?php

function linkform($errors=array(),$input=array()) {
	$form = file_get_contents("forms/link.html");
	
	if(!empty($errors)) { // Replace placeholders with error messages and post back input
		
		foreach($errors as $err => $str) {
			$placeholder = "{" . strtoupper($err) . "-ERR}";
			$form = str_replace($placeholder,$str,$form);
		}	
		
		foreach($input as $field => $data) {
			$placeholder = "{" . strtoupper($field) . "}";
			$form = str_replace($placeholder,$data,$form);
		}
	}
	
	if(!isset($input[nsfw])) { // Make sure the NSFW checkbox is correct (off by default)
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
	print($form);	
	//return($form);
}

function loginform($errors=array(),$input=array()) {
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

function regform($errors=array(),$input=array()) {
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


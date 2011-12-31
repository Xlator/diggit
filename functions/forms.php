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
			$cats .= "<a href=# class=catselect>$cat[name]</a> ";
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

function commentform($id=0,$hide=0) { // Outputs a comment form for the given comment id, optionally hidden
	$form = file_get_contents("forms/comment.html");
	if(intval($_SESSION[id]) != 0) { // Make sure we're logged in
		if($hide == 1) { $hide = "style=display:none;"; }
			
		$placeholders = array("ID" => $id, "LINKID" => $_GET[linkid], "HIDE" => $hide,
				      "FORMATTING" => file_get_contents("templates/comment-formatting.html"));
		
		foreach($placeholders as $p => $value) {
			$form = str_replace("{".$p."}",$value,$form);
		}
		return($form);
	}
}


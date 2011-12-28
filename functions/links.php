<?php

/* --------- Link input validation/cleaning functions --------- */

function linkErrors($input) { // Takes an array of link input, returns array of errors (if any) or false on no errors.
	if(trim($input[title]) == "") { $error[title] = "You must enter a link title"; }
	if(!filter_var(trim($input[url]),FILTER_VALIDATE_URL)) { $error[url] = "You must enter a valid URL"; }
	
	else { // Check if the hostname ends in a tld, otherwise return error.
		$parsed_url = parse_url($input[url]);
		$re = "/\.[a-z]{2,4}$/";
		if(!preg_match($re,$parsed_url[host])) { $error[url] = "You must enter a valid URL"; }
		// If the link already exists, return error.
		elseif(linkExists($input[url]) && intval($input[edit]) == 0) { $error[url] = "This link has already been posted"; }
	}
		
	if(isset($error)) { return($error); }
	else { return(false); }
}

function cleanLink($input) { // Takes an array of link data, returns same but sanitized.
	// Prefix URL with http:// if it was missing from the input
	if(trim($input[url]) != "" && !preg_match("#^https?://.*$#",$input[url])) { $input[url] = "http://" . $input[url]; } 	
		
		$curly = array("{","}");
		$curly_replace = array("&#123;","&#125;");
		
		foreach($input as $field => $data) {
			$input[$field] = filter_var(trim($data),FILTER_SANITIZE_SPECIAL_CHARS);
			$input[$field] = str_replace($curly,$curly_replace,$input[$field]); // Replace curly brackets (needed for placeholders to work)
				
		if($field == "url") {
			$input[url] = str_replace("&#38;","&",$input[url]); // Put &s back into URL
		
		}
	}	
	return($input);
}

/* --------- Link output ---------*/

function printLink($link) { // Prints a link
	$template = file_get_contents("templates/link.html");
	$nsfw = "";
	$time = timeSince($link[time]);
	if($link[nsfw] == 1) { $nsfw = "<strong class=nsfw>:: NSFW ::</strong>"; }
	else { $nsfw = "<strong class=nsfw style=display:none;>:: NSFW ::</strong>"; }
	if($link[points] == NULL) { $link[points] = 0; }
	if($link[points] == 1 || $link[points] == -1) { $p = "point"; }
	else { $p = "points"; }
	$points = "$link[points] $p";
	$vote = getMyVote($_SESSION[id],$link[id],'link');
	$arrows = voteArrows($vote,$link[id]);
	
	switch($link[comments]) {
		case 0:
			$comments = "<a href=comments.php?linkid=$link[id]>comment</a>";
		break;
		case 1:
			$comments = "<a href=comments.php?linkid=$link[id]>1 comment</a>";
		break;
		default:
			$comments = "<a href=comments.php?linkid=$link[id]>$link[comments] comments</a>";
			break;
	}
	
	//If we are logged in, show edit/delete/nsfw buttons on own links
	if($_SESSION[id] == $link[user]) {	
		if($link[nsfw] == 0) { $nsfwlink = "<a class='nsfw' href=# id=$link[id]>nsfw?</a>"; }
		else { $nsfwlink = "<a class='nsfw on' href=# id=$link[id]>sfw?</a>"; }
		$buttons = "$nsfwlink 
			   <a class=linkedit href=#>edit</a>
			   <a class=linkdel href=edit.php?id=$link[id]&delete=1>delete</a> <span style=display:none;float:left;><a class='linkdel no' href=#>no</a><a class='linkdel yes' href=# id=$link[id]>yes</a></span>";
		$input = array("edit" => $link[id], "title" => $link[title], "cat" => $link[category], "url" => $link[link], "nsfw" => $link[nsfw]);
		$editform = linkform(array(),$input);
	}
	else { $buttons=""; }

	$placeholders = array("TITLE" => $link[title], "URL" => $link[link],
		         "DOMAIN" => $link[domain], "USER" => getUsername($link[user]),
			 "POINTS" => $points, "CAT" => $link[category],
			 "NSFW" => $nsfw, "TIME" => $time, "BUTTONS" => $buttons,
			 "COMMENTS" => $comments, "ID" => $link[id],
			 "ARROWS" => $arrows, "EDITFORM" => $editform);

	
	foreach($placeholders as $p => $value) {
		$template = str_replace("{".$p."}",$value,$template);
	}
	return($template);
}
?>

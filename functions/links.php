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
	$time = timeSince($link[time]);

	if($link[nsfw] == 1) { $nsfw = "<strong class=nsfw>:: NSFW ::</strong>"; }
	else { $nsfw = "<strong class=nsfw style=display:none;>:: NSFW ::</strong>"; }
	
	if($link[points] == NULL) { $link[points] = 0; }
	$link[points] .= ($link[points] == 1 || $link[points] == -1) ? ' point' : ' points';

	$myvote = 0;
	if($link[myvote]) { $myvote = $link[myvote]; }
		
	if($_SESSION[lastvisited] == $link[id]) { $last = "class=lastvisited"; }
	
	$arrows = voteArrows($myvote,$link[id]);
	
	switch($link[comments]) {
		case 0:
			$commentstext = "comment";
			break;
		case 1:
			$commentstext = "1 comment";
			break;
		default:
			$commentstext = "$link[comments] comments";
			break;
	}

	if($link[category] != "main") { // Only show the category if it isn't 'main'
		$cat = "to ".buildLink("?category=$link[category]",$link[category],"linkcat"); 
	}
	
	// Build the links for standard stuff	
	$hlink = "<a href='$link[link]' id=$link[id]>$link[title]</a>"; 
	$user = buildLink("user.php?id=$link[user]",$link[username]);
	$domain = buildLink("?domain=$link[domain]",$link[domain],"domain"); 
	$comments = buildLink("comments.php?linkid=$link[id]",$commentstext);

	if($link[category] != "main") { // Only show the category if it isn't 'main'
		$cat = "to ".buildLink("?category=$link[category]",$link[category],"linkcat"); 
	}

	//If we are logged in, show edit/delete/nsfw buttons on own links
	if(intval($_SESSION[id]) == $link[user]) {	
		
		if($link[nsfw] == 0) { $nsfwtext="nsfw?"; $nsfwclass = "nsfw"; } 
		else { $nsfwtext = "sfw?"; $nsfwclass = "nsfw on"; } 
		$nsfwtoggle = buildLink("#",$nsfwtext,$nsfwclass);
		$edit = buildLink("#","edit","linkedit");
		$delete = buildLink("#","delete","linkdel") . "
			<span style=display:none;float:left;>
			" . buildLink("#","no","linkdel no") 
			  . buildLink("#","yes","linkdel yes") . "
			  </span>";
		$buttons =  "$nsfwtoggle $edit $delete";

		// Populate and create the link edit form
		$input = array("edit" => $link[id], "title" => $link[title], "cat" => $link[category], "url" => $link[link], "nsfw" => $link[nsfw]);
		$editform = linkform(array(),$input);
	}

	$placeholders = array("LINK" => $hlink,
		         "DOMAIN" => $domain, "USER" => $user, "USERID" => $link[user],
			 "POINTS" => $link[points], "CAT" => $cat,
			 "NSFW" => $nsfw, "TIME" => $time, "BUTTONS" => $buttons,
			 "COMMENTS" => $comments, "ID" => $link[id],
			 "ARROWS" => $arrows, "EDITFORM" => $editform,
		 	 "LAST" => $last);

	
	foreach($placeholders as $p => $value) {
		$template = str_replace("{".$p."}",$value,$template);
	}
	return($template);
}
?>

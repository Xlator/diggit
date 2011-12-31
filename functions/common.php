<?php

/* --------- Common functions (used throughout the site) --------- */

function timeSince($mysqltimestamp) { // returns time since given timestamp, rounded to the largest whole unit (e.g 3 hours 12 minutes ~ 3 hours)
	$time = strtotime($mysqltimestamp);
	$diff = time() - $time;
	if($diff == 0) { return("Just now"); }
	$units = array("seconds","minutes", "hours", "days", "weeks", "months", "years","decades");
	$periods = array(1,60,60,24,7,4.35,12,10);
	
	for($j= 0; $diff >= $periods[$j] && $j < count($periods)-1; $j++) {
		$diff /= $periods[$j]; 
	}	
	$j--;
	$diff = round($diff); 
	$unit = $units[$j];
	if($diff == 1) { $unit = substr($unit,0,-1); }
	return("$diff $unit ago");
}

function printHeader() { // Outputs the site header
	$header = file_get_contents("templates/header.html");
	$points = getMyPoints($_SESSION[id]);
	if($points == NULL) { $points = 0; }
	if($_SESSION[id]==0) { $login = "<a href=login.php class=login>login/register</a>"; 
				$submit = ""; }
	else { $login = "<a href=user.php?id=$_SESSION[id] class=login>".getUsername($_SESSION[id])." ($points)</a> <a href=./?logout=1 class=login>logout</a>"; 
		$submit = "<a href=submit.php>submit a link</a>"; }
			
	$q = "?";		
	if($_GET[category] != NULL) {
		$q = "?category=$_GET[category]&";
		$cat = "<a class=headercat href=./?category=$_GET[category]>$_GET[category]</a> <strong>::</strong>";
		$title = ":: $_GET[category]";
	}
	elseif($_GET[domain] != NULL) {
		$q = "?domain=$_GET[domain]&";
		$domain = "<a class=headercat href=./?domain=$_GET[domain]>$_GET[domain]</a> <strong>::</strong>";
		$title = ":: $_GET[domain]";
	}
	elseif(isset($_GET[id])) {
		$title = ":: ".getUsername($_GET[id]);
	}
	
	$placeholders = array("USERID" => $_SESSION[id], "LOGIN" => $login, "SUBMIT" => $submit, 
			      "NEW" => " <a href=./$q" . "order=new>what's new?</a> ",
			      "HOT" => " <a href=./$q" . "order=hot>what's hot?</a> ",
			      "TOP" => " <a href=./$q" . "order=top>most popular</a> ",
		      	      "CATEGORY" => $cat, "DOMAIN" => $domain, "TITLE" => $title);

	foreach($placeholders as $p => $value) {
		$header = str_replace("{".$p."}",$value,$header);
	}
	print($header);
}

function voteArrows($myvote,$subjectid) { // Outputs the appropriate voting arrows depending on the logged in user's vote
	$arrows = "<div class='vote'>\n";
	if($_SESSION[id] != 0) { 
		if($myvote == 0) {	
			$arrows .= "<div class=\"arrowup \" id=$subjectid style=' '></div>\n";
			$arrows .= "<div class=\"arrowdown \" id=$subjectid style=' '></div>\n";
		}
		if($myvote == 1) {	
			$arrows .= "<div class=\"arrowup upvote\" id=$subjectid style=' '></div>\n";
			$arrows .= "<div class=\"arrowdown \" id=$subjectid style=' '></div>\n";
		}
		if($myvote == -1) {	
			$arrows .= "<div class=\"arrowup \" id=$subjectid style=' '></div>\n";
			$arrows .= "<div class=\"arrowdown downvote\" id=$subjectid style=' '></div>\n";
		}
	}
	$arrows .= "</div>";
	return($arrows);
}	

function pagination($page, $totalpages) {
	if(intval($page) == 0) { return(false); }
	$request = parse_url($_SERVER[REQUEST_URI]);
	if(!isset($request[query])) { $q = "?page="; }
	else { // Make sure the query string is properly formed
		$request[query] = preg_replace("/(page=[\d]+)/","",$request[query]); // Remove "page" from query string
		$request[query] = preg_replace("/^(&)/","",$request[query]); // Remove any & at the beginning of the query string
		$request[query] = preg_replace("/(&(?=&))/","",$request[query]); // Remove duplicate &s
		$request[query] = preg_replace("/(&)$/","",$request[query]); // Remove any & at the end of the query string
		$q = "?$request[query]&page="; 
	}
	$out .= "<span class=pagination>Page $page | ";
	if($page > 1) { $out .= sprintf("<a href=$q%s>Previous page</a> ",$page - 1); }
	if($page < $totalpages) { $out .= sprintf("<a href=$q%s>Next page</a>",$page+1); }
	$out .= "</span>";	
	return($out);
}

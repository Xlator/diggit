<?php

function timeSince($mysqltimestamp) {
	$time = strtotime($mysqltimestamp);
	$diff = time() - $time;
	$units = array("seconds","minutes", "hours", "days", "weeks", "months", "years");
	$periods = array(1,60,60,24,7,4.35,12);
	
	for($j= 0; $diff >= $periods[$j] && $j < count($periods)-1; $j++) {
		$diff /= $periods[$j]; 
	}	
	$j--;
	$diff = round($diff); 
	$unit = $units[$j];
	if($diff == 1) { $unit = substr($unit,0,-1); }
	return("$diff $unit ago");
}

function printHeader() {
	$header = file_get_contents("templates/header.html");
	$points = getMyPoints($_SESSION[id]);
	if($_SESSION[id]==0) { $login = "<a href=login.php class=login>login/register</a>"; 
				$submit = ""; }
	else { $login = "<a href=user.php?id=$_SESSION[id] class=login>$_SESSION[username] ($points)</a> <a href=?logout=1 class=login>logout</a>"; 
		$submit = "<a href=submit.php>submit a link</a>"; }
	
	$placeholders = array("USERID" => $_SESSION[id], "LOGIN" => $login,
			      "SUBMIT" => $submit);
	foreach($placeholders as $p => $value) {
		$header = str_replace("{".$p."}",$value,$header);
	}
	print($header);
}

function voteArrows($myvote,$subjectid) {
	$arrows = "<div class=vote>\n";
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

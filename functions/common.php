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
	$_SESSION[id] = intval($_SESSION[id]);
	$header = file_get_contents("templates/header.html");
	$points = getMyPoints($_SESSION[id]);
	$cats = getCategories(5);
	$categories = " <strong style=margin-left:2em;> :: top categories :: </strong>";
	foreach($cats as $c) {
		$categories .= buildLink("?category=$c[name]",$c[name])." "; 
	}

	if($_SESSION[id]==0) { $login = buildLink("login.php","log in/register","login"); }
	else { 
		$login = buildLink("?logout=1","log out","login") . "::" .  
			 buildLink("user.php?id=$_SESSION[id]",getUsername($_SESSION[id])." ($points)","login"); 
		$submit = buildLink("submit.php","submit a link"); 
	}
			
	if($_GET[category] != NULL) { // Are we in a category?
		$q[category] = $_GET[category];
		$title = "$_GET[category]";
	}
	elseif($_GET[domain] != NULL) { // Are we in a domain?
		$q[domain] = $_GET[domain];
		$title = "$_GET[domain]";
	}
	elseif(isset($_GET[id])) { // Are we on a user page?
		$q[id] = $_GET[id];
		$title = getUsername($_GET[id]);
		$titleurl = "users.php";
	}
	
	$sitetitlelink = buildLink(""," :: " . SITE_TITLE . " ::","diggit")."";
	if(!empty($q)) { // Create the page title link 
		$qs = buildQueryString($q);
		$titlelink = buildLink($titleurl."?".$qs,$title,"headercat")."::";
		$title = ":: $title";
		unset($q[id]);	
	}
	
	$new = buildLink($index."?".buildQueryString($q,"order=new"),"what's new?");
	$hot = buildLink($index."?".buildQueryString($q,"order=hot"),"what's hot?");
	$top = buildLink($index."?".buildQueryString($q,"order=top"),"most popular");

	$placeholders = array("USERID" => $_SESSION[id], "LOGIN" => $login, "SUBMIT" => $submit, 
			      "NEW" => $new, "HOT" => $hot, "TOP" => $top,
			      "CATEGORY" => $cat, "DOMAIN" => $domain, "PAGE_TITLE" => $title, "TITLE_LINK" => $titlelink,
			      "SITE_TITLE" => SITE_TITLE, "SITE_TITLE_LINK" => $sitetitlelink, "CATS" => $categories, "PREFIX" => PREFIX);

	foreach($placeholders as $p => $value) {
		$header = str_replace("{".$p."}",$value,$header);
	}
	print($header);
}

function voteArrows($myvote,$subjectid) { // Outputs the appropriate voting arrows depending on the logged in user's vote
	$_SESSION[id] = intval($_SESSION[id]);
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

function pagination($page, $totalpages) { // Pagination function, takes page info from getLinks() / getComments()
	if(intval($page) == 0) { return(false); }
	parse_str($_SERVER[QUERY_STRING],$query);
	
	if(preg_match("#^".PREFIX."([a-z]+\.php)$#",$_SERVER[SCRIPT_NAME],$match)) {
		$filename = $match[1];
	}

	if($page > 1) { 
		$query[page] = $page - 1;
		$prevpage = buildLink($filename."?".buildQueryString($query),"Previous page"). "| ";
	}
	if($page < $totalpages) { 
		$query[page] = $page + 1;
		$nextpage = " | " . buildLink($filename."?".buildQueryString($query),"Next page");
	}
	return("<span class=pagination>$prevpage"."Page $page"."$nextpage</span>");
}

function buildQueryString($qs,$suffix=NULL) { // Takes an array from parse_str, returns a query string
	if(!empty($qs)) {
		foreach($qs as $k => $v) {
			$querystring .= "$k=$v&";
		}
	}

	return(trim($querystring.$suffix,"&"));
}

function buildLink($url="#", $text="link", $class=NULL, $id=NULL, $name=NULL) { // Construct an internal hyperlink
	if($class != NULL) { $class=" class='$class'"; }
	if($id != NULL) { $id=" id='$id'"; }
	if($name != NULL) { $name=" name='name'"; }
	return("<a href='".PREFIX."$url'$id$class$name>$text</a>");
}

function spanHide() { // Returns a hidden span with the contents of all arguments in order
	$args = func_get_args();
	return("<span style=display:none;>".implode(" ",$args)."</span>");
}

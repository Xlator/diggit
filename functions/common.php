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
		$username = getUsername($_SESSION[id]);
		$login = buildLink("?logout=1","log out","login") . "::" .  
			buildLink("user.php?name=$username","$username ($points)","login"); 
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
		$titleurl = "user.php";
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
				if(REWRITE == 'on' && $url != "#") { $url = simplifyURL($url); }
					return("<a href='".PREFIX."$url'$id$class$name>$text</a>");
}

function simplifyURL($url) { // Rewrite a query string to a static URL
	$url = parse_url($url);
	parse_str($url[query],$qs);
	if($url[path] == PREFIX || !isset($url[path])) { // Is the link pointing to the index?
		if(isset($qs[category])) { $simple_url .= "category/$qs[category]/"; }
		elseif(isset($qs[domain])) { $simple_url .= "domain/$qs[domain]/"; }
			if(isset($qs[order])) { $simple_url .= "$qs[order]/"; }
				if(isset($qs[logout])) { $simple_url .= "logout/"; }
	}

	elseif($url[path] == "comments.php") { // Is the link pointing to a comments page?
		if(isset($qs[category])) { $simple_url .= "category/$qs[category]/"; }
			$simple_url .= "comments/$qs[linkid]/";
		if(isset($qs[title])) { $simple_url .= "$qs[title]"; }
	}

	elseif($url[path] == "user.php") { // Is the link pointing to a user page?
		if(!isset($qs[type]) || $qs[type] == "links") { $simple_url .= "user/$qs[name]/links/"; }
		elseif($qs[type] == "comments") { $simple_url .= "user/$qs[name]/comments/"; }
	}

	elseif($url[path] == "login.php") { $simple_url .= "login/"; }
	elseif($url[path] == "submit.php") { $simple_url .= "submit/"; }

		if(isset($qs[page])) { $simple_url .= "p$qs[page]/"; }

			return($simple_url);
}

function spanHide() { // Returns a hidden span with the contents of all arguments in order
	$args = func_get_args();
	return("<span style=display:none;>".implode(" ",$args)."</span>");
}

function urlTitle($str) { // Sanitize link title for URL
	// Characters to translate
	$invalid = array('Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z',
		'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A',
		'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E',
		'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
		'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
		'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a',
		'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i',
		'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
		'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
		'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', "@" => "at");

	$hyphen = array("_","+","=","/","&",":",";","."," "); // Replace with hyphens
	$strip = array("$","<",">","?","!","{","}","[","]","`","'","%","^","\"","'","#39"); // Remove
	$str = str_replace(array_keys($invalid), array_values($invalid), html_entity_decode($str));
	$str = str_replace($hyphen, "-", $str);
	$str = str_replace($strip, "", $str);
	$str = preg_replace("/[-]+/","-",$str); // Remove double hyphens
	return(trim($str,"-"));
}

function unbase36($str) { // Return decimal value of base 36 encoded number
	$dec = base_convert($str,36,10);
	return(intval($dec));
}

function base36($int) { // Return base 36 encoding of integer
	$int = intval($int);
	$b36 = base_convert($int,10,36);
	return($b36);
}

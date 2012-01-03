<?php

/* --------- Comment output functions --------- */

function commentTree($comments,$parent=NULL,$layer=1) { // Builds comment tree, and calls printComment() for each comment in the correct order.
	if(!empty($comments)) {
		$indent = $layer * 2; // Indentation
		foreach ($comments as $c) {
			if($c[parent]==$parent) {
				print(printComment($c,$indent));
				commentTree($comments,$c[id],$layer+1); // Find children of current comment and output them in the next layer
			}
		}
	}
}

function printComment($comment,$indent) { // Outputs a comment
	$template = file_get_contents("templates/comment.html");
	$comment[points] .= ($comment[points] == 1 || $comment[points] == -1) ? " point" : " points";

	if(isset($_GET[linkid])) { // Comment page format
		$user = buildLink("user.php?id=$comment[userid]",$comment[username]); //"<a href=user.php?id=$comment[userid]>$comment[username]</a>";
	}

	elseif(isset($_GET[id])) { // User page format (link title in category)
		$link = buildLink("comments.php?linkid=$comment[linkid]#$comment[id]",$comment[title]) . " in " .
			buildLink("?category=$comment[category]",$comment[category],"linkcat"); //<a href=comments.php?linkid=$comment[linkid]#$comment[id]>$comment[title]</a> in <a class=linkcat href=./?category=$comment[category]>$comment[category]</a>";
	}
	
	$myvote = 0;
	if($comment[myvote]) {
		$myvote = $comment[myvote]; // Current user's vote
	}
	
	$arrows = voteArrows($myvote,$comment[id]);
	
	if($comment[deleted] != 0) { 
		$text = "<em>deleted</em>"; 
		$arrows = str_replace("'vote'","'vote hide'",$arrows);
	}
	
	else { $text = parseComment($comment[text]); }
		
	
	if(!isset($_GET[id])) {	
	$reply = commentform($comment[id],1); // 1 to hide the form by default
		if(intval($_SESSION[id]) != 0 && $comment[deleted] == 0) { $replylink = buildLink("#","reply","reply",$comment[id]); } //"<a class=reply id=$comment[id] href=#>reply</a>"; }
			
		if(intval($_SESSION[id]) == $comment[userid] && $comment[deleted] == 0) { 
			$edit = buildLink("#","edit","edit",$comment[id]); //"<a class=edit id=$comment[id] href=#>edit</a>"; 
			$delete = buildLink("#","delete","delete") ." ".
				spanHide(buildLink("#","yes","delete  yes",$comment[id]), buildLink("#","no","delete  no"));
				/*<a class=delete href=#>delete</a> 
				<a class='delete  yes' href=# id=$comment[id]>yes</a> / 
				<a class='delete  no' href=#>no</a></span>"; */
		}
	}

	if($comment[deleted] != 0) {
		$deleted = "class=deleted";
	}
	
	$placeholders = array("ID" => $comment[id], "USER" => $user, "LINK" => $link, 
			      "USERID" => $comment[userid], "TIME" => timeSince($comment[time]), 
			      "ARROWS" => $arrows, "TEXT" => $text, "POINTS" => $comment[points],
			      "INDENT" => $indent, "REPLYBOX" => $reply, "REPLY" => $replylink, 
			      "EDIT" => $edit, "DELETE" => $delete, "LINKID" => $comment[linkid],
			      "DELETED" => $deleted);

	foreach($placeholders as $p => $value) {
		$template = str_replace("{".$p."}",$value,$template);
	}
	
	return($template);

}

function parseComment($text) { // Parses comment formatting and sanitizes comment string
	$text = filter_var($text,FILTER_SANITIZE_SPECIAL_CHARS);
	
	$curly = array("{","}");
	$curly_replace = array("&#123;","&#125;");
	$text = str_replace($curly,$curly_replace,$text);
	
	//$quote = "/^> (.*)\n(?!\>)|$/s";
/*(?<![\n]{1}?)*/	
	
	$quote = "/.*?> ([^>]+)(\n(?!\>)|$)/s";
	$code = "/^[ ]{4}(.*)\\n(?!\>)|$/s";


	$searches = array("bold" => "/\*{2}([^*]+?)\*{2}/m",
			"italic" => "/\*{1}([^*]+?)\*{1}/m",
			"link" => "/\[(.*)\]\((.*)\)/");
	$replacements = array("bold" => "<strong>\$1</strong>",
			    "italic" => "<em>\$1</em>",
			      "link" => "<a href=\"$2\">$1</a>");
	//$text = preg_replace_callback($quote,"parseQuote",$text);
	//$text = preg_replace_callback($code,"parseCode",$text);
	$text = preg_replace($searches,$replacements,$text);
	
	return(nl2br($text));
}

/*
function parseQuote($text) { // Callback function for preg_replace for quotes in comments (not working)
	$lines = explode("\n",$text[0]);
	$linecount = count($lines);
	$c = 1;
	$q = "<q>";
	//print_r($lines);
	foreach($lines as $i => $l) {
		if($l == "") { $q .= ""; }
		else {
		if(preg_match("/^> (.*)/s",$l,$match)) {
			if($c == $linecount) { // Close the q tag if we're on the last line of the comment
				$q .= $match[1]."</q>";
				break;
			}		
			else {
				$q .= "$match[1]\n";
				array_shift($lines); // Remove matching lines from the array of remaining lines
			}
		}

		else { // If we're no longer quoting, end the q tag and insert the rest of the comment before returning the string.
			$q .= "</q>".implode("\n",$lines);
			break;
		}
		}
	}
	return($q);
}

function parseCode($text) { // Callback function for preg_replace for code blocks in comments (not working)
	$lines = explode("\n",$text[0]);
	$linecount = count($lines);
	$c = 1;
	$q = "<code>";
	foreach($lines as $i => $l) {
		if(preg_match("/^[ ]{4}(.*)/",$l,$match)) {
			if($c == $linecount) { // Close the code tag if we're on the last line of the comment
				$q .= filter_var($match[1],FILTER_SANITIZE_SPECIAL_CHARS)."</code>";
				break;
			}		
			else {
				$q .= filter_var($match[1],FILTER_SANITIZE_SPECIAL_CHARS)."\n";
				array_shift($lines); // Remove matching lines from the array of remaining lines
			}
		}

		else { // If we're no longer in a code block, end the code tag and insert the rest of the comment before returning the string.
			$q .= "</code>".implode("\n",$lines);
			break;
		}
	}
	return($q);
}

 */
	

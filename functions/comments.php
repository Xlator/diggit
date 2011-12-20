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
	if($comment[points] == NULL) { $comment[points] = 0; }
	if($comment[points] == 1 || $comment[points] == -1) { $p = "point"; }
	else { $p = "points"; }
	$points = "$comment[points] $p";
	$vote = getMyVote($_SESSION[id],$comment[id],'comment');
	$arrows = voteArrows($vote,$comment[id]);
	$text = parseComment($comment[text]);
	$reply = commentform($comment[id],1); // 1 to hide the form by default
	$placeholders = array("ID" => $comment[id], "USER" => $comment[username], 
			      "USERID" => $comment[userid], "TIME" => timeSince($comment[time]), 
			      "ARROWS" => $arrows, "TEXT" => $text, "POINTS" => $points,
		      	      "INDENT" => $indent, "REPLYBOX" => $reply);
	foreach($placeholders as $p => $value) {
		$template = str_replace("{".$p."}",$value,$template);
	}
	return($template);
}

function parseComment($text) {
	$searches = array("bold" => "/\*{2}([^*]+?)\*{2}/m",
			  "italic" => "/\*{1}([^*]+?)\*{1}/m",
			  "quote" => "/^> (.*)/m",
		  	  "code" => "/^    (.*)/m");
	$replacements = array("bold" => "<strong>\$1</strong>",
		              "italic" => "<em>\$1</em>",
			      "quote" => "<q>\$1</q>",
		      	      "code" => "<pre>\$1</pre>" );

	$text = preg_replace($searches,$replacements,$text);
	return($text);
}	

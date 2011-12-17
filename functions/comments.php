<?php

/* --------- Comment output functions --------- */

function commentTree($comments,$parent=NULL,$layer=1) { // Builds comment tree, and calls printComment() for each comment in the correct order.
	if(!empty($comments)) {
		$indent = $layer * 3; // Indentation
		foreach ($comments as $c) {
			if($c[parent]==$parent) {
				print(printComment($c));
				commentTree($comments,$c[id],$layer+1); // Find children of current comment and output them in the next layer
			}
		}
	}
}

function printComment($comment) { // Outputs a comment
	$template = file_get_contents("templates/comment.html");
	if($comment[points] == NULL) { $comment[points] = 0; }
	if($comment[points] == 1 || $comment[points] == -1) { $p = "point"; }
	else { $p = "points"; }
	$points = "$comment[points] $p";
	$vote = getMyVote($_SESSION[id],$comment[id],'comment');
	$arrows = voteArrows($vote,$comment[id]);
	$placeholders = array("ID" => $comment[id], "USER" => $comment[username], 
			      "USERID" => $comment[userid], "TIME" => timeSince($comment[time]), 
			      "ARROWS" => $arrows, "TEXT" => $comment[text], "POINTS" => $points);
	foreach($placeholders as $p => $value) {
		$template = str_replace("{".$p."}",$value,$template);
	}
	return($template);
}

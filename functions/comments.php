<?php

function commentTree($comments,$parent=NULL,$layer=1) {
	if(!empty($comments)) {
		$indent = $layer * 3; // Indentation
		foreach ($comments as $c) {
			if($c[parent]==$parent) {
//				print("<p class=comment style='margin-left:".$indent."em; margin-bottom:1em;'>$c[id] $c[username] ".timeSince($c[time])."<br />$c[text]");
				print("<pre>"); print_r($c);
				print(printComment($c));
				commentTree($comments,$c[id],$layer+1); // Find children of current comment and output them in the next layer
			}
		}
	}
}

function printComment($comment) {
	$template = file_get_contents("templates/comment.html");
	return($template);
}

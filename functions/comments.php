<?php

function commentTree($comments,$parent=NULL,$layer=1) {
	if(!empty($comments)) {
		$indent = $layer * 3;
		foreach ($comments as $c) {
			if($c[parent]==$parent) {
				print("<p class=comment style='margin-left:".$indent."em; margin-bottom:1em;'>$c[id] $c[username] $c[time]<br />$c[text]");
				commentTree($comments,$c[id],$layer+1);
			}
		}
	}
}

<?php

session_start();
$_SESSION[id] = intval($_SESSION[id]);
if($_GET[logout]==1) { session_destroy(); header("Location: ./");}
if(!isset($_SESSION[id])) { $_SESSION[id] = 0; } // Set id to 0 if we aren't logged in
require("config.php");
require("functions/db.php");
require("functions/links.php");
require("functions/common.php");
require("functions/forms.php");

if(!isset($_GET[category])) { $_GET[category] = NULL; }
elseif(!categoryExists($_GET[category])) { header("Location: ./"); } // Redirect back to start if category doesn't exist

if(!isset($_GET[domain])) { $_GET[domain] = NULL; }
elseif(!domainExists($_GET[domain])) { header("Location: ./"); } // Redirect if the domain doesn't exist

if(intval($_GET[page] == 0)) { $_GET[page] = 1; }
$links = getLinks($_GET[page],LINKS_PER_PAGE,$_GET[category],NULL,$_GET[domain]);

// If we're on the first page, no category has been specified and there are no links (empty links table, basically), just print the header
if(empty($links[0]) && $_GET[category] == NULL && $_GET[page] == 1) { 
	printHeader();
}

elseif(!empty($links[0])) { // Print the header followed by the links
printHeader();
$pagination = pagination($links[page],$links[totalpages],"links");
print($pagination);
print "<div class=linklist><ul>";

	foreach($links[0] as $link) {
	print(printLink($link));
	}


print "</ul></div>";
print($pagination);
}

// Redirect to start if we're in a category that exists or a page other than the first if there are no links
else { header("Location: ./"); }

?>

<?php

// Include helper functions
require("functions/dbhelper.php");

function getUsername($id) { // Returns name of user with given id
	$id = intval($id);
	return(dbFirstResult("SELECT username FROM users WHERE id=$id"));
}

function getUserid($username) { // Returns id of user with given name
	$username = dbEscape($username);
	return(dbFirstResult("SELECT id FROM users WHERE username='$username'"));
}

function getPassword($username) { // Returns password hash of user with given name
	$username = dbEscape($username);
	return(dbFirstResult("SELECT password FROM users WHERE username='$username'"));
}

function userExists($parameter,$value) { // Checks if a user exists, either by email or username
	$value = dbEscape($value);
	return(dbResultExists("SELECT id FROM users WHERE $parameter='$value'"));
}

function registerUser($input) { // Writes user info to database on successful registration 
	$input = dbEscapeArray($input);
	$salt = generateSalt(); 		
	$hash = hashPassword($input[password],$salt);
	$insert = dbQueryId("INSERT INTO users (username,email,password) VALUES ('$input[username]','$input[email]','$hash')");
	return($insert);
}

function getLinks($page=1,$limit=25,$category=NULL) { // Fetch and return array of links
	$page = intval($page);
	switch($_GET[order]) {
		case "new":
			$order = "time DESC, points DESC";
			break;
		case "hot":
		default:	
			$order = "votes DESC, points DESC, time DESC";
			break;
	}

	$page--;
	$offset=($page*$limit); // Pagination	
	$query = "
	SELECT links.*,IFNULL(r.votes,0) as votes,IFNULL(t.points,0) as points,c.comments FROM links
	LEFT JOIN recentvotes AS r ON links.id=r.subjectid AND r.type='link' 
	LEFT JOIN totalvotes AS t ON links.id=t.subjectid AND t.type='link'
	LEFT JOIN commentcounts AS c ON links.id=c.linkid
	ORDER BY $order LIMIT $offset,$limit";
	return(dbResultArray($query));
}

function linkExists($url) { // Return true if the given URL has already been posted
	$url = dbEscape($url);
	return(dbResultExists("SELECT id FROM links WHERE link='$url'"));
}

function categoryExists($cat) { // Return true if the given category exists
	$cat = dbEscape($cat);
	return(dbResultExists("SELECT * FROM categories WHERE name='$cat'"));
}

function getCategories($ownerid=false) { // Get an array of categories, optionally only those owned by a specific user
	$ownerid = intval($ownerid);
	return(dbResultArray("SELECT * FROM categories" . ($ownerid? ' WHERE owner=' . $ownerid : '')));
}

function sendLink($input) { 
	// takes an array of sanitized input to insert into the database
	// on success, returns the id of the submitted link
	$input = dbEscapeArray($input);
	
	if(!isset($input[cat])) { $input[cat] = "main"; }
		
	if(!isset($input[nsfw])) { $input[nsfw] = 0; }
	elseif($input[nsfw] == "on") { $input[nsfw] = 1; }	
		
	// extract hostname from URL
	$url = parse_url($input[url]);
	$domain = $url[host];
	
	if($input[cat] == "") { $input[cat] = "main"; }
		
	// create the category if it doesn't previously exist in the database
	if(!categoryExists($input[cat])) {
		dbQuery("INSERT INTO categories (name) VALUES ('$input[cat]')");
	}	
	
	$query = "INSERT INTO links (title,link,domain,category,user,nsfw)
		VALUES ('$input[title]','$input[url]','$domain','$input[cat]',$_SESSION[id],$input[nsfw])";
	
	$id = dbQueryId($query);
	
	vote($_SESSION[id],$id,'link',1); // Auto-upvote own submissions
	return($id);
}

function sendComment($input) {
	// takes an array of sanitized input
	$input = dbEscapeArray($input);
}

function getComments($linkid) { // Returns an array of comments to the given link ID
	$linkid = intval($linkid);
	return(dbResultArray("SELECT c.*,u.username FROM comments AS c JOIN users AS u ON u.id=c.userid WHERE c.linkid=$linkid"));
}


function getMyVote($userid,$subjectid,$type) { // Return given users vote for given link/comment (or 0 if they haven't voted)
	$result = dbFirstResult("SELECT vote FROM votes WHERE subjectid=$subjectid AND userid=$userid AND type='$type'");
	if($result==NULL) { return(0); }
	return($result);
}

function getMyPoints($userid) { // Get given user's total points (from their submissions and comments)
	$query = "SELECT SUM(IFNULL(t.points,0)) as points FROM users 
		  LEFT JOIN links ON links.user=users.id 
		  LEFT JOIN comments ON comments.userid=users.id 
		  LEFT JOIN totalvotes AS t ON (links.id=t.subjectid AND t.type='link') 
		  			    OR (comments.id=t.subjectid AND t.type='comment') 
					    WHERE users.id=$userid GROUP BY user ORDER BY points DESC";
	return(dbFirstResult($query));
}

function vote($userid,$subjectid,$type,$vote) { // Enters, removes or edits a vote from user for subject, returns new vote count.
	$type = dbEscape($type);
	$userid = intval($userid);
	$subjectid = intval($subjectid);
	$vote = intval($vote);

	switch($vote) {
	case 0:
		// Unset/delete vote
		dbQuery("DELETE FROM votes WHERE userid=$userid AND subjectid=$subjectid AND type='$type'");
		break;
	case 1:
	case -1:
		// Check if we've already voted
		$result = dbFirstResult("SELECT * FROM votes WHERE userid=$userid AND subjectid=$subjectid AND type='$type'");
		
		if(empty($result)) { // Make a new vote if we haven't voted before
			dbQuery("INSERT INTO votes (userid,type,subjectid,vote) VALUES($userid,'$type',$subjectid,$vote)");	
		}
	
		elseif($vote != $row[vote]) { // Update our old vote if we have voted before
			dbQuery("UPDATE votes SET vote=$vote WHERE userid=$userid AND subjectid=$subjectid AND type='$type'");
		}
		
		break;
	default: // Any vote other than +1, -1 or 0
		return("Invalid vote!");
		break;
	}

	// Grab and return the new vote count
	$result = dbFirstResult("SELECT points FROM totalvotes WHERE subjectid=$subjectid AND type='$type'");
	//var_dump($result[0]);
	if(empty($result)) { $result = 0; }		
	if($result != 1 && $result != -1) { $p = "points"; }
	else { $p = "point"; }
	return("$result $p");
}

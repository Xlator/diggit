<?php

function getUsername($id) {
	global $db;
	$query = "SELECT username FROM users WHERE id=$id";
	$result = mysqli_query($db,$query);
	if($result === false) { return(false); }
	$row = mysqli_fetch_array($result);
	return $row[0];
}

function getUserid($username) {
	global $db;
	$query = "SELECT id FROM users WHERE username='$username'";
	$result = mysqli_query($db,$query);
	if($result === false) { return(false); }
	$row = mysqli_fetch_array($result);
	return $row[0];
}

function getPassword($username) {
	global $db;
	$query = "SELECT password FROM users WHERE username='$username'";
	$result = mysqli_query($db,$query);
	$row = mysqli_fetch_assoc($result);
	return($row[password]);
}

function userExists($parameter,$value) {
	global $db;
	$query = "SELECT * FROM users WHERE $parameter='$value'";
	$result = mysqli_query($db,$query);
	$row = mysqli_fetch_assoc($result);
	if(!empty($row)) { return(true); }
	return(false);
}

function registerUser($input) { 
	global $db;
	$salt = generateSalt();
	$hash = hashPassword($input[password],$salt);
	$query = "INSERT INTO users (username,email,password) VALUES ('$input[username]','$input[email]','$hash')";
	if(!mysqli_query($db,$query)) { print(mysqli_error($db)); die(); }
	$id = mysqli_insert_id($db);
	return($id);
}


function getRecentVotes() {
	// recentvotes is a view of the sum of the votes from the last 48 hours of each link
	global $db;
	$query = "SELECT * FROM recentvotes";
	$result = mysqli_query($db,$query);
	if($result === false) { return(false); }
	while($row = mysqli_fetch_assoc($result)) { 
		$recentvotes[$row[subjectid]] = $row[votes];
	}
	return $recentvotes;
}

function getTotalVotes() {
	// totalvotes is a view of the sum of all votes for each link
	global $db;
	$query = "SELECT * FROM totalvotes";
	$result = mysqli_query($db,$query);
	if($result === false) { return(false); }	
	while($row = mysqli_fetch_assoc($result)) {
		$totalvotes[$row[subjectid]] = $row[points];
	}
	return $totalvotes;
}

#function getComments($linkid) {
#	global $db;
#	$query = "SELECT * FROM comments WHERE linkid='$linkid'";
#	$result = mysqli_query($db,$query);
#	if($result === false) { return(false); }
#	while($row = mysqli_fetch_assoc($result)) {
#		$comments[$row[id]] = $row;
#	}
#	return $comments;
#}

function getLinks($page=1,$limit=25,$category=NULL) {
	global $db;
	
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
	LEFT JOIN recentvotes AS r ON links.id=r.subjectid AND r.vote_subject='link' 
	LEFT JOIN totalvotes AS t ON links.id=t.subjectid AND t.vote_subject='link'
	LEFT JOIN commentcounts AS c ON links.id=c.linkid
	ORDER BY $order LIMIT $offset,$limit";
	$result = mysqli_query($db,$query);
	if($result === false) { return(mysqli_error($db)); }
	while($row = mysqli_fetch_assoc($result)) {
		$links[] = $row;
	}
	return($links);
}

function linkExists($url) {
	global $db;
	$query = "SELECT id FROM links WHERE link='$url'";
	$result = mysqli_query($db,$query);
	$row = mysqli_fetch_array($result); 
	if($row == NULL) { return(false); }
	else { return($row[0]); }
}

function categoryExists($cat) {
	global $db;
	$query = "SELECT * FROM categories WHERE name='$cat'";
	$result = mysqli_query($db,$query);
	$row = mysqli_fetch_array($result);
	if($row == NULL) { return(false); }
	else { return($row[0]); }
}

function getCategories($ownerid=false) { // Get an array of categories
	global $db;
	$query = "SELECT * FROM categories" . ($ownerid? ' WHERE owner=' . $ownerid : '');
	$result = mysqli_query($db,$query);
	while($row = mysqli_fetch_assoc($result)) {
		if($row[name] != "main") { // Don't include the 'main' category
			$cats[] = $row;
		}
	}
	return($cats);
}

function sendLink($input) {
	// takes an array of sanitized input to insert into the database
	// on success, returns the id of the submitted link
	global $db;
	if(!isset($input[cat])) { $input[cat] = "main"; }
	if(!isset($input[nsfw])) { $input[nsfw] = 0; }
	elseif($input[nsfw] == "on") { $input[nsfw] = 1; }	
	// extract hostname from URL
	$url = parse_url($input[url]);
	$domain = $url[host];
	if($input[cat] == "") { $input[cat] = "main"; }
	// create the category if it doesn't previously exist in the database
	if(!categoryExists($input[cat])) {
		if(!mysqli_query($db,"INSERT INTO categories (name) VALUES ('$input[cat]')")) {
			print mysqli_error($db); die(); }
	}	
	
	$query = "INSERT INTO links (title,link,domain,category,user,nsfw)
		VALUES ('$input[title]','$input[url]','$domain','$input[cat]',$_SESSION[id],$input[nsfw])";
	if(!mysqli_query($db,$query)) { print mysqli_error($db); die(); }
	
	$id = mysqli_insert_id($db); // Get link id
	vote($_SESSION[id],$id,'link',1); // Auto-upvote own submissions

	return($id);
}

function sendComment($input) {
	// takes an array of sanitized input
}

function getComments($linkid) {
	global $db;
	$query = "SELECT c.*,u.username FROM comments AS c JOIN users AS u ON u.id=c.userid WHERE c.linkid=$linkid";
	$result = mysqli_query($db,$query);
	while($row = mysqli_fetch_assoc($result)) {
		$comments[] = $row;
	}
	return($comments);
}


function getMyVote($userid,$subjectid,$type) {
	global $db;
	$query = "SELECT vote FROM votes WHERE subjectid=$subjectid AND userid=$userid AND vote_subject='$type'";
	$result = mysqli_query($db,$query);
	$row = mysqli_fetch_assoc($result);
	if(empty($row)) {
		return(0);
	}
	else {
		return($row[vote]);
	}
}

function getMyPoints($userid) {
	global $db;
	$query = "SELECT SUM(IFNULL(t.points,0)) as points FROM users 
		  LEFT JOIN links ON links.user=users.id 
		  LEFT JOIN comments ON comments.userid=users.id 
		  LEFT JOIN totalvotes AS t ON (links.id=t.subjectid AND t.vote_subject='link') 
		  			    OR (comments.id=t.subjectid AND t.vote_subject='comment') 
		  WHERE users.id=$userid GROUP BY user ORDER BY points DESC";
	$result = mysqli_query($db,$query);
	$row = mysqli_fetch_assoc($result);
	
	if(!empty($row)) {
		return($row[points]);
	}
	
	else {
		return(0);
	}
}

function vote($userid,$subjectid,$type,$vote) {
	global $db;
	if($vote == 0) { // Unset/delete vote 
		$query = "DELETE FROM votes WHERE userid=$userid AND subjectid=$subjectid AND vote_subject='$type'";
		if(!mysqli_query($db,$query)) { print(mysqli_error($db)); die(); }
	}
	
	else {  // Check if we have already vote
		$query = "SELECT * FROM votes WHERE userid=$userid AND subjectid=$subjectid AND vote_subject='$type'";
		$result = mysqli_query($db,$query);
		$row = mysqli_fetch_assoc($result);
		if(empty($row)) { // Make a new vote if we haven't voted before
			$query = "INSERT INTO votes (userid,vote_subject,subjectid,vote) VALUES($userid,'$type',$subjectid,$vote)";	
			if(!mysqli_query($db,$query)) { print mysqli_error($db); die(); }
		}
	
		elseif($vote != $row[vote]) { // Update our old vote if we have voted before
			$query = "UPDATE votes SET vote=$vote WHERE userid=$userid AND subjectid=$subjectid AND vote_subject='$type'";
			if(!mysqli_query($db,$query)) { print mysqli_error($db); die(); }
		}
	}
	// Grab and return the new vote count
	$query = "SELECT * FROM totalvotes WHERE subjectid=$subjectid AND vote_subject='$type'";
	$result = mysqli_query($db,$query);
	$row = mysqli_fetch_assoc($result);
	if(empty($row)) { $row[points] = 0; }		
	if($row[points] != 1 && $row[points] != -1) { $p = "points"; }
	else { $p = "point"; }
	return("$row[points] $p");
}

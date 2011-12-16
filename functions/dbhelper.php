<?php

function dbConn() { // Returns database link
	return mysqli_connect(DBHOST,DBUSER,DBPASS,DBNAME);
}

function dbQuery($query) { // Returns result of query, logs error and returns false on failure
	$db = dbConn();
	$result = mysqli_query($db,$query);
	if($result !== false) {
		return($result);
	}
	error_log("MySQL Query Error: " . mysqli_error($db)); 
	return(false);
}

function dbQueryId($query) { // Returns insert id of query
	$db = dbConn();
	$result = mysqli_query($db,$query);
	if($result != false) {
		return(mysqli_insert_id($db));
	}
	error_log("MySQL Query Error: " . mysqli_error($db));
	return(false);
}


function dbFirstResult($query) { // Returns first row of query result as indexed array
	$result = dbQuery($query);
	if($result === false) { return(false); }
	$row = mysqli_fetch_array($result);
	return $row[0];
}

function dbFirstResultAssoc($query) { // Returns first row of query result as associative array
	$result = dbQuery($query);
	if($result === false) { return(false); }
	$row = mysqli_fetch_assoc($result);
	return $row;
}


function dbResultArray($query) { // Returns query result as associative array
	$result = dbQuery($query);
	if($result === false) { return(false); }
	while($row = mysqli_fetch_assoc($result)) {
		$output[] = $row;
	}
	return($output);
}

function dbResultExists($query) { // Returns true if a result is found, false if it isn't
	$result = dbQuery($query);
	$row = mysqli_fetch_array($result);
	if(!empty($row)) { return(true); }
	return(false);
}

function dbEscape($string) { // Returns escaped string to prevent SQL insertion attacks
	$db = dbConn();
	return(mysqli_real_escape_string($db,$string));
}

function dbEscapeArray($array) { // Returns escaped variable to prevent SQL insertion
	return(array_map("dbEscape",$array));
}


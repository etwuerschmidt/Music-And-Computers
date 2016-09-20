<?php
	//Connect to DB, select random email
	$db = new mysqli('localhost', 'musi3390', 'musi3390', 'musi3390_db');
	$query = "SELECT Name, Body FROM emails ORDER BY Rand() LIMIT 1";
	$result = $db->query($query);
	$row = $result->fetch_array();
	$send = $row[0] . "~" . $row[1];

	//Delete email so email cannot be reselected
	$query = "DELETE FROM emails WHERE Name = '$row[0]' AND Body = '$row[1]'";
	$db->query($query);
	
	echo($send);
	
?>
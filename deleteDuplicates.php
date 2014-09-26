<?php

	/*
		Removes duplicate entries of Medias on Wordpress 4.0
		This cleans up duplicates created by a WPML.ORG bug that can 
		generate thousands of extra duplicate copies of each media item.

		https://code.google.com/p/wp-delete-duplicates/source/browse/deleteDuplicates.php

		## Instructions

			1) First configure the database access by inserting name of database, user, and password
			2) Optional : If your wordpress database prefix is anything other than "wp_" (default value), run the function at the end of the script with the name of your posts table name. Something like : deleteDuplicates("yourPrefix_posts");
			else just run the function without parameters.
	*/

	## database config

		$cfg['servers']['host'] = 'localhost';
		$cfg['servers']['db'] = 'YOUR_DATABASE_NAME';
		$cfg['servers']['user'] = 'YOUR_USERNAME';
		$cfg['servers']['password'] = 'YOUR_PASSWORD';
	
		if (!isset($conn )) $conn = mysqli_connect(
			$cfg['servers']['host'], 
			$cfg['servers']['user'], 
			$cfg['servers']['password'],
			$cfg['servers']['db']) 
				OR die(mysqli_error($conn));

		if (!mysqli_set_charset($conn, 'utf8')) echo "mysqli_set_charset fail";

	## functions

	function deleteDuplicates($table = 'wp_posts', $colHavingDuplicates = 'guid', $idColName = 'ID')
	{
		global $conn;
		$query = mysqli_query($conn, "SELECT " . $idColName . ", " . $colHavingDuplicates . " FROM " . $table)
			or die(mysqli_error($conn));

		$existingEntries = array();
		$duplicatesIds = array();

		## find duplicates 
		while ($rows = mysqli_fetch_array($query))
		{
			// if image URL value exists in array, add it's ID to duplicatesIds
			// else enter image URL in existingEntries array
			if (in_array($rows[$colHavingDuplicates], $existingEntries)) $duplicatesIds[] = $rows[$idColName];
				else $existingEntries[] = $rows[$colHavingDuplicates];
		}

		## report number of duplicates
		$idsDeleteList = implode(', ', $duplicatesIds);
		echo count($duplicatesIds). " total entries to delete. <br>\r\n" . $idsDeleteList;

		## delete duplicates
		if (!empty($duplicatesIds)) 
			$query = mysqli_query($conn, "DELETE FROM " . $table . " WHERE " . $idColName . " IN (". $idsDeleteList .")")
				or die(mysqli_error($conn));
	}

	## MAIN ########################

	deleteDuplicates();

?>
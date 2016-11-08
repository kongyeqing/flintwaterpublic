<?php

@define("__ROOT__", dirname(dirname(__FILE__)));
require __ROOT__ . "/includes/database_config.php";

function queries($choice, $var = "", $var2 = array()) {
	global $mysqli;
	
	$mysqli->set_charset("utf8");
	
	$choice = $mysqli->real_escape_string($choice);
	$var = $mysqli->real_escape_string($var);

	/* Dashboard Queries */
	if (strcmp($choice, "test_data") === 0) {
		$query = "SELECT Year(`dateUpdated`) AS year, Month(`dateUpdated`) AS month, Avg(`leadLevel`) AS avgLeadLevel, Avg(`copperLevel`) AS avgCopperLevel, Sum(`leadLevel`) AS totalTests FROM `waterCondition` GROUP BY year ASC, month ASC;";
	}
	else if (strcmp($choice, "total_locations") === 0) {
		$query = "SELECT DISTINCT `latitude`, `longitude`, COUNT(`latitude`) AS totalLocationsTested FROM waterCondition;";
	}
	else if (strcmp($choice, "total_approved_repairs") === 0) {
		$query = "SELECT COUNT(`address`) as totalApprovedRepairs FROM ConstructionInfo;";
	}
	else if (strcmp($choice, "time_period") === 0) {
		$query = "SELECT DISTINCT Year(`dateUpdated`) AS year, Month(`dateUpdated`) AS month FROM waterCondition ORDER BY year ASC, month ASC;";
	}
	/* Report Queries */
	else if (strcmp($choice, "water_tests") === 0) {
		// group by
		/*if (strcmp($var2["group_by"], "") !== 0) {
			$groupby_clause = " GROUP BY ";
		
			switch($var2["group_by"]) {
				case "date":
					$groupby_clause .= "`dateUpdated`";
				break;
				
				case "address":
					$groupby_clause .= "`address`";
				break;
			}
		}
		else
			$groupby_clause = "";*/
		
		// order by
		if (strcmp($var2["order_by"], "") !== 0) {
			$orderby_clause = " ORDER BY ";
		
			switch($var2["order_by"]) {
				case "date_asc":
					$orderby_clause .= "`dateUpdated` ASC";
				break;
				
				case "date_desc":
					$orderby_clause .= "`dateUpdated` DESC";
				break;
				
				case "lead_asc":
					$orderby_clause .= "`leadLevel` ASC";
				break;
				
				case "lead_desc":
					$orderby_clause .= "`leadLevel` DESC";
				break;
				
				case "copper_asc":
					$orderby_clause .= "`copperLevel` ASC";
				break;
				
				case "copper_desc":
					$orderby_clause .= "`copperLevel` DESC";
				break;
				
				case "address_asc":
					$orderby_clause .= "`address` ASC";
				break;
				
				case "address_desc":
					$orderby_clause .= "`address` DESC";
			}
		}
		else
			$orderby_clause = "";
		
		// limits
		if (sizeof($var2["limit"]) > 0) {
			$limit_clause = "";
			
			if (strcmp($var2["limit"]["lead_greater"], "") !== 0) {
				$limit_clause .= sprintf(" && `leadLevel` > %s", $var2["limit"]["lead_greater"]);
			}
			if (strcmp($var2["limit"]["lead_less"], "") !== 0) {
				$limit_clause .= sprintf(" && `leadLevel` < %s", $var2["limit"]["lead_less"]);
			}
			if (strcmp($var2["limit"]["copper_greater"], "") !== 0) {
				$limit_clause .= sprintf(" && `copperLevel` > %s", $var2["limit"]["copper_greater"]);
			}
			if (strcmp($var2["limit"]["copper_less"], "") !== 0) {
				$limit_clause .= sprintf(" && `copperLevel` < %s", $var2["limit"]["copper_less"]);
			}			
		}
		else
			$limit_clause = "";
			
		$where_clause = "Month(`dateUpdated`) IN(" . implode(",", $var2["months"]) . ") && Year(`dateUpdated`) IN(" . implode(",", $var2["years"]) . ")"
						. $limit_clause;
		
		$query = sprintf("SELECT `address`, `leadLevel`, `copperLevel`, `dateUpdated` FROM `waterCondition` WHERE %s%s%s;", $where_clause, $groupby_clause, $orderby_clause);
	}
	else if (strcmp($choice, "all_water_tests2") === 0) {
		$query = "SELECT `address`, `leadLevel`, `copperLevel`, `dateUpdated` FROM `waterCondition` GROUP BY `address` ORDER BY `dateUpdated` DESC;";
	}
	else if (strcmp($choice, "high_water_tests1") === 0) {
		$query = "SELECT `address`, `leadLevel`, `copperLevel`, `dateUpdated` FROM `waterCondition` WHERE `leadLevel` > 15 ORDER BY `dateUpdated` DESC;";
	}
	else if (strcmp($choice, "high_water_tests2") === 0) {
		$query = "SELECT `address`, `leadLevel`, `copperLevel`, `dateUpdated` FROM `waterCondition` WHERE `leadLevel` > 15 GROUP BY `address` ORDER BY `dateUpdated` DESC;";
	}
	/* Edit Page Queries */
	else if (strcmp($choice, "resource_locations") === 0) {
		$query = "SELECT aidAddress FROM AidLocation WHERE aidAddress != '' ORDER BY aidAddress+0<>0 DESC, aidAddress+0, aidAddress;";
	}
	else if (strcmp($choice, "edit_resource_load") === 0) {
		$query = sprintf("SELECT latitude, longitude, locationName, AidLocation.aidAddress, city, zipcode, hours, phone, notes, GROUP_CONCAT(resType) AS resType FROM AidLocation INNER JOIN ResourcesQuantity ON AidLocation.aidAddress = ResourcesQuantity.aidAddress WHERE AidLocation.aidAddress = '%s';", $var);
	}
	if (strcmp($choice, "edit_resource_submit") === 0) {
		$query = sprintf("UPDATE AidLocation SET latitude = '%s', longitude = '%s', locationName = '%s', city = '%s', state='MI', zipcode = '%s', hours = '%s', phone = '%s', notes = '%s' WHERE aidAddress = '%s';", $var2["latitude"], $var2["longitude"], $var2["site"], $var2["city"], $var2["zipcode"], $mysqli->real_escape_string($var2["hours"]), $var2["phone"], $mysqli->real_escape_string($var2["notes"]), $var2["address"]);
		
		/* Deal with the resource types. */
		$query .= sprintf("DELETE FROM ResourcesQuantity WHERE aidAddress = '%s';", $var2["address"]);
		
		foreach ($var2["categories"] as $value)
			$query .= sprintf("INSERT INTO ResourcesQuantity (resType, aidAddress, quantity) VALUES ('%s', '%s', 1000);", $value, $var2["address"]);
	}
	else if (strcmp($choice, "new_resource") === 0) {
		$query = sprintf("INSERT INTO AidLocation (latitude, longitude, locationName, aidAddress, city, state, zipcode, hours, phone, notes) VALUES ('%s', '%s', '%s', '%s', '%s', 'MI', '%s', '%s', '%s', '%s');", $var2["latitude"], $var2["longitude"], $var2["site"], $var2["address"], $var2["city"], $var2["zipcode"], $mysqli->real_escape_string($var2["hours"]), $var2["phone"], $mysqli->real_escape_string($var2["notes"]));
		
		foreach ($var2["categories"] as $value)
			$query .= sprintf("INSERT INTO ResourcesQuantity (resType, aidAddress, quantity) VALUES ('%s', '%s', 1000);", $value, $var2["address"]);
	}
	else if (strcmp($choice, "delete_resource") === 0) {
		$query = sprintf("DELETE FROM AidLocation WHERE aidAddress = '%s';", $var);
		$query .= sprintf("DELETE FROM ResourcesQuantity WHERE aidAddress = '%s';", $var);
	}
	/*else if (strcmp($choice, "") === 0) {
		
	}*/
	
	/* Use the multi-query function if there are multiple semi-colons. */
	if (substr_count($query, ";") > 1)
		return $mysqli->multi_query($query);
	else
		return $mysqli->query($query);
}
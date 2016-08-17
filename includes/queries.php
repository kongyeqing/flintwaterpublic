<?php

require_once "database_config.php";

function queries($choice, $var = "", $var2 = "") {
	global $mysqli;
	$choice = $mysqli->real_escape_string($choice);
	$var = $mysqli->real_escape_string($var);

	if (strcmp($choice, "lead") === 0) {
		$query = "SELECT Geo.latitude, Geo.longitude, leadlevel, testID, StAddress FROM waterCondition, (SELECT * FROM GeoLocation) Geo WHERE propertyID = locationID ORDER BY leadLevel ASC;";
	}
	else if (strcmp($choice, "providers") === 0) {
		if (strcmp($var, "") === 0)
			$query = "SELECT locationName, REPLACE(AidLocation.aidAddress,'\r','') AS aidAddress, hours, REPLACE(phone,'\r','') AS phone, city, zipcode, resType, notes, latitude, longitude FROM AidLocation, ResourcesQuantity  JOIN GeoLocation ON aidAddress = REPLACE(StAddress,'\r','') WHERE AidLocation.aidAddress = ResourcesQuantity.aidAddress;";
		else {
			$query = "SELECT resType FROM `ResourcesQuantity` WHERE REPLACE(aidAddress,'\\r','') = '" . $var . "';";

			//print $query . "\n";
		}
	}
	else if(strcmp($choice, "report") === 0) {
		$query = sprintf("INSERT INTO ReportProblem VALUES ('%s', '%s', '%s', '%s');",
		$_POST['location'], $_POST['problemType'], $_POST['description'], $var2);

	}
	else if(strcmp($choice, "inaccuracy") === 0) {
		$query = sprintf("INSERT INTO AidInaccuracy (address, reason, reportTicket) VALUES ('%s', '%s', '%s');",
		$_POST['address'], $_POST['reason'], $var2);

	}
	else {
		echo "There is a problem with the query.";
	}

	return $mysqli->query($query);
}

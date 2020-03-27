<?php
//set to false when testing
$live = true;
//used to check speed of JSON creation
$start = microtime(true);

//Set appropriate header
if($live !== true) {
	header('Content-Type: text/javascript');
} else {
	header("'Content-Type: application/json' charset='utf-8'");
}

define('DB_HOST', 'mysql.XXXX.com');
define('DB_NAME', 'XXXX');
define('DB_USER', 'XXXX');
define('DB_PASSWORD', 'XXXX');

$dbc2 = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

mysqli_set_charset($dbc2,'utf8');

$startRow_rs_properties = 1;
$maxRows_rs_properties = 3000;

$query_rs_properties = "SELECT lm.ListingKey, lm.MLS_NUM, lm.BathroomsTotal, lm.BedroomsTotal, lm.ListPrice, lm.OriginatingSystemName, lm.ParkingTotal, lm.OwnershipType, lm.PropertyType, lm.PublicRemarks, 

la.ListingKey, la.MLS_NUM, la.UnparsedAddress, la.City, la.StateOrProvince, la.PostalCode, la.Lat, la.Lng, la.FormattedAddress
FROM listing_master AS lm 
INNER JOIN listing_address AS la
ON la.ListingKey = lm.ListingKey 
WHERE la.City = 'Ottawa' AND la.Lat <> '0' AND la.Lng <> '0'";

$query_limit_rs_properties = sprintf("%s LIMIT %d, %d", $query_rs_properties, $startRow_rs_properties, $maxRows_rs_properties);

if (mysqli_more_results($dbc2)) mysqli_next_result($dbc2);
$rs_properties = mysqli_query($dbc2, $query_limit_rs_properties);
//$row_rs_properties = mysqli_fetch_assoc($rs_properties);
$totalRows_rs_properties = mysqli_num_rows($rs_properties);

if($live !== true) {
	echo "Total rows found in query: ".$totalRows_rs_properties."<br/>";
	echo "------------------------------------------------------------------------<br/>";
}

if ($totalRows_rs_properties > 0) {
	
	$properties = array();
	
	for ($i=0; $i<$totalRows_rs_properties; $i++) {
		$row_rs_properties = mysqli_fetch_assoc($rs_properties);
		
		$address = $row_rs_properties['UnparsedAddress'];
		
		$find = '';
		if (preg_match('/\bUNIT\b/',$address)) {
			$find = true;
		} else {
			$find = false;
		}
		
		if($find) {
			$searchfor = "UNIT";
			$string_position = strpos($address,$searchfor);
			
			$before = substr($address,0,$string_position);//before UNIT
			$after = substr($address,strpos($address,$searchfor),strlen($address));//after UNIT

			$address1 = trim(ucwords(strtolower($before)));
			$address2 = trim(ucwords(strtolower($after)));
		} else {
			$address1 = trim(ucwords(strtolower($address)));
			$address2 = '';
		}
		
		//CREATE ARRAY
		/*
		$listingkey = $row_rs_properties['ListingKey'];
		*/
		
		//CREATES NUMERICALLY INDEXED ARRAY STARTING WITH 0
		$properties[$i] = array();
		$properties[$i]['listingkey'] = $row_rs_properties['ListingKey'];
		$properties[$i]['mlsnum'] = $row_rs_properties['MLS_NUM'];
		$properties[$i]['propertytype'] = $row_rs_properties['PropertyType'];
		$properties[$i]['address1'] = $address1;
		$properties[$i]['address2'] = $address2;
		$properties[$i]['city'] = $row_rs_properties['City'];
		$properties[$i]['province'] = $row_rs_properties['StateOrProvince'];
		$properties[$i]['postalcode'] =	$row_rs_properties['PostalCode'];
		$properties[$i]['listprice'] = $row_rs_properties['ListPrice'];		
		//$properties[$i]['ownershiptype'] = $row_rs_properties['OwnershipType'];
		$properties[$i]['bedrooms'] = $row_rs_properties['BedroomsTotal'];
		$properties[$i]['bathrooms'] = $row_rs_properties['BathroomsTotal'];
		$properties[$i]['latitude'] = $row_rs_properties['Lat'];
		$properties[$i]['longitude'] = $row_rs_properties['Lng'];
		//$properties[$i]['description'] = $row_rs_properties['PublicRemarks'];
		
		/*
		//CREATES OBJECT, NOT ARRAY => BECAUSE ARRAY KEYS MUST BE SEQUENTIAL
		$properties[$i][$listingkey] = array();
		//$properties[$i]['listingkey']['mlsnum'] = $row_rs_properties['MLS_NUM'];
		$properties[$i][$listingkey]['propertytype'] = $row_rs_properties['PropertyType'];
		$properties[$i][$listingkey]['address'] = $address;
		$properties[$i][$listingkey]['listprice'] = $row_rs_properties['ListPrice'];		
		//$properties['listingkey']['ownershiptype'] = $row_rs_properties['OwnershipType'];
		$properties[$i][$listingkey]['bedrooms'] = $row_rs_properties['BedroomsTotal'];
		$properties[$i][$listingkey]['bathrooms'] = $row_rs_properties['BathroomsTotal'];
		$properties[$i][$listingkey]['latitude'] = $row_rs_properties['Lat'];
		$properties[$i][$listingkey]['longitude'] = $row_rs_properties['Lng'];
		//$properties['listingkey']['description'] = $row_rs_properties['PublicRemarks'];
		*/
		
		/*
		//CREATES OBJECT, NOT ARRAY => BECAUSE ARRAY KEYS MUST BE SEQUENTIAL
		$properties[$listingkey] = array();
		$properties[$listingkey]['propertytype'] = $row_rs_properties['PropertyType'];
		$properties[$listingkey]['address'] = $address;
		$properties[$listingkey]['listprice'] = $row_rs_properties['ListPrice'];
		$properties[$listingkey]['bedrooms'] = $row_rs_properties['BedroomsTotal'];
		$properties[$listingkey]['bathrooms'] = $row_rs_properties['BathroomsTotal'];
		$properties[$listingkey]['latitude'] = $row_rs_properties['Lat'];
		$properties[$listingkey]['longitude'] = $row_rs_properties['Lng'];
		*/		
		
		/*
		//CREATE ARRAY OF OBJECTS, BUT THE LISTINGKEY CANNOT BE USED FOR SEARCHING
		$properties[$listingkey] = array();	
		//$id = $i + 1;
		//$properties[$listingkey]['id'] = "".$id."";
		//$properties[$listingkey]['mlsnum'] = $row_rs_properties['MLS_NUM'];
		$properties[$listingkey]['propertytype'] = $row_rs_properties['PropertyType'];
		$properties[$listingkey]['address'] = $address;
		$properties[$listingkey]['listprice'] = $row_rs_properties['ListPrice'];
		//$properties[$listingkey]['ownershiptype'] = $row_rs_properties['OwnershipType'];
		$properties[$listingkey]['bedrooms'] = $row_rs_properties['BedroomsTotal'];
		$properties[$listingkey]['bathrooms'] = $row_rs_properties['BathroomsTotal'];
		$properties[$listingkey]['latitude'] = $row_rs_properties['Lat'];
		$properties[$listingkey]['longitude'] = $row_rs_properties['Lng'];
		//$properties[$listingkey]['description'] = $row_rs_properties['PublicRemarks'];
		*/
	}

	//code block used to view JSON in browser
	if($live !== true) {
		echo "Change header to text/javascript to view Pretty JSON.  Change header back to application/json to send JSON.\n";
		$properties = stripslashes(json_encode($properties, JSON_PRETTY_PRINT));
		echo $properties;
		
		$jsonTime = microtime(true) - $start;
		echo "\nJSON encoded in ".$jsonTime." seconds with total rows found in query: ".$totalRows_rs_properties."";
	} else {
		//echo "\"".$properties = stripslashes(json_encode($properties))."\"";
		echo $properties = json_encode($properties);
	}
}
?>

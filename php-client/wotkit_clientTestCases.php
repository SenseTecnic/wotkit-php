<html>
<head>
<title>WoTKIT API PHP-Test-Client </title>
<link href="lib/css/bootstrap.min.css" rel="stylesheet" media="screen" />
<link href="lib/css/custom.css" rel="stylesheet"/>
<body>
<script src="http://code.jquery.com/jquery.js"></script>
<script src="lib/js/bootstrap.min.js"></script>
<?php
/*
 * Run this script for General Key Based Testing
 * (assumes newly initilized database)
 * NOTE: any function run "as admin" uses a key NOT an access token
 *
 */


/**
 * Client Set-Up
 **/
 
require_once('wotkit_client.php');
require_once('wotkit_clientConfig.php');

//SERVER & AUTHENTICATION
$wotkit_client = new wotkit_client(BASE_URL, CLIENT_ID, CLIENT_SECRET);

//---------------------------------------------------------------------------------------//

/**
 * Set Test Case Variables
 **/
$failures = 0;
$test_count = 0;

//SENSOR NAMES 
	//-sensor ids or names can be used for updating sensor or sensor data
	$generic_sensor = "api-client-test-sensor"; //non-existent
	$additional_generic_sensor = "api-client-test-sensor-additional"; //non-existent
	$existing_data_sensor =  //pre-existing 40-42
		array("api-data-test-1", //id=40 - has pre-supplied data
			  "api-data-test-2", //id=41 - is an 'actuator'
			  "api-data-test-3"); //id=42 - has data added and then deleted

	$existing_data_sensor_full = 
		array("tester.api-data-test-1","tester.api-data-test-2","tester.api-data-test-3");
	$unowned_sensor_full = "sensetecnic.mule1"; //id=1
	$unowned_sensor_short = "mule1";
	$unowned_actuator_full = "sensetecnic.qofob"; //id=13
	$private_unowned_sensor = "sensetecnic.api-test-private"; //id=43
	
//SENSOR INPUTS	
	//-currently able to update sensor name
	//-no error when trying to update owner	
	$new_sensor_input = array(
	//"private"=>false, 
	"visibility"=>"PUBLIC",
	"name"=>$generic_sensor, 
	"description"=>"api client test sensor desc", 
	"longName"=>"api client test sensor long", 
	"latitude"=>4, 
	"longitude"=>6,
	"tags"=>array("testing the tags","t e s t i n g ","tags"));

	$updated_sensor_input_1 = array(
	 "name"=>$generic_sensor, 
	 "longName"=>"api client test sensor long updated", 
	 "description"=>"api client test sensor desc updated",
	 "latitude"=>55,
	 "longitude"=>-125,
	 //"private"=>true,
	 "visibility"=>"PRIVATE",
	 "tags"=>array("updating the tags","tags"));
	 //"fields"=>[{"name":"value","longName":"Data","type":"NUMBER","units":"cm"}]);
	 //"owner"=>"can't be changed"));
	 
	$updated_sensor_input_2 = array(
	 "name"=>$generic_sensor, 
	 "longName"=>"api client test sensor long", 
	 "description"=>"api client test sensor desc",
	 //"private"=>false
	 "visibility"=>"PUBLIC"
	 );
	 
	$updated_sensor_input_3 = array(
	 "name"=> $unowned_sensor_short, 
	 "longName"=>$unowned_sensor_full, 
	 "description"=>$unowned_sensor_full);
	 
	$additional_sensor_input = array(
	//"private"=>false, 
	"visibility"=>"PUBLIC",
	"name"=>$additional_generic_sensor, 
	"description"=>"api client test sensor additional desc", 
	"longName"=>"api client test sensor additional long", 
	"latitude"=>4, 
	"longitude"=>6,
	"tags"=>array("testing the tags","t e s t i n g ","tags", "additional"));
	
	$invalid_sensor_input = array(
	"name"=>"invalid-sensor", 
	"description"=>"invalid sensor desc", 
	"latitude"=>4, 
	"longitude"=>6);
	//can include any fake fields and sensor will NOT be invalid

//SENSOR FIELDS	
	$invalid_field_required = array("name"=>"testfield", "longName"=>"Test Field","units"=>"mm");	
	$invalid_field_default = array("name"=>"value", "type"=>"STRING");
	$new_field = array ("required"=>true,"name"=>"testfield", "longName"=>"Test Field", "type"=>"NUMBER",  "units"=>"mm");	
	$updated_field = array ("required"=>false, "name"=>"testfield","longName"=>"Updated Test Field", "type"=>"STRING","units"=>"cm");	
	$num_field = array ("required"=>false,"name"=>"numtestfield", "longName"=>"Num Test Field", "type"=>"NUMBER");	
	$string_field = array ("required"=>false,"name"=>"stringtestfield", "longName"=>"String Test Field", "type"=>"STRING");	

//SENSOR DATA
	$nonStandard_sensor_data = array( "value" => 5, "lat" => 6, "lng" => 7, 
								       "message" => "test message with test field", "testfield"=>9);	 
//QUERYING SENSOR DATA 
	$start_time = strtotime("7 January 2013 14:00")*1000;
	$end_time = strtotime("8 January 2013 13:00")*1000;	
	$location_vancouver =  array(50,-124,48,-122); //N,W,S,E
	$location_edmonton = array(54,-114,52,-113);//N,W,S,E
	$location_winnipeg =  array(50,-98,48,-96); //N,W,S,E
	$location_kilkenny =  array(53,-8,52,-7); //N,W,S,E
	$location_invalid_ns =  array(1,4,3,2); //N,W,S,E
	$location_invalid_toolarge =  array(100,4,3,2); //N,W,S,E

//ACTUATOR NAME AND INPUTS
	$actuator_name = $existing_data_sensor[1];
	$actuator_name_full = $existing_data_sensor_full[1]; 
	$actuator_message = array ("button"=>"on","slider"=>15, "message"=>"test message");	
	$actuator_message_display = "button=on&slider=15&message=test message";	

//USERS
	$new_user_name = "newuser";
	$invalid_user_name = "3";
	
	$invalid_name_user_input = array(
	"username" => $invalid_user_name, 
	"firstname" => "API Testing",
	"lastname" => "Lastname",
	"email" => "email@address.com", 
	"password" => "password");
	
	$missing_property_user_input = array(
	"username" => $new_user_name,	
	"firstname" => "API Testing",
	"email" => "email@address.com", 
	"password" => "password");
	
	$new_user_input = array(
	"username" => $new_user_name,
	"firstname" => "API Testing",
	"lastname" => "Lastname",
	"email" => "email@address.com", 
	"password" => "password");
	
	$invalid_updated_user_input = array(
	"username" => "changed_user_name");
	
	$updated_user_input = array(
	"username" => $new_user_name,
	"firstname" => "Firstname Changed",
	"email" => "new_email@address.com",
	"password" => "password2");

//ORGANIZATIONS
	$new_org_mandatory = array(
	"name"=>"test-organization", 
	"longName"=>"First Test Organization");
	$new_org_invalid_name = array(
	"name"=>"0", 
	"longName"=>"Invalid Organization");
	$new_org_missing_longname = array("name"=>"invalid-organization");
	$updated_org_name = array(
	"name"=>"updated-test-organiztion", 
	"longName"=>"First Test Organization Updated");	
	$updated_org_all = array(
	"longName"=>"First Test Organization Updated", 
	"description"=>"Updated with description", 
	"imageUrl"=>"placeholder");
	
	$new_org_all = array(
	"name"=>"test-organization-2", 
	"longName"=>"Second Test Organization",
	"description"=>"Second Test Organization Description", 
	"imageUrl"=>"placeholder");					
	$updated_org_longName = array("longName"=>"ONLY FIELD NOT NULL");
	
	$added_members = array('tester', 'tester-admin');
	
	$org_sensor_input = array(
	"organization"=>$new_org_all['name'],
	"visibility"=>"ORGANIZATION",
	"name"=>"org-sensor", 
	"description"=>"sensor for org: ".$new_org_all['name'], 
	"longName"=>"org sensor", 
	"latitude"=>44, 
	"longitude"=>66);
	
//Table of Contents	
	$toc_keys = array('Sensors', 'Subscriptions', 'Data', 'Raw Data', 'Formatted Data', 
	                  'Querying Sensors', 'Aggregate Sensor Data', 'Actuators', 'Users', 'News', 
					  'Stats', 'Tags', 'Organizations', 'Public Functions', 'Results');

//---------------------------------------------------------------------------------------//

echo '<div class="container">';
echo '<div class="pager">';
foreach ($toc_keys as $label){
	echo '<a href="#'.$label.'">'.$label.'</a> ** ';
}
echo '</div>';

/**
 * Begin Tests
 **/

//SENSORS

printLabel($toc_key[0],"[*****TESTING SENSORS******]");

printLabel(null,"....testing creation of multiple sensors.......", true);
#Create TWO sensors: 'api-client-test-sensor' & 'api-client-test-sensor_additional' 
#Create MULTIPLE sensors 
	$title = "\n\n [CREATE MULTIPLE sensors: '".$generic_sensor."' & '".$additional_generic_sensor."'] \n";
	$expected = 2;
	$response = $wotkit_client->createMultipleSensor(array($new_sensor_input, $additional_sensor_input), true);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'],array($generic_sensor=>true,$additional_generic_sensor=>true));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);

#Create TWO EXISTING sensors 'api-client-test-sensor' & 'api-client-test-sensor_additional'
#Create MULTIPLE existing sensors	
	$title="\n\n [CREATE multiple EXISTING sensors: '".$generic_sensor."' & '".$additional_generic_sensor."'] \n";
	$response = $wotkit_client->createMultipleSensor(array($new_sensor_input, $additional_sensor_input), true);	
	$test_status = $wotkit_client->checkHTTPcode(409);
	$problem = checkError($response['data'], 'already exists', 'already exists');
	displayTestResults ($problem, false, $title, $test_status, $response);

#Create AN INVALID sensor
//can include any fake fields and sensor will NOT be invalid
//excluding a manadatory field WILL make sensor invalid
	$title = "\n\n [CREATE an INVALID sensor by excluding longName - a mandatory field] \n";
	$response = $wotkit_client->createMultipleSensor(array($invalid_sensor_input), true);	
	$test_status = $wotkit_client->checkHTTPcode(400);
	$problem = checkError($response['data'], 'Missing required field', 'longName');
	displayTestResults ($problem, false, $title, $test_status, $response);

#Query  'api-client-test-sensor'
#Check for a SINGLE sensor that DOES exist
	$title = "\n\n [QUERY sensor: '".$generic_sensor."']\n";
	$response = $wotkit_client->getSensors($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $new_sensor_input); 
	displayTestResults ($problem, false, $title, $test_status, $response);

#Delete 'api-client-test-sensor'
#Delete a SINGLE sensor that DOES exist
	$title = "\n\n [DELETE sensor: '".$generic_sensor."'] \n";
	$response = $wotkit_client->deleteSensor($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);

#Query 'api-client-test-sensor-additional'
#Check for a SINGLE sensor that DOES exist
	$title = "\n\n [QUERY sensor: '".$additional_generic_sensor."']\n";
	$response = $wotkit_client->getSensors($additional_generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	//$problem = checkArraysEqual($response['data'], $additional_sensor_input); 
	//displayTestResults ($problem, false, $title, $test_status, $response);
	displayTestResults (null, true, $title, $test_status, $response);

#Delete 'api-client-test-sensor_additional'
#Delete a SINGLE sensor that DOES exist
	$title = "\n\n [DELETE sensor: '".$additional_generic_sensor."'] \n";
	$response = $wotkit_client->deleteSensor($additional_generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
printLabel(null, "....done testing creation of multiple sensors.......", true);

	
#Create 'api-client-test-sensor'
#Create new sensor
	$title = "\n\n [CREATE sensor: '".$generic_sensor."'] \n";
	$response = $wotkit_client->createSensor($new_sensor_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);

#Create the already existing 'api-client-test-sensor'
#Create an existing sensor
	$title = "\n\n [CREATE EXISTING sensor: '".$generic_sensor."'] \n";
	$response = $wotkit_client->createSensor($new_sensor_input);	
	$test_status = $wotkit_client->checkHTTPcode(409);
	$problem = checkError($response['data'], 'already exists', 'already exists');
	displayTestResults ($problem, false, $title, $test_status, $response);


#Query created 'api-client-test-sensor'
#Check for a SINGLE sensor that DOES exist
	$title = "\n\n [QUERY sensor: '".$generic_sensor."']\n";
	$response = $wotkit_client->getSensors($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'],$new_sensor_input); 
	displayTestResults ($problem, false, $title, $test_status, $response);
	
	
//!!!!!!!CAN change sensor name	
#Update 'api-client-test-sensor'
#Update longname(required), description(required), privacy, lat, lng, tags   
	$title = "\n\n [UPDATE longname, description, visibilty, lat, lng, & tags for sensor: '".$generic_sensor."']\n";
	$response = $wotkit_client->updateSensor($generic_sensor, $updated_sensor_input_1);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
#Query created 'api-client-test-sensor'
#Check for a SINGLE updated sensor that DOES exist
	$title = "\n\n [QUERY sensor: '".$generic_sensor."']\n";
	$response = $wotkit_client->getSensors ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $updated_sensor_input_1);
	displayTestResults ($problem, false, $title, $test_status, $response);
		
#Update 'api-client-test-sensor'
#Update name, longname, description (required)  
	$title = "\n\n [UPDATE longname, description, & visibilty for sensor:'".$generic_sensor."']\n";
	$response = $wotkit_client->updateSensor($generic_sensor, $updated_sensor_input_2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
#Query created 'api-client-test-sensor'
#Check for a SINGLE updated sensor that DOES exist
	$title = "\n\n [QUERY created '".$generic_sensor."']\n";
	$response = $wotkit_client->getSensors ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $updated_sensor_input_2);
	displayTestResults ($problem, false, $title, $test_status, $response);

	

	//----------------Sensor Fields---------------//
	//SENSOR FIELDS
	printLabel(null, ".....testing sensor fields......", true);

	#Query mulitple fields for 'api-client-test-sensor'
		$title = "\n\n [QUERY multiple fields for sensor: '".$generic_sensor."']\n";
		$expected = 4;
		$response = $wotkit_client->getSensorFields ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkTagsOrSensors($response['data'], array('value', 'lat', 'lng', 'message'));
		displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);

	#Create incomplete field to 'api-client-test-sensor'	
		$title = "\n\n [CREATE new field with INCOMPLETE field information for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->updateSensorField ($generic_sensor, $invalid_field_required);
		$test_status = $wotkit_client->checkHTTPcode(400);
		$problem = checkError($response['data'], 'Missing required field');
		displayTestResults ($problem, false, $title, $test_status, $response);
		
	#Update default field of 'api-client-test-sensor'	
		$title = "\n\n [UPDATE protected subfield of the default field 'value' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->updateSensorField ($generic_sensor, $invalid_field_default);
		$test_status = $wotkit_client->checkHTTPcode(400);
		$problem = checkError($response['data'], 'Missing required field', 'Cannot change');
		displayTestResults ($problem, false, $title, $test_status, $response);
		
	#Create new field to 'api-client-test-sensor'	
		$title = "\n\n [CREATE new field '".$new_field[name]."' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->updateSensorField ($generic_sensor, $new_field);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);	
		
	#Query single "testfield" field for 'api-client-test-sensor'
		$title =  "\n\n [QUERY single field '".$new_field[name]."' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->getSensorFields ($generic_sensor, $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkArraysEqual($response['data'], $new_field);
		displayTestResults ($problem, false, $title, $test_status, $response);
		
	#Update "testfield" field for 'api-client-test-sensor'
		$title = "\n\n [UPDATE existing field '".$new_field[name]."' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->updateSensorField ($generic_sensor, $updated_field);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);

	#Query single "testfield" field for 'api-client-test-sensor'
		$title = "\n\n [QUERY single field '".$new_field[name]."' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->getSensorFields ($generic_sensor, $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkArraysEqual($response['data'], $updated_field);
		displayTestResults ($problem, false, $title, $test_status, $response);
		
	#Query mulitple fields for 'api-client-test-sensor'
		$title = "\n\n [QUERY multiple fields for sensor: '".$generic_sensor."']\n";
		$expected = 5;
		$response = $wotkit_client->getSensorFields ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkTagsOrSensors($response['data'], array('value', 'lat', 'lng', 'message', $new_field['name']));
		displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);
		
	#Send data to 'testfield' field for 'api-client-test-sensor'
		$title = "\n\n [SEND data to all fields for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->sendNonStandardSensorData($generic_sensor, $nonStandard_sensor_data);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
		
	#Query sensor data for 'api-client-test-sensor'
		$title = "\n\n [QUERY sensor data for sensor: '".$generic_sensor."']\n";
		$expected = 1;
		$response = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, true, $title, $test_status, $response, $expected, true);
		
	#Query single "testfield" field for 'api-client-test-sensor'
		$title = "\n\n [QUERY single custom field '".$new_field[name]."' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->getSensorFields ($generic_sensor, $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);

	#Query single "value" field for 'api-client-test-sensor'
		$title = "\n\n [QUERY single default field 'value' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->getSensorFields ($generic_sensor, "value");
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);

	#Query mulitple fields for 'api-client-test-sensor'
		$title = "\n\n [QUERY multiple fields for sensor: '".$generic_sensor."']\n";
		$expected = 5;
		$response = $wotkit_client->getSensorFields ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, true, $title, $test_status, $response, $expected, true);		
	
	#Delete "testfield" field for 'api-client-test-sensor'
		$title = "\n\n [DELETE single field '".$new_field[name]."' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->deleteSensorField ($generic_sensor, $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
		
	#Query mulitple fields for 'api-client-test-sensor'
		$title = "\n\n [QUERY multiple fields for sensor: '".$generic_sensor."']\n";
		$expected = 4;
		$response = $wotkit_client->getSensorFields ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkTagsOrSensors($response['data'], array('value', 'lat', 'lng', 'message'));
		displayTestResults ($problem, true, $title, $test_status, $response, $expected, true);
			
	#Query deleted "testfield" field for 'api-client-test-sensor'
		$title = "\n\n [QUERY deleted field '".$new_field[name]."' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->getSensorFields ($generic_sensor, $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode(400);
		displayTestResults (null, false, $title, $test_status, $response);

	#Delete required "value" field for 'api-client-test-sensor'
		$title = "\n\n [DELETE required field 'value' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->deleteSensorField ($generic_sensor, "value");
		$test_status = $wotkit_client->checkHTTPcode(400);
		$problem = checkError($response['data'], 'Missing required field', 'cannot be deleted');
		displayTestResults ($problem, false, $title, $test_status, $response);
		
	#Query mulitple fields for 'api-client-test-sensor'
		$title = "\n\n [QUERY multiple fields for sensor: '".$generic_sensor."']\n";
		$expected = 4;
		$response = $wotkit_client->getSensorFields ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkTagsOrSensors($response['data'], array('value', 'lat', 'lng', 'message'));
		displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);	

	printLabel(null, ".....done testing sensor fields......", true);
	//-------------------------------------------------------------//


#Delete 'api-client-test-sensor'
#Delete a SINGLE sensor that DOES exist
	$title = "\n\n [DELETE sensor: '".$generic_sensor."'] \n";
	$response = $wotkit_client->deleteSensor($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);

#Query deleted 'api-client-test-sensor'
#Check for a SINGLE sensor that DOES NOT exist
	$title = "\n\n [QUERY deleted sensor: '".$generic_sensor."']\n";
	$response = $wotkit_client->getSensors($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], 'No sensor');
	displayTestResults ($problem, false, $title, $test_status, $response);

#Query deleted 'api-client-test-sensor_additional'
#Check for a SINGLE sensor that DOES NOT exist
	$title = "\n\n [QUERY deleted sensor: '".$additional_generic_sensor."']\n";
	$response = $wotkit_client->getSensors($additional_generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], 'No sensor');
	displayTestResults ($problem, false, $title, $test_status, $response);
	
#Query private sensor 'sensetecnic.api-test-private'
#Check for a SINGLE PRIVATE sensor 
	$title = "\n\n [QUERY private, unowned sensor: '".$private_unowned_sensor."']\n";
	$response = $wotkit_client->getSensors($private_unowned_sensor);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayTestResults (null, false, $title, $test_status, $response);

#Delete non-existent sensor 'not-real-sensor'
#Delete a SINGLE sensor that DOES NOT exist
	$title = "\n\n [DELETE sensor: 'not-real-sensor'] \n";
	$response = $wotkit_client->deleteSensor( "not-real-sensor");
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], 'No sensor');
	displayTestResults ($problem, false, $title, $test_status, $response);
	
#Update another user's sensor
#Update a sensor you don't own
	$title = "\n\n [UPDATE another user's sensor: '".$unowned_sensor_full."']\n";
	$response = $wotkit_client->updateSensor($unowned_sensor_full, $updated_sensor_input_3);
	$test_status = $wotkit_client->checkHTTPcode(401);
	$problem = checkError($response['data'], 'Not the owner');
	displayTestResults ($problem, false, $title, $test_status, $response);



//SENSOR SUBSCRIPTIONS
printLabel($toc_keys[1], "[*****TESTING SENSOR SUBSCRIPTIONS******]");	

#Get subscribed sensors
	$title = "\n\n [QUERY subscribed sensors]\n";
	$expected = 3;
	$response = $wotkit_client->getSubscribedSensors();
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1], $unowned_sensor_short));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);
	
#Subscribe to a non-private sensor
	$title = "\n\n [SUBSCRIBE to sensor: '".$existing_data_sensor[2]."']\n";
	$response = $wotkit_client->subscribeSensor($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
#Subscribe to an already subscribed sensor
	$title = "\n\n [SUBSCRIBE to already subscribed sensor: '".$existing_data_sensor[2]."']\n";
	$response = $wotkit_client->subscribeSensor($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode(401);
	$problem = checkError($response['data'], 'already subscribed');
	displayTestResults ($problem, false, $title, $test_status, $response);

#Subscribed to a private, non-owned sensor 
	$title = "\n\n [SUBSCRIBE to another user's private sensor: '".$private_unowned_sensor."']\n";
	$response = $wotkit_client->subscribeSensor($private_unowned_sensor);
	$test_status = $wotkit_client->checkHTTPcode(401);
	$problem = checkError($response['data'], 'is private');
	displayTestResults ($problem, false, $title, $test_status, $response);

#Get subscribed sensors
	$title = "\n\n [QUERY subscribed sensors]\n";
	$expected = 4;
	$response = $wotkit_client->getSubscribedSensors();
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1], $existing_data_sensor[2], $unowned_sensor_short));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);

#Unsubscribe sensor	
	$title = "\n\n [UNSUBSCRIBE from sensor: '".$existing_data_sensor[2]."']\n";
	$response = $wotkit_client->unsubscribeSensor($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
#Unsubscribe from not subscribed sensor	
	$title = "\n\n [UNSUBSCRIBE from already unsubscribed sensor: '".$existing_data_sensor[2]."']\n";
	$response = $wotkit_client->unsubscribeSensor($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode(401);
	$problem = checkError($response['data'], 'not subscribed');
	displayTestResults ($problem, false, $title, $test_status, $response);
	
#Get subscribed sensors
	$title = "\n\n [QUERY subscribed sensors]\n";
	$expected = 3;
	$response = $wotkit_client->getSubscribedSensors();
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1], $unowned_sensor_short));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);

	

//SENSOR DATA
printLabel($toc_keys[2], "[*****TESTING SENSOR DATA******]");

	//----------------Posting Sensor Data as Name/Value or JSON---------------//
	printLabel(null, "[......testing different ways of sending sensor data..........]", true);

	#Create 'api-client-test-sensor'
	#Create new sensor
		$title = "\n\n [CREATE sensor: '".$generic_sensor."'] \n";
		$response = $wotkit_client->createSensor($new_sensor_input);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
		
	#Create new field for 'api-client-test-sensor'	
		$title = "\n\n [CREATE numeric, non-required field '".$num_field[name]."' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->updateSensorField ($generic_sensor, $num_field);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);	

	#Create new field for 'api-client-test-sensor'	
		$title = "\n\n [CREATE string, non-required field '".$string_field[name]."' for sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->updateSensorField ($generic_sensor, $string_field);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response, null, true);	
		
	#Post Name/Value pair valid data
		$data_array = array( "value" => 1, "lat" => 2, "lng" => 2, 
							"message" => "test message", 
							$num_field[name]=>9, $string_field[name]=>"hello name/value string!");
		$title = "\n\n [POST Name/Value pair valid data to sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
		
		$title = "\n\n [QUERY data from sensor: '".$generic_sensor."']\n";
		$expected = 1;
		$response = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkArraysEqual($response['data'][0], $data_array);
		displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);

	#Post JSON valid data
		$data_array = array( "value" => 1, "lat" => 2, "lng" => 2, 
							"message" => "test message", 
							$num_field[name]=>99, $string_field[name]=>"hello JSON string!");
		$title = "\n\n [POST JSON valid data to sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array, true);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
		
		$title = "\n\n [QUERY data from sensor: '".$generic_sensor."']\n";
		$expected = 2;
		$response = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkArraysEqual($response['data'][1], $data_array);
		displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);

	#Post Name/Value pair invalid data
		$data_array = array( "value" => 5, "lat" => 6, "lng" => 7, 
							"message" => "test message with test field", 
							$num_field[name]=>"hello", $string_field[name]=>9);
		$title = "\n\n [POST Name/Value pair invalid data -string to numerical field- to sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array);
		$test_status = $wotkit_client->checkHTTPcode(400);
		$problem = checkError($response['data'], 'Invalid field content');
		displayTestResults ($problem, false, $title, $test_status, $response);
		
		$title = "\n\n [QUERY data from sensor: '".$generic_sensor."']\n";
		$expected = 2;
		$response = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response, $expected, true);

	#Post JSON invalid data
		$title = "\n\n [POST JSON invalid data -string to numerical field- to sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array, true);
		$test_status = $wotkit_client->checkHTTPcode(400);
		$problem = checkError($response['data'], 'Invalid field content');
		displayTestResults ($problem, false, $title, $test_status, $response);
		
		$title = "\n\n [QUERY data from sensor: '".$generic_sensor."']\n";
		$expected = 2;
		$response = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response, $expected, true);

	#Post Name/Value pair undeclared data
		$data_array = array( "value" => 5, "lat" => 6, "lng" => 7, 
							"message" => "test message with test field", 
							$num_field[name]=>9, $string_field[name]=>"hello",
							"madeupNUMfield"=>9, "madeupSTRINGfield"=>"hi name/value!");
		$title = "\n\n [POST Name/Value pair undeclared data to sensor: '".$generic_sensor."']\n";
		$response = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
		
		$title = "\n\n [QUERY data from sensor: '".$generic_sensor."']\n";
		$expected = 3;
		$response = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkArraysEqual($response['data'][2], $data_array);
		displayTestResults ($problem, false, $title, $test_status, $response, $expected);

	#Post JSON undeclared data
		$data_array = array( "value" => 5, "lat" => 6, "lng" => 7, 
							"message" => "test message with test field", 
							$num_field[name]=>9, $string_field[name]=>"hello",
							"madeupNUMfield"=>99, "madeupSTRINGfield"=>"hi JSON!");
		$title = "\n\n [POST JSON undeclared data to sensor: '".$generic_sensor." -- numerical field should be recognized']\n";
		$response = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array, true);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
		
		$title = "\n\n [QUERY data from sensor: '".$generic_sensor."']\n";
		$expected = 4;
		$response = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkArraysEqual($response['data'][3], $data_array);
		if ($problem)
			$problem = ! is_numeric($response['data'][3]['madeupNUMfield']);
		displayTestResults ($problem, false, $title, $test_status, $response, $expected);

	#Delete 'api-client-test-sensor'
	#Delete a SINGLE sensor that DOES exist
		$title = "\n\n [DELETE sensor: '".$generic_sensor."'] \n";
		$response = $wotkit_client->deleteSensor($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);

	printLabel(null, "[.............done testing sending sensor data in different ways..........]", true);
	//----------------------------------------------------------------------------------//



#Send data to another user's sensor 'sensetecnic.mule1'
#Send data to a sensor you don't own
	$title = "\n\n [UPDATE DATA from another user's sensor: '".$unowned_sensor_full."']\n";
	$value = rand(1,100);
	$lat = rand(1,100);
	$lng = rand(1,100);
	$message = "test message #"; 
	$response = $wotkit_client->sendSensorData( $unowned_sensor_full,$value, $lat, $lng, $message);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayTestResults (null, false, $title, $test_status, $response);


#Sending 3 pieces of data to 'api-data-test-3'
#Sending 3 pieces data to existing sensor
	for($i=1; $i<=3; $i++)
	{	$value = rand(1,100);
		$lat = rand(1,100);
		$lng = rand(1,100);
		$message = "test message ".($i+10); 
		$data_title = "\n value=".$value." lat=".$lat." lng=".$lng." message=".$message." \n";
		$title = "[SEND data -".$data_title."- to sensor: '".$existing_data_sensor[2]."']";
		$response = $wotkit_client->sendSensorData($existing_data_sensor[2], $value, $lat, $lng, $message);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
	};

#Query data from 'api-data-test-3'
#Query data from existing sensor
	$title = "\n\n [QUERY data from sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 3;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, true, $title, $test_status, $response, $expected, true);

#Update 2nd piece of data from 'api-data-test-3' (using timestamp as long)
#Update 2nd piece of data from existing sensor
	$title = "\n\n [UPDATE 2nd data piece (using timestamp as long) from sensor:'".$existing_data_sensor[2]."'] \n";
	$saved_response = json_decode($wotkit_client->response, true);
	$updated_sensor_data = array(array("timestamp" =>$saved_response[1][timestamp],
	 "value"=>100,
	 "lat"=>100,
	 "lng"=>100,
	 "message"=>"updated fields")
	 );
	$response = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);

	
#Query data from existing sensor
	$title = "\n\n [QUERY data from sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 3;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'][1], $updated_sensor_data);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);	
	
#Send new piece of data	
   $title = "\n\n [Send 'new' piece of data to sensor: '".$existing_data_sensor[2]."'] \n"; 
	$old_timezone  = date_default_timezone_get();
	date_default_timezone_set('UTC');
	$timestamp_number = time()*1000;
	$sensor_data = array(
	 "timestamp" =>$timestamp_number,
	 "value"=>100,
	 "lat"=>100,
	 "lng"=>100,
	 "message"=>"new data");
	$response = $wotkit_client->sendNonStandardSensorData($existing_data_sensor[2], $sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);

#Query data from existing sensor
	$title = "\n\n [QUERY data from sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 4;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'][3], $sensor_data);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);		
	
#Update new piece of data from 'api-data-test-3' (using timestamp as string)
#Update new piece of data from existing sensor
	$title = "\n\n [UPDATE 'new' data (using timestamp as string) from sensor: '".$existing_data_sensor[2]."'] \n";
	$timestamp_string = date('o-m-d!G:i:s', $timestamp_number/1000);
	$timestamp_string = str_replace('!', 'T', $timestamp_string);
	$timestamp_string .= ".000z";
	$updated_sensor_data = array(array("timestamp" =>$timestamp_string,
	 "value"=>600,
	 "lat"=>600,
	 "lng"=>600,
	 "message"=>"updated new data")
	 );
	$response = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);	

#Query data from existing sensor
	$title = "\n\n [QUERY data from sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 4;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'][3], $updated_sensor_data);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);		

#Delete new data from 'api-data-test-3'
#Delete new data from existing sensor
	$title = "\n\n [DELETE 'new' data from sensor: '".$existing_data_sensor[2]."'] \n";
	$response = $wotkit_client->deleteSensorData($existing_data_sensor[2], $timestamp_number);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);

#Query data from existing sensor
	$title = "\n\n [QUERY data from sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 3;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected, true);	
	$saved_data = $response['data'];
	
date_default_timezone_set($old_timezone);

#Update 2nd piece of data from 'api-data-test-3' with INVALID DATA (string in numerical field)
#Update 2nd piece of data from existing sensor with INVALID DATA 
	$title = "\n\n [UPDATE 2nd data piece with INVALID DATA (string in numerical field) for sensor: '".$existing_data_sensor[2]."'] \n";
	$updated_sensor_data = array(array("timestamp" =>$saved_response[1][timestamp],
	 "value"=>"100string",
	 "lat"=>"100string",
	 "lng"=>"100string",
	 "message"=>"updated fields")
	 );
	$response = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode(400);
	$problem = checkError($response['data'], 'Invalid field content', 'expected NUMBER');
	displayTestResults ($problem, false, $title, $test_status, $response);
	
#Update 2nd piece of data from 'api-data-test-3' with INVALID DATA (missing required field)
#Update 2nd piece of data from existing sensor with INVALID DATA
	$title = "\n\n [UPDATE 2nd data piece with INVALID DATA (missing required field) for sensor: '".$existing_data_sensor[2]."'] \n";
	$updated_sensor_data = array(array("timestamp" =>$saved_response[1][timestamp],
	 "lat"=>100,
	 "lng"=>100,
	 "message"=>"updated fields")
	 );
	$response = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode(400);
	$problem = checkError($response['data'], 'Missing required field', 'value');
	displayTestResults ($problem, false, $title, $test_status, $response);

#Update 2nd piece of data from 'api-data-test-3' with INVALID DATA (data from future)
#Update 2nd piece of data from existing sensor with INVALID DATA
	$title = "\n\n [UPDATE 2nd data piece with INVALID DATA (data from future) for sensor: '".$existing_data_sensor[2]."'] \n";
	$updated_sensor_data = array(array("timestamp" => time()*1000 + 60000,
	 "value"=>100,
	 "lat"=>100,
	 "lng"=>100,
	 "message"=>"updated fields")
	 );
	$response = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode(400);
	$problem = checkError($response['data'], 'Invalid field content','future');
	displayTestResults ($problem, false, $title, $test_status, $response);	
	
#Query data from 'api-data-test-3'
#Query data from existing sensor
	$title = "\n\n [QUERY data from sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 3;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual ($response['data'][0], $saved_data[0]);
	if(!$problem)
		$problem = checkArraysEqual ($response['data'][1], $saved_data[1]);
	if(!$problem)
		$problem = checkArraysEqual ($response['data'][2], $saved_data[2]);	
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);
	
#Update all data from 'api-data-test-3'
#Update all data from existing sensor
	$title = "\n\n [UPDATE ALL data for sensor:'".$existing_data_sensor[2]."'] \n";
	$saved_response = json_decode($wotkit_client->response, true);
	end($saved_response);
	$last_key=key($saved_response);
	$updated_sensor_data = array(
	array("timestamp"=>$saved_response[0][timestamp],
	 "value"=>"6",
	 "lat"=>64,
	 "lng"=>-623,
	 "message"=>"start timestamp"),
	 array("timestamp"=>$saved_response[$last_key][timestamp],
	 "value"=>9,
	 "lat"=>94,
	 "lng"=>-913,
	 "message"=>"end timestamp")
	 );
	$response = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
#Query data from 'api-data-test-3'
#Query data from existing sensor
	$title = "\n\n [QUERY data from sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 2;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'][0], $updated_sensor_data[0]);
	if (!$problem)
		$problem = checkArraysEqual($response['data'][1], $updated_sensor_data[1]);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);
	
#Delete latest data from 'api-data-test-3'
#Delete latest data from existing sensor
	$title = "\n\n [DELETE latest data from sensor: '".$existing_data_sensor[2]."'] \n";
	$saved_response = json_decode($wotkit_client->response, true);
	end($saved_response);
	$last_key=key($saved_response);
	$time_stamp=$saved_response[$last_key][timestamp];
	$response = $wotkit_client->deleteSensorData( $existing_data_sensor[2], $time_stamp);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
#Querying data from 'api-data-test-3'
#Querying data from existing sensor
	$title = "\n\n [QUERY data from sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 1;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected, true);



	//----------------Sensor Fields---------------//
	//SENSOR FIELDS
	printLabel(null, ".....testing sensor fields......", true);

	#Query mulitple fields for 'api-data-test-3'
		$title = "\n\n [QUERY multiple fields from sensor: '".$existing_data_sensor[2]."']\n";
		$expected = 4;
		$response = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkTagsOrSensors($response['data'], array('value', 'lat', 'lng', 'message'));
		displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);
		
	#Create new field to 'api-data-test-3'	
		$title = "\n\n [CREATE new field '".$new_field[name]."' from sensor: '".$existing_data_sensor[2]."']\n";
		$response = $wotkit_client->updateSensorField ($existing_data_sensor[2], $new_field);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);	
		
	#Query mulitple fields for 'api-data-test-3'
		$title = "\n\n [QUERY multiple fields from sensor: '".$existing_data_sensor[2]."']\n";
		$expected = 5;
		$response = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkTagsOrSensors($response['data'], array('value', 'lat', 'lng', 'message', $new_field[name]));
		displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);

	#Query single "testfield" field for 'api-data-test-3'
		$title = "\n\n [QUERY single field '".$new_field[name]."' from sensor: '".$existing_data_sensor[2]."']\n";
		$response = $wotkit_client->getSensorFields ($existing_data_sensor[2], $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkArraysEqual($response['data'], $new_field);
		displayTestResults ($problem, false, $title, $test_status, $response);
		
	#Send data to 'testfield' field for 'api-data-test-3'
		$title = "\n\n [SEND data to all fields for sensor: '".$existing_data_sensor[2]."']\n";
		$response = $wotkit_client->sendNonStandardSensorData($existing_data_sensor[2], $nonStandard_sensor_data);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
		
	#Querying data from 'api-data-test-3'
	#Querying data from existing  sensor
		$title = "\n\n [QUERY all data from sensor: '".$existing_data_sensor[2]."'] \n";
		$expected = 2;
		$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkArraysEqual($response['data'][1], $nonStandard_sensor_data);
		displayTestResults ($problem, true, $title, $test_status, $response, $expected, true);
		
	#Query single "testfield" field for 'api-data-test-3'
		$title = "\n\n [QUERY single field '".$new_field[name]."' from sensor: '".$existing_data_sensor[2]."']\n";
		$response = $wotkit_client->getSensorFields ($existing_data_sensor[2], $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
		
	#Query mulitple fields for 'api-data-test-3'
		$title = "\n\n [QUERY multiple fields from sensor: '".$existing_data_sensor[2]."']\n";
		$expected = 5;
		$response = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkTagsOrSensors($response['data'], array('value', 'lat', 'lng', 'message', $new_field[name]));
		displayTestResults ($problem, true, $title, $test_status, $response, $expected, true);	
		
	#Delete "testfield" field for 'api-data-test-3'
		$title = "\n\n [DELETE single field '".$new_field[name]."' from sensor: '".$existing_data_sensor[2]."']\n";
		$response = $wotkit_client->deleteSensorField ($existing_data_sensor[2], $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
		
	#Query mulitple fields for 'api-data-test-3'
		$title = "\n\n [QUERY multiple fields from sensor: '".$existing_data_sensor[2]."']\n";
		$expected = 4;
		$response = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
		$test_status = $wotkit_client->checkHTTPcode();
		$problem = checkTagsOrSensors($response['data'], array('value', 'lat', 'lng', 'message'));
		displayTestResults ($problem, true, $title, $test_status, $response, $expected, true);	

	printLabel(null, ".....done testing sensor fields......", true);	
	//---------------------------------------------------------------//

			
#Querying data from 'api-data-test-3'
#Querying data from existing  sensor
	$title = "\n\n [QUERY data from sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 2;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected, true);

#Deleting latest data from 'api-data-test-3'
#Deleting latest data from existing sensor
	$title = "\n\n [DELETE latest data from sensor: '".$existing_data_sensor[2]."'] \n";
	$saved_response = json_decode($wotkit_client->response, true);
	end($saved_response);
	$last_key=key($saved_response);
	$time_stamp=$saved_response[$last_key][timestamp];
	$response = $wotkit_client->deleteSensorData( $existing_data_sensor[2], $time_stamp);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);

#Querying data from 'api-data-test-3'
#Querying data from existing  sensor
	$title = "\n\n [QUERY data from sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 1;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected, true);
	
#Deleting latest data from 'api-data-test-3'
#Deleting latest data from existing sensor
	$title = "\n\n [DELETE latest data from sensor: '".$existing_data_sensor[2]."'] \n";
	$saved_response = json_decode($wotkit_client->response, true);
	end($saved_response);
	$last_key=key($saved_response);
	$time_stamp=$saved_response[$last_key][timestamp];
	$response = $wotkit_client->deleteSensorData( $existing_data_sensor[2], $time_stamp);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
		
#Querying data from EMPTY 'api-data-test-3'
#Querying data from existing EMPTY sensor
	$title = "\n\n [QUERY data from EMPTY sensor: '".$existing_data_sensor[2]."'] \n";
	$expected = 0;
	$response = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected, true);

#Deleting latest data from EMPTY 'api-data-test-3'
#Deleting latest data from EMPTY existing sensor
	$title = "\n\n [DELETE latest data from EMPTY sensor: '".$existing_data_sensor[2]."'] \n";
	$saved_response = json_decode($wotkit_client->response, true);
	end($saved_response);
	$last_key=key($saved_response);
	$time_stamp=$saved_response[$last_key][timestamp];
	$response = $wotkit_client->deleteSensorData( $existing_data_sensor[2], $time_stamp);
	$test_status = $wotkit_client->checkHTTPcode(405);
	//!!!!!!!!!!! no readable error message
	displayTestResults (null, false, $title, $test_status, $response);

	

//RAW SENSOR DATA RETRIEVAL
printLabel($toc_keys[3], "[*****TESTING RAW SENSOR DATA RETRIEVAL******]");

#Sending data to make sensors active 
	for ($i=0; $i<2; $i++){
		$value = rand(1,100);
		$lat = rand(1,100);
		$lng = rand(1,100);
		$message = "test message to be active ".rand(100,200); 
		$data_title = "\n value=".$value." & lat=".$lat." & lng=".$lng." & message=".$message;
		$title = "[Sending data -".$data_title."- to sensor: ".$existing_data_sensor[$i]." (to make it active)]";
		$response = $wotkit_client->sendSensorData( $existing_data_sensor[$i],$value, $lat, $lng, $message);
		$test_status = $wotkit_client->checkHTTPcode();
		displayTestResults (null, false, $title, $test_status, $response);
	}
	
#Querying all raw data
	$title = "\n\n [Querying all from sensor: '".$existing_data_sensor[1]."'] \n";
	$expected = 1;
	$response = $wotkit_client->getRawSensorData($existing_data_sensor[1]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, true, $title, $test_status, $response, $expected, true);
	
#Querying all raw data
	$title = "\n\n [Querying all from sensor: '".$existing_data_sensor[0]."'] \n";
	$expected = 4;
	$response = $wotkit_client->getRawSensorData($existing_data_sensor[0]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, true, $title, $test_status, $response, $expected, true);	
	$saved_data = $response['data'];
	
#Querying raw data START END
	$title = "\n\n [Querying raw data from > 2pm January 7th to <= 1pm January 8th from sensor: '".$existing_data_sensor[0]."'] \n";
	$expected = 1;
	$response = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, $end_time);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response[data], $saved_data[2]);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);

#Querying raw data BEFORE
	$title = "\n\n [Querying elements of raw data <= 1.5hr BEFORE 2pm January 7th from sensor: '".$existing_data_sensor[0]."'] \n";
	$expected = 2;
	$response = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, NULL, NULL, NULL, 1.5*3600000);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response[data][0], $saved_data[0]);
	if (!$problem)
		$problem = checkArraysEqual($response[data][1], $saved_data[1]);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);
	
#Querying raw data AFTER
	$title = "\n\n [Querying elements of raw data > 1 hr AFTER 2pm January 7th from sensor: '".$existing_data_sensor[0]."'] \n";
	$expected = 1;
	$response = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, NULL, 3600000);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response[data], $saved_data[3]);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);
	
#Querying raw data BEFOREE
	$title = "\n\n [Querying last 3 elements of raw data BEFORE now from sensor: '".$existing_data_sensor[0]."'] \n";
	$expected = 3;
	$response = $wotkit_client->getRawSensorData($existing_data_sensor[0], NULL, NULL, NULL, NULL, NULL, 3);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected, true); 

#Querying raw data AFTERE
	$title = "\n\n [Querying first 2 elements of raw data AFTER 2pm January 7th from  sensor: '".$existing_data_sensor[0]."'] \n";
	$expected = 2;
	$response = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, NULL, NULL,2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected, true);
	
#Querying raw data REVERSE = false
	$title = "\n\n [Querying all raw data, oldest to newest, from sensor: '".$existing_data_sensor[0]."'] \n";
	$expected = 4;
	$response = $wotkit_client->getRawSensorData($existing_data_sensor[0], NULL, NULL, NULL, NULL, NULL, NULL, "false");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkDates($response['data'][0]['timestamp'], $response['data'][3]['timestamp']);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);
	
#Querying raw data REVERSE = true
	$title = "\n\n [Querying all raw data, newest to oldest, from sensor: '".$existing_data_sensor[0]."'] \n";
	$expected = 4;
	$response = $wotkit_client->getRawSensorData($existing_data_sensor[0], NULL, NULL, NULL, NULL, NULL, NULL, "true");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkDates($response['data'][3]['timestamp'], $response['data'][0]['timestamp']);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);	

	

//FORMATTED SENSOR DATA RETREIVAL
printLabel($toc_keys[4], "[*****TESTING FORMATTED SENSOR DATA RETREIVAL******]");

#Querying formatted data
	$title = "\n\n [Querying formatted data in HTML table where value>30 from sensor: '".$existing_data_sensor[0]."'] \n";
	$response = $wotkit_client->getFormattedSensorData( $existing_data_sensor[0], "select * where value>20", 1, "html"); 
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, true, $title, $test_status, $response);//special case in function?

	

//QUERYING SENSORS	
printLabel($toc_keys[5], "[*****TESTING QUERYING SENSORS******]");
	
#Querying ALL
	//should not include private sensor
	$title = "\n\n [Query ALL (ASSUMES returned value is correct number of sensors)] \n";
	$response = $wotkit_client->getSensors(null,"all") ;
	$total_sensors = count($response[data]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, true, $title, $test_status, $response, $total_sensors);
	
#Querying CONTRIBUTED
	$title = "\n\n [Query CONTRIBUTED] \n";
	$expected = 3;
	$response = $wotkit_client->getSensors (null,"contributed");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1], $existing_data_sensor[2]));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);
	
#Querying SUBSCRIBED
	$title = "\n\n [Query SUBSCRIBED] \n";
	$expected = 3;
	$response = $wotkit_client->getSensors (null, "subscribed");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1], $unowned_sensor_short));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);

#Querying ACTIVE
	//$title = "\n\n [Query ACTIVE] \n";
	//$expected = 3;
	//$data = $wotkit_client->getSensors (null, NULL,"true");
	//$test_status = $wotkit_client->checkHTTPcode();
	//displayOutput ($data, $test_status, $expected);
	
#Querying PRIVATE
	$title = "\n\n [Query PRIVATE] \n";
	$private = 1;
	$response = $wotkit_client->getSensors (null, NULL, NULL,"true");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[2]));
	displayTestResults ($problem, false, $title, $test_status, $response, $private);
	
#Querying NOT PRIVATE
	$title = "\n\n [Query NOT PRIVATE] \n";
	$expected = $total_sensors - $private;
	$response = $wotkit_client->getSensors (null, NULL, NULL,"false");
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected);
	
#Querying SUBSCRIBED & ACTIVE
	$title = "\n\n [Query SUBSCRIBED & ACTIVE] \n";
	$expected = 2;
	$response = $wotkit_client->getSensors (null,"subscribed", "true");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1]));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);
	
#Querying SUBSCRIBED & INACTIVE
	$title = "\n\n [Query SUBSCRIBED & INACTIVE] \n";
	$expected = 1;
	$response = $wotkit_client->getSensors (null, "subscribed", "false");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($unowned_sensor_short));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);

#Querying TAGGED single
	$title = "\n\n [Query TAGGED data] \n";
	$expected = 3;
	$response = $wotkit_client->getSensors (null, NULL, NULL, NULL, "data");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1], $existing_data_sensor[2]));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);
	
#Querying TAGGED single
	$title = "\n\n [Query TAGGED Canada] \n";
	$expected = 3;
	$response = $wotkit_client->getSensors (null, NULL, NULL, NULL, "Canada");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1], $existing_data_sensor[2]));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);
	
#Querying TAGGED multiple
	$title = "\n\n [Query TAGGED vancouver, edmonton] \n";
	$expected = 2;
	$response = $wotkit_client->getSensors (null, NULL, NULL, NULL,"vancouver,edmonton");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1]));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);
	
#Querying TAGGED Cross Tags
	$title = "\n\n [Query TAGGED data, Canada] \n";
	$expected = 3;
	$response = $wotkit_client->getSensors(null, NULL, NULL, NULL, "data,Canada") ;
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1], $existing_data_sensor[2]));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);
	
#Querying TEXT
	$title = "\n\n [Query TEXT api-] \n";
	$expected = 3;
	$response = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, "api-") ;
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0], $existing_data_sensor[1], $existing_data_sensor[2]));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);

#Querying OFFSET
	$title = "\n\n [Query OFFSET=35] \n";
	$offset = 35;
	$expected = $total_sensors - $offset;
	$response = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, $offset) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected);
	
#Querying OFFSET & LIMIT
	$title = "\n\n [Query OFFSET=15 & LIMIT=5] \n";
	$expected = 5;
	$response = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, 15, 5) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected);	
	
#Querying LOCATION
	$title = "\n\n [Query LOCATION = invalid North/South coordinates] \n";
	$response = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_invalid_ns) ;
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], 'No sensor', 'smaller than');
	displayTestResults ($problem, false, $title, $test_status, $response);	
	
#Querying LOCATION
	$title = "\n\n [Query LOCATION = invalid North coordinate] \n";
	$response = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_invalid_toolarge) ;
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], 'No sensor', 'out of bound');
	displayTestResults ($problem, false, $title, $test_status, $response);	
	
#Querying LOCATION
	$title = "\n\n [Query LOCATION = vancouver] \n";
	$expected = 23;
	$response = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_vancouver) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected);	
	
#Querying LOCATION & ACTIVE
	$title = "\n\n [Query LOCATION = vancouver & ACTIVE] \n";
	$expected = 1;
	$response = $wotkit_client->getSensors(null, NULL, "true", NULL, NULL, NULL, NULL, NULL, $location_vancouver) ;
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[0]));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);	

#Querying LOCATION 
	$title = "\n\n [Query LOCATION = winnipeg ] \n";
	$expected = 1;
	$response = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_winnipeg) ;
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array($existing_data_sensor[2]));
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);	
	
#Querying LOCATION with no results
	$title = "\n\n [Query LOCATION = Kilkenny -- where there are NO sensors ] \n";
	$expected = 0;
	$response = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_kilkenny) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected);	
	

	
//AGGREGATE SENSOR DATA
printLabel($toc_keys[6], "[*****TESTING QUERYING AGGREGATE SENSOR DATA******]");

#Querying data from subscribed, active sensors from last hour
	$title = "\n\n [Query aggregated sensor data: SUBSCRIBED, ACTIVE, last hour] \n";
	$expected = 2;
	$params = array("scope" => "subscribed", "active" => true, "before" => 3600000 );	
	$response = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected);	
	
#Querying data from subscribed, active sensors from last 10
	$title = "\n\n [Query aggregated sensor data: SUBSCRIBED, ACTIVE, last 10] \n";
	$expected = 5;
	$params = array("scope" => "subscribed", "active" => true, "beforeE" => 10 );	
	$response = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected);	
	
#Querying data from subscribed, active sensors during hour after 7 January 2013 14:00
	$title = "\n\n [Query aggregated sensor data: SUBSCRIBED, ACTIVE, during hour after 7 January 2013 14:00] \n";
	$expected = 1;
	$params = array("scope" => "subscribed", "active" => true, "start" => $start_time, "after"=>3600000 );	
	$response = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected);	
	
#Querying data from active sensors orderBy Time
	$title = "\n\n [Query aggregated sensor data: ACTIVE ordered by time] \n";
	$expected = 5;
	$params = array("active" => true,"orderBy" => "time" );	
	$response = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, true, $title, $test_status, $response, $expected, true);	
	
#Querying data from active sensors orderBy Sensor
	$title = "\n\n [Query aggregated sensor data: ACTIVE ordered by sensor] \n";
	$expected = 5;
	$params = array("active" => true, "orderBy" => "sensor" );	
	$response = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, true, $title, $test_status, $response, $expected, true);	

	

	//DELETE DATA 
	printLabel(null, "[.......deleting newly added data.......]", true);

		#Deleting latest data from 'api-data-test-1' & 'api-data-test-2'
		#Deleting latest data from existing sensors
			for ($i=0; $i<2; $i++){
				$title = "\n\n [DELETE latest data from sensor: '".$existing_data_sensor[$i]."'] \n";
				$response = $wotkit_client->getSensorData($existing_data_sensor[$i]);
				$saved_response = json_decode($wotkit_client->response, true);
				end($saved_response);
				$last_key=key($saved_response);
				$time_stamp=$saved_response[$last_key][timestamp];
				$response = $wotkit_client->deleteSensorData( $existing_data_sensor[$i], $time_stamp);
				$test_status = $wotkit_client->checkHTTPcode();
				displayTestResults (null, false, $title, $test_status, $response);
			}
			
	printLabel(null, "[.......done deleteing newly added data........]", true);		

		

	
//ACTUATORS	
printLabel($toc_keys[7], "[*****TESTING CONTROL OF ACTUATORS******]");

#Subscribe to, send data to, and get data from actuator you DO own
	$title = "\n\n [Subscribe to, send data to, and get data from OWNED actuator: '".$actuator_name."'] \n";
	$expected = 1;
	echo nl2br("Sending messge ".$actuator_message_display."\n");
	$response = $wotkit_client->testActuator($actuator_name, $actuator_message);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $actuator_message);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);	

#Send data to actuator you DO NOT own
	$title = "\n\n [Send data - with NO credentials - to PUBLIC actuator: '".$actuator_name_full."'] \n";
	$public = true;
	$expected = 1;
	$response = $wotkit_client->subscribeActuator($actuator_name);
	$sub_id = $response["data"]["subscription"];	
	$response=$wotkit_client->sendActuator($actuator_name_full, $actuator_message, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);	
	
	echo nl2br("Sent message: ".$actuator_message_display."\n");
	$title = "\n\n [Get message from OWNED actuator: '".$actuator_name_full."'] \n";
	$response = $wotkit_client->getActuator($sub_id);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $actuator_message);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);	

#Send data to actuator you DO NOT own
	$title = "\n\n [Send data - with NON-OWNER credentials - to PUBLIC actuator: '".$actuator_name_full."'] \n";
	$public = true;
	$expected = 1;
	$response = $wotkit_client->subscribeActuator($actuator_name);
	$sub_id = $response["data"]["subscription"];	
	$response = $wotkit_client->sendActuator($actuator_name_full, $actuator_message, $public, "other");
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
	echo nl2br("Sent message: ".$actuator_message_display."\n");
	$title = "\n\n [Get message from OWNED actuator: '".$actuator_name_full."'] \n";
	$response = $wotkit_client->getActuator($sub_id);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $actuator_message);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);	
	
#Subscribe to PRIVATE actuator you DO NOT own
	$title = "\n\n [Subscribe to UNOWNED, PRIVATE actuator: '".$private_unowned_sensor."'] \n";
	$response = $wotkit_client->subscribeActuator($private_unowned_sensor, $actuator_message);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayTestResults (null, false, $title, $test_status, $response);	
	
#Send message to PRIVATE actuator you DO NOT own
	$title = "\n\n [Send message to UNOWNED, PRIVATE actuator: '".$private_unowned_sensor."'] \n";
	echo nl2br("Sending messge ".$actuator_message_display."\n");
	$response = $wotkit_client->sendActuator($private_unowned_sensor, $actuator_message);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayTestResults (null, false, $title, $test_status, $response);	


	
//USERS
printLabel($toc_keys[8], "[*****TESTING USERS******]");	

#Create invalid username
	$title = "\n\n [CREATE user with invalid name :'".$invalid_user_name."', as ADMIN] \n";
	$response = $wotkit_client->createUser("admin", $invalid_name_user_input);
	$test_status = $wotkit_client->checkHTTPcode(500);
	$problem = checkError($response['data'], "'username': rejected value");
	displayTestResults ($problem, false, $title, $test_status, $response);
	
#Create invalid user with missing property
	$title = "\n\n [CREATE user with missing properties, as ADMIN] \n";
	$response = $wotkit_client->createUser("admin", $missing_property_user_input);
	$test_status = $wotkit_client->checkHTTPcode(500);
	$problem = checkError($response['data'], "'lastname': rejected value");
	displayTestResults ($problem, false, $title, $test_status, $response);
	
#Create user 
	$title = "\n\n [CREATE user: '".$new_user_name."', as ADMIN] \n";
	$response = $wotkit_client->createUser("admin", $new_user_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
#Create existing user 
	$title = "\n\n [CREATE EXISTING user: '".$new_user_name."', as ADMIN] \n";
	$response = $wotkit_client->createUser("admin", $new_user_input);
	$test_status = $wotkit_client->checkHTTPcode(409);
	displayTestResults (null, false, $title, $test_status, $response);
	
#Query existing user 
	$title = "\n\n [QUERY existing user: '".$new_user_name."', as ADMIN] \n";
	$response = $wotkit_client->getUsers("admin", $new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $new_user_input);
	displayTestResults ($problem, false, $title, $test_status, $response);
	
#Update existing user 
	$title = "\n\n [UPDATE existing user: '".$new_user_name."', as ADMIN] \n";
	$response = $wotkit_client->updateUser("admin", $new_user_name, $updated_user_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
#Query existing user 
	$title = "\n\n [QUERY existing user: '".$new_user_name."', as ADMIN] \n";
	$response = $wotkit_client->getUsers("admin", $new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $updated_user_input);
	displayTestResults ($problem, false, $title, $test_status, $response);
	$saved_data = $response['data'];
	
#Update existing user with invalid data 
	$title = "\n\n [UPDATE username of existing user: '".$new_user_name."', as ADMIN -- not allowed] \n";
	$response = $wotkit_client->updateUser("admin", $new_user_name, $invalid_updated_user_input);
	$test_status = $wotkit_client->checkHTTPcode(400);
	$problem = checkError($response['data'], 'Extraneous field', 'Cannot change username');
	displayTestResults ($problem, false, $title, $test_status, $response);
	
#Query existing user 
	$title = "\n\n [QUERY existing user: '".$new_user_name."', as ADMIN] \n";
	$response = $wotkit_client->getUsers("admin", $new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $saved_data);
	displayTestResults ($problem, false, $title, $test_status, $response);
	
#Query all users with "api" in name
	$title = "\n\n [QUERY users with TEXT='api', as ADMIN] \n";
	$expected = 2;
	$response = $wotkit_client->getUsers("admin", null, "api");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = !($response['data'][0]["username"] == "tester-admin");
	if (!$problem)
		$problem = !($response['data'][1]["username"] == "tester");
	displayTestResults ($problem, false, $title, $test_status, $response, $expected);		
	
#Query all users REVERSE = true
	$title = "\n\n [QUERY existing users from oldest to newest, REVERSE=true, LIMIT=7, as ADMIN] \n";
	$expected = 7;
	$response = $wotkit_client->getUsers("admin", null, null, true, null, 7);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = !($response['data'][0]['id'] < $response['data'][5]['id']);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);	
	
#Query all users REVERSE = false
	$title = "\n\n [QUERY existing users from oldest to newest, REVERSE=false, LIMIT=7, as ADMIN] \n";
	$expected = 7;
	$response = $wotkit_client->getUsers("admin", null, null, false, null, 7);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = !($response['data'][0]['id'] > $response['data'][5]['id']);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);	
	
#Query all users LIMIT 2 
	$title = "\n\n [QUERY all users LIMIT=2, as ADMIN] \n";
	$expected = 2;
	$response = $wotkit_client->getUsers("admin", null, null, null, null, 2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected);	
	
#Query all users OFFSET 5 LIMIT 2 
	$title = "\n\n [QUERY all users OFFSET=3 & LIMIT=2, as ADMIN] \n";
	$expected = 2;
	$response = $wotkit_client->getUsers("admin", null, null, null, 3, 2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected);	
	
#Delete user 'new-user-api-testing'
#Delete existing user 
	$title = "\n\n [DELETE EXISTING user: '".$new_user_name."', as ADMIN] \n";
	$response = $wotkit_client->deleteUser("admin", $new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);	
	
#Delete non-existent user 'new-user-api-testing'
	$title = "\n\n [DELETE NON-EXISTENT user: '".$new_user_name."', as ADMIN] \n";
	$response = $wotkit_client->deleteUser("admin", $new_user_name);
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], 'No user', 'No user');
	displayTestResults ($problem, false, $title, $test_status, $response);	
	
#Query non-existent user 
	$title = "\n\n [QUERY NON-EXISTENT user: '".$new_user_name."', as ADMIN] \n";
	$response = $wotkit_client->getUsers("admin", $new_user_name);
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], 'No user', 'No user');
	displayTestResults ($problem, false, $title, $test_status, $response);	

	
	
//NEWS
printLabel($toc_keys[9], "[*****TESTING NEWS******]");	

#Query news
	$title = "\n\n [QUERY news with NO CREDENTIALS] \n";
	$response = $wotkit_client->getNews();
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, true, $title, $test_status, $response);
	
	
	

//STATS
printLabel($toc_keys[10], "[*****TESTING STATS******]");	

#Query stats
	$title = "\n\n [QUERY stats with NO CREDENTIALS] \n";
	$response = $wotkit_client->getNews();
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, true, $title, $test_status, $response);

	

//TAGS
printLabel($toc_keys[11], "[*****TESTING TAGS******]");

#Query all tags
	$title = "\n\n [QUERY ALL tags (ASSUMES returned value is the correct number of tags)] \n";
	$response = $wotkit_client->getTags("all");
	$all_tags = count($response['data']);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, true, $title, $test_status, $response, $all_tags);	
	
#Query subscribed tags
	$expected = 4;
	$title = "\n\n [QUERY SUBSCRIBED tags] \n";
	$response = $wotkit_client->getTags("subscribed");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array('canada', 'data', 'vancouver', 'edmonton'));
	displayTestResults($problem, false, $title, $test_status, $response, $expected);	
	
#Query contributed tags
	$expected = 5;
	$title = "\n\n [QUERY CONTRIBUTED tags] \n";
	$response = $wotkit_client->getTags("contributed");
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array('canada', 'data', 'vancouver', 'edmonton', 'winnipeg'));
	displayTestResults($problem, false, $title, $test_status, $response, $expected);	
	
#Query all tags for private sensors
	$private_tags = 1;
	$expected = 3;
	$title = "\n\n [QUERY tags for PRIVATE sensors] \n";
	$response = $wotkit_client->getTags(null, true);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array('canada', 'data','winnipeg'));
	displayTestResults($problem, false, $title, $test_status, $response, $expected);	
	
#Query tags for sensors with specific text
	$expected = 3; 
	$title = "\n\n [QUERY tags for sensors with TEXT=api-data-test-2] \n";
	$response = $wotkit_client->getTags(null, null, "api-data-test-2", null, null, null, null);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array('canada', 'data','edmonton'));
	displayTestResults($problem, false, $title, $test_status, $response, $expected);
	
#Query subscribed, active
	$sub_active_expected = 4; 
	$title = "\n\n [QUERY SUBSCRIBED and ACTIVE tags] \n";
	$response = $wotkit_client->getTags("subscribed", null, null, true, null, null, null);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array('canada', 'data', 'vancouver', 'edmonton'));
	displayTestResults($problem, false, $title, $test_status, $response, $sub_active_expected);
	
#Query subscribed, active, offset
	$offset = 1; 
	$expected = $sub_active_expected - $offset;  
	$title = "\n\n [QUERY SUBSCRIBED and ACTIVE tags with OFFSET=".$offset."] \n";
	$response = $wotkit_client->getTags("subscribed", null, null, true, 1, null, null );
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array('data', 'vancouver', 'edmonton'));
	displayTestResults($problem, false, $title, $test_status, $response, $expected);
	
#Query subscribed, active, limit
	$limit = 1; 
	$title = "\n\n [QUERY SUBSCRIBED and ACTIVE with LIMIT=".$limit."] \n";
	$response = $wotkit_client->getTags("subscribed", null, null, true, null, $limit, null );
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array('canada'));
	displayTestResults($problem, false, $title, $test_status, $response, $limit);
	
#Query location tags (no sensors)
	$expected = 0;
	$title = "\n\n [QUERY LOCATION=Kilkenny tags -- no sensors exist] \n";
	$response = $wotkit_client->getTags(null, null, null, null, null, null, $location_kilkenny );
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected);

#Query location tags (public sensors)
	$expected = 3;
	$title = "\n\n [QUERY LOCATION=Edmonton tags -- a public sensor exists] \n";
	$response = $wotkit_client->getTags(null, null, null, null, null, null, $location_edmonton );
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array('canada','data','edmonton'));
	displayTestResults($problem, false, $title, $test_status, $response, $expected);	
	
#Query location tags (private sensors)
	$expected = 3;
	$title = "\n\n [QUERY LOCATION=Winnipeg tags -- a private sensor exists] \n";
	$response = $wotkit_client->getTags(null, null, null, null, null, null, $location_winnipeg );
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array('canada','data','winnipeg'));
	displayTestResults($problem, false, $title, $test_status, $response, $expected);
	
#Query location tags, invalid data
	$title = "\n\n [QUERY LOCATION=invalid North/South coordinate tags] \n";
	$response = $wotkit_client->getTags(null, null, null, null, null, null, $location_invalid_ns );
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], 'No sensor', 'smaller');
	displayTestResults($problem, false, $title, $test_status, $response);
	
#Query location tags, invalid data
	$title = "\n\n [QUERY LOCATION=invalid North coordinate tags] \n";
	$response = $wotkit_client->getTags(null, null, null, null, null, null, $location_invalid_toolarge );
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], 'No sensor', 'out of bound');
	displayTestResults($problem, false, $title, $test_status, $response);

	$public = true;
	
#Query all, with no credentials
	$title = "\n\n [QUERY ALL tags, with NO CREDENTIALS] \n";
	$expected = $all_tags - $private_tags;
	$response = $wotkit_client->getTags("all", null, null, null, null, null, null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected);	

#Query all, limit 2, with no credentials
	$title = "\n\n [QUERY ALL tags, with LIMIT=".$limit." and NO CREDENTIALS] \n";
	$limit = 2;
	$response = $wotkit_client->getTags("all", null, null, null, null, $limit, null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $limit);	
	
#Query subscribed, with no credentials
	$title = "\n\n [QUERY SUBSCRIBED tags, with NO CREDENTIALS] \n";
	$expected = 0;
	$response = $wotkit_client->getTags("subscribed", null, null, null, null, null, null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected);	
	
#Query private, with no credentials
	$title = "\n\n [QUERY tags for PRIVATE sensors, with NO CREDENTIALS] \n";
	$expected = 0;
	$response = $wotkit_client->getTags(null, true, null, null, null, null, null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected);	

#Query location tags, with no credentials (no sensors)
	$expected = 0;
	$title = "\n\n [QUERY LOCATION=Kilkenny tags, with NO CREDENTIALS -- no sensors exist] \n";
	$response = $wotkit_client->getTags(null, null, null, null, null, null, $location_kilkenny, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected);
	
#Query location tags, with no credentials (private sensors)
	$expected = 0;
	$title = "\n\n [QUERY LOCATION=Winnipeg tags, with NO CREDENTIALS -- a private sensor exists] \n";
	$response = $wotkit_client->getTags(null, null, null, null, null, null, $location_winnipeg, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected);
	
#Query location tags, with no credentials (public sensors)
	$expected = 3;
	$title = "\n\n [QUERY LOCATION=Edmonton tags, with NO CREDENTIALS -- a public sensor exists] \n";
	$response = $wotkit_client->getTags(null, null, null, null, null, null, $location_edmonton, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'], array('canada','data','edmonton'));
	displayTestResults($problem, false, $title, $test_status, $response, $expected);



//Organizations	
printLabel($toc_keys[12], "[*****TESTING ORGANIZATIONS******]");

#Query all organizations
	$expected = 1;
	$title = "[QUERY all organizations]";
	$response = $wotkit_client->getOrganizations(null);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkTagsOrSensors($response['data'][0]['name'], array('sensetecnic'));
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);

#Create new organization using mandatory fields 
	$title = "[CREATE new organization: '".$new_org_mandatory['name']."' (using mandatory fields), as ADMIN]";
	$response = $wotkit_client->createOrganization("admin", $new_org_mandatory);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response);

#Query existing organization
	$expected = 3;
	$title = "\n\n [QUERY organization: '".$new_org_mandatory['name']."'] \n";
	$response = $wotkit_client->getOrganizations(null, $new_org_mandatory['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $new_org_mandatory);
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);

#Create EXISTING organization
	$title = "[CREATE existing organization: '".$new_org_mandatory['name']."', as ADMIN]";
	$response = $wotkit_client->createOrganization("admin", $new_org_mandatory);
	$test_status = $wotkit_client->checkHTTPcode(409);
	$problem = checkError($response["data"], "already exists", "already exists");
	displayTestResults($problem, false, $title, $test_status, $response);

#Create organization with invalid name
	$title = "\n\n [CREATE new organization INVALID NAME, as ADMIN] \n";
	$response = $wotkit_client->createOrganization("admin", $new_org_invalid_name);
	$test_status = $wotkit_client->checkHTTPcode(400);
	$problem = checkError($response["data"], "invalid name");
	displayTestResults($problem, false, $title, $test_status, $response);

#Create organization with missing required field, longName
	$title = "\n\n [CREATE new organization MISSING required field (longName), as ADMIN] \n";
	$response = $wotkit_client->createOrganization("admin", $new_org_missing_longname);
	$test_status = $wotkit_client->checkHTTPcode(400);
	$problem = checkError($response["data"], "invalid longName");
	displayTestResults($problem, false, $title, $test_status, $response);

#Query all organizations
	$expected = 2;
	$title = "\n\n [QUERY all organizations] \n";
	$response = $wotkit_client->getOrganizations();
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'][0], $new_org_mandatory);
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);

#Update non-existent organization 
	$title = "\n\n [UPDATE non-existent organization, as ADMIN] \n";
	$response = $wotkit_client->updateOrganization("admin", 'not-real-org', $updated_org_all);
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response["data"], "No organization", "No organization");
	displayTestResults($problem, false, $title, $test_status, $response);

#Update existing organization NAME
	$title = "\n\n [UPDATE PROTECTED field (name) of existing organization: '".$new_org_mandatory['name']."', as ADMIN] \n";
	$response = $wotkit_client->updateOrganization("admin", $new_org_mandatory['name'], $updated_org_name);
	$test_status = $wotkit_client->checkHTTPcode(400);
	$problem = checkError($response['data'], 'Extraneous', 'Cannot change the field "name"');
	displayTestResults($problem, false, $title, $test_status, $response);

#Update existing organization
	$title = "\n\n [UPDATE all fields of existing organization: '".$new_org_mandatory['name']."', as ADMIN] \n";
	$response = $wotkit_client->updateOrganization("admin", $new_org_mandatory['name'], $updated_org_all);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response);

#Query organization	
	$expected = 5;
	$title = "\n\n [QUERY organization: '".$new_org_mandatory['name']."'] \n";
	$response = $wotkit_client->getOrganizations(null, $new_org_mandatory['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $updated_org_all);
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);					  

#Create new organization	
	$title = "\n\n [CREATE new organization: '".$new_org_all['name']."' (using all fields), as ADMIN] \n";
	$response = $wotkit_client->createOrganization("admin", $new_org_all);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response);

#Query organization					  						  
	$expected = 5;
	$title = "\n\n [QUERY organization: '".$new_org_all['name']."'] \n";
	$response = $wotkit_client->getOrganizations(null, $new_org_all['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $new_org_all);
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);	

#Update existing organization longName only
	$title = "\n\n [UPDATE longName of existing organization: '".$new_org_all['name']."', as ADMIN] \n";
	$response = $wotkit_client->updateOrganization("admin", $new_org_all['name'], $updated_org_longName);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response);

#Query organization	
	$expected = 3;
	$title = "\n\n [QUERY organization: '".$new_org_all['name']."'] \n";
	$response = $wotkit_client->getOrganizations(null, $new_org_all['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $updated_org_longName);
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);
	
#List members
	$expected = 0;
	$title = "\n\n [GET MEMBERS of organization: '".$new_org_all['name']."', as ADMIN] \n";
	$response = $wotkit_client->getOrganizationMembers("admin", $new_org_all['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected, true);

#Add members
	$title = "\n\n [ADD MEMBERS to organization: '".$new_org_all['name']."', as ADMIN] \n";
	$response = $wotkit_client->addOrganizationMembers("admin", $new_org_all['name'], $added_members);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response);

#List memebers
	$expected = 2;
	$title = "\n\n [GET MEMBERS of organization: '".$new_org_all['name']."', as ADMIN] \n";
	$response = $wotkit_client->getOrganizationMembers("admin", $new_org_all['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = !(($response['data'][0]['username'] == $added_members[0]) && ($response['data'][1]['username'] == $added_members[1]));
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);

#Remove members
	$title = "\n\n [REMOVE MEMBER 'tester' from organization: '".$new_org_all['name']."', as ADMIN] \n";
	$response = $wotkit_client->removeOrganizationMembers("admin", $new_org_all['name'], array($added_members[1]));
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response);

#List members
	$expected = 1;
	$title = "\n\n [GET MEMBERS of organization: '".$new_org_all['name']."', as ADMIN] \n";
	$response = $wotkit_client->getOrganizationMembers("admin", $new_org_all['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = !( $response['data'][0]['username'] == $added_members[0] );
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);				

#Remove member
	$title = "\n\n [REMOVE NON-EXISTENT MEMBER 'tester' from organization: '".$new_org_all['name']."', as ADMIN] \n";
	$response = $wotkit_client->removeOrganizationMembers("admin", $new_org_all['name'], array($added_members[1]));
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response);

#List members
	$expected = 1;
	$title = "\n\n [GET MEMBERS of organization: '".$new_org_all['name']."', as ADMIN] \n";
	$response = $wotkit_client->getOrganizationMembers("admin", $new_org_all['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = !( $response['data'][0]['username'] == $added_members[0] );
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);	
	
#Create new sensor with org visibility
	$title = "\n\n [CREATE sensor: '".$org_sensor_input['name']."' with organzition: '".$new_org_all['name']."'] \n";
	$response = $wotkit_client->createSensor($org_sensor_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);	

#Query sensors by org
	$expected = 1;
	$title = "\n\n [QUERY sensors with ORGS='".$new_org_all['name']."']\n";
	$response = $wotkit_client->getSensors(null, null, null, null, null, null, null, null, null, $new_org_all['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkArraysEqual($response['data'], $org_sensor_input);
	displayTestResults ($problem, false, $title, $test_status, $response, $expected, true);

#Query sensors by org, with NO CREDENTIALS
	$expected = 0;
	$title = "\n\n [QUERY sensors with ORGS='".$new_org_all['name']."', with NO CREDENTIALS]\n";
	$response = $wotkit_client->getSensors(null, null, null, null, null, null, null, null, null, $new_org_all['name'], true);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response, $expected, true);	

#Delete sensor with org visibility
	$title = "\n\n [DELETE sensor: '".$org_sensor_input['name']."'] \n";
	$response = $wotkit_client->deleteSensor($org_sensor_input['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults (null, false, $title, $test_status, $response);
	
#Query all organizations 
	$expected = 3;
	$title = "\n\n [QUERY all organizations] \n";
	$response = $wotkit_client->getOrganizations();
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, true, $title, $test_status, $response, $expected);

#Query organizations by TEXT
	$expected = 2;
	$title = "\n\n [QUERY organizations with text 'test'] \n";
	$response = $wotkit_client->getOrganizations(null, null, 'test');
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = !(($response['data'][0]['name'] == $new_org_all['name'])&&($response['data'][1]['name'] == $new_org_mandatory['name']));
	displayTestResults($problem, false, $title, $test_status, $response, $expected);

#Query organizations by offset
	$expected = 2;
	$title = "\n\n [QUERY organizations with OFFSET=1] \n";
	$response = $wotkit_client->getOrganizations(null, null, null, 1);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected);

#Query organizations by limit
	$expected = 1;
	$title = "\n\n [QUERY organizations with LIMIT=1] \n";
	$response = $wotkit_client->getOrganizations(null, null, null, null, 1);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected);				
				
#Delete organization with memebers
	$title = "\n\n [DELETE no member organization: '".$new_org_mandatory['name']."', as ADMIN] \n";
	$response = $wotkit_client->deleteOrganization("admin", $new_org_mandatory['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response);

#Delete organization without members
	$title = "\n\n [DELETE 1 member organization: '".$new_org_all['name']."', as ADMIN] \n";
	$response = $wotkit_client->deleteOrganization("admin", $new_org_all['name']);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response);

#Delete non-existent organization
	$title = "\n\n [DELETE NON-EXISTENT organization: '".$new_org_all['name']."', as ADMIN] \n";
	$response = $wotkit_client->deleteOrganization("admin", $new_org_all['name']);
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], "No organization", "No organization");
	displayTestResults($problem, false, $title, $test_status, $response);

#Query non-existent organization
	$expected = 1;
	$title = "\n\n [QUERY NON-EXISTENT organization: '".$new_org_all['name']."'] \n";
	$response = $wotkit_client->getOrganizations(null, $new_org_all['name']);
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], "No organization", "No organization");
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);



//PUBLIC FUNCTIONS
printLabel($toc_keys[13], "[*****TESTING PUBLIC FUNCTIONS******]");	

$public = true;

#Query for multiple sensors
	$title = "\n\n [QUERY PUBLIC sensors, LIMIT=5 (with NO CREDENTIALS)]\n";
	$expected = 5;
	$response = $wotkit_client->getSensors(null, null, null, null, null, null, null, 5, null, null, $public );
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected);

#Query 'api-client-test-sensor'
#Query a SINGLE sensor that DOES exist
	$title = "\n\n [QUERY PUBLIC, existing sensor: '".$existing_data_sensor_full[0]."' (with NO CREDENTIALS)]\n";
	$response = $wotkit_client->getSensors($existing_data_sensor_full[0], null, null, null, null, null, null, null, null, null, $public );
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response);
	
#Query a single, PRIVATE sensor that does exist
	$title = "\n\n [QUERY PRIVATE, existing sensor: '".$private_unowned_sensor."' (with NO CREDENTIALS)]\n";
	$response = $wotkit_client->getSensors($private_unowned_sensor, null, null, null, null, null, null, null, null, null, $public );
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayTestResults(null, false, $title, $test_status, $response);		

#Query a single, public sensor that DOES NOT exist
	$title = "\n\n [QUERY NOT-EXISTENT sensor: '".$invalid_sensor_input["name"]."' (with NO CREDENTIALS)]\n";
	$response = $wotkit_client->getSensors($invalid_sensor_input["name"], null, null, null, null, null, null, null, null, null, $public);
	$test_status = $wotkit_client->checkHTTPcode(404);
	$problem = checkError($response['data'], 'No sensor');
	displayTestResults($problem, false, $title, $test_status, $response);	
	
#Query sensor data from PUBLIC sensor	
	$title = "\n\n [QUERY data from PUBLIC sensor: '".$existing_data_sensor_full[0]."' (with NO CREDENTIALS)]\n";
	$expected = 3;
	$response = $wotkit_client->getSensorData($existing_data_sensor_full[0], $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected, true);
	
#Query sensor data from PRIVATE sensor	
	$title = "\n\n [QUERY data from PRIVATE sensor: '".$existing_data_sensor_full[2]."' (with NO CREDENTIALS)]\n";
	$response = $wotkit_client->getSensorData($existing_data_sensor_full[2], $public);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayTestResults(null, false, $title, $test_status, $response, null);
	
#Query raw data
	$title = "\n\n [QUERY all raw data, from newest to oldest, from sensor: '".$existing_data_sensor_full[0]."' (with NO CREDENTIALS)] \n";
	$expected = 2;
	$response = $wotkit_client->getRawSensorData($existing_data_sensor_full[0], NULL, NULL, NULL, NULL, NULL, $expected, "true", $public);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = checkDates($response['data'][0], $response['data'][1]);
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);	

#Query formatted data
	$title = "\n\n [QUERY formatted data in HTML table where value>30 from sensor: '".$existing_data_sensor_full[0]."' (with NO CREDENTIALS)] \n";
	$response = $wotkit_client->getFormattedSensorData( $existing_data_sensor_full[0], "select * where value>20", 1, "html", NULL, $public); 
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, true, $title, $test_status, $response);	
	
#Query sensor fields from PUBLIC sensors
	$title = "\n\n [QUERY fields from PUBLIC sensor: '".$existing_data_sensor_full[0]."' (with NO CREDENTIALS)]\n";
	$expected = 4;
	$response = $wotkit_client->getSensorFields ($existing_data_sensor_full[0], null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected, true);	
	
#Query sensor fields from PRIVATE sensors
	$title = "\n\n [QUERY fields from PRIVATE sensor: '".$existing_data_sensor_full[2]."' (with NO CREDENTIALS)]\n";
	$response = $wotkit_client->getSensorFields ($existing_data_sensor_full[2], null, $public);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayTestResults(null, false, $title, $test_status, $response);

#Query organization	WITHOUT CREDENTIALS
	$expected = 1;
	$title = "\n\n [QUERY all organizations, with NO CREDENTIALS] \n";
	$response = $wotkit_client->getOrganizations(null, null, null, null, null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayTestResults(null, false, $title, $test_status, $response, $expected, true);
	
#Query organization	WITHOUT CREDENTIALS
	$expected = 5;
	$org = 'sensetecnic';
	$title = "\n\n [QUERY all organization: '".$org."', with NO CREDENTIALS] \n";
	$response = $wotkit_client->getOrganizations(null, $org, null, null, null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	$problem = !(($response['data']['name'] == $org) && ($response['data']['id'] == 1));
	displayTestResults($problem, false, $title, $test_status, $response, $expected, true);	


	
//RESULTS		
printLabel($toc_keys[14], "[*****RESULTS******] ");
if ( $failures === 0 )
	echo "ALL TESTS PASSED" ;	
else
	echo '<font color="red">TESTS FAILED </font> = '.$failures ;

echo '<br><br><a href="#">Back to top</a>';	
echo '</div>';	
	
//HELPER FUNCTIONS
	//Outputs results of test in a formatted fashion
	function displayTestResults ($problem=null, $visual_check, $title, $test_status, $response, $expected=null, $special_case=false){
		global $failures;
		global $test_count;
		
		$pass_code = 0; // if http codes match
		$pass_expected = 1; // if # of expected & # of received match
		
		//Check if received HTTP Code was correct
		if (stristr($test_status,'<b>PASS</b> '))
			$pass_code = 1;
		//Determine Expected vs Found Queries Status
		if($expected != NULL || $expected === 0){
			$found = count($response['data']);
			if ( $expected == $found ){
				$test_status .= '<br><b>PASS</b>';
			}else{
				$pass_expected = 0;
				$test_status .= '<br><font color="red">FAIL</font>.';
			}			
			$test_status .=" - Expected ".$expected." = Returned ".$found;
		}
		// Check if automated data check passed
		if($problem == true)
			$test_status .='<br><font color="red">Automated Data Check FAILED</font>.';
		if($problem === false)
			$test_status .='<br>Automated Data Check Passed.';
		if($problem == null || empty($problem))
			$problem = false;
			
		//Format response	
		echo '<div class="accordion" id="accordian"'.$test_count.'>';
		echo '<div class="accordion-group">';
		echo '<div class="accordion-heading">';
	
		if ( $pass_code && $pass_expected && !$problem ){
			if ($visual_check){
				echo '<a class="accordion-toggle btn btn-info" data-toggle="collapse" data-parent="#accordion'.$test_count.'" href="#collapse'.$test_count.'">';
				echo '<i class="icon-flag"></i>';	
			}else{
				echo '<a class="accordion-toggle btn" data-toggle="collapse" data-parent="#accordion'.$test_count.'" href="#collapse'.$test_count.'">';
				echo '<i class="icon-ok"></i>';	
			}
		}else{
			echo '<a class="accordion-toggle btn btn-danger" data-toggle="collapse" data-parent="#accordion'.$test_count.'" href="#collapse'.$test_count.'">';
			echo '<i class="icon-remove icon-white"></i>';
			$failures ++;
		}
		echo "    ".$test_count.'.  '.$title;
		echo '</a></div>';
		
		if ( $pass_code && $pass_expected && !$problem )
			echo '<div id="collapse'.$test_count.'" class="accordion-body collapse">';
		else
			echo '<div id="collapse'.$test_count.'" class="accordion-body collapse in">';
		
		
		echo '<div class="accordion-inner">';	
		echo '<dl>';
		
		//Print HTTP Code Status and EXPECTED status (if exists) and Automated Data Check Status (if exists)
		echo '<dt class="li-divider">Status:</dt>';
		echo '<dd class="list">'.$test_status.'</dd>';
		
		//Prints authentication used
		echo '<dt class="li-divider">Permission:</dt>';
		echo '<dd class="list">'.$response['permission'].'</dd>';
		
		//Prints HTTP method and URL
		echo '<dt class="li-divider">Request:</dt>';
		echo '<dd class="list">'.$response['request'].': '.$response['url'].'</dd>';
		
		//Prints response in collapsible div
		echo '<dt class="li-divider">Response:</dt>';
		echo '<dd class="list">';
		echo '<div class="accordion" id="NestedAccordian"'.$test_count.'>';
		echo '<div class="accordion-group">';
		echo '<div class="accordion-heading">';
        echo '<a class="accordion-toggle" data-toggle="collapse" data-parent="#NestedAccordian'.$test_count.'" href="#NestedCollapse'.$test_count.'">';
		echo '<h6>HTTP '.$response['code'].'</h6>';
		echo '</a></div>';
		echo '<div id="NestedCollapse'.$test_count.'" class="accordion-body collapse">';
		echo '<div class="accordion-inner">';
		if ( $expected == NULL || $special_case )
			echo'<pre>'.print_r($response['data'], true).'</pre>';//For a more readable response
		else{
			//SPECIAL CASE - assumed multiple results and readable response unnecessary
			$data_long=json_encode($response['data'], true);
			echo $data_long; 
		}
		echo '</div></div></div></div>';
		echo '</dd>';
		echo '</dl>';	
	
		echo '</div></div></div></div>';

		$test_count++;
	}
	
	//Outputs headings in a formatted fashion
	function printLabel($key, $title, $small=false){
		if( $small )
			echo '<h5>'.$title.'</h5>'; 
		else
			echo '<a name="'.$key.'"><h3>'.$title.'</h3></a>'; 
	}
	
	//Checks the actual array values contain the same fields as in the desired array
	function checkArraysEqual($actual, $desired){
		if ( $actual == NULL || $desired == NULL || 
		     $actual==""     || $desired == "" || 
			 empty($actual)  || empty($desired) )
			return true;
				
		$problem = false;
		foreach( array_keys($actual) as $key ){
			if ($problem)
				break;
			if ( array_key_exists($key, $desired) ){
				if (is_array($actual[$key])){
					foreach (array_keys($actual[$key]) as $nested_key ){ 
						if( $nested_key == "timestamp" ){
							$actual_timestamp = $actual[$key][$nested_key];
							$desired_timestamp = $desired[$key][$nested_key];
							if (!is_numeric($actual[$key])) 
								$actual_timestamp = strtotime($actual[$key][$nested_key])*1000;
							if (!is_numeric($desired[$key])) 
								$desired_timestamp = strtotime($desired[$key][$nested_key])*1000;

							if ($actual_timestamp != $desired_timestamp)
								$problem = true;
						}else{
							if (!in_array($actual[$key][$nested_key], $desired[$key]))
								$problem = true;
						}
					}
				}else{
					if ($key == "timestamp"){
						$actual_timestamp = $actual[$key];
						$desired_timestamp = $desired[$key];
						if (!is_numeric($actual[$key])) 
							$actual_timestamp = strtotime($actual[$key])*1000;
						if (!is_numeric($desired[$key])) 
							$desired_timestamp = strtotime($desired[$key])*1000;

						if ($actual_timestamp != $desired_timestamp)
							$problem = true;
					}else {
						if($actual[$key] != $desired[$key])
							$problem = true;
					}
				}
			}
		}
		return $problem;
	}
	
	//Checks past timestamp(ms) is smaller than recent timestamp(ms)
	function checkDates ($past, $recent){
		if ( $past == NULL || $recent == NULL || 
			 $past==""     || $recent == "" || 
			 empty($past)  || empty($recent) )
				return true;
		if (!is_numeric($past)) 
			$past = strtotime($past)*1000;
		if (!is_numeric($recent)) 
			$recent = strtotime($recent)*1000;

		$problem = false; 
		if ($past > $recent)
			$problem = true;
			
		return $problem;
	}
	
	//Checks tags/sensors have the expected name fields
	function checkTagsOrSensors ($received, $expected){
		$problem = false;
		foreach( $received as $value ){			
			if (!in_array($value['name'], $expected)){
				$problem = true;
				break;
			}
		}
		return $problem;
	}
	
	//Checks error message contains expected keywords
	function checkError($error, $keyword, $developerKeyword=null){
		$problem = false; 
		if ( !stristr($error['error']['message'], $keyword) )
			$problem = true;
		if ($developerKeyword != null)
			if ( !stristr($error['error']['developerMessage'], $developerKeyword) )
				$problem = true;
	
		return $problem;
	}

?>

</body>
</html>
<?php
/*
 * Run this script for General Key Based Testing
 * (assumes newly initilized database)
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
	"private"=>"false", 
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
	 "private"=>"true",
	 "tags"=>array("updating the tags","tags"));
	 //"fields"=>[{"name":"value","longName":"Data","type":"NUMBER","units":"cm"}]);
	 //"owner"=>"can't be changed"));
	 
	$updated_sensor_input_2 = array(
	 "name"=>$generic_sensor, 
	 "longName"=>"api client test sensor long", 
	 "description"=>"api client test sensor desc",
	 "private"=>"false");
	 
	$updated_sensor_input_3 = array(
	 "name"=> $unowned_sensor_short, 
	 "longName"=>$unowned_sensor_full, 
	 "description"=>$unowned_sensor_full);
	 
	$additional_sensor_input = array(
	"private"=>"false", 
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
	$invalid_field_required = array ("name"=>"testfield", "longName"=>"Test Field","units"=>"mm");	
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
	
//---------------------------------------------------------------------------------------//

/**
 * Begin Tests
 **/

//SENSORS
echo nl2br("[*****TESTING SENSORS******] \n");

#Create TWO sensors 
#Create multiple sensors: 'api-client-test-sensor' & 'api-client-test-sensor_additional' 
	echo nl2br("\n\n [CREATE multiple sensors:'".$generic_sensor."' & '".$additional_generic_sensor."'] \n");
	$expected = 2;
	$data = $wotkit_client->createMultipleSensor(array($new_sensor_input, $additional_sensor_input), true);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	//$message="Sensors Created";

#Create an existing sensor	
#Create TWO EXISTING sensors 'api-client-test-sensor' & 'api-client-test-sensor_additional'
	echo nl2br("\n\n [CREATE EXISTING multiple sensors:'".$generic_sensor."' & '".$additional_generic_sensor."'] \n");
	$data = $wotkit_client->createMultipleSensor(array($new_sensor_input, $additional_sensor_input), true);	
	$test_status = $wotkit_client->checkHTTPcode(409);
	displayOutput ($data, $test_status, NULL);
	
#Create AN INVALID sensor
//can include any fake fields and sensor will NOT be invalid
//excluding a manadatory field WILL make sensor invalid
	echo nl2br("\n\n [CREATE an INVALID sensor by excluding mandatory field] \n");
	$data = $wotkit_client->createMultipleSensor(array($invalid_sensor_input), true);	
	$test_status = $wotkit_client->checkHTTPcode(400);
	displayOutput ($data, $test_status, NULL);	
	
#Query  'api-client-test-sensor'
#Check for a SINGLE sensor that DOES exist
	echo nl2br("\n\n [QUERY '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensors($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message = "Sensor does not exist";	

#Delete 'api-client-test-sensor'
#Delete a SINGLE sensor that DOES exist
	echo nl2br("\n\n [DELETE '".$generic_sensor."'] \n");
	$data = $wotkit_client->deleteSensor($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message = "Deleted Sensor";

#Query  'api-client-test-sensor-additional'
#Check for a SINGLE sensor that DOES exist
	echo nl2br("\n\n [QUERY '".$additional_generic_sensor."']\n");
	$data = $wotkit_client->getSensors($additional_generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message = "Sensor does not exist";	

#Delete 'api-client-test-sensor_additional'
#Delete a SINGLE sensor that DOES exist
	echo nl2br("\n\n [DELETE '".$additional_generic_sensor."'] \n");
	$data = $wotkit_client->deleteSensor($additional_generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message = "Deleted Sensor";	

#Create 'api-client-test-sensor'
#Create new sensor
	echo nl2br("\n\n [CREATE '".$generic_sensor."'] \n");
	$data = $wotkit_client->createSensor($new_sensor_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	//$message="Sensor Created";
	
#Create the already existing 'api-client-test-sensor'
#Create an existing sensor
	echo nl2br("\n\n [CREATE exisiting '".$generic_sensor."'] \n");
	$data = $wotkit_client->createSensor($new_sensor_input);	
	$test_status = $wotkit_client->checkHTTPcode(409);
	displayOutput ($data, $test_status, NULL);
	
#Query created 'api-client-test-sensor'
#Check for a SINGLE sensor that DOES exist
	echo nl2br("\n\n [QUERY created '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensors($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
//!!!!!!!CAN change sensor name	
#Update 'api-client-test-sensor'
#Update longname(required), description(required), private    
	echo nl2br("\n\n [UPDATE '".$generic_sensor."']\n");
	$data = $wotkit_client->updateSensor( $generic_sensor, $updated_sensor_input_1);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message ="Updated Sensor";
	
#Query created 'api-client-test-sensor'
#Check for a SINGLE updated sensor that DOES exist
	echo nl2br("\n\n [QUERY created '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensors ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Update 'api-client-test-sensor'
#Update name, longname, description (required)  
	echo nl2br("\n\n [UPDATE longname & description change on '".$generic_sensor."']\n");
	$data = $wotkit_client->updateSensor($generic_sensor, $updated_sensor_input_2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message = "Updated Sensor";
	
#Query created 'api-client-test-sensor'
#Check for a SINGLE updated sensor that DOES exist
	echo nl2br("\n\n [QUERY created '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensors ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);

	

	//----------------Sensor Fields---------------//
	//SENSOR FIELDS
	echo nl2br("\n\n .....testing sensor fields...... \n");

	#Query mulitple fields for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY multiple fields for '".$generic_sensor."']\n");
		$expected = 4;
		$data = $wotkit_client->getSensorFields ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);

	#Create incomplete field to 'api-client-test-sensor'	
		echo nl2br("\n\n [CREATE new field with INCOMPLETE field information to '".$generic_sensor."']\n");
		$data = $wotkit_client->updateSensorField ($generic_sensor, $invalid_field_required);
		$test_status = $wotkit_client->checkHTTPcode(400);
		displayOutput ($data, $test_status,NULL);	
		
	#Update default field of 'api-client-test-sensor'	
		echo nl2br("\n\n [UPDATE protected subfield of a default field 'value' for '".$generic_sensor."']\n");
		$data = $wotkit_client->updateSensorField ($generic_sensor, $invalid_field_default);
		$test_status = $wotkit_client->checkHTTPcode(400);
		displayOutput ($data, $test_status,NULL);
		
	#Create new field to 'api-client-test-sensor'	
		echo nl2br("\n\n [CREATE new field '".$new_field[name]."' for '".$generic_sensor."']\n");
		$data = $wotkit_client->updateSensorField ($generic_sensor, $new_field);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);	
		
	#Query single "testfield" field for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY single field '".$new_field[name]."' for '".$generic_sensor."']\n");
		$data = $wotkit_client->getSensorFields ($generic_sensor, $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, null);
		
	#Update "testfield" field for 'api-client-test-sensor'
		echo nl2br("\n\n [UPDATE existing field '".$new_field[name]."' for '".$generic_sensor."']\n");
		$data = $wotkit_client->updateSensorField ($generic_sensor, $updated_field);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);

	#Query single "testfield" field for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY single field '".$new_field[name]."' for '".$generic_sensor."']\n");
		$data = $wotkit_client->getSensorFields ($generic_sensor, $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, null);
		
	#Query mulitple fields for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY multiple fields for '".$generic_sensor."']\n");
		$expected = 5;
		$data = $wotkit_client->getSensorFields ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);	
		
	#Send data to 'testfield' field for 'api-client-test-sensor'
		echo nl2br("\n\n [SEND data to all fields for '".$generic_sensor."']\n");
		$data = $wotkit_client->sendNonStandardSensorData($generic_sensor, $nonStandard_sensor_data);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);
		
	#QUERY data 	
		echo nl2br("\n\n [QUERY data for '".$generic_sensor."']\n");
		$data = $wotkit_client->getSensorData($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);
		
	#Query single "testfield" field for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY single field '".$new_field[name]."' for '".$generic_sensor."']\n");
		$data = $wotkit_client->getSensorFields ($generic_sensor, $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);

	#Query single "value" field for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY single field 'value' for '".$generic_sensor."']\n");
		$data = $wotkit_client->getSensorFields ($generic_sensor, "value");
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, null);

	#Query mulitple fields for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY multiple fields for '".$generic_sensor."']\n");
		$expected = 5;
		$data = $wotkit_client->getSensorFields ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);		
		
	#Query sensor data for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY sensor data for '".$generic_sensor."']\n");
		$expected = 1;
		$data = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);	

	#Delete "testfield" field for 'api-client-test-sensor'
		echo nl2br("\n\n [DELETE single field '".$new_field[name]."' for '".$generic_sensor."']\n");
		$data = $wotkit_client->deleteSensorField ($generic_sensor, $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);
		
	#Query mulitple fields for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY multiple fields for '".$generic_sensor."']\n");
		$expected = 4;
		$data = $wotkit_client->getSensorFields ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);	
			
	#Query deleted "testfield" field for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY deleted field '".$new_field[name]."' for '".$generic_sensor."']\n");
		$data = $wotkit_client->getSensorFields ($generic_sensor, $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode(400);
		displayOutput ($data, $test_status,NULL);

	#Delete required "value" field for 'api-client-test-sensor'
		echo nl2br("\n\n [DELETE required field 'value' for '".$generic_sensor."']\n");
		$data = $wotkit_client->deleteSensorField ($generic_sensor, "value");
		$test_status = $wotkit_client->checkHTTPcode(400);
		displayOutput ($data, $test_status,NULL);
		
	#Query mulitple fields for 'api-client-test-sensor'
		echo nl2br("\n\n [QUERY multiple fields for '".$generic_sensor."']\n");
		$expected = 4;
		$data = $wotkit_client->getSensorFields ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);	

	echo nl2br("\n\n .....done testing sensor fields...... \n");
	//-------------------------------------------------------------//


#Delete 'api-client-test-sensor'
#Delete a SINGLE sensor that DOES exist
	echo nl2br("\n\n [DELETE '".$generic_sensor."'] \n");
	$data = $wotkit_client->deleteSensor($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message = "Deleted Sensor";

#Query deleted 'api-client-test-sensor'
#Check for a SINGLE sensor that DOES NOT exist
	echo nl2br("\n\n [QUERY deleted '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensors($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status,NULL);
	//$message = "Sensor does not exist";

#Query deleted 'api-client-test-sensor_additional'
#Check for a SINGLE sensor that DOES NOT exist
	echo nl2br("\n\n [QUERY deleted '".$additional_generic_sensor."']\n");
	$data = $wotkit_client->getSensors($additional_generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status,NULL);
	//$message = "Sensor does not exist";
	
#Query private sensor 'sensetecnic.api-test-private'
#Check for a SINGLE PRIVATE sensor 
	echo nl2br("\n\n [QUERY private, unowned sensor: '".$private_unowned_sensor."']\n");
	$data = $wotkit_client->getSensors($private_unowned_sensor);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status, NULL);

#Delete non-existent sensor 'not-real-sensor'
#Delete a SINGLE sensor that DOES NOT exist
	echo nl2br("\n\n [DELETE 'not-real-sensor'] \n");
	$data = $wotkit_client->deleteSensor( "not-real-sensor");
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status,NULL);
	//$message="Sensor does not exist";
	
#Update another user's sensor
#Update a sensor you don't own
	echo nl2br("\n\n [UPDATE another user's sensor '".$unowned_sensor_full."']\n");
	$data = $wotkit_client->updateSensor($unowned_sensor_full, $updated_sensor_input_3);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,NULL);

	
	
//SENSOR SUBSCRIPTIONS
echo nl2br("\n\n [*****TESTING SENSOR SUBSCRIPTIONS******] \n");	

#Get subscribed sensors
	echo nl2br("\n\n [QUERY subscribed sensors]\n");
	$expected = 3;
	$data = $wotkit_client->getSubscribedSensors();
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);

#Subscribe to a non-private sensor
	echo nl2br("\n\n [SUBSCRIBE to '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->subscribeSensor($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
#Subscribe to an already subscribed sensor
	echo nl2br("\n\n [SUBSCRIBE to already subscribed '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->subscribeSensor($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status, NULL);

#Subscribed to a private, non-owned sensor 
	echo nl2br("\n\n [SUBSCRIBE to another user's private sensor '".$private_unowned_sensor."']\n");
	$data = $wotkit_client->subscribeSensor($private_unowned_sensor);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,NULL);

#Get subscribed sensors
	echo nl2br("\n\n [QUERY subscribed sensors]\n");
	$expected = 4;
	$data = $wotkit_client->getSubscribedSensors();
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);

#Unsubscribe sensor	
	echo nl2br("\n\n [UNSUBSCRIBE from '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->unsubscribeSensor($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Unsubscribe from not subscribed sensor	
	echo nl2br("\n\n [UNSUBSCRIBE from already unsubscribed'".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->unsubscribeSensor($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,NULL);
	
#Get subscribed sensors
	echo nl2br("\n\n [QUERY subscribed sensors]\n");
	$expected = 3;
	$data = $wotkit_client->getSubscribedSensors();
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	

	
	
//SENSOR DATA
echo nl2br("\n\n [*****TESTING SENSOR DATA******] \n");

	//----------------Posting Sensor Data as Name/Value or JSON---------------//
	echo nl2br("\n\n [......testing different ways of sending sensor data..........] \n");

	#Create 'api-client-test-sensor'
	#Create new sensor
		echo nl2br("\n\n [CREATE '".$generic_sensor."'] \n");
		$data = $wotkit_client->createSensor($new_sensor_input);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, NULL);
		//$message="Sensor Created";
		
	#Create new field to 'api-client-test-sensor'	
		echo nl2br("\n\n [CREATE numeric, nonrequired field '".$num_field[name]."' for '".$generic_sensor."']\n");
		$data = $wotkit_client->updateSensorField ($generic_sensor, $num_field);
		$data = $wotkit_client->updateSensorField ($generic_sensor, $num_field);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);	

	#Create new field to 'api-client-test-sensor'	
		echo nl2br("\n\n [CREATE string, nonrequired field '".$string_field[name]."' for '".$generic_sensor."']\n");
		$data = $wotkit_client->updateSensorField ($generic_sensor, $string_field);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, NULL, true);	
		
	#Post Name/Value pair valid data
		$data_array = array( "value" => 1, "lat" => 2, "lng" => 2, 
							"message" => "test message", 
							$num_field[name]=>9, $string_field[name]=>"hello name/value string!");
		echo nl2br("\n\n [Post Name/Value pair valid data to '".$generic_sensor."']\n");
		$data = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);
		
		echo nl2br("\n\n....query....\n");
		$expected = 1;
		$data = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);

	#Post JSON valid data
		$data_array = array( "value" => 1, "lat" => 2, "lng" => 2, 
							"message" => "test message", 
							$num_field[name]=>99, $string_field[name]=>"hello JSON string!");
		echo nl2br("\n\n [Post JSON valid data to '".$generic_sensor."']\n");
		$data = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array, true);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);
		
		echo nl2br("\n\n....query....\n");
		$expected = 2;
		$data = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);

	#Post Name/Value pair invalid data
		$data_array = array( "value" => 5, "lat" => 6, "lng" => 7, 
							"message" => "test message with test field", 
							$num_field[name]=>"hello", $string_field[name]=>9);
		echo nl2br("\n\n [Post Name/Value pair invalid data to '".$generic_sensor."']\n");
		$data = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array);
		$test_status = $wotkit_client->checkHTTPcode(400);
		displayOutput ($data, $test_status,NULL);
		
		echo nl2br("\n\n....query....\n");
		$expected = 2;
		$data = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected);

	#Post JSON invalid data
		echo nl2br("\n\n [Post JSON invalid data to '".$generic_sensor."']\n");
		$data = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array, true);
		$test_status = $wotkit_client->checkHTTPcode(400);
		displayOutput ($data, $test_status,NULL);
		
		echo nl2br("\n\n....query....\n");
		$expected = 2;
		$data = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected);

	#Post Name/Value pair undeclared data
		$data_array = array( "value" => 5, "lat" => 6, "lng" => 7, 
							"message" => "test message with test field", 
							$num_field[name]=>9, $string_field[name]=>"hello",
							"madeupNUMfield"=>9, "madeupSTRINGfield"=>"hi name/value!");
		echo nl2br("\n\n [Post Name/Value pair undeclared data to '".$generic_sensor."']\n");
		$data = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);
		
		echo nl2br("\n\n....query....\n");
		$expected = 3;
		$data = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected);

	#Post JSON undeclared data
		$data_array = array( "value" => 5, "lat" => 6, "lng" => 7, 
							"message" => "test message with test field", 
							$num_field[name]=>9, $string_field[name]=>"hello",
							"madeupNUMfield"=>99, "madeupSTRINGfield"=>"hi JSON!");
		echo nl2br("\n\n [Post JSON undeclared data to '".$generic_sensor."']\n");
		$data = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array, true);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);
		
		echo nl2br("\n\n....query....\n");
		$expected = 4;
		$data = $wotkit_client->getSensorData ($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected);

	#Delete 'api-client-test-sensor'
	#Delete a SINGLE sensor that DOES exist
		echo nl2br("\n\n [DELETE '".$generic_sensor."'] \n");
		$data = $wotkit_client->deleteSensor($generic_sensor);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);
		//$message = "Deleted Sensor";

	echo nl2br("\n\n [.............done testing sending sensor data..........] \n");
	//----------------------------------------------------------------------------------//

	

#Send data to another user's sensor 'sensetecnic.mule1'
#Send data to a sensor you don't own
	echo nl2br("\n\n [UPDATE another user's sensor data '".$unowned_sensor_full."']\n");
	$value = rand(1,100);
	$lat = rand(1,100);
	$lng = rand(1,100);
	$message = "test message #"; 
	$data = $wotkit_client->sendSensorData( $unowned_sensor_full,$value, $lat, $lng, $message);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,NULL);
	//$message="Not Authorized";
	//would be 404 error if you didn't specify owner's name

#Sending 3 pieces of data to 'api-data-test-3'
#Sending 3 pieces data to existing sensor
	echo nl2br("\n\n [SEND 3 pieces of data to '".$existing_data_sensor[2]."'] \n");
	for($i=1; $i<=3; $i++)
	{	$value = rand(1,100);
		$lat = rand(1,100);
		$lng = rand(1,100);
		$message = "test message ".($i+10); 
		echo nl2br("\n value=".$value." lat=".$lat." lng=".$lng." message=".$message." \n");
		$data = $wotkit_client->sendSensorData($existing_data_sensor[2], $value, $lat, $lng, $message);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status);
	};
	
#Query data from 'api-data-test-3'
#Query data from existing sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$expected = 3;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Update 2nd piece of data from 'api-data-test-3' (using timestamp as long)
#Update 2nd piece of data from existing sensor
	echo nl2br("\n\n [UPDATE 2nd data piece from '".$existing_data_sensor[2]."' (using timestamp as long)] \n");
	
	$saved_response = json_decode($wotkit_client->response, true);
	
	$updated_sensor_data = array(array("timestamp" =>$saved_response[1][timestamp],
	 "value"=>100,
	 "lat"=>100,
	 "lng"=>100,
	 "message"=>"updated fields")
	 );
	$data = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message="Sensor updated";

#Query data from existing sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$expected = 3;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	
	
#Send new piece of data	
    echo nl2br("\n\n [Send new piece of data to '".$existing_data_sensor[2]."'] \n"); 
	$old_timezone  = date_default_timezone_get();
	date_default_timezone_set('UTC');
	$timestamp_number = time()*1000;
	$sensor_data = array(
	 "timestamp" =>$timestamp_number,
	 "value"=>100,
	 "lat"=>100,
	 "lng"=>100,
	 "message"=>"new data");
	$data = $wotkit_client->sendNonStandardSensorData($existing_data_sensor[2], $sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,null);	

#Query data from existing sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$expected = 4;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	
	
#Update new piece of data from 'api-data-test-3' (using timestamp as string)
#Update new piece of data from existing sensor
	echo nl2br("\n\n [UPDATE new data piece from '".$existing_data_sensor[2]."' (using timestamp as string)] \n");
	$timestamp_string = date('o-m-d!G:i:s', $timestamp_number/1000);
	$timestamp_string = str_replace('!', 'T', $timestamp_string);
	$timestamp_string .= ".000z";
	$updated_sensor_data = array(array("timestamp" =>$timestamp_string,
	 "value"=>600,
	 "lat"=>600,
	 "lng"=>600,
	 "message"=>"updated new data")
	 );
	$data = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message="Sensor updated";	

#Query data from existing sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$expected = 4;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	

#Delete new data from 'api-data-test-3'
#Delete new data from existing sensor
	echo nl2br("\n\n [DELETE new data from '".$existing_data_sensor[2]."'] \n");
	$data = $wotkit_client->deleteSensorData($existing_data_sensor[2], $timestamp_number);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	$message="Sensor data deleted";	

#Query data from existing sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$expected = 3;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	

date_default_timezone_set($old_timezone);

#Update 2nd piece of data from 'api-data-test-3' with INVALID DATA (string in numerical field)
#Update 2nd piece of data from existing sensor with INVALID DATA 
	echo nl2br("\n\n [UPDATE 2nd data piece from '".$existing_data_sensor[2]."' with INVALID DATA (string in numerical field)] \n");
	$updated_sensor_data = array(array("timestamp" =>$saved_response[1][timestamp],
	 "value"=>"100string",
	 "lat"=>"100string",
	 "lng"=>"100string",
	 "message"=>"updated fields")
	 );
	$data = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode(400);
	displayOutput ($data, $test_status,NULL);
	//$message="Sensor updated";
	
#Update 2nd piece of data from 'api-data-test-3' with INVALID DATA (missing required field)
#Update 2nd piece of data from existing sensor with INVALID DATA
	echo nl2br("\n\n [UPDATE 2nd data piece from '".$existing_data_sensor[2]."' with INVALID DATA (missing required field)] \n");
	$updated_sensor_data = array(array("timestamp" =>$saved_response[1][timestamp],
	 "lat"=>100,
	 "lng"=>100,
	 "message"=>"updated fields")
	 );
	$data = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode(400);
	displayOutput ($data, $test_status,NULL);
	//$message="Sensor updated";	

#Update 2nd piece of data from 'api-data-test-3' with INVALID DATA (data from future)
#Update 2nd piece of data from existing sensor with INVALID DATA
	echo nl2br("\n\n [UPDATE 2nd data piece from '".$existing_data_sensor[2]."' with INVALID DATA (data from future)] \n");
	$updated_sensor_data = array(array("timestamp" => time()*1000 + 60000,
	 "value"=>100,
	 "lat"=>100,
	 "lng"=>100,
	 "message"=>"updated fields")
	 );
	$data = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode(400);
	displayOutput ($data, $test_status,NULL);
	//$message="Sensor updated";	
	
	
#Query data from 'api-data-test-3'
#Query data from existing sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$expected = 3;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Update all data from 'api-data-test-3'
#Update all data from existing sensor
	echo nl2br("\n\n [UPDATE all data from '".$existing_data_sensor[2]."'] \n");
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
	$data = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message="Sensor updated";
	
#Query data from 'api-data-test-3'
#Query data from existing sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$expected = 2;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Delete latest data from 'api-data-test-3'
#Delete latest data from existing sensor
	echo nl2br("\n\n [DELETE latest data from '".$existing_data_sensor[2]."'] \n");
	$saved_response = json_decode($wotkit_client->response, true);
	end($saved_response);
	$last_key=key($saved_response);
	$time_stamp=$saved_response[$last_key][timestamp];
	$data = $wotkit_client->deleteSensorData( $existing_data_sensor[2], $time_stamp);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message="Sensor data deleted";
	
#Querying data from 'api-data-test-3'
#Querying data from existing sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$expected = 1;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);



	//----------------Sensor Fields---------------//
	//SENSOR FIELDS
	echo nl2br("\n\n .....testing sensor fields...... \n");

	#Query mulitple fields for 'api-data-test-3'
		echo nl2br("\n\n [QUERY multiple fields for '".$existing_data_sensor[2]."']\n");
		$expected = 4;
		$data = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);
		
	#Create new field to 'api-data-test-3'	
		echo nl2br("\n\n [CREATE new field '".$new_field[name]."' for '".$existing_data_sensor[2]."']\n");
		$data = $wotkit_client->updateSensorField ($existing_data_sensor[2], $new_field);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);	
		
	#Query mulitple fields for 'api-data-test-3'
		echo nl2br("\n\n [QUERY multiple fields for '".$existing_data_sensor[2]."']\n");
		$expected = 5;
		$data = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);

	#Query single "testfield" field for 'api-data-test-3'
		echo nl2br("\n\n [QUERY single field '".$new_field[name]."' for '".$existing_data_sensor[2]."']\n");
		$data = $wotkit_client->getSensorFields ($existing_data_sensor[2], $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, null);
		
	#Send data to 'testfield' field for 'api-data-test-3'
		echo nl2br("\n\n [Send data to all fields for '".$existing_data_sensor[2]."']\n");
		$data = $wotkit_client->sendNonStandardSensorData($existing_data_sensor[2], $nonStandard_sensor_data);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);
		
	#Query single "testfield" field for 'api-data-test-3'
		echo nl2br("\n\n [QUERY single field '".$new_field[name]."' for '".$existing_data_sensor[2]."']\n");
		$data = $wotkit_client->getSensorFields ($existing_data_sensor[2], $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, null);
		
	#Query mulitple fields for 'api-data-test-3'
		echo nl2br("\n\n [QUERY multiple fields for '".$existing_data_sensor[2]."']\n");
		$expected = 5;
		$data = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);	

	#Querying data from 'api-data-test-3'
	#Querying data from existing  sensor
		echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
		$expected = 2;
		$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);	
		
	#Delete "testfield" field for 'api-data-test-3'
		echo nl2br("\n\n [DELETE single field '".$new_field[name]."' for '".$existing_data_sensor[2]."']\n");
		$data = $wotkit_client->deleteSensorField ($existing_data_sensor[2], $new_field[name]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status,NULL);
		
	#Query mulitple fields for 'api-data-test-3'
		echo nl2br("\n\n [QUERY multiple fields for '".$existing_data_sensor[2]."']\n");
		$expected = 4;
		$data = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, $expected, true);	

	echo nl2br("\n\n .....done testing sensor fields...... \n");	
	//---------------------------------------------------------------//


#Querying data from 'api-data-test-3'
#Querying data from existing  sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$expected = 2;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);

#Deleting latest data from 'api-data-test-3'
#Deleting latest data from existing sensor
	echo nl2br("\n\n [DELETE latest data from '".$existing_data_sensor[2]."'] \n");
	$saved_response = json_decode($wotkit_client->response, true);
	end($saved_response);
	$last_key=key($saved_response);
	$time_stamp=$saved_response[$last_key][timestamp];
	$data = $wotkit_client->deleteSensorData( $existing_data_sensor[2], $time_stamp);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	//$message="Sensor data deleted";

#Querying data from 'api-data-test-3'
#Querying data from existing  sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$expected = 1;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Deleting latest data from 'api-data-test-3'
#Deleting latest data from existing sensor
	echo nl2br("\n\n [DELETE latest data from '".$existing_data_sensor[2]."'] \n");
	$saved_response = json_decode($wotkit_client->response, true);
	end($saved_response);
	$last_key=key($saved_response);
	$time_stamp=$saved_response[$last_key][timestamp];
	$data = $wotkit_client->deleteSensorData( $existing_data_sensor[2], $time_stamp);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	//$message="Sensor data deleted";
		
#Querying data from EMPTY 'api-data-test-3'
#Querying data from existing EMPTY sensor
	echo nl2br("\n\n [QUERY data from EMPTY '".$existing_data_sensor[2]."'] \n");
	$expected = 0;
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);

#Deleting latest data from EMPTY 'api-data-test-3'
#Deleting latest data from EMPTY existing sensor
	echo nl2br("\n\n [DELETE latest data from '".$existing_data_sensor[2]."'] \n");
	$saved_response = json_decode($wotkit_client->response, true);
	end($saved_response);
	$last_key=key($saved_response);
	$time_stamp=$saved_response[$last_key][timestamp];
	$data = $wotkit_client->deleteSensorData( $existing_data_sensor[2], $time_stamp);
	$test_status = $wotkit_client->checkHTTPcode(405);
	displayOutput ($data, $test_status, NULL);

	

//RAW SENSOR DATA RETREIVAL
echo nl2br("\n\n [*****TESTING RAW SENSOR DATA RETREIVAL******] \n");

#Sending data to make sensors active 
	echo nl2br("\n\n [Sending data to 'api-data-test-1', and 'api-data-test-2' sensors (to make them active)] \n");

	for ($i=0; $i<2; $i++){
		$value = rand(1,100);
		$lat = rand(1,100);
		$lng = rand(1,100);
		$message = "test message to be active ".rand(100,200); 
		echo nl2br("\n value=".$value." & lat=".$lat." & lng=".$lng." & message=".$message);
		$data = $wotkit_client->sendSensorData( $existing_data_sensor[$i],$value, $lat, $lng, $message);
		$test_status = $wotkit_client->checkHTTPcode();
		displayOutput ($data, $test_status, NULL);
	}
#Querying all raw data
	echo nl2br("\n\n [Querying all from '".$existing_data_sensor[0]."'] \n");
	$expected = 4;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	

#Querying all raw data
	echo nl2br("\n\n [Querying all from '".$existing_data_sensor[1]."'] \n");
	$expected = 1;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[1]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);		

#Querying raw data START END
	echo nl2br("\n\n [Querying raw data from > 2pm January 7th to <= 1pm January 8th from '".$existing_data_sensor[0]."'] \n");
	$expected = 1;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, $end_time);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Querying raw data BEFORE
	echo nl2br("\n\n [Querying elements of raw data <= 1.5hr BEFORE 2pm January 7th from '".$existing_data_sensor[0]."'] \n");
	$expected = 2;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, NULL, NULL, NULL, 1.5*3600000);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Querying raw data AFTER
	echo nl2br("\n\n [Querying elements of raw data > 1 hr AFTER 2pm January 7th from '".$existing_data_sensor[0]."'] \n");
	$expected = 1;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, NULL, 3600000);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Querying raw data BEFOREE
	echo nl2br("\n\n [Querying last 3 elements of raw data BEFORE now from '".$existing_data_sensor[0]."'] \n");
	$expected = 3;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], NULL, NULL, NULL, NULL, NULL, 3);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true); 

#Querying raw data AFTERE
	echo nl2br("\n\n [Querying first 2 elements of raw data AFTER 2pm January 7th from '".$existing_data_sensor[0]."'] \n");
	$expected = 2;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, NULL, NULL,2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Querying raw data REVERSE = false
	echo nl2br("\n\n [Querying all raw data, oldest to newest, from '".$existing_data_sensor[0]."'] \n");
	$expected = 4;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], NULL, NULL, NULL, NULL, NULL, NULL, "false");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Querying raw data REVERSE = true
	echo nl2br("\n\n [Querying all raw data, newest to oldest, from '".$existing_data_sensor[0]."'] \n");
	$expected = 4;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], NULL, NULL, NULL, NULL, NULL, NULL, "true");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	

	

//FORMATTED SENSOR DATA RETREIVAL
echo nl2br("\n\n [*****TESTING FORMATTED SENSOR DATA RETREIVAL******] \n");

#Querying formatted data
	echo nl2br("\n\n [Querying formatted data in HTML table where value>30 from '".$existing_data_sensor[0]."'] \n");
	$data = $wotkit_client->getFormattedSensorData( $existing_data_sensor[0], "select * where value>20", 1, "html"); 
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);//special case in function?

	
	
//QUERYING SENSORS	
echo nl2br("\n\n [*****TESTING QUERYING SENSORS******] \n");
	
#Querying ALL
	//should not include private sensor
	echo nl2br("\n\n [Query ALL] \n");
	$data = $wotkit_client->getSensors(null,"all") ;
	$total_sensors = count($data);
	$test_status = $wotkit_client->checkHTTPcode();
	echo nl2br("ASSUMES this returned correct number of sensors");
	displayOutput ($data, $test_status, $total_sensors);
	echo nl2br("\n assumes this is the correct number of tags");
	
#Querying CONTRIBUTED
	echo nl2br("\n\n [Query CONTRIBUTED] \n");
	$expected = 3;
	$data = $wotkit_client->getSensors (null,"contributed");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Querying SUBSCRIBED
	echo nl2br("\n\n [Query SUBSCRIBED] \n");
	$expected = 3;
	$data = $wotkit_client->getSensors (null, "subscribed");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);

#Querying ACTIVE
	//echo nl2br("\n\n [Query ACTIVE] \n");
	//$expected = 3;
	//$data = $wotkit_client->getSensors (null, NULL,"true");
	//$test_status = $wotkit_client->checkHTTPcode();
	//displayOutput ($data, $test_status, $expected);
	
#Querying PRIVATE
	echo nl2br("\n\n [Query PRIVATE] \n");
	$private = 1;
	$data = $wotkit_client->getSensors (null, NULL, NULL,"true");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $private);
	
#Querying NOT PRIVATE
	echo nl2br("\n\n [Query NOT PRIVATE] \n");
	$expected = $total_sensors - $private;
	$data = $wotkit_client->getSensors (null, NULL, NULL,"false");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Querying SUBSCRIBED & ACTIVE
	echo nl2br("\n\n [Query SUBSCRIBED & ACTIVE] \n");
	$expected = 2;
	$data = $wotkit_client->getSensors (null,"subscribed", "true");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Querying SUBSCRIBED & INACTIVE
	echo nl2br("\n\n [Query SUBSCRIBED & INACTIVE] \n");
	$expected = 1;
	$data = $wotkit_client->getSensors (null, "subscribed", "false");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);

#Querying TAGGED single
	echo nl2br("\n\n [Query TAGGED data] \n");
	$expected = 3;
	$data = $wotkit_client->getSensors (null, NULL, NULL, NULL, "data");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Querying TAGGED single
	echo nl2br("\n\n [Query TAGGED Canada] \n");
	$expected = 3;
	$data = $wotkit_client->getSensors (null, NULL, NULL, NULL, "Canada");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Querying TAGGED multiple
	echo nl2br("\n\n [Query TAGGED vancouver, edmonton] \n");
	$expected = 2;
	$data = $wotkit_client->getSensors (null, NULL, NULL, NULL,"vancouver,edmonton");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Querying TAGGED Cross Tags
	echo nl2br("\n\n [Query TAGGED data, Canada] \n");
	$expected = 3;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, "data,Canada") ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Querying TEXT
	echo nl2br("\n\n [Query TEXT api-] \n");
	$expected = 3;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, "api-") ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);

#Querying OFFSET
	echo nl2br("\n\n [Query OFFSET=35] \n");
	$offset = 35;
	$expected = $total_sensors - $offset;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, $offset) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Querying OFFSET & LIMIT
	echo nl2br("\n\n [Query OFFSET=15 & LIMIT=5] \n");
	$expected = 5;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, 15, 5) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Querying LOCATION
	echo nl2br("\n\n [Query LOCATION = invalid North/South coordinates] \n");
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_invalid_ns) ;
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status, null);	
	
#Querying LOCATION
	echo nl2br("\n\n [Query LOCATION = invalid North coordinate] \n");
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_invalid_toolarge) ;
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status, null);
	
#Querying LOCATION
	echo nl2br("\n\n [Query LOCATION = vancouver] \n");
	$expected = 23;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_vancouver) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Querying LOCATION & ACTIVE
	echo nl2br("\n\n [Query LOCATION = vancouver & ACTIVE] \n");
	$expected = 1;
	$data = $wotkit_client->getSensors(null, NULL, "true", NULL, NULL, NULL, NULL, NULL, $location_vancouver) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	

#Querying LOCATION 
	echo nl2br("\n\n [Query LOCATION = winnipeg ] \n");
	$expected = 1;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_winnipeg) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Querying LOCATION with no results
	echo nl2br("\n\n [Query LOCATION = Kilkenny -- with NO sensors ] \n");
	$expected = 0;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_kilkenny) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	

	
//AGGREGATE SENSOR DATA
echo nl2br("\n\n [*****TESTING QUERYING AGGREGATE SENSOR DATA******] \n");

#Querying data from subscribed, active sensors from last hour
	echo nl2br("\n\n [Query aggregated sensor data: SUBSCRIBED, ACTIVE, last hour] \n");
	$expected = 2;
	$params = array("scope" => "subscribed", "active" => true, "before" => 3600000 );	
	$data = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Querying data from subscribed, active sensors from last 10
	echo nl2br("\n\n [Query aggregated sensor data: SUBSCRIBED, ACTIVE, last 10] \n");
	$expected = 5;
	$params = array("scope" => "subscribed", "active" => true, "beforeE" => 10 );	
	$data = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Querying data from subscribed, active sensors during hour after 7 January 2013 14:00
	echo nl2br("\n\n [Query aggregated sensor data: SUBSCRIBED, ACTIVE, during hour after 7 January 2013 14:00] \n");
	$expected = 1;
	$params = array("scope" => "subscribed", "active" => true, "start" => $start_time, "after"=>3600000 );	
	$data = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Querying data from active sensors orderBy Time
	echo nl2br("\n\n [Query aggregated sensor data: ACTIVE ordered by time] \n");
	$expected = 5;
	$params = array("active" => true,"orderBy" => "time" );	
	$data = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Querying data from active sensors orderBy Sensor
	echo nl2br("\n\n [Query aggregated sensor data: ACTIVE ordered by sensor] \n");
	$expected = 5;
	$params = array("active" => true, "orderBy" => "sensor" );	
	$data = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	

	

	//DELETE DATA 
	echo nl2br("\n\n [.......DELETING NEWLY ADDED DATA.......] \n");

		#Deleting latest data from 'api-data-test-1' & 'api-data-test-2'
		#Deleting latest data from existing sensors
			for ($i=0; $i<2; $i++){
				echo nl2br("\n\n [DELETE latest data from '".$existing_data_sensor[$i]."'] \n");
				$data = $wotkit_client->getSensorData($existing_data_sensor[$i]);
				$saved_response = json_decode($wotkit_client->response, true);
				end($saved_response);
				$last_key=key($saved_response);
				$time_stamp=$saved_response[$last_key][timestamp];
				$data = $wotkit_client->deleteSensorData( $existing_data_sensor[$i], $time_stamp);
				$test_status = $wotkit_client->checkHTTPcode();
				displayOutput ($data, $test_status, NULL);
			}
			
	echo nl2br("\n\n [.......done deleteing newly added data........] \n");		
	

	
//ACTUATORS	
echo nl2br("\n\n [*****TESTING CONTROL OF ACTUATORS******] \n");

#Subscribe to, send data to, and get data from actuator you DO own
	echo nl2br("\n\n [--Subscribe to, send data to, and get data from OWNED actuator '".$actuator_name."'] \n");
	$expected = 1;
	echo nl2br("Sending messge ".$actuator_message_display."\n");
	echo nl2br("Response:\n");
	$data=$wotkit_client->testActuator($actuator_name, $actuator_message);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	

#Send data to actuator you DO NOT own
	echo nl2br("\n\n [--Without credentials -> Send data to PUBLIC actuator '".$actuator_name_full."'] \n");
	echo nl2br("Sending messge: ".$actuator_message_display."\n");
	$public = true;
	$expected = 1;
	$data=$wotkit_client->subscribeActuator($actuator_name);
	$sub_id = $data["subscription"];	
	$data=$wotkit_client->sendActuator($actuator_name_full, $actuator_message, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, null);	
	echo nl2br("\nGet Response:\n");
	$data = $wotkit_client->getActuator($sub_id);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Send data to actuator you DO NOT own
	echo nl2br("\n\n [--With non-owner credentials -> Send data to PUBLIC actuator '".$actuator_name_full."'] \n");
	echo nl2br("Sending messge: ".$actuator_message_display."\n");
	$public = true;
	$expected = 1;
	$data=$wotkit_client->subscribeActuator($actuator_name);
	$sub_id = $data["subscription"];	
	$data=$wotkit_client->sendActuator($actuator_name_full, $actuator_message, $public, true);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, null);
	echo nl2br("\nGet Response:\n");
	$data = $wotkit_client->getActuator($sub_id);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Subscribe to PRIVATE actuator you DO NOT own
	echo nl2br("\n\n [--Subscribe to UNOWNED, PRIVATE actuator '".$private_unowned_sensor."'] \n");
	$data=$wotkit_client->subscribeActuator($private_unowned_sensor, $actuator_message);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status, null);	
	
#Send message to PRIVATE actuator you DO NOT own
	echo nl2br("\n\n [--Send message to UNOWNED, PRIVATE actuator '".$private_unowned_sensor."'] \n");
	echo nl2br("Sending messge ".$actuator_message_display."\n");
	$data=$wotkit_client->sendActuator($private_unowned_sensor, $actuator_message);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status, null);	


	
//USERS
echo nl2br("\n\n [*****TESTING USERS******] \n");	

#Create invalid username
	echo nl2br("\n\n [CREATE user with invalid name '".$invalid_user_name."'] \n");
	$data = $wotkit_client->createUser($invalid_name_user_input);
	$test_status = $wotkit_client->checkHTTPcode(500);
	displayOutput ($data, $test_status, NULL);
	
#Create invalid user with missing property
	echo nl2br("\n\n [CREATE user with missing properties] \n");
	$data = $wotkit_client->createUser($missing_property_user_input);
	$test_status = $wotkit_client->checkHTTPcode(500);
	displayOutput ($data, $test_status, NULL);
	
#Create user 
	echo nl2br("\n\n [CREATE user '".$new_user_name."'] \n");
	$data = $wotkit_client->createUser($new_user_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
#Create existing user 
	echo nl2br("\n\n [CREATE EXISTING user '".$new_user_name."'] \n");
	$data = $wotkit_client->createUser($new_user_input);
	$test_status = $wotkit_client->checkHTTPcode(409);
	displayOutput ($data, $test_status, NULL);
	
#Query existing user 
	echo nl2br("\n\n [QUERY existing user '".$new_user_name."'] \n");
	$data = $wotkit_client->getUsers($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
#Update existing user 
	echo nl2br("\n\n [UPDATE existing user '".$new_user_name."'] \n");
	$data = $wotkit_client->updateUser($new_user_name, $updated_user_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
#Query existing user 
	echo nl2br("\n\n [QUERY existing user '".$new_user_name."'] \n");
	$data = $wotkit_client->getUsers($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
#Update existing user with invalid data 
	echo nl2br("\n\n [UPDATE existing user '".$new_user_name."' with new username -- Not Allowed'] \n");
	$data = $wotkit_client->updateUser($new_user_name, $invalid_updated_user_input);
	$test_status = $wotkit_client->checkHTTPcode(400);
	displayOutput ($data, $test_status, NULL);
	
#Query existing user 
	echo nl2br("\n\n [QUERY existing user '".$new_user_name."'] \n");
	$data = $wotkit_client->getUsers($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
#Query all users with "api" in name
	echo nl2br("\n\n [QUERY existing user with TEXT='api'] \n");
	$expected = 1;
	$data = $wotkit_client->getUsers(null, "api");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);		
	
#Query all users REVERSE = true
	echo nl2br("\n\n [QUERY existing users from oldest to newest, REVERSE=true, LIMIT = 6] \n");
	$expected = 6;
	$data = $wotkit_client->getUsers(null, null, true, null, 6);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Query all users REVERSE = false
	echo nl2br("\n\n [QUERY existing users from oldest to newest, REVERSE=false, LIMIT = 6] \n");
	$expected = 6;
	$data = $wotkit_client->getUsers(null, null, false, null, 6);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Query all users LIMIT 2 
	echo nl2br("\n\n [QUERY all users LIMIT = 2] \n");
	$expected = 2;
	$data = $wotkit_client->getUsers(null, null, null, null, 2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Query all users OFFSET 5 LIMIT 2 
	echo nl2br("\n\n [QUERY all users OFFSET = 5 & LIMIT = 2] \n");
	$expected = 2;
	$data = $wotkit_client->getUsers(null, null, null, 5, 2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Delete user 'new-user-api-testing'
#Delete existing user 
	echo nl2br("\n\n [DELETE EXISTING user '".$new_user_name."'] \n");
	$data = $wotkit_client->deleteUser($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, null);	
	
#Delete non-existent user 'new-user-api-testing'
	echo nl2br("\n\n [DELETE NON-EXISTENT user '".$new_user_name."'] \n");
	$data = $wotkit_client->deleteUser($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status, null);	
	
#Query non-existent user 
	echo nl2br("\n\n [QUERY NON-EXISTENT user '".$new_user_name."'] \n");
	$data = $wotkit_client->getUsers($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status, NULL);

	
	
//NEWS
echo nl2br("\n\n [*****TESTING NEWS******] \n");	

#Query news
	echo nl2br("\n\n [QUERY news with NO CREDENTIALS] \n");
	$data = $wotkit_client->getNews();
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);	
	
	
	
//STATS
echo nl2br("\n\n [*****TESTING STATS******] \n");	

#Query stats
	echo nl2br("\n\n [QUERY stats with NO CREDENTIALS] \n");
	$data = $wotkit_client->getNews();
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);	

	
	
//TAGS
echo nl2br("\n\n [*****TESTING TAGS******] \n");
	
#Query all tags
	echo nl2br("\n\n [QUERY SCOPE=ALL tags] \n");
	$data = $wotkit_client->getTags("all");
	$all_tags = count($data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $all_tags);	
	echo nl2br("\n assumes this is the correct number of tags");
	
#Query subscribed tags
	$expected = 4;
	echo nl2br("\n\n [QUERY SCOPE=SUBSCRIBED tags] \n");
	$data = $wotkit_client->getTags("subscribed");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Query contributed tags
	$expected = 5;
	echo nl2br("\n\n [QUERY SCOPE=CONTRIBUTED tags] \n");
	$data = $wotkit_client->getTags("contributed");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Query all tags for private sensors
	$private_tags = 1;
	$expected = 3;
	echo nl2br("\n\n [QUERY tags for PRIVATE sensors] \n");
	$data = $wotkit_client->getTags(null, true);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Query tags for sensors with specific text
	$expected = 3; 
	echo nl2br("\n\n [QUERY tags for sensors with TEXT=api-data-test-2] \n");
	$data = $wotkit_client->getTags(null, null, "api-data-test-2", null, null, null, null );
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Query subscribed, active
	$expected = 4; 
	echo nl2br("\n\n [QUERY SCOPE=SUBSCRIBED and ACTIVE tags] \n");
	$data = $wotkit_client->getTags("subscribed", null, null, true, null, null, null);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Query subscribed, active, offset
	$expected = 3; 
	echo nl2br("\n\n [QUERY SCOPE=SUBSCRIBED and ACTIVE with OFFSET=1] \n");
	$data = $wotkit_client->getTags("subscribed", null, null, true, 1, null, null );
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Query subscribed, active, limit
	$expected = 1; 
	echo nl2br("\n\n [QUERY SCOPE=SUBSCRIBED and ACTIVE with LIMIT=1] \n");
	$data = $wotkit_client->getTags("subscribed", null, null, true, null, 1, null );
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Query location tags (no sensors)
	$expected = 0;
	echo nl2br("\n\n [QUERY LOCATION=Kilkenny tags] \n");
	$data = $wotkit_client->getTags(null, null, null, null, null, null, $location_kilkenny );
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);

#Query location tags (public sensors)
	$expected = 3;
	echo nl2br("\n\n [QUERY LOCATION=Edmonton tags] \n");
	$data = $wotkit_client->getTags(null, null, null, null, null, null, $location_edmonton );
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Query location tags (private sensors)
	$expected = 3;
	echo nl2br("\n\n [QUERY LOCATION=Winnipeg tags] \n");
	$data = $wotkit_client->getTags(null, null, null, null, null, null, $location_winnipeg );
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Query location tags, invalid data
	echo nl2br("\n\n [QUERY LOCATION=invalid North/South coordinate tags] \n");
	$data = $wotkit_client->getTags(null, null, null, null, null, null, $location_invalid_ns );
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status, null);
	
#Query location tags, invalid data
	echo nl2br("\n\n [QUERY LOCATION=invalid North coordinate tags] \n");
	$data = $wotkit_client->getTags(null, null, null, null, null, null, $location_invalid_toolarge );
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status, null);
	
	$public = true;
	
#Query all, with no credentials
	echo nl2br("\n\n [QUERY SCOPE=ALL tags, with NO CREDENTIALS] \n");
	$expected = $all_tags - $private_tags;
	$data = $wotkit_client->getTags("all", null, null, null, null, null, null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Query all, limit 2, with no credentials
	echo nl2br("\n\n [QUERY SCOPE=ALL tags, with LIMIT=2 and NO CREDENTIALS] \n");
	$expected = 2;
	$data = $wotkit_client->getTags("all", null, null, null, null, 2, null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Query subscribed, with no credentials
	echo nl2br("\n\n [QUERY SCOPE=SUBSCRIBED tags, with NO CREDENTIALS] \n");
	$expected = 0;
	$data = $wotkit_client->getTags("subscribed", null, null, null, null, null, null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
#Query private, with no credentials
	echo nl2br("\n\n [QUERY tags for PRIVATE sensors, with NO CREDENTIALS] \n");
	$expected = 0;
	$data = $wotkit_client->getTags(null, true, null, null, null, null, null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	

#Query location tags, with no credentials (no sensors)
	$expected = 0;
	echo nl2br("\n\n [QUERY LOCATION=Kilkenny tags, with NO CREDENTIALS -- no sensors exist] \n");
	$data = $wotkit_client->getTags(null, null, null, null, null, null, $location_kilkenny, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Query location tags, with no credentials (private sensors)
	$expected = 0;
	echo nl2br("\n\n [QUERY LOCATION=Winnipeg tags, with NO CREDENTIALS -- a private sensor exists] \n");
	$data = $wotkit_client->getTags(null, null, null, null, null, null, $location_winnipeg, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
#Query location tags, with no credentials (public sensors)
	$expected = 3;
	echo nl2br("\n\n [QUERY LOCATION=Edmonton tags, with NO CREDENTIALS] \n");
	$data = $wotkit_client->getTags(null, null, null, null, null, null, $location_edmonton, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
	
//PUBLIC FUNCTIONS
echo nl2br("\n\n [*****TESTING PUBLIC FUNCTIONS******] \n");	

$public = true;

#Query for multiple sensors
	echo nl2br("\n\n [QUERY PUBLIC, existing sensors, LIMIT=5, with NO CREDENTIALS]\n");
	$expected = 5;
	$data = $wotkit_client->getSensors(null, null, null, null, null, null, null, 5, null, $public );
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);

#Query  'api-client-test-sensor'
#Query a SINGLE sensor that DOES exist
	echo nl2br("\n\n [QUERY PUBLIC, existing sensor: '".$existing_data_sensor_full[0]."', with NO CREDENTIALS]\n");
	$data = $wotkit_client->getSensors($existing_data_sensor_full[0], null, null, null, null, null, null, null, null, $public );
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, null);
	
#Query a single, PRIVATE sensor that does exist
	echo nl2br("\n\n [QUERY PRIVATE, existing sensor: '".$private_unowned_sensor."', with NO CREDENTIALS]\n");
	$data = $wotkit_client->getSensors($private_unowned_sensor, null, null, null, null, null, null, null, null, $public );
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,NULL);		

#Query a single, public sensor that DOES NOT exist
	echo nl2br("\n\n [QUERY NOT-EXISTENT sensor: '".$invalid_sensor_input["name"]."', with NO CREDENTIALS]\n");
	$data = $wotkit_client->getSensors($invalid_sensor_input["name"], null, null, null, null, null, null, null, null, $public );
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status,NULL);	
	
#Query sensor data from PUBLIC sensor	
	echo nl2br("\n\n [QUERY data from PUBLIC sensor: '".$existing_data_sensor_full[0]."', with NO CREDENTIALS]\n");
	$expected = 3;
	$data = $wotkit_client->getSensorData($existing_data_sensor_full[0], $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Query sensor data from PRIVATE sensor	
	echo nl2br("\n\n [QUERY data from PRIVATE sensor: '".$existing_data_sensor_full[2]."', with NO CREDENTIALS]\n");
	$data = $wotkit_client->getSensorData($existing_data_sensor_full[2], $public);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status, null);
	
#Query raw data
	echo nl2br("\n\n [QUERY all raw data, from newest to oldest, from sensor'".$existing_data_sensor_full[0]."', with NO CREDENTIALS] \n");
	$expected = 2;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor_full[0], NULL, NULL, NULL, NULL, NULL, $expected, "true", $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	

#Query formatted data
	echo nl2br("\n\n [QUERY formatted data in HTML table where value>30 from '".$existing_data_sensor_full[0]."', with NO CREDENTIALS] \n");
	$data = $wotkit_client->getFormattedSensorData( $existing_data_sensor_full[0], "select * where value>20", 1, "html", NULL, $public); 
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);//special case in function?		
	
#Query sensor fields from PUBLIC sensors
	echo nl2br("\n\n [QUERY fields from PUBLIC sensor: '".$existing_data_sensor_full[0]."', with NO CREDENTIALS]\n");
	$expected = 4;
	$data = $wotkit_client->getSensorFields ($existing_data_sensor_full[0], null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	
	
#Query sensor fields from PRIVATE sensors
	echo nl2br("\n\n [QUERY fields from PRIVATE sensor: '".$existing_data_sensor_full[2]."', with NO CREDENTIALS]\n");
	$data = $wotkit_client->getSensorFields ($existing_data_sensor_full[2], null, $public);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status, NULL);	

	
	
//RESULTS		
echo nl2br("\n\n [*****RESULTS******] \n");
if ( $failures === 0 )
	echo "ALL TESTS PASSED" ;	
else
	echo '<font color="red">TESTS FAILED =</font>'.$failures ;
	
	
	
//HELPER FUNCTIONS
	//Outputs results of test in readable fashion, checks HTTP code
	function displayOutput ($data, $test_status, $expected=NULL, $special_case=false){
		global $failures;
					
		//Print Data
		if ( $expected == NULL || $special_case )
		{
			echo'<pre>'.print_r($data, true).'</pre>';//For a more readable response
		}
		else{
			//SPECIAL CASE - assumed multiple results!!!
			$data_long=json_encode($data, true);
			echo $data_long; 
		}

		//Print HTTP Code Status
		echo nl2br("\n".$test_status."\n");
		if ( !stristr($test_status,'<b>PASS</b> ') )
			$failures ++;
	
		//Print Expected vs Found Queries Status
		if( $expected != NULL || $expected === 0)
		{
			$found = count($data);
	
			if ($expected == $found ){
				echo '<b>PASS</b>';
			}
			else {
				echo '<font color="red">FAIL</font>.' ;
				$failures ++;
			};
			echo nl2br(" Expected ".$expected." = Returned ".$found);
		}
	}
	
?>
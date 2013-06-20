<?php
/*
 * Run this script for General Key Based Testing
 * (assumes newly initilized database)
 *
 *TODO: add actuator, fix failed counting
 */

require_once('wotkit_client.php');
require_once('wotkit_clientConfig.php');

//SERVER & AUTHENTICATION
	$wotkit_client = new wotkit_client(BASE_URL, CLIENT_ID, CLIENT_SECRET);

//---------------------------------------------------------------------------------------//
/*Set Variables*/

$failures = 0;

//SENSOR NAMES 
	//-sensor ids or names can be used for updating sensor or sensor data
	$generic_sensor = "api-client-test-sensor"; //non-existent
	$additional_generic_sensor = "api-client-test-sensor-additional"; //non-existent
	$existing_data_sensor = 
		array("api-data-test-1","api-data-test-2","api-data-test-3"); //pre-existing 40-42
	//id=40 - has pre-supplied data
	//id=41 - is an 'actuator'
	//id=42 - has data added and then deleted
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
	$location_winnipeg =  array(50,-98,48,-96); //N,W,S,E
	$location_kilkenny =  array(53,-8,52,-7); //N,W,S,E

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
/*Begin Tests*/


//TESTING SENSORS
echo nl2br("[*****TESTING SENSORS******] \n");

#Create TWO sensors 
#Create multiple sensors: 'api-client-test-sensor' & 'api-client-test-sensor_additional' 
	echo nl2br("\n\n [CREATE multiple sensors:'".$generic_sensor."' & '".$additional_generic_sensor."'] \n");
	$expected = 2;
	$data = $wotkit_client->createMultipleSensor(array($new_sensor_input, $additional_sensor_input), true);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	//$message="Sensors Created";
	
#Create TWO EXISTING sensors 'api-client-test-sensor' & 'api-client-test-sensor_additional' 
#Create an existing sensor
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
	
//!!!!!!!CAN change name??	
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
//TESTING SENSOR FIELDS
echo nl2br("\n\n .....testing sensor fields...... \n");
#Query mulitple fields for 'api-client-test-sensor'
	echo nl2br("\n\n [QUERY multiple fields for '".$generic_sensor."']\n");
	$expected = 4;
	$data = $wotkit_client->getSensorFields ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);

#Add incomplete field to 'api-client-test-sensor'	
	echo nl2br("\n\n [ADD new field with INCOMPLETE field information to '".$generic_sensor."']\n");
	$data = $wotkit_client->updateSensorField ($generic_sensor, $invalid_field_required);
	$test_status = $wotkit_client->checkHTTPcode(400);
	displayOutput ($data, $test_status,NULL);	
	
#Update default field of 'api-client-test-sensor'	
	echo nl2br("\n\n [UPDATE protected subfield of a default field 'value' for '".$generic_sensor."']\n");
	$data = $wotkit_client->updateSensorField ($generic_sensor, $invalid_field_default);
	$test_status = $wotkit_client->checkHTTPcode(400);
	displayOutput ($data, $test_status,NULL);
	
#Add new field to 'api-client-test-sensor'	
	echo nl2br("\n\n [ADD new field '".$new_field[name]."' for '".$generic_sensor."']\n");
	$data = $wotkit_client->updateSensorField ($generic_sensor, $new_field);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	
	
#Query single "testfield" field for 'api-client-test-sensor'
	echo nl2br("\n\n [QUERY single field '".$new_field[name]."' for '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensorFields ($generic_sensor, $new_field[name]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
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
	
#Get data 	
	echo nl2br("\n\n [GET data for '".$generic_sensor."']\n");
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
	displayOutput ($data, $test_status,NULL);

#Query mulitple fields for 'api-client-test-sensor'
	echo nl2br("\n\n [QUERY multiple fields for '".$generic_sensor."']\n");
	$expected = 5;
	$data = $wotkit_client->getSensorFields ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	

#Query single "value" field for 'api-client-test-sensor'
	echo nl2br("\n\n [QUERY single field 'value' for '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensorFields ($generic_sensor, "value");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Query sensor data for 'api-client-test-sensor'
	echo nl2br("\n\n [QUERY sensor data for '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensorData ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);

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

echo nl2br("\n\n ........... \n");
//---------------------------------------//

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

#Query deleted 'api-client-test-sensor'
#Check for a SINGLE sensor that DOES NOT exist
	echo nl2br("\n\n [QUERY '".$generic_sensor."']\n");
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

#Delete non-existant sensor 'not-real-sensor'
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

	
//TESTING SENSOR SUBSCRIPTIONS
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

	
//TESTING SENSOR DATA
echo nl2br("\n\n [*****TESTING SENSOR DATA******] \n");

echo nl2br("\n\n [TESTING SENDING SENSOR DATA] \n");
#Create 'api-client-test-sensor'
#Create new sensor
	echo nl2br("\n\n [CREATE '".$generic_sensor."'] \n");
	$data = $wotkit_client->createSensor($new_sensor_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	//$message="Sensor Created";
	
#Add new field to 'api-client-test-sensor'	
	echo nl2br("\n\n [ADD numeric, nonrequired field '".$num_field[name]."' for '".$generic_sensor."']\n");
	$data = $wotkit_client->updateSensorField ($generic_sensor, $num_field);
	$data = $wotkit_client->updateSensorField ($generic_sensor, $num_field);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	

#Add new field to 'api-client-test-sensor'	
	echo nl2br("\n\n [ADD string, nonrequired field '".$string_field[name]."' for '".$generic_sensor."']\n");
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
	
	$expected = 2;
	$data = $wotkit_client->getSensorData ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);

#Post JSON invalid data
	echo nl2br("\n\n [Post JSON invalid data to '".$generic_sensor."']\n");
	$data = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array, true);
	$test_status = $wotkit_client->checkHTTPcode(400);
	displayOutput ($data, $test_status,NULL);
	
	$expected = 2;
	$data = $wotkit_client->getSensorData ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);


#Post Name/Value pair undeclared data
	$data_array = array( "value" => 5, "lat" => 6, "lng" => 7, 
						"message" => "test message with test field", 
						$num_field[name]=>9, $string_field[name]=>"hello",
						"madeupNUMfield"=>9, "madeupSTRINGfield"=>"hi name/value!");
	echo nl2br("\n\n [Post Name/Value pair undeclared data to '".$generic_sensor."']\n");
	$data = $wotkit_client->sendNonStandardSensorData($generic_sensor, $data_array);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
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

echo nl2br("\n\n [------------------------] \n");


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
	
	
#Update 2nd piece of data from 'api-data-test-3'
#Update 2nd piece of data from existing sensor
	echo nl2br("\n\n [UPDATE 2nd data piece from '".$existing_data_sensor[2]."'] \n");
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
	echo $last_key;
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
	echo ($time_stamp);
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
//TESTING SENSOR FIELDS
echo nl2br("\n\n .....testing sensor fields...... \n");
#Query mulitple fields for 'api-data-test-3'
	echo nl2br("\n\n [QUERY multiple fields for '".$existing_data_sensor[2]."']\n");
	$expected = 4;
	$data = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Add new field to 'api-data-test-3'	
	echo nl2br("\n\n [ADD new field '".$new_field[name]."' for '".$existing_data_sensor[2]."']\n");
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
	displayOutput ($data, $test_status,NULL);	
	
#Send data to 'testfield' field for 'api-data-test-3'
	echo nl2br("\n\n [Send data to all fields for '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->sendNonStandardSensorData($existing_data_sensor[2], $nonStandard_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Query single "testfield" field for 'api-data-test-3'
	echo nl2br("\n\n [QUERY single field '".$new_field[name]."' for '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->getSensorFields ($existing_data_sensor[2], $new_field[name]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
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

echo nl2br("\n\n ........... \n");	
//--------------------------------//


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


//TESTING RAW SENSOR DATA RETREIVAL
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
	echo nl2br("\n\n [Querying all raw data, oldest to newest, from ".$existing_data_sensor[0]."'] \n");
	$expected = 4;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], NULL, NULL, NULL, NULL, NULL, NULL, "false");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Querying raw data REVERSE = true
	echo nl2br("\n\n [Querying all raw data, newest to oldest, from ".$existing_data_sensor[0]."'] \n");
	$expected = 4;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], NULL, NULL, NULL, NULL, NULL, NULL, "true");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	


//TESTING FORMATTED SENSOR DATA RETREIVAL
echo nl2br("\n\n [*****TESTING FORMATTED SENSOR DATA RETREIVAL******] \n");
#Querying formatted data
	echo nl2br("\n\n [Querying formatted data in HTML table where value>30 from '".$existing_data_sensor[0]."'] \n");
	$data = $wotkit_client->getFormattedSensorData( $existing_data_sensor[0], "select * where value>20", 1, "html"); 
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);//special case in function?

	
//TESTING QUERYING SENSORS	
echo nl2br("\n\n [*****TESTING QUERYING SENSORS******] \n");
	
	#Querying ALL
	//should not include private sensor
	echo nl2br("\n\n [Query ALL] \n");
	$data = $wotkit_client->getSensors(null,"all") ;
	$total_sensors = count($data);
	$test_status = $wotkit_client->checkHTTPcode();
	echo nl2br("ASSUMES this returned correct number of sensors");
	displayOutput ($data, $test_status, $total_sensors);
	
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

	
	//#Querying ACTIVE
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
	echo nl2br("\n\n [Query TEXT api] \n");
	$expected = 3;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, "api") ;
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
	echo nl2br("\n\n [Query LOCATION = vancouver] \n");
	$expected = 23;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $location_vancouver) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
	#Querying LOCATION & ACTIVE
	echo nl2br("\n\n [Query LOCATION = vancouver & active] \n");
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
	

//TESTING AGGREGATE SENSOR DATA
echo nl2br("\n\n [*****TESTING QUERYING AGGREGATE SENSOR DATA******] \n");

	#Querying data from subscribed, active sensors from last hour
	echo nl2br("\n\n [Query aggregated sensor data: subscribed, active, last hour] \n");
	$expected = 2;
	$params = array("scope" => "subscribed", "active" => true, "before" => 3600000 );	
	$data = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
	#Querying data from subscribed, active sensors from last 10
	echo nl2br("\n\n [Query aggregated sensor data: subscribed, active, last 10] \n");
	$expected = 5;
	$params = array("scope" => "subscribed", "active" => true, "beforeE" => 10 );	
	$data = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
	#Querying data from subscribed, active sensors during hour after 7 January 2013 14:00
	echo nl2br("\n\n [Query aggregated sensor data: subscribed, active, during hour after 7 January 2013 14:00] \n");
	$expected = 1;
	$params = array("scope" => "subscribed", "active" => true, "start" => $start_time, "after"=>3600000 );	
	$data = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
	#Querying data from active sensors orderBy Time
	echo nl2br("\n\n [Query aggregated sensor data:  active ordered by time] \n");
	$expected = 5;
	$params = array("active" => true,"orderBy" => "time" );	
	$data = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
	#Querying data from active sensors orderBy Sensor
	echo nl2br("\n\n [Query aggregated sensor data:  active ordered by sensor] \n");
	$expected = 5;
	$params = array("active" => true, "orderBy" => "sensor" );	
	$data = $wotkit_client->getAggregatedData ($params);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	

	

//DELETING ADDED DATA 
echo nl2br("\n\n [*****DELETING ADDED DATA ******] \n");

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
	

//TESTING DATA FOR ACTUATORS	
echo nl2br("\n\n [*****TESTING DATA FOR ACTUATORS******] \n");

	#Subscribe to, send data to, and get data from actuator you DO own
	echo nl2br("\n\n [Subscribe to, send data to, and get data from OWNED actuator '".$actuator_name."'] \n");
	$expected = 1;
	echo nl2br("Sending messge ".$actuator_message_display."\n");
	echo nl2br("Response:\n");
	$data=$wotkit_client->testActuator($actuator_name, $actuator_message);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	

	/*
	#Send data to actuator you DO NOT own
	echo nl2br("\n\n [Without credentials -> Send data to PUBLIC actuator '".$actuator_name_full."'] \n");
	echo nl2br("Sending messge ".$actuator_message_display."\n");
	$public = true;
	$expected = 1;
	$data=$wotkit_client->subscribeActuator($actuator_name);
	$sub_id = $data["subscription"];	
	$data=$wotkit_client->sendActuator($actuator_name_full, $actuator_message, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	echo nl2br("\nResponse:\n");
	$data = $wotkit_client->getActuator($sub_id);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	*/
	
	#Send data to PRIVATE actuator you DO NOT own
	echo nl2br("\n\n [Send data to UNOWNED, PRIVATE actuator '".$private_unowned_sensor."'] \n");
	echo nl2br("Sending messge ".$actuator_message_display."\n");
	$data=$wotkit_client->sendActuator($private_unowned_sensor, $actuator_message);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,null);	
	
	
//TESTING USERS
echo nl2br("\n\n [*****TESTING USERS******] \n");	
	#Create invalid username
	echo nl2br("\n\n [Create user with invalid name '".$invalid_user_name."'] \n");
	$data = $wotkit_client->createUser($invalid_name_user_input);
	$test_status = $wotkit_client->checkHTTPcode(500);
	displayOutput ($data, $test_status, NULL);
	
	#Create invalid user with missing property
	echo nl2br("\n\n [Create user with missing properties] \n");
	$data = $wotkit_client->createUser($missing_property_user_input);
	$test_status = $wotkit_client->checkHTTPcode(500);
	displayOutput ($data, $test_status, NULL);
	
	#Create user 
	echo nl2br("\n\n [Create user '".$new_user_name."'] \n");
	$data = $wotkit_client->createUser($new_user_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
	#Create existing user 
	echo nl2br("\n\n [Create existing user '".$new_user_name."'] \n");
	$data = $wotkit_client->createUser($new_user_input);
	$test_status = $wotkit_client->checkHTTPcode(409);
	displayOutput ($data, $test_status, NULL);
	
	#Query existing user 
	echo nl2br("\n\n [Query existing user '".$new_user_name."'] \n");
	$data = $wotkit_client->getUsers($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
	#Update existing user 
	echo nl2br("\n\n [Update existing user '".$new_user_name."'] \n");
	$data = $wotkit_client->updateUser($new_user_name, $updated_user_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
	#Query existing user 
	echo nl2br("\n\n [Query existing user '".$new_user_name."'] \n");
	$data = $wotkit_client->getUsers($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
	#Update existing user with invalid data 
	echo nl2br("\n\n [Update existing user '".$new_user_name."' with new username'] \n");
	$data = $wotkit_client->updateUser($new_user_name, $invalid_updated_user_input);
	$test_status = $wotkit_client->checkHTTPcode(400);
	displayOutput ($data, $test_status, NULL);
	
	#Query existing user 
	echo nl2br("\n\n [Query existing user '".$new_user_name."'] \n");
	$data = $wotkit_client->getUsers($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	
	#Query all users with "api" in name
	echo nl2br("\n\n [Query existing user with text='api'] \n");
	$expected = 1;
	$data = $wotkit_client->getUsers(null, "api");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);		
	
	#Query all users REVERSE = true
	echo nl2br("\n\n [Query existing users from oldest to newest reverse=true LIMIT = 6'] \n");
	$expected = 6;
	$data = $wotkit_client->getUsers(null, null, true, null, 6);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
	#Query all users REVERSE = false
	echo nl2br("\n\n [Query existing users from oldest to newest reverse=false LIMIT = 6'] \n");
	$expected = 6;
	$data = $wotkit_client->getUsers(null, null, false, null, 6);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
	#Query all users LIMIT 2 
	echo nl2br("\n\n [Query all users LIMIT = 2'] \n");
	$expected = 2;
	$data = $wotkit_client->getUsers(null, null, null, null, 2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
	#Query all users OFFSET 5 LIMIT 2 
	echo nl2br("\n\n [Query all users OFFSET = 5 & LIMIT = 2'] \n");
	$expected = 2;
	$data = $wotkit_client->getUsers(null, null, null, 5, 2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	
	
	#Delete user 'new-user-api-testing'
	#Delete existing user 
	echo nl2br("\n\n [DELETE existing user '".$new_user_name."'] \n");
	$data = $wotkit_client->deleteUser($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, null);	
	
	#Delete non-existent user 'new-user-api-testing'
	echo nl2br("\n\n [DELETE non-existant user '".$new_user_name."'] \n");
	$data = $wotkit_client->deleteUser($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status, null);	
	
	#Query non-existant user 
	echo nl2br("\n\n [Query non-exisitant user '".$new_user_name."'] \n");
	$data = $wotkit_client->getUsers($new_user_name);
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status, NULL);

	
//TESTING NEWS
echo nl2br("\n\n [*****TESTING NEWS******] \n");	
	#Get news
	echo nl2br("\n\n [Get news with no credentials] \n");
	$data = $wotkit_client->getNews();
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);	
	
	
//TESTING STATS
echo nl2br("\n\n [*****TESTING STATS******] \n");	
	#Get news
	echo nl2br("\n\n [Get stats with no credentials] \n");
	$data = $wotkit_client->getNews();
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);	

	
//PUBLIC FUNCTIONS
echo nl2br("\n\n [*****TESTING PUBLIC FUNCTIONS******] \n");	
$public = true;
#Query for multiple sensors
	echo nl2br("\n\n [QUERY PUBLIC, existing sensors]\n");
	$expected = 5;
	$data = $wotkit_client->getSensors(null, null, null, null, null, null, null, 5, null, $public );
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);

#Query  'api-client-test-sensor'
#Query a SINGLE sensor that DOES exist
	echo nl2br("\n\n [QUERY PUBLIC, existing sensor: '".$existing_data_sensor_full[0]."']\n");
	$data = $wotkit_client->getSensors($existing_data_sensor_full[0], null, null, null, null, null, null, null, null, $public );
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Query a single, PRIVATE sensor that does exist
	echo nl2br("\n\n [QUERY PRIVATE, existing sensor: '".$private_unowned_sensor."']\n");
	$data = $wotkit_client->getSensors($private_unowned_sensor, null, null, null, null, null, null, null, null, $public );
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,NULL);		

#Query a single, public sensor that DOES NOT exist
	echo nl2br("\n\n [QUERY non-existant sensor: '".$invalid_sensor_input["name"]."']\n");
	$data = $wotkit_client->getSensors($invalid_sensor_input["name"], null, null, null, null, null, null, null, null, $public );
	$test_status = $wotkit_client->checkHTTPcode(404);
	displayOutput ($data, $test_status,NULL);	
	
#Get sensor data from PUBLIC sensor	
	echo nl2br("\n\n [GET data from PUBLIC sensor: '".$existing_data_sensor_full[0]."']\n");
	$expected = 3;
	$data = $wotkit_client->getSensorData($existing_data_sensor_full[0], $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);
	
#Get sensor data from PRIVATE sensor	
	echo nl2br("\n\n [GET data from PRIVATE sensor: '".$existing_data_sensor_full[2]."']\n");
	$data = $wotkit_client->getSensorData($existing_data_sensor_full[2], $public);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status, null);
	
#Querying raw data
	echo nl2br("\n\n [Querying all raw data, from newest to oldest, from sensor'".$existing_data_sensor_full[0]."'] \n");
	$expected = 2;
	$data = $wotkit_client->getRawSensorData($existing_data_sensor_full[0], NULL, NULL, NULL, NULL, NULL, $expected, "true", $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	

#Querying formatted data
	echo nl2br("\n\n [Querying formatted data in HTML table where value>30 from '".$existing_data_sensor_full[0]."'] \n");
	$data = $wotkit_client->getFormattedSensorData( $existing_data_sensor_full[0], "select * where value>20", 1, "html", NULL, $public); 
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);//special case in function?		
	
#Query sensor fields from PUBLIC sensors
	echo nl2br("\n\n [QUERY  field from PUBLIC sensor: '".$existing_data_sensor_full[0]."']\n");
	$expected = 4;
	$data = $wotkit_client->getSensorFields ($existing_data_sensor_full[0], null, $public);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected, true);	
	
#Query sensor fields from PRIVATE sensors
	echo nl2br("\n\n [QUERY  field from PRIVATE sensor: '".$existing_data_sensor_full[2]."']\n");
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
	function displayOutput ($data, $test_status, $expected=NULL, $special_case=false)
	{
		global $failures;
					
		//Print Data
		if ( $expected == NULL || $special_case )
		{
			echo'<pre>'.print_r($data, true).'</pre>';//For a more readable response
		}
		else{
			//SPECIAL CASE - assumed multiple queries!!!!!
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
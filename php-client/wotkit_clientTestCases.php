<?php
//TODO
//test data aggregation
//test oauth2

require_once('wotkit_client.php');

//"http://pincushion.magic.ubc.ca:8080/api/";
$wotkit_client = new wotkit_client("http://localhost:8080/api/");

//LIST OF SENSOR NAMES 
//sensor ids or names can be used for updating sensor or sensor data
$generic_sensor = "api-client-test-sensor"; //non-existent
$existing_data_sensor = 
array("api-data-test-1","api-data-test-2","api-data-test-3"); //pre-existing 40-42
//id=40 - has pre-supplied data
//id=41 - is an 'actuator'
//id=42 - has data added and then deleted
$unowned_sensor = "sensetecnic.mule1"; //id=1
$private_unowned_sensor = "sensetecnic.api-test-private"; //id=43
	
//SENSOR INPUTS	
//currently able to update sensor name
//no error when trying to update owner	
$new_sensor_input = array(
	"private"=>"false", 
	"name"=>$generic_sensor, 
	"description"=>"api client test sensor desc", 
	"longName"=>"api client test sensor long", 
	"latitude"=>4, 
	"longitude"=>6,
	"tags"=>["testing the tags","t e s t i n g ","tags"]);

$updated_sensor_input_1 = array(
	 "name"=>$generic_sensor, 
	 "longName"=>"api client test sensor long updated", 
	 "description"=>"api client test sensor desc updated",
	 "latitude"=>55,
	 "longitude"=>-125,
	 "private"=>"true",
	 "tags"=>["updating the tags","tags"]);
	 //"fields"=>[{"name":"value","longName":"Data","type":"NUMBER","units":"cm"}]);
	 //"owner":"anything will be accepted"
	 
$updated_sensor_input_2 = array(
	 "name"=>$generic_sensor, 
	 "longName"=>"api client test sensor long", 
	 "description"=>"api client test sensor desc",
	 "latitude"=>55,
	 "longitude"=>-125,
	 "private"=>"false");
	 
$updated_sensor_input_3 = array(
	 "name"=> $unowned_sensor, 
	 "longName"=>$unowned_sensor, 
	 "description"=>$unowned_sensor);

$invalid_field_required = array ("name"=>"testfield", "longName"=>"Test Field","units"=>"mm");	
$invalid_field_default = array("name"=>"value", "type"=>"STRING");
$new_field = array ("required"=>true,"name"=>"testfield", "longName"=>"Test Field", "type"=>"NUMBER",  "units"=>"mm");	
$updated_field = array ("required"=>false, "name"=>"testfield","longName"=>"Updated Test Field", "type"=>"STRING","units"=>"cm");	

//SENSOR DATA
$nonStandard_sensor_data = array( "value" => 5, "lat" => 6, "lng" => 7, 
								  "message" => "test message with test field", "testfield"=>9);	 
//ACTUATOR NAME AND INPUTS
$actuator_name = $existing_data_sensor[1];
$actuator_message = "button=on&value=5";	 

//TESTING SENSORS
echo nl2br("[*****TESTING SENSORS******] \n");
#Create 'api-client-test-sensor'
#Create new sensor
	echo nl2br("\n\n [CREATE '".$generic_sensor."'] \n");
	$data = $wotkit_client->createSensor($new_sensor_input);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	//$message="Sensor Created";

//BUG - should give an error message. Instead no change produced. 	
#Create the already existing 'api-client-test-sensor'
#Create an existing sensor
	echo nl2br("\n\n [CREATE exisiting '".$generic_sensor."'] \n");
	$data = $wotkit_client->createSensor($new_sensor_input);	
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);
	echo nl2br("\n This is a bug, should have given an error not 204.\n");
	
#Query created 'api-client-test-sensor'
#Check for a SINGLE sensor that DOES exist
	echo nl2br("\n\n [QUERY created '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensors($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
//!!!!!!!CAN change name??	
#Update 'api-client-test-sensor'
#Update longname, description, private (required)   
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

//----------------Sensor Fields------------//
//TESTING SENSOR FIELDS
echo nl2br("\n\n .....testing sensor fields...... \n");
#Query mulitple fields for 'api-client-test-sensor'
	echo nl2br("\n\n [QUERY multiple fields for '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensorFields ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);

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
	displayOutput ($data, $test_status,NULL);
	
#Query mulitple fields for 'api-client-test-sensor'
	echo nl2br("\n\n [QUERY multiple fields for '".$generic_sensor."']\n");
	$data = $wotkit_client->getSensorFields ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	
	
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
	$data = $wotkit_client->getSensorFields ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	

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
	$data = $wotkit_client->getSensorFields ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	
		
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
	$data = $wotkit_client->getSensorFields ($generic_sensor);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	

echo nl2br("\n\n ........... \n");
//--------------------------

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
	
#Query private sensor 'sensetecnic.api-test-private'
#Check for a SINGLE PRIVATE sensor 
	echo nl2br("\n\n [QUERY private '".$private_unowned_sensor."']\n");
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
	echo nl2br("\n\n [UPDATE another user's sensor '".$unowned_sensor."']\n");
	$data = $wotkit_client->updateSensor($unowned_sensor, $updated_sensor_input_3);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,NULL);
	//$message="Not Authorized";
	//would be 404 error if you didn't specify owner's name

//TESTING SENSOR SUBSCRIPTIONS
echo nl2br("\n\n [*****TESTING SENSOR SUBSCRIPTIONS******] \n");	
#Get subscribed sensors
	echo nl2br("\n\n [QUERY subscribed sensors]\n");
	$data = $wotkit_client->getSubscribedSensors();
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);

#Subscribe to a non-private sensor
	echo nl2br("\n\n [SUBSCRIBE to '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->subscribeSensor($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Subscribe to an already subscribed sensor
	echo nl2br("\n\n [SUBSCRIBE to already subscribed '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->subscribeSensor($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,NULL);

#Subscribed to a private, non-owned sensor 
	echo nl2br("\n\n [SUBSCRIBE to another user's private sensor '".$private_unowned_sensor."']\n");
	$data = $wotkit_client->subscribeSensor($private_unowned_sensor);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,NULL);

#Get subscribed sensors
	echo nl2br("\n\n [QUERY subscribed sensors]\n");
	$data = $wotkit_client->getSubscribedSensors();
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);

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
	$data = $wotkit_client->getSubscribedSensors();
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	

	
//TESTING SENSOR DATA
echo nl2br("\n\n [*****TESTING SENSOR DATA******] \n");

#Send data to another user's sensor 'sensetecnic.mule1'
#Send data to a sensor you don't own
	echo nl2br("\n\n [UPDATE another user's sensor data '".$unowned_sensor."']\n");
	$value = rand(1,100);
	$lat = rand(1,100);
	$lng = rand(1,100);
	$message = "test message #"; 
	$data = $wotkit_client->sendSensorData( $unowned_sensor,$value, $lat, $lng, $message);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status,NULL);
	//$message="Not Authorized";
	//would be 404 error if you didn't specify owner's name

#Sending 3 pieces of data to 'api-data-test-3'
#Sending 3 pieces data to existing sensor
//bad data can still go through
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
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Update 2nd piece of data from 'api-data-test-3'
#Update 2nd piece of data from existing sensor
//bad data can still go through; success should be 204 but is 201?
	echo nl2br("\n\n [UPDATE 2nd data piece from '".$existing_data_sensor[2]."'] \n");
	$saved_response = json_decode($wotkit_client->response, true);
	$updated_sensor_data = array(array("timestamp" =>$saved_response[1][timestamp],
	 "value"=>100,
	 "lat"=>100,
	 "lng"=>100,
	 "message"=>"updated timestamp")
	 );
	$data = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message="Sensor updated";
	
	
#Query data from 'api-data-test-3'
#Query data from existing sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
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
	 "message"=>"end timestamps")
	 );
	$data = $wotkit_client->updateSensorData($existing_data_sensor[2], $updated_sensor_data);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	//$message="Sensor updated";
	
#Query data from 'api-data-test-3'
#Query data from existing sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Delete latest data from 'api-data-test-3'
#Delete latest data from existing sensor
	//successful even if nothing is deleted
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
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);


	//-------------------------------
//TESTING SENSOR FIELDS
echo nl2br("\n\n .....testing sensor fields...... \n");
#Query mulitple fields for 'api-data-test-3'
	echo nl2br("\n\n [QUERY multiple fields for '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Add new field to 'api-data-test-3'	
	echo nl2br("\n\n [ADD new field '".$new_field[name]."' for '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->updateSensorField ($existing_data_sensor[2], $new_field);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	
	
#Query mulitple fields for 'api-data-test-3'
	echo nl2br("\n\n [QUERY multiple fields for '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	

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
	$data = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	

#Querying data from 'api-data-test-3'
#Querying data from existing  sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	
	
#Delete "testfield" field for 'api-data-test-3'
	echo nl2br("\n\n [DELETE single field '".$new_field[name]."' for '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->deleteSensorField ($existing_data_sensor[2], $new_field[name]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Query mulitple fields for 'api-data-test-3'
	echo nl2br("\n\n [QUERY multiple fields for '".$existing_data_sensor[2]."']\n");
	$data = $wotkit_client->getSensorFields ($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);	

echo nl2br("\n\n ........... \n");	
//-------------------------------

#Querying data from 'api-data-test-3'
#Querying data from existing  sensor
	echo nl2br("\n\n [QUERY data from '".$existing_data_sensor[2]."'] \n");
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);

#Deleting latest data from 'api-data-test-3'
#Deleting latest data from existing sensor
	//successful even if nothing is deleted
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
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);
	
#Deleting latest data from 'api-data-test-3'
#Deleting latest data from existing sensor
	//successful even if nothing is deleted
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
	$data = $wotkit_client->getSensorData($existing_data_sensor[2]);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);

//TESTING RAW SENSOR DATA RETREIVAL
echo nl2br("\n\n [*****TESTING RAW SENSOR DATA RETREIVAL******] \n");

#Sending data to make sensors active 
//bad data can still go through
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

#Querying raw data
	$start_time = strtotime("7 January 2013 14:00")*1000;
	$end_time = strtotime("8 January 2013 13:00")*1000;
	
#Querying raw data START END
	echo nl2br("\n\n [Querying raw data from 2pm January 7th to 1pm January 8th from '".$existing_data_sensor[0]."'] \n");
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, $end_time);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status);
	
#Querying raw data BEFORE
	echo nl2br("\n\n [Querying elements of raw data 1hr BEFORE 2pm January 7th from '".$existing_data_sensor[0]."'] \n");
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, NULL, NULL, NULL, 3600000);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status);
	
#Querying raw data AFTER
	echo nl2br("\n\n [Querying elements of raw data 1 hr AFTER 2pm January 7th from '".$existing_data_sensor[0]."'] \n");
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, NULL, 3600000);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status);
	
#Querying raw data BEFOREE
	echo nl2br("\n\n [Querying last 3 elements of raw data BEFORE now from '".$existing_data_sensor[0]."'] \n");
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], NULL, NULL, NULL, NULL, NULL, 3);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL); 

#Querying raw data AFTERE
	echo nl2br("\n\n [Querying first 2 elements of raw data AFTER 2pm January 7th from '".$existing_data_sensor[0]."'] \n");
	$data = $wotkit_client->getRawSensorData($existing_data_sensor[0], $start_time, NULL, NULL,2);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status,NULL);

	
//TESTING FORMATTED SENSOR DATA RETREIVAL
echo nl2br("\n\n [*****TESTING FORMATTED SENSOR DATA RETREIVAL******] \n");
#Querying formatted data
	echo nl2br("\n\n [Querying formatted data in HTML table where value>30 from '".$existing_data_sensor[0]."'] \n");
	$data = $wotkit_client->getFormattedSensorData( $existing_data_sensor[0], "select * where value>20", 1, "html"); 
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);//special case?
	
	
//TESTING QUERYING SENSORS	
echo nl2br("\n\n [*****TESTING QUERYING SENSORS******] \n");
	
	#Querying ALL
	echo nl2br("\n\n [Query ALL] \n");
	$expected = 40;
	$data = $wotkit_client->getSensors(null,"all") ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
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
	echo nl2br("\n\n [Query ACTIVE] \n");
	$expected = 3;
	$data = $wotkit_client->getSensors (null, NULL,"true");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
	#Querying PRIVATE
	echo nl2br("\n\n [Query PRIVATE] \n");
	$expected = 1;
	$data = $wotkit_client->getSensors (null, NULL, NULL,"true");
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
	#Querying NOT PRIVATE
	echo nl2br("\n\n [Query NOT PRIVATE] \n");
	$expected = 39;
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
	
	//BUG - Outputs sensors with both tags twice.
	#Querying TAGGED Cross Tags
	echo nl2br("\n\n [Query TAGGED data, Canada] \n");
	$expected = 3;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, "data,Canada") ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	echo nl2br("\n This is a BUG.\n"); 
	
	#Querying TEXT
	echo nl2br("\n\n [Query TEXT api] \n");
	$expected = 3;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, "api") ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);

	#Querying OFFSET
	echo nl2br("\n\n [Query OFFSET=35] \n");
	$expected = 5;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, 35) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);
	
	#Querying OFFSET & LIMIT
	echo nl2br("\n\n [Query OFFSET=15 & LIMIT=5] \n");
	$expected = 5;
	$data = $wotkit_client->getSensors(null, NULL, NULL, NULL, NULL, NULL, 15, 5) ;
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, $expected);	

//TESTING DATA FOR ACTUATORS	
echo nl2br("\n\n [*****TESTING DATA FOR ACTUATORS******] \n");

	#Subscribe to, send data to, and get data from actuator you DO own
	echo nl2br("\n\n [Subscribe, send data to, and get data from actuator '".$actuator_name."'] \n");
	$data=$wotkit_client->testActuator($actuator_name, $actuator_message);
	$test_status = $wotkit_client->checkHTTPcode();
	displayOutput ($data, $test_status, NULL);	
	
	
	//BUG - should not allow you to do this.	
	//MAKES this sensor active!
	#Subscribe to, send data to, get data from actuator you DO NOT own
	echo nl2br("\n\n [Subscribe to , send data to, and get data from actuator '".$unowned_sensor."'] \n");
	/*
	$data=$wotkit_client->testActuator($unowned_sensor, $actuator_message);
	$test_status = $wotkit_client->checkHTTPcode(401);
	displayOutput ($data, $test_status, NULL);	
	*/
	echo nl2br("This is a BUG.\n"); 

//HELPER FUNCTIONS
	//Outputs results of test in readable fashion, checks HTTP code
	function displayOutput ($data, $test_status, $expected=NULL)
	{
		//Print Data
		if ( $expected == NULL )
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
		
		//Print Expected vs Found Queries Status
		if( $expected != NULL )
		{
			$found = count($data);
			if ($expected == $found ){
				echo '<b>PASS</b>';
			}
			else {
				echo '<font color="red">FAIL</font>.' ;
			};
			echo nl2br(" Expected ".$expected." = Returned ".$found);
		}
	}
	
?>
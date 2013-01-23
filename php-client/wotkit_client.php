<?php 
//todo: 

//2. fix login with key
//3. HELPER FUNCTION IN FIXING - give back decoded data!

class wotkit_client {

	//Uses a set key with "mike" as owner
	private $key_id = "3bfeb222062cb0a6";
	private $key_password = "894a1b762806471f";
	private $secret;
	private $base_url = "http://wotkit.sensetecnic.com/api"; 
	public $response;
	public $response_info;
	public $expected_http_code;

	
	function __construct( $base_url=NULL ){
		//$id, $password, $secret, 
		
		if ($base_url != null)
			$this->setBaseURL ($base_url);
		if ($id != null and $password != null)
			$this->setKey($id, $password);
		if ($secret != null)
			$this->setSecret($secret);
	}
	
	private function setKey($id, $password){
		$this->key_id = $id;
		$this->key_password = $password;
	}
	
	private function setBaseURL ($base_url){
		$this->base_url = $base_url;
	}
	
	private function setSecret ($secret){
	}
	
	
//Public Helper Function
	public function checkHTTPcode( $expected_http_code=NULL, $message=NULL )
	{
		if( $expected_http_code == NULL )
		{	
			$expected_http_code = $this->expected_http_code;
		}
		if ( $this->response_info[http_code] == $expected_http_code )
		{
			$response ='<b>PASS</b> ';
		}
		else {
			$response = '<font color="red">FAIL</font> ';
		};
		
		$response .=" - HTTP ".$this->response_info[http_code];
		
		if ( $message != NULL ){
			$response.=" ".$message;
		}
		
		return $response;
	}
	
//Actuators
	public function testActuator ($sensor, $message){
		$data = $this->subscribeActuator($sensor);
		$response = json_decode($data, true);
		$sub_id = $response[subscription];
		$data = $this->sendActuator($sensor, $message);
		$data = $this->getActuator($sub_id);
		$data = json_decode($data, true);
		return $data;		
	}
	
	private function subscribeActuator($sensor){
		$message=1;
		$data = $this->processRequest("control/sub/".$sensor, "POST", $message, true);
		return $data;
	}
	
	private function sendActuator($sensor, $message){
		$data = $this->processRequest("sensors/".$sensor."/message", "POST", $message, false);
		return $data;
	}
	
	private function getActuator($sub_id){
		$data = $this->processRequest("control/sub/".$sub_id."?wait=10", "GET");
		return $data;
	}
	
//Sensors
	public function createSensor($data_array){
		//already exisits error message if missing required field
		//name,longname,desc
		$this->expected_http_code = 201;
		
		$sensor_input = $this->ArraytoJSON($data_array);
		$data = $this->processRequest( "sensors", "POST", $sensor_input);	
		
		$data = json_decode($data, true);
		return $data;
		
	}
	
	public function getSensors($sensor=NULL, $scope=NULL, $active=NULL, $private=NULL, $tags=NULL, $text=NULL, $offset=NULL, $limit=NULL) 
	{	$this->expected_http_code = 200;
		
		if ($sensor == NULL){
			$params = array("scope" => $scope, "active" => $active, "private" => $private, 
							"tags" => $tags, "text" => $text, "offset" => $offset, "limit" => $limit);
			  
			$url_string = "sensors?".$this->ArraytoNameValuePairs($params);
		}
		else{
			$url_string = "sensors/".$sensor;
		}
		
		$data = $this->processRequest ( $url_string, "GET");
		
		$data = json_decode($data, true);
		return $data;
	}
	
	/*
	public function getSingleSensor($sensor){
		$this->expected_http_code = 200;
		
		$data = $this->processRequest ( "sensors/".$sensor, "GET");
		$data = json_decode($data, true);
		return $data;
	}
	
	public function getMultipleSensors($scope=NULL, $active=NULL, $private=NULL, $tags=NULL, $text=NULL, $offset=NULL, $limit=NULL) 
	{	$this->expected_http_code = 200;
	
		$params = array("scope" => $scope, "active" => $active, "private" => $private, 
								 "tags" => $tags, "text" => $text, "offset" => $offset, "limit" => $limit);
			  
		$url_string = "sensors?".$this->ArraytoNameValuePairs($params);
			
		$data = $this->processRequest ( $url_string, "GET");
		
		$data = json_decode($data, true);
		return $data;
	}
	*/
	
	public function updateSensor($sensor, $data_array){
		$this->expected_http_code = 204;
		
		$updated_sensor_input = $this->ArraytoJSON($data_array);
		$data = $this->processRequest( "sensors/".$sensor, "PUT", $updated_sensor_input);
		$data = json_decode($data, true);
		return $data;
	}
	
	public function deleteSensor($sensor){
		$this->expected_http_code = 204;
		
		$data = $this->processRequest( "sensors/".$sensor, "DELETE");
		$data = json_decode($data, true);
		return $data;
	}
//Sensor Subscriptions
	public function getSubscribedSensors (){
		$this->expected_http_code = 200;
		
		$data = $this->processRequest ("subscribe", "GET");
		$data = json_decode($data, true);
		return $data;
	}
	
	public function subscribeSensor ($sensor){
		$this->expected_http_code = 204;
		
		$data = $this->processRequest ("subscribe/".$sensor, "PUT");
		$data = json_decode($data, true);
		return $data;
	}
	
	public function unsubscribeSensor ($sensor){
		$this->expected_http_code = 204;
		
		$data = $this->processRequest ("subscribe/".$sensor, "DELETE");
		$data = json_decode($data, true);
		return $data;
	}

//Sensor Fields
	public function getSensorFields ($sensor=null, $field=null){
		$this->expected_http_code = 200;
		
		$data = $this->processRequest ("sensors/".$sensor."/fields/".$field, "GET");
		$data = json_decode($data, true);
		return $data;
	}
	
	/*
	public function getSingleSensorField ($sensor, $field){
		$this->expected_http_code = 200;
		
		$data = $this->processRequest ("sensors/".$sensor."/fields/".$field, "GET");
		$data = json_decode($data, true);
		return $data;
	}
	
	public	function getMultipleSensorFields($sensor){
		$this->expected_http_code = 200;
		
		$data = $this->processRequest ("sensors/".$sensor."/fields", "GET");
		$data = json_decode($data, true);
		return $data;
	}
	*/
	
	public function updateSensorField($sensor, $data_array){
	//public function updateSensorField($sensor, $name, $type, $longName=null, $required=null, $units=null){
	//	$data_array = array{"name"=>$name, "type"=>type, "longName"=>longName, 
	//						"required"=>$required, "units=>$units);	
		$this->expected_http_code = 204;
		
		$field = $data_array[name];
		$updated_sensor_field = $this->ArraytoJSON($data_array);
		$data = $this->processRequest( "sensors/".$sensor."/fields/".$field, "PUT", $updated_sensor_field);
		$data = json_decode($data, true);
		return $data;
	}
	
	public function deleteSensorField($sensor, $field){
		$this->expected_http_code = 204;
		
		$data = $this->processRequest( "sensors/".$sensor."/fields/".$field, "DELETE");
		$data = json_decode($data, true);
		return $data;
	}
	
//Sensor Data
	public function getSensorData ($sensor){
		$this->expected_http_code = 200;
		
		$data = $this->processRequest( "sensors/".$sensor."/data", "GET");
		$data = json_decode($data, true);
		return $data;
	}
	
	public function sendSensorData ($sensor, $value ,$lat=NULL, $lng=NULL, $message=NULL, $timestamp=NULL){
		$this->expected_http_code = 201;
		
		$params = array( "timestamp" => $timestamp, "value" => $value, 
						 "lat" => $lat, "lng" => $lng, "message" => $message );
		$sensor_data = $this->ArraytoNameValuePairs($params);
		$data = $this->processRequest( "sensors/".$sensor."/data", "POST", $sensor_data, false);
	
		$data = json_decode($data, true);
		//Allows time for the new Sensor Data to be processed before moving on. 
		//Without sleep, you may try to query data before it has been processed. 
		sleep(1); 
		
		return $data;
	}
	
	public function sendNonStandardSensorData($sensor, $data_array){
		$this->expected_http_code = 201;
		$sensor_data = $this->ArraytoNameValuePairs($data_array);
		$data = $this->processRequest( "sensors/".$sensor."/data", "POST", $sensor_data, false);
	
		$data = json_decode($data, true);
		//Allows time for the new Sensor Data to be processed before moving on. 
		//Without sleep, you may try to query data before it has been processed. 
		sleep(1); 

		return $data;
	}
	
	public function updateSensorData ($sensor, $multi_dim_array){
		$this->expected_http_code = 204;
		
		$sensor_data = '[';
		
		$started = false;
		foreach ( $multi_dim_array as $array ){
			if ( $started == false ){
				$started = true;
			}
			else{
				$sensor_data .= ",";
			};
			$sensor_data .=  $this->ArraytoJSON($array);	
		};
		$sensor_data .= ']';

		$data = $this->processRequest( "sensors/".$sensor."/data", "PUT", $sensor_data);
		
		$data = json_decode($data, true);
		return $data;
	}
	
	public function deleteSensorData ($sensor, $timestamp){
		$this->expected_http_code = 204;
		
		$data = $this->processRequest( "sensors/".$sensor."/data/".$timestamp, "DELETE");
		$data = json_decode($data, true);
		return $data;
	}

	public function getRawSensorData($sensor, $start=NULL, $end=NULL, $after=NULL, $afterE=NULL, $before=NULL, $beforeE=NULL){
		$this->expected_http_code = 200;
		
		$params = array("start" => $start, "end" => $end, "after" => $after, 
						"afterE" => $afterE, "before" => $before, "beforeE" => $beforeE);
			  
		$url_string = "sensors/".$sensor."/data?".$this->ArraytoNameValuePairs($params);
		
		$data = $this->processRequest ( $url_string, "GET");	
		
		$data = json_decode($data, true);
		return $data;
	}
	
	public function getFormattedSensorData($sensor, $tq=NULL, $reqID=NULL, $out=NULL, $outfile=NULL){
		$this->expected_http_code = 200;
		
		$url_string = "sensors/".$sensor."/dataTable?";
		
		if ( $reqID != NULL ){
			$url_string .= "tqx=reqId:".$reqID;
			if( $out != NULL ){
				$url_string .= ";out:".$out;
				if( $outfile != NULL ){
					$url_string .= ";outfile:".$outfile;
				}
			}
			
			if( $tq != NULL ){
				$url_string .= "&";
			}
		}
		
		if( $tq != NULL ){
			$url_string .= "tq=".rawurlencode ($tq);
		}
				
		$data = $this->processRequest ( $url_string, "GET");
		
		return $data;
	}

//Basic Request
	//@param str  $url
	//				task specific url ending
	//@param str  $request
	//				one of: GET, POST, PUT, DELETE
	//@param str  $data
	//@param bool $isJson 
	//				whether input $data is JSON string 
	private function processRequest ($url, $request, $data=[], $isJSON=1){
	
		//Clearing old data
		$this->response =array();
		$this->response_info =array();
		
		//Updating to full URL
		$url = $this->base_url . $url ;
		
		#Initializing a cURL session
		$ch = curl_init();

		#Setting cURL options
		//Entering URL
		curl_setopt($ch, CURLOPT_URL, $url);
		//Logging In
		curl_setopt($ch, CURLOPT_USERPWD,"{$this->key_id}:{$this->key_password}");
		//Allows results to be saved in variable and not printed out
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		if ( $isJSON )
		{
			//Necessary for these actions: Create New Sensor, Update Sensor
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); 
		}

		if ($request == "PUT")
		{
			//Update Sensor or Sensor Data
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		}
		
		if ($request == "DELETE")
		{
			//Delete Sensor
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		}

		if ($request != "GET" and $data!=NULL)
		{
			//'Postfields' for POST or PUT requests
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		//Save response and information
		$this->response = curl_exec($ch);
		$this->response_info = curl_getinfo($ch);
		
		#Closing cURL
		curl_close($ch);
		
		return $this->response;
	}
	
//Helper Functions
	private function ArraytoNameValuePairs($params){
		
		$started = false;
		$url_string="";
		
		foreach( array_keys($params) as $key){
			
			if ( $params[$key] != NULL){
				if ( $started == false){
					$started= true;
				}	
				else{
					$url_string .= "&";
				}
				$url_string.= $key."=".$params[$key];
			}
		}
		
		echo $url_string;
		return $url_string;
	}

	private function ArraytoJSON($data_array){
		
		$started = false; 
		$contains_tags = false;
		$sensor_input = "{";
		
		foreach( array_keys($data_array) as $key){
			if ( $started == false){
				$started = true;
			}	
			else{
				$sensor_input.= ',';
			};
			
			if ($key == "tags"){
				$contains_tags = true;
			}
			else{
				if(is_numeric($data_array[$key])==true){
					$sensor_input .='"'.$key.='":'.$data_array[$key];
				}
				else{
					$sensor_input .='"'.$key.='":"'.$data_array[$key].'"';
				};
			};			
				
		}
		
		if ($contains_tags == true){
			$started = false;
			$sensor_input.= '"tags":[';
			foreach( $data_array[tags] as $tag ){
					
					if ( $started == false){
						$started = true;
					}	
					else{
						$sensor_input.= ',';
					};
						$sensor_input.= '"'.$tag.'"';
			}
			$sensor_input.= ']';								
		}
		$sensor_input.= "}";	
		
		return $sensor_input;
	}
}
?>
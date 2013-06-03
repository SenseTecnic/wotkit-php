<?php 

class wotkit_client {

	//Uses a set key in database with "tester" as owner
	private $key_id = "3bfeb222062cb0a6";
	private $key_password = "894a1b762806471f";
	
	//Uses Oauth2
	private $client_id;
	private $client_secret;
	private $accessToken = 'access_token=none';
	private $oauthTokenURL = "oauth/token";
	private $redirectURL = "http://localhost/wotkit_clientTestCases.php";
	private $hasParameters;
	
	private $base_url = "http://wotkit.sensetecnic.com/api/"; 
	
	public $response;
	public $response_info;
	public $expected_http_code;
	

	function __construct( $base_url=NULL, $client_id=NULL, $client_secret=NULL ){
		
		if ($base_url != null)
			$this->setBaseURL ($base_url);
		
		if ($client_id != null and $client_secret != null){
			$this->oauthTokenURL = $this->base_url.$this->oauthTokenURL;
			$this->setClient($client_id, $client_secret);
			$this->obtainAccessToken();
		}
	}
	
	private function setBaseURL ($base_url){
		$this->base_url = $base_url;
	}
	
	private function setClient($client_id, $client_secret){
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
	}

	private function obtainAccessToken (){
		$code = $_GET['code'];
		$accessToken = "access_token=none";
		$ch = curl_init();
		if(isset($code)) {
			// try to get an access token
			$url = $this->oauthTokenURL;
			$params = array( 
					 "code" => $code,
					 "client_id" => $this->client_id ,
					 "client_secret" => $this->client_secret,
					 "redirect_uri" => $this->redirectURL,
					 "grant_type" => "authorization_code"
					);
			
			$data = $this->ArraytoNameValuePairs ($params);
	
			$this->accessToken = "setting";
			$response = $this->processRequest($url, "POST", $data, false);
			$json = json_decode($response);
			$accessToken = $json->access_token;	
			
			if ($accessToken == null)
				$accessToken = "access_token=none";
		}	
		
		$this->accessToken = "access_token=".$accessToken;

		echo nl2br("Using ".$this->accessToken."\n\n");
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
	
//Actuators Functions
	public function testActuator ($sensor, $message){
		$this->expected_http_code = 200;

		$data = $this->subscribeActuator($sensor);
		$response = json_decode($data, true);
		$sub_id = $response[subscription];
		$data = $this->sendActuator($sensor, $message);
		$data = $this->getActuator($sub_id);
		$data = json_decode($data, true);
		return $data;		
	}
	
	private function subscribeActuator($sensor){
		$this->hasParameters = false;
		$dummy_data=1; //dummy data becuase processRequest() wants all POSTS/PUTS to have data 
		$data = $this->processRequest("control/sub/".$sensor, "POST", $dummy_data, true);
		return $data;
	}
	
	private function sendActuator($sensor, $message){
		$this->hasParameters = false;
		$data = $this->processRequest("sensors/".$sensor."/message", "POST", $message, false);
		return $data;
	}
	
	private function getActuator($sub_id){
		$this->hasParameters = true;
		$data = $this->processRequest("control/sub/".$sub_id."?wait=10", "GET");
		return $data;
	}
	
//Sensor Functions
	public function createSensor($data_array){
		//required fields name, longname, desc
		$this->expected_http_code = 201;
		$this->hasParameters = false;
	
		$sensor_input = $this->ArraytoJSON($data_array);

		$data = $this->processRequest( "sensors", "POST", $sensor_input);	
		
		$data = json_decode($data, true);
		return $data;
		
	}
	
	public function createMultipleSensor($multi_dim_array){
		//required fields name, longname, desc
		$this->expected_http_code = 201;
		$this->hasParameters = false;
		
		$sensor_input = $this->ArraytoJSONList($multi_dim_array);
		
		$data = $this->processRequest( "sensors", "PUT", $sensor_input);	
		
		$data = json_decode($data, true);
		echo $data;
		return $data;
		
	}
	
	
	public function getSensors($sensor=NULL, $scope=NULL, $active=NULL, $private=NULL, $tags=NULL, $text=NULL, $offset=NULL, $limit=NULL, $location=NULL) 
	{	$this->expected_http_code = 200;
	
		if ($sensor == NULL){
			$params = array("scope" => $scope, "active" => $active, "private" => $private, 
							"tags" => $tags, "text" => $text, "offset" => $offset, "limit" => $limit,
							"location" => $this->NWSELocationBox($location));
			  
			$url_string = "sensors?".$this->ArraytoNameValuePairs($params);
			$this->hasParameters = true;
			
			if ( substr($url_string, -1) == '?' ){
				$url_string=substr_replace($url_string ,"",-1);
				$this->hasParameters = false;
			}
			
		}
		else{
			$url_string = "sensors/".$sensor;
			$this->hasParameters = false;
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
		$this->hasParameters = false;
		
		$updated_sensor_input = $this->ArraytoJSON($data_array);
		$data = $this->processRequest( "sensors/".$sensor, "PUT", $updated_sensor_input);
		$data = json_decode($data, true);
		return $data;
	}
	
	public function deleteSensor($sensor){
		$this->expected_http_code = 204;
		$this->hasParameters = false;
		
		$data = $this->processRequest( "sensors/".$sensor, "DELETE");
		$data = json_decode($data, true);
		return $data;
	}
//Sensor Subscription Functions
	public function getSubscribedSensors (){
		$this->expected_http_code = 200;
		$this->hasParameters = false;
		
		$data = $this->processRequest ("subscribe", "GET");
		$data = json_decode($data, true);
		return $data;
	}
	
	public function subscribeSensor ($sensor){
		$this->expected_http_code = 204;
		$this->hasParameters = false;
		
		$data = $this->processRequest ("subscribe/".$sensor, "PUT");
		$data = json_decode($data, true);
		return $data;
	}
	
	public function unsubscribeSensor ($sensor){
		$this->expected_http_code = 204;
		$this->hasParameters = false;
		
		$data = $this->processRequest ("subscribe/".$sensor, "DELETE");
		$data = json_decode($data, true);
		return $data;
	}

//Sensor Field Functions
	public function getSensorFields ($sensor=null, $field=null){
		$this->expected_http_code = 200;
		$this->hasParameters = false;
		
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
		$this->hasParameters = false;
		
		$field = $data_array[name];
		$updated_sensor_field = $this->ArraytoJSON($data_array);
		$data = $this->processRequest( "sensors/".$sensor."/fields/".$field, "PUT", $updated_sensor_field);
		$data = json_decode($data, true);
		return $data;
	}
	
	public function deleteSensorField($sensor, $field){
		$this->expected_http_code = 204;
		$this->hasParameters = false;
		
		$data = $this->processRequest( "sensors/".$sensor."/fields/".$field, "DELETE");
		$data = json_decode($data, true);
		return $data;
	}
//Aggregated Sensor Data
	  public function getAggregatedData ($params){
	//public function getSensors($scope=NULL, $active=NULL, $private=NULL, $tags=NULL, $text=NULL,
	//						     $start=NULL, $end=NULL, $after=NULL, $afterE=NULL, $before=NULL, $beforeE=NULL,
	//							 $orderBy=NULL) 
	//{	
	//	$params = array("scope" => $scope, "active" => $active, "private" => $private, 
	//					"tags" => $tags, "text" => $text, 
	//					"start" => $start, "end" => $end, "after" => $after, 
	//					"afterE" => $afterE, "before" => $before, "beforeE" => $beforeE,
	//				    "orderBy" => $orderBy);
		
		$this->expected_http_code = 200;
		$this->hasParameters = true;
		
		$url_string = "data?".$this->ArraytoNameValuePairs($params);
		
		if ( substr($url_string, -1) == '?' ){
			$url_string=substr_replace($url_string ,"",-1);
			$this->hasParameters = false;
		}

		$data = $this->processRequest ($url_string, "GET");
		
		$data = json_decode($data, true);
		return $data;
	}

	
//Sensor Data Functions
	public function getSensorData ($sensor){
		$this->expected_http_code = 200;
		$this->hasParameters = false;
		
		$data = $this->processRequest( "sensors/".$sensor."/data", "GET");
		$data = json_decode($data, true);
		return $data;
	}
	
	public function sendSensorData ($sensor, $value ,$lat=NULL, $lng=NULL, $message=NULL, $timestamp=NULL){
		$this->expected_http_code = 201;
		$this->hasParameters = false;
		
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
		$this->hasParameters = false;
		
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
		$this->hasParameters = false;
		/*
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
		*/
		
		$sensor_data = $this->ArraytoJSONList($multi_dim_array);
		
		$data = $this->processRequest( "sensors/".$sensor."/data", "PUT", $sensor_data);
		
		$data = json_decode($data, true);
		return $data;
	}
	
	public function deleteSensorData ($sensor, $timestamp){
		$this->expected_http_code = 204;
		$this->hasParameters = false;
		
		$data = $this->processRequest( "sensors/".$sensor."/data/".$timestamp, "DELETE");
		$data = json_decode($data, true);
		return $data;
	}

	public function getRawSensorData($sensor, $start=NULL, $end=NULL, $after=NULL, $afterE=NULL, $before=NULL, $beforeE=NULL, $reverse=NULL){
		$this->expected_http_code = 200;
		$this->hasParameters = true;
		
		$params = array("start" => $start, "end" => $end, "after" => $after, 
						"afterE" => $afterE, "before" => $before, "beforeE" => $beforeE,
						"reverse"=> $reverse);
			  
		$url_string = "sensors/".$sensor."/data?".$this->ArraytoNameValuePairs($params);
		
		if ( substr($url_string, -1) == '?' ){
			$url_string=substr_replace($url_string ,"",-1);
			$this->hasParameters = false;
		}
		
		$data = $this->processRequest ( $url_string, "GET");	
		
		$data = json_decode($data, true);
		return $data;
	}
	
	public function getFormattedSensorData($sensor, $tq=NULL, $reqID=NULL, $out=NULL, $outfile=NULL){
		$this->expected_http_code = 200;
		$this->hasParameters = true;
		
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
		
		if ( substr($str, -1) == '?' ){
			$url_string=substr_replace($string ,"",-1);
			$this->hasParameters = false;
		}
		
		$data = $this->processRequest ( $url_string, "GET");
		
		return $data;
	}

//Users	
	public function getUsers($user=NULL, $text=NULL, $reverse=NULL, $offset=NULL, $limit=NULL) 
	{	$this->expected_http_code = 200;
	
		if ($user == NULL){
			$params = array("text" => $text, "reverse" => $reverse, "offset" => $offset, 
							"limit" => $limit );
			  
			$url_string = "users?".$this->ArraytoNameValuePairs($params);
			$this->hasParameters = true;
			
			if ( substr($url_string, -1) == '?' ){
				$url_string=substr_replace($url_string ,"",-1);
				$this->hasParameters = false;
			}	
		}
		else{
			$url_string = "users/".$user;
			$this->hasParameters = false;
		}
	
		$data = $this->processRequest ( $url_string, "GET");
		
		$data = json_decode($data, true);
		return $data;
	}
	
	public function createUser($user_array){
		//required fields username, firstname, lastname, email, password
		$this->expected_http_code = 201;
		$this->hasParameters = false;
	
		$user_input = $this->ArraytoJSON($user_array);

		$data = $this->processRequest( "users", "POST", $user_input);	
		
		$data = json_decode($data, true);
		return $data;		
	}
	
	public function updateUser($user, $user_array){
		$this->expected_http_code = 204;
		$this->hasParameters = false;
		
		$updated_user_input = $this->ArraytoJSON($user_array);
		$data = $this->processRequest( "users/".$user, "PUT", $updated_user_input);
		$data = json_decode($data, true);
		return $data;
	}
	
	public function deleteUser($user){
		$this->expected_http_code = 204;
		$this->hasParameters = false;
		
		$data = $this->processRequest( "users/".$user, "DELETE");
		$data = json_decode($data, true);
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
	private function processRequest ($url, $request, $data=null, $isJSON=1){
	
		//Clearing old data
		$this->response =array();
		$this->response_info =array();
		
		//Updating to full URL
		if($this->accessToken != "setting" )
			$url = $this->base_url . $url ;
		
		#Initializing a cURL session
		$ch = curl_init();

		#Setting cURL options
		
		//Logging In
		if ($this->accessToken != "setting" ){
			if ( $this->accessToken == "access_token=none" ){
				curl_setopt($ch, CURLOPT_USERPWD,"{$this->key_id}:{$this->key_password}");
				echo "key";
			}
			else{
				if ( $this->hasParameters == true )
					$url = $url."&".$this->accessToken;
				else	
					$url = $url."?".$this->accessToken;
			}		
		}
		
		//Entering URL
		curl_setopt($ch, CURLOPT_URL, $url);
		
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
	
//Private Helper Functions
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
		
		//echo $url_string;
		return $url_string;
	}
	
	private function ArraytoJSONList($multi_dim_array){
		$started = false;
		$sensor_input = "[";

		foreach ($multi_dim_array as $data_array){
			if ( $started == false){
				$started = true;
			}	
			else{
				$sensor_input.= ',';
			};
			$sensor_input .= $this->ArraytoJSON($data_array);
		};
		$sensor_input.= ']';
		
		return $sensor_input;
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
	
	private function NWSELocationBox ($location){
		if( $location == NULL ){
			return NULL;
		}
		
		//$corners = array_chunk($location, 2);
		//$location_box = implode(':', array(implode(',', $corners[0]), implode(',', $corners[1])));
	
		$location_box = $location[0].",".$location[1].":".$location[2].",".$location[3];
		return $location_box;
	}
	

	
}
?>
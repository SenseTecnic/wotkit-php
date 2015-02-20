<?php

require_once('wotkit_clientConfig.php');

class wotkit_client {

	//A set key in database with "tester" as owner
	private $key_id = "3bfeb222062cb0a6";
	private $key_password = "894a1b762806471f";

	//A set key in database with "tester-admin" as owner
	private $admin_key_id = "9ae8fee004d7385c";
	private $admin_key_password = "27cb43c11491929b";

	private $tester_alt_username = "tester-alt";
	private $tester_alt_password = "fFbjfpDEdyw9e3fkAFxH";

	//Necessary for Oauth2
	private $client_id;
	private $client_secret;
	private $accessToken = 'none';
	private $oauthTokenURL = "oauth/token";
	private $redirectURL = REDIRECT_URL;
	private $hasParameters;

	private $base_url = BASE_URL;

	public $response;
	public $response_info;
	public $expected_http_code;


/*
 *Client Constructors
 */
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

/*
 *Oauth2 Functions
 */
	private function obtainAccessToken (){		
		$accessToken = "none";
		//$ch = curl_init();
		if(array_key_exists('code', $_GET)) {
			// try to get an access token
			$url = $this->oauthTokenURL;
			$params = array(
					 "code" => $_GET['code'],
					 "client_id" => $this->client_id ,
					 "client_secret" => $this->client_secret,
					 "redirect_uri" => $this->redirectURL,
					 "grant_type" => "authorization_code"
					);

			//$data = $this->ArraytoNameValuePairs ($params);
			$data = http_build_query($params);
			$this->accessToken = "setting";
			$response = $this->processRequest($url, "POST", $data, false);
			$json = json_decode($response['data']);
			$accessToken = $json->access_token;

			if ($accessToken == null)
				$accessToken = "none";
		}

		$this->accessToken = "access_token=".$accessToken;
	}


/*
 *Public Helper Function
 */
	public function checkHTTPcode( $expected_http_code=NULL, $message=NULL ){
		if( $expected_http_code == NULL )
			$expected_http_code = $this->expected_http_code;

		if ( $this->response_info['http_code'] == $expected_http_code )
			$response ='<b>PASS</b> ';
		else
			$response = '<font color="red">FAIL</font> ';

		$response .=" - HTTP ".$this->response_info['http_code'];

		if ( $message != NULL )
			$response.=" ".$message;

		return $response;
	}

/*
 * WoTKit API Functions
 */

//Actuators Functions

	public function testActuator ($sensor, $message){
		$this->expected_http_code = 200;

		$response = $this->subscribeActuator($sensor);
		$sub_id = $response['data']['subscription'];
		$response = $this->sendActuator($sensor, $message);
		$response = $this->getActuator($sub_id);

		return $response;
	}

	public function subscribeActuator($sensor){
		$this->expected_http_code = 200;
		$this->hasParameters = false;

		$dummy_data = 1; //dummy data becuase processRequest() wants all POSTS/PUTS to have data
		$response = $this->processRequest("control/sub/".$sensor, "POST", $dummy_data, true);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	/* sendActuator()
	 * public = true  => do not supply any credentials
	 * special_user (string) => supply credentials for different user: "admin" (key), "other"(basic authentication)
	 *
	 */
	public function sendActuator($sensor, $message, $public=false, $special_user=null){
		$this->expected_http_code = 200; //documentation said 204, but always get 200....
		$this->hasParameters = false;

		//$message = $this->ArraytoNameValuePairs($message);
		$message = http_build_query($message);
		$response = $this->processRequest("sensors/".$sensor."/message", "POST", $message, false, $public, $special_user);
		//do not JSON decode to save time
		return $response;
	}

	public function getActuator($sub_id){
		$this->expected_http_code = 200;
		$this->hasParameters = true;

		$response = $this->processRequest("control/sub/".$sub_id."?wait=10", "GET");
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}



//Sensor Functions

	public function createSensor($data_array){
		//required fields name, longname, desc
		$this->expected_http_code = 201;
		$this->hasParameters = false;

		//$sensor_input = $this->ArraytoJSON($data_array);
		$sensor_input = json_encode($data_array);
		$response = $this->processRequest("sensors", "POST", $sensor_input);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function createMultipleSensor($multi_dim_array){
		//required fields name, longname, desc
		$this->expected_http_code = 201;
		$this->hasParameters = false;

		//$sensor_input = $this->ArraytoJSONList($multi_dim_array);
		$sensor_input = json_encode($multi_dim_array);
		$response = $this->processRequest("sensors", "PUT", $sensor_input);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	/* getSensors()
	 * public = true  => do not supply any credentials
	 */
	public function getSensors($sensor=NULL, $scope=NULL, $active=NULL, $visibility=NULL, $tags=NULL, $text=NULL, $offset=NULL, $limit=NULL, $location=NULL, $orgs=NULL, $metadata=NULL, $public=false)
	{
		$this->expected_http_code = 200;

		$url_string = "sensors";

		if ($sensor == NULL){
			$params = array("scope" => $scope, "active" => $active, 
							"visibility" => $visibility,
							"tags" => $tags, "text" => $text, 
							"offset" => $offset, 
							"limit" => $limit,
							"location" => $this->NWSELocationBox($location), 
							"owners" => $orgs,
							"metadata"=>$this->formatMetadata($metadata) );
			//$param_string = $this->ArraytoNameValuePairs($params);
			$param_string = http_build_query($params);

			if (empty($param_string))
				$this->hasParameters = false;
			else{
				$url_string .= "?".$param_string;
				$this->hasParameters = true;
			}
		}else{
			$url_string .= "/".$sensor;
			$this->hasParameters = false;
		}

		$response = $this->processRequest ( $url_string, "GET", null, 1, $public);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	/*
	public function getSingleSensor($sensor){
		$this->expected_http_code = 200;

		$data = $this->processRequest ( "sensors/".$sensor, "GET");
		$data['data'] = json_decode($data['data'], true);
		return $data;
	}

	public function getMultipleSensors($scope=NULL, $active=NULL, $private=NULL, $tags=NULL, $text=NULL, $offset=NULL, $limit=NULL)
	{	$this->expected_http_code = 200;

		$params = array("scope" => $scope, "active" => $active, "private" => $private,
								 "tags" => $tags, "text" => $text, "offset" => $offset, "limit" => $limit);

		$url_string = "sensors?".$this->ArraytoNameValuePairs($params);

		$data = $this->processRequest ( $url_string, "GET");

		$data['data'] = json_decode($data['data'], true);
		return $data;
	}
	*/

	public function updateSensor($sensor, $data_array){
		$this->expected_http_code = 204;
		$this->hasParameters = false;

		//$updated_sensor_input = $this->ArraytoJSON($data_array);
		$updated_sensor_input = json_encode($data_array);
		$response = $this->processRequest("sensors/".$sensor, "PUT", $updated_sensor_input);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function deleteSensor($sensor){
		$this->expected_http_code = 204;
		$this->hasParameters = false;

		$response = $this->processRequest( "sensors/".$sensor, "DELETE");
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}



//Sensor Subscription Functions

	public function getSubscribedSensors (){
		$this->expected_http_code = 200;
		$this->hasParameters = false;

		$response = $this->processRequest ("subscribe", "GET");
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function subscribeSensor ($sensor){
		$this->expected_http_code = 204;
		$this->hasParameters = false;

		$response = $this->processRequest ("subscribe/".$sensor, "PUT");
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function unsubscribeSensor ($sensor){
		$this->expected_http_code = 204;
		$this->hasParameters = false;

		$response = $this->processRequest ("subscribe/".$sensor, "DELETE");
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}



//Sensor Field Functions

	/* getSensorFields()
	 * public = true  => do not supply any credentials
	 */
	public function getSensorFields ($sensor=null, $field=null, $public=false){
		$this->expected_http_code = 200;
		$this->hasParameters = false;

		$response = $this->processRequest ("sensors/".$sensor."/fields/".$field, "GET", null, 1, $public);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	/*
	public function getSingleSensorField ($sensor, $field){
		$this->expected_http_code = 200;

		$data = $this->processRequest ("sensors/".$sensor."/fields/".$field, "GET");
		$data['data'] = json_decode($data['data'], true);
		return $data;
	}

	public	function getMultipleSensorFields($sensor){
		$this->expected_http_code = 200;

		$data = $this->processRequest ("sensors/".$sensor."/fields", "GET");
		$data['data'] = json_decode($data['data'], true);
		return $data;
	}
	*/

	public function updateSensorField($sensor, $data_array){
// public function updateSensorField($sensor, $name, $type, $longName=null, $required=null, $units=null)
//	{
// 		$data_array = array{"name"=>$name, "type"=>type, "longName"=>longName,
//						    "required"=>$required, "units=>$units);
		$this->expected_http_code = 204;
		$this->hasParameters = false;

		$field = $data_array['name'];
		//$updated_sensor_field = $this->ArraytoJSON($data_array);
		$updated_sensor_field = json_encode($data_array);
		$response = $this->processRequest( "sensors/".$sensor."/fields/".$field, "PUT", $updated_sensor_field);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function deleteSensorField($sensor, $field){
		$this->expected_http_code = 204;
		$this->hasParameters = false;

		$response = $this->processRequest( "sensors/".$sensor."/fields/".$field, "DELETE");
		$response['data'] = json_decode($response['data'], true);
		return $response;
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
		$url_string = "data";

		//$param_string = $this->ArraytoNameValuePairs($params);
		$param_string = http_build_query($params);
		if (empty($param_string))
			$this->hasParameters = false;
		else{
			$url_string .= "?".$param_string;
			$this->hasParameters = true;
		}

		$response = $this->processRequest ($url_string, "GET");
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}



//Sensor Data Functions

	/* getSensorData()
	 * public = true  => do not supply any credentials
	 * start and before are in seconds
	 */
	public function getSensorData ($sensor, $public=false, $start="", $before=""){
		$this->expected_http_code = 200;
		$this->hasParameters = false;

		$request_url = "sensors/".$sensor."/data";

		if ($start != "" && $before != "") {
			$request_url .= "?start=".($start*1000)."&before=".($before*1000);
		}

		$response = $this->processRequest($request_url, "GET", null, 1, $public);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function sendSensorData ($sensor, $value ,$lat=NULL, $lng=NULL, $message=NULL, $timestamp=NULL, $public=false){
		$this->expected_http_code = 201;
		$this->hasParameters = false;

		$params = array( "timestamp" => $timestamp, "value" => $value,
						 "lat" => $lat, "lng" => $lng, "message" => $message );
		//$sensor_data = $this->ArraytoNameValuePairs($params);
		$sensor_data = http_build_query($params);
		$response = $this->processRequest( "sensors/".$sensor."/data", "POST", $sensor_data, false, $public);
		$response['data'] = json_decode($response['data'], true);

		//Allows time for the new Sensor Data to be processed before moving on.
		//Without sleep, you may try to query data before it has been processed.
		sleep(2);

		return $response;
	}

	public function sendNonStandardSensorData($sensor, $data_array, $JSON=false){
		$this->expected_http_code = 201;
		$this->hasParameters = false;
		$url_string = "sensors/".$sensor."/data";

		if ($JSON)
			//$sensor_data = $this->ArraytoJSON($data_array);
			$sensor_data = json_encode($data_array);
		else
			//$sensor_data = $this->ArraytoNameValuePairs($data_array);
			$sensor_data = http_build_query($data_array);
		$response = $this->processRequest($url_string, "POST", $sensor_data, $JSON);
		$response['data'] = json_decode($response['data'], true);

		//Allows time for the new Sensor Data to be processed before moving on.
		//Without sleep, you may try to query data before it has been processed.
		sleep(2);

		return $response;
	}


	public function updateSensorData ($sensor, $multi_dim_array){
		$this->expected_http_code = 204;
		$this->hasParameters = false;

		//$sensor_data = $this->ArraytoJSONList($multi_dim_array);
		$sensor_data = json_encode($multi_dim_array);
		$response = $this->processRequest("sensors/".$sensor."/data", "PUT", $sensor_data);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}


	public function deleteSensorData ($sensor, $timestamp){
		$this->expected_http_code = 204;
		$this->hasParameters = false;

		$response = $this->processRequest( "sensors/".$sensor."/data/".$timestamp, "DELETE", $special_user="ADMIN");
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	/* getRawSensorData()
	 * public = true  => do not supply any credentials
	 */
	public function getRawSensorData($sensor, $start=NULL, $end=NULL, $after=NULL, $afterE=NULL, $before=NULL, $beforeE=NULL, $reverse=NULL, $public=false){
		$this->expected_http_code = 200;
		$url_string = "sensors/".$sensor."/data";

		$params = array("start" => $start, "end" => $end, "after" => $after,
						"afterE" => $afterE, "before" => $before, "beforeE" => $beforeE,
						"reverse"=> $reverse);
		//$param_string = $this->ArraytoNameValuePairs($params);
		$param_string = http_build_query($params);
		if(empty($param_string))
			$this->hasParameters = false;
		else{
			$url_string .= "?".$param_string;
			$this->hasParameters = true;
		}

		$response = $this->processRequest ($url_string, "GET", null, 1, $public);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	/* getFormattedSensorData()
	 * public = true  => do not supply any credentials
	 */
	public function getFormattedSensorData($sensor, $tq=NULL, $reqID=NULL, $out=NULL, $outfile=NULL, $public=false){
		$this->expected_http_code = 200;
		$this->hasParameters = true;

		$url_string = "sensors/".$sensor."/dataTable?";

		if ( $reqID != NULL ){
			$url_string .= "tqx=reqId:".$reqID;
			if( $out != NULL ){
				$url_string .= ";out:".$out;
				if( $outfile != NULL )
					$url_string .= ";outfile:".$outfile;
			}

			if( $tq != NULL )
				$url_string .= "&";
		}

		if( $tq != NULL )
			$url_string .= "tq=".rawurlencode ($tq);

		if ( substr($url_string, -1) == '?' ){
			$url_string=substr_replace($url_string ,"",-1);
			$this->hasParameters = false;
		}

		$response = $this->processRequest ($url_string, "GET", null, 1, $public);
		return $response;
	}



//Users
//**must have ROLE_ADMIN credentials to use these functions
//*** special_user = "admin" => supply KEY for admin user

	public function getUsers($special_user=NULL, $user=NULL, $text=NULL, $reverse=NULL, $offset=NULL, $limit=NULL)
	{	$this->expected_http_code = 200;
		$url_string = "users";

		if ($user == NULL){
			$params = array("text" => $text, "reverse" => $reverse, "offset" => $offset,
							"limit" => $limit );
			//$param_string = $this->ArraytoNameValuePairs($params);
			$param_string = http_build_query($params);
			if(empty($param_string))
				$this->hasParameters = false;
			else{
				$url_string .= "?".$param_string;
				$this->hasParameters = true;
			}
		}
		else{
			$url_string .= "/".$user;
			$this->hasParameters = false;
		}

		$response = $this->processRequest ($url_string, "GET", null, 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function createUser($special_user=NULL, $user_array){
		//required fields username, firstname, lastname, email, password
		$this->expected_http_code = 201;
		$this->hasParameters = false;

		//$user_input = $this->ArraytoJSON($user_array);
		$user_input = json_encode($user_array);
		$response = $this->processRequest( "users", "POST", $user_input, 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function updateUser($special_user=NULL, $user, $user_array){
		$this->expected_http_code = 204;
		$this->hasParameters = false;

		//$updated_user_input = $this->ArraytoJSON($user_array);
		$updated_user_input = json_encode($user_array);
		$response = $this->processRequest("users/".$user, "PUT", $updated_user_input, 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function deleteUser($special_user=NULL, $user){
		$this->expected_http_code = 204;
		$this->hasParameters = false;
		$response = $this->processRequest("users/".$user, "DELETE", null, 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}


//Organizations
//**must have ROLE_ADMIN credentials to use these functions (expect getOrganizations)
//*** special_user = "admin" => supply KEY for admin user

	/* getOrganizations()
	 * public = true  => do not supply any credentials
	 */
	public function getOrganizations($special_user=null, $org_name=null, $text=null, $offset=null, $limit=NULL, $public=false){
		$this->expected_http_code = 200;
		$url_string = "orgs";

		if (empty($org_name)){
			$params = array("text"=>$text, "offset"=>$offset, "limit"=>$limit);
			//$param_string = $this->ArraytoNameValuePairs($params);
			$param_string = http_build_query($params);
			if (empty($param_string))
				$this->hasParameters = false;
			else{
				$url_string .= "?".$param_string;
				$this->hasParameters = true;
			}
		}else{
			$url_string .= "/".$org_name;
			$this->hasParameters = false;
		}

		$response = $this->processRequest ($url_string, "GET", null, 1, $public, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function createOrganization($special_user=null, $new_org){
		$this->expected_http_code = 201;

		//$new_org_data = $this->ArraytoJSON($new_org);
		$new_org_data = json_encode($new_org);
		$response = $this->processRequest ("orgs", "POST", $new_org_data, 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function updateOrganization($special_user=null, $org_name, $updated_org){
		$this->expected_http_code = 204;

		//$updated_org_data = $this->ArraytoJSON($updated_org);
		$updated_org_data = json_encode($updated_org);
		$response = $this->processRequest ("orgs/".$org_name, "PUT", $updated_org_data, 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function deleteOrganization($special_user=null, $org_name, $hardDelete=false){
		$this->expected_http_code = 204;

		$qArray = Array("hardDelete" => $hardDelete);
		$qParams = http_build_query($qArray);

		$response = $this->processRequest ("orgs/".$org_name."?".$qParams, "DELETE", null, 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function getOrganizationMembers($special_user=null, $org_name){
		$this->expected_http_code = 200;

		$response = $this->processRequest ("orgs/".$org_name."/members", "GET", null, 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function addOrganizationMembers($special_user=null, $org_name, $member_array){
		$this->expected_http_code = 204;

		$response = $this->processRequest ("orgs/".$org_name."/members", "POST", json_encode($member_array), 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function removeOrganizationMembers($special_user=null, $org_name, $member_array){
		$this->expected_http_code = 204;

		$response = $this->processRequest ("orgs/".$org_name."/members", "DELETE", json_encode($member_array), 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

	public function updateOrganizationMemberRole($special_user=null, $org_name, $username, $role) {
		$this->expected_http_code = 204;

		$member = array("username" => $username ,"role" => $role);

		$response = $this->processRequest ("orgs/".$org_name."/members/".$username, "PUT", json_encode($member), 1, false, $special_user);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}

//News
//**does not require any credentials

	public function getNews(){
		$this->expected_http_code = 200;
		$this->hasParameters = false;

		$response = $this->processRequest( "news", "GET", null, 1, true);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}



//Stats
//**does not require any credentials

	public function getStats(){
		$this->expected_http_code = 200;
		$this->hasParameters = false;

		$response = $this->processRequest( "stats", "GET", null, 1, true);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}



//Tags

	/* getTags()
	 * public = true  => do not supply any credentials
	 */
	public function getTags($scope=NULL, $visibility=NULL, $text=NULL, $active=NULL, $offset=NULL, $limit=NULL, $location=NULL, $public=false){
		$this->expected_http_code = 200;
		$this->hasParameters = true;
		$params = array("scope" => $scope, "visibility" => $visibility, "text" => $text, "active"=> $active,
						"offset" => $offset, "limit" => $limit, "location" => $this->NWSELocationBox($location) );

		//$url_string = "tags?".$this->ArraytoNameValuePairs($params);
		$url_string = "tags?".http_build_query($params);

		$response = $this->processRequest($url_string, "GET", null, false, $public);
		$response['data'] = json_decode($response['data'], true);
		return $response;
	}



/*
 *Basic Curl Request
 */
	//@param str  $url
	//				task specific url ending
	//@param str  $request
	//				one of: GET, POST, PUT, DELETE
	//@param str  $data
	//@param bool $isJson
	//				whether input $data is JSON string
	//@param bool $public
	//				whether not to provide credentials
	//@param str $special_user
	//				one of : "admin" or "other"
	//				provide a different user's credentials user:
	//				"admin" (key), "other"(basic authentication)
	private function processRequest ($url, $method, $data=null, $isJSON=1, $public=false, $special_user=NULL){

		//Clearing old data
		$this->response = array();
		$this->response_info = array();

		//Updating to full URL
		if($this->accessToken != "setting" )
			$url = $this->base_url . $url ;

		#Initializing a cURL session
		$ch = curl_init();

		#Setting cURL options

		//Logging In
		if ( !$public && empty($special_user)){
			if ($this->accessToken != "setting"){
				if ( $this->accessToken == "access_token=none" ){
					curl_setopt($ch, CURLOPT_USERPWD,"{$this->key_id}:{$this->key_password}");
					$permission = "Using key for user: 'tester'.";
				}
				else{
					if ( $this->hasParameters == true )
						$url = $url."&".$this->accessToken;
					else
						$url = $url."?".$this->accessToken;
					$permission = "Using access token.";
				}

			}
		} else{
			if (empty($special_user)) {
				$permission = "Public, no authentication.";
			}

			if ($special_user == "other") {
				curl_setopt($ch, CURLOPT_USERPWD, "{$this->tester_alt_username}:{$this->tester_alt_password}");
				$permission = "Loggged in as user: 'tester_alt'.";
			}

			if ($special_user == "admin") {
				curl_setopt($ch, CURLOPT_USERPWD,"{$this->admin_key_id}:{$this->admin_key_password}");
				$permission = "Using key for user: 'tester-admin'.";
			}
		}

		//Entering URL
		curl_setopt($ch, CURLOPT_URL, $url);

		//Allows results to be saved in variable and not printed out
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		if ( $isJSON )
			//Necessary for these actions: Create New Sensor, Update Sensor
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

		if ($method == "PUT")
			//Update Sensor or Sensor Data
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

		if ($method == "DELETE")
			//Delete Sensor
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

		if ($method != "GET" and $data!=NULL){
			//'Postfields' for POST or PUT methods
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		//Save response and information
		$this->response = curl_exec($ch);
		$this->response_info = curl_getinfo($ch);

		#Closing cURL
		curl_close($ch);

		$response = array(
				"permission"=>$permission,
				"url"=>$url,
				"method"=>$method,
				"request" => array(
						"headers" => $this,
						"body"    => $data
					),
				"code"=>$this->response_info['http_code'],
				"data"=>$this->response
			);

		return $response;
	}

	private function NWSELocationBox ($location){
		if( $location == NULL )
			return NULL;

		//$corners = array_chunk($location, 2);
		//$location_box = implode(':', array(implode(',', $corners[0]), implode(',', $corners[1])));

		$location_box = $location[0].",".$location[1].":".$location[2].",".$location[3];
		return $location_box;
	}

	private function formatMetadata ($metadata){
		if ( $metadata == null || empty($metadata) )
			return null;
		$metadataString = "";
		foreach ( array_keys($metadata) as $key ){
			$metadataString .= $key;
			if ( empty($metadata[$key]) or $metadata[$key]== null)
				$metadataString .=';';
			else
				$metadataString .=':'.$metadata[$key].';';
		}
		$metadataString = substr($metadataString, 0, -1);
		echo "Metadata searched is: ".$metadataString;
		return $metadataString;
	}
}

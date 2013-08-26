<html>
<head>
<title>WoTKIT API PHP-Test-Client </title>
<link href="lib/css/bootstrap.min.css" rel="stylesheet" media="screen" />
<link href="lib/css/custom.css" rel="stylesheet"/>
<body>
<script src="http://code.jquery.com/jquery.js"></script>
<script src="lib/js/bootstrap.min.js"></script>
<?php
require_once('wotkit_client.php');
require_once('wotkit_clientConfig.php');
require_once('helper_functions.php');

$wotkit_client = new wotkit_client(BASE_URL, CLIENT_ID, CLIENT_SECRET);

$invalid_user_name = "3"; //must be at least four characters

  $invalid_name_user_input = array(
  "username" => $invalid_user_name, 
  "firstname" => "API Testing",
  "lastname" => "Lastname",
  "email" => "email@address.com", 
  "password" => "password");

#Create invalid username
  $title = "\n\n [CREATE user with invalid name :'".$invalid_user_name."', as ADMIN] \n";
  $response = $wotkit_client->createUser("admin", $invalid_name_user_input);
  $test_status = $wotkit_client->checkHTTPcode(400);
  $problem = checkError($response['data'], 'invalid', 'username');
  displayTestResults ($problem, false, $title, $test_status, $response);;

?>

</body>
</html>

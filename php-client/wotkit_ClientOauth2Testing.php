
<?php
/*
 * Run this script for Oauth2 Testing
 * (assumes newly initilized database)
 */

 /*!!!!PLEASE DO NOT USE THIS FEATURE WITH FIREFOX!!!!*/
 
require_once('wotkit_clientConfig.php');

header( 'Location: '.BASE_URL.'oauth/authorize?client_id='.CLIENT_ID.'&response_type=code&redirect_uri=http://localhost/wotkit_clientTestCases.php' );

?>


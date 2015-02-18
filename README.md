wotkit-php
==========

PHP Client binding for the wotkit


FILES:

1. wotkit_client.php
  - set of functions for WoKIT api & authentication
  
2. wotkit_clientTestCases.php
  - set of test cases for WoKIT api
  - assumes a newly initialized database
  
3. wotkit_clientConfig.php
  - configuration file
  - set base_url for the WoKIT and client_id & client_secret (for Oauth2) here
  
4. wotkit_ClientOauth2Testing.php
  - run this file for Oauth2 testing 
  - NOTE: ANY FUNCTION RUN "AS ADMIN" is using a key NOT an access token	
  - do NOT run this file with Firefox
  

TESTING:

1. Make sure the WoTKit database is initialized with the latest data fixtures.
2. Set configuration parameters in "wotkit_clientConfig.php"
3. Run "wotkit_ClientOauth2Testing.php" using Chrome or IE* to test with Oauth
   Run "wotkit_clientTestCases.php" to test with keys

Note: Any RED, open tests have failed. Any BLUE tests need to visually checked.



*This script does not work with Firefox when using using Oauth2. 
(The behaviour on Firefox is as follows:
The script runs (not to completion) using Oauth2 ,
and then without warning or prompting, the script runs again (this time to completion) 
using key value authentication.)

wotkit-php
==========

PHP Client binding for the wotkit


FILES:

wotkit_client.php
  set of functions for WoKIT api & authentication
  
wotkit_clientTestCases.php
  set of test cases for WoKIT api
  assumes a newly initialized database
  
wotkit_clientConfig.php
  configuration file
  set base_url for the WoKIT and client_id & client_secret (for Oauth2) here
  
wotkit_ClientOauth2Testing.php
  run this file for Oauth2 testing 
  do NOT run this file with Firefox
  

TESTING:

1. Set configuration parameters in "wotkit_clientConfig.php"
2. Run "wotkit_ClientOauth2Testing.php" using Chrome or IE*



*This script does not work with Firefox when using using Oauth2. 
(The behaviour on Firefox is as follows:
The script runs (not to completion) using Oauth2 ,
and then without warning or prompting, the script runs again (this time to completion) 
using key value authentication.)

<?php

//HELPER FUNCTIONS
//Outputs results of test in a formatted fashion
/*
 * $problem ==> boolean result of any tests run on data; null if no tests run
 * $visual_check ==> boolean value indicating whether user should visually inspect that test
 * $title ==> string title of test
 * $test_status ==> result of checking HTTP codes
 * $response ==> wotkit client response
 * $expected ==> number of items expected from $response['data']
 * $special_case ==> boolean value indicating whether the $response should be formatted in a user-readable way
		** By default, if $expected != null, $response NOT formatted in a user-readable way
		   If $special_case == true && $expected != null, $response is formatted in a user-readable way
 */
function displayTestResults ($problem=null, $visual_check, $title, $test_status, $response, $expected=null, $special_case=false){
	global $failures;
	global $test_count;

	$pass_code = 0; // if http codes match
	$pass_expected = 1; // if # of expected & # of received match

	//Check if received HTTP Code was correct
	if (stristr($test_status,'<b>PASS</b> '))
		$pass_code = 1;
	//Determine Expected vs Found Queries Status
	if($expected != NULL || $expected === 0){
		$found = count($response['data']);
		if ( $expected == $found ){
			$test_status .= '<br><b>PASS</b>';
		}else{
			$pass_expected = 0;
			$test_status .= '<br><font color="red">FAIL</font>.';
		}
		$test_status .=" - Expected ".$expected." = Returned ".$found;
	}
	// Check if automated data check passed
	if($problem == true)
		$test_status .='<br><font color="red">Automated Data Check FAILED</font>.';
	if($problem === false)
		$test_status .='<br>Automated Data Check Passed.';
	if($problem == null || empty($problem))
		$problem = false;

	//Format response
	echo '<div class="accordion" id="accordian"'.$test_count.'>';
	echo '<div class="accordion-group">';
	echo '<div class="accordion-heading">';

	if ( $pass_code && $pass_expected && !$problem ){
		if ($visual_check){
			echo '<a class="accordion-toggle btn btn-info" data-toggle="collapse" data-parent="#accordion'.$test_count.'" href="#collapse'.$test_count.'">';
			echo '<i class="icon-flag"></i>';
		}else{
			echo '<a class="accordion-toggle btn" data-toggle="collapse" data-parent="#accordion'.$test_count.'" href="#collapse'.$test_count.'">';
			echo '<i class="icon-ok"></i>';
		}
	}else{
		echo '<a class="accordion-toggle btn btn-danger" data-toggle="collapse" data-parent="#accordion'.$test_count.'" href="#collapse'.$test_count.'">';
		echo '<i class="icon-remove icon-white"></i>';
		$failures ++;
	}
	echo "    ".$test_count.'.  '.$title;
	echo '</a></div>';

	if ( $pass_code && $pass_expected && !$problem )
		echo '<div id="collapse'.$test_count.'" class="accordion-body collapse">';
	else
		echo '<div id="collapse'.$test_count.'" class="accordion-body collapse in">';


	echo '<div class="accordion-inner">';
	echo '<dl>';

	//Print HTTP Code Status and EXPECTED status (if exists) and Automated Data Check Status (if exists)
	echo '<dt class="li-divider">Status:</dt>';
	echo '<dd class="list">'.$test_status.'</dd>';

	//Prints authentication used
	echo '<dt class="li-divider">Permission:</dt>';
	echo '<dd class="list">'.$response['permission'].'</dd>';

	//Prints HTTP method and URL
	echo '<dt class="li-divider">Request:</dt>';
	echo '<dd class="list">'.$response['method'].': '.$response['url'];
	echo '<pre style="font-size:10px">';
	echo print_r($response['request']['headers']);
	echo print_r($response['request']['body']);
	echo '</pre>';
	echo '</dd>';

	//Prints response in collapsible div
	echo '<dt class="li-divider">Response:</dt>';
	echo '<dd class="list">';
	echo '<div class="accordion" id="NestedAccordian"'.$test_count.'>';
	echo '<div class="accordion-group">';
	echo '<div class="accordion-heading">';
    echo '<a class="accordion-toggle" data-toggle="collapse" data-parent="#NestedAccordian'.$test_count.'" href="#NestedCollapse'.$test_count.'">';
	echo '<h6>HTTP '.$response['code'].'</h6>';
	echo '</a></div>';
	echo '<div id="NestedCollapse'.$test_count.'" class="accordion-body collapse">';
	echo '<div class="accordion-inner">';
	if ( $expected == NULL || $special_case )
		echo'<pre>'.print_r($response, true).'</pre>';//For a more readable response
	else{
		//assumes multiple results so readable response unnecessary
		$data_long=json_encode($response, true);
		echo $data_long;
	}
	echo '</div></div></div></div>';
	echo '</dd>';
	echo '</dl>';

	echo '</div></div></div></div>';

	$test_count++;
}

//Outputs headings in a formatted fashion
function printLabel($key, $title, $small=false){
	if( $small )
		echo '<h5>'.$title.'</h5>';
	else
		echo '<a name="'.$key.'"><h3>'.$title.'</h3></a>';
}

//Checks the common fields between the actual array and the desired array are equal
function checkArraysEqual($actual, $desired){
	if ( $actual == NULL || $desired == NULL ||
	     $actual==""     || $desired == "" ||
		 empty($actual)  || empty($desired) )
		return true;

	$problem = false;
	foreach( array_keys($actual) as $key ){
		if ($problem)
			break;
		if ( array_key_exists($key, $desired) ){
			if (is_array($actual[$key])){
				foreach (array_keys($actual[$key]) as $nested_key ){
					if( $nested_key == "timestamp" ){
						$actual_timestamp = $actual[$key][$nested_key];
						$desired_timestamp = $desired[$key][$nested_key];
						if (!is_numeric($actual[$key]))
							$actual_timestamp = strtotime($actual[$key][$nested_key])*1000;
						if (!is_numeric($desired[$key]))
							$desired_timestamp = strtotime($desired[$key][$nested_key])*1000;

						if ($actual_timestamp != $desired_timestamp)
							$problem = true;
					}else{
						if ($key == "metadata"){
							if($actual[$key][$nested_key] != $desired[$key][$nested_key])
								$problem = true;
							else{
								if (!in_array($actual[$key][$nested_key], $desired[$key]))
									$problem = true;
							}
						}
					}
				}
			}else{
				if ($key == "timestamp"){
					$actual_timestamp = $actual[$key];
					$desired_timestamp = $desired[$key];
					if (!is_numeric($actual[$key]))
						$actual_timestamp = strtotime($actual[$key])*1000;
					if (!is_numeric($desired[$key]))
						$desired_timestamp = strtotime($desired[$key])*1000;

					if ($actual_timestamp != $desired_timestamp)
						$problem = true;
				}else {
					if($actual[$key] != $desired[$key])
						$problem = true;
				}
			}
		}
	}

	// Format Response (rayh -- added this otherwise impossible to debug)
	if ($problem == true) {
			echo 'EXPECTED';
			echo json_encode($desired);
			echo '<br/>';
			echo 'ACTUAL';
			echo json_encode($actual);
			echo '<br/>';
	}

	return $problem;
}

//Checks past timestamp(ms) is smaller than recent timestamp(ms)
function checkDates ($past, $recent){
	if ( $past == NULL || $recent == NULL ||
		 $past==""     || $recent == "" ||
		 empty($past)  || empty($recent) )
			return true;
	if (!is_numeric($past))
		$past = strtotime($past)*1000;
	if (!is_numeric($recent))
		$recent = strtotime($recent)*1000;

	$problem = false;
	if ($past > $recent)
		$problem = true;

	return $problem;
}

//Checks tags/sensors have the expected name fields
function checkTagsOrSensors ($received, $expected){
	$problem = false;
	foreach( $received as $value ){
		if (!in_array($value['name'], $expected)){
			$problem = true;
			break;
		}
	}
	return $problem;
}

//Checks error message contains expected keywords
function checkError($error, $keyword, $developerKeyword=null) {
	$problem = false;
	$innerProblem = ($developerKeyword != null);

	if ( !stristr($error['error']['message'], $keyword) ) {
		$problem = true;
	}

    // Check the developer message
	if ( array_key_exists('developerMessage', $error['error'])
			&& stristr($error['error']['developerMessage'][0], $developerKeyword) ){
		$innerProblem = false;
	}

    if ($innerProblem) {
      foreach($error['error']['moreInfo'] as $key => $msg ) {
        if (stristr($key, $developerKeyword)) {
          $innerProblem = false;
          break;
        }

        if (stristr($msg, $developerKeyword)) {
          $innerProblem = false;
          break;
        }
      }
    }

	return $problem || $innerProblem;
}



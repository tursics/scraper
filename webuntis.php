<?php
	$uriBase = 'https://mese.webuntis.com/WebUntis/';
	$cookie = tempnam ("/tmp", "webuntisCookie");
//	$cookie = './temp/tempWebuntisCookie';
	$userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 12_3_1) AppleWebKit (KHTML, like Gecko) Chrome Safari (compatible; Tursics-Bot; +https://www.tursics.de)';
	$timeout = 5;
	$filterCity = 'Berlin';
	$SCHOOLQUERY_BACKEND_URL = '';
	$SCHOOLQUERY_REQUEST_PREFIX = '';
	$SCHOOLQUERY_DEBOUNCE_THRESHOLD_MS = '';
	$MAX_SCHOOLS = 0;
	$UNTIS_URL = '';

	function fetch($uri, $referer = null, $post = null) {
		global $cookie;
		global $timeout;
		global $userAgent;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

		if ($post != null) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
		}

		if ($referer != null) {
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo '<p style="color:red">Error: ' . curl_error($ch) . '</p>';
		}
		curl_close($ch);
		return $result;
    }

	function getEnvironment() {
		global $SCHOOLQUERY_BACKEND_URL;
		global $SCHOOLQUERY_REQUEST_PREFIX;
		global $SCHOOLQUERY_DEBOUNCE_THRESHOLD_MS;
		global $MAX_SCHOOLS;
		global $UNTIS_URL;

		$uri = 'https://webuntis.com/environment.json';

		$html = fetch($uri);
		$json = json_decode($html);

		$SCHOOLQUERY_BACKEND_URL = $json->SCHOOLQUERY_BACKEND_URL;
		$SCHOOLQUERY_REQUEST_PREFIX = $json->SCHOOLQUERY_REQUEST_PREFIX;
		$SCHOOLQUERY_DEBOUNCE_THRESHOLD_MS = intval($json->SCHOOLQUERY_DEBOUNCE_THRESHOLD_MS);
		$MAX_SCHOOLS = intval($json->MAX_SCHOOLS);
		$UNTIS_URL = $json->UNTIS_URL;
	}

	function getTimeTable($serverUrl) {
		$uri = $serverUrl . '#/basic/timetable';

		$html = fetch($uri, $referer);

		$dom = new DOMDocument;
		@$dom->loadHTML($html);

		$text = $dom->textContent;

		return $uri;
	}

	function getPageConfig($referer, $schoolId, $date) {
		global $uriBase;
		$type = 1;
		$formatId = 1;
		$isMyTimetableSelected = 'false';

		$uri = $uriBase . 'api/public/timetable/weekly/pageconfig';
		$uri .= '?type=' . $type . '&id=' . $schoolId . '&date=' . $date . '&formatId=' . $formatId . '&isMyTimetableSelected=' . $isMyTimetableSelected;

		$html = fetch($uri, $referer);
		$json = json_decode($html);

		$line = '';
		foreach($json->data->elements as $element) {
//			$line .= $element->displayname . ' (' . $element->longName . '), Klassenlehrer*in ' . $element->classteacher->longName . ', Kapazität von ' . $element->capacity;
//			$line .= $element->displayname . ' ' . $element->classteacher->longName . ' ';
			$line .= $element->displayname . ',';
		}

		return $line;
	}

	function getSchoolQuery($search) {
		global $SCHOOLQUERY_BACKEND_URL;
		global $SCHOOLQUERY_REQUEST_PREFIX;
		global $filterCity;

		$now = time() . '000';
		$uri = $SCHOOLQUERY_BACKEND_URL;
		$post = (object) [
			'id' => $SCHOOLQUERY_REQUEST_PREFIX . $now,
			'jsonrpc' => '2.0',
			'method' => 'searchSchool',
			'params' => [
				(object) [
					'search' => $search,
    			]
			],
		];
		$html = fetch($uri, $referer, $post);
		$json = json_decode($html);

		if ($json->id == 'error') {
			echo '<p style="color:red">Error: ' . $json->error->message . '</p>';
			return null;
		}

		$result = array();
		foreach($json->result->schools as $school) {
			if (stripos($school->address, $filterCity) !== false) {
				$result[] = $school;
			}
		}

		if (count($result) > 1) {
			foreach($result as $school) {
				echo $school->displayName . ': ' . $school->address . '<br>';
			}
			return null;
		}

		return $result[0];
	}

	getEnvironment();
	$school = getSchoolQuery('planck max');

	if ($school != null) {
		echo 'displayName: ' . $school->displayName . '<br>';
		echo 'address: ' . $school->address . '<br>';
		echo '<br>';

		$uri = getTimeTable($school->serverUrl);
		sleep(1);

		$time = strtotime(date('Y-m-01'));
		$lastLine = '';
		$emptyLine = 0;

		while ($emptyLine < 3) {
			$date = date('Y-m-d', $time);
			$line = getPageConfig($uri, $school->schoolId, $date);

			if ($lastLine != $line) {
				$emptyLine = 0;
				$lastLine = $line;

				if ($line != '') {
					echo $date . ' ' . $line . '<br>';
				}
			}
			if ($line == '') {
				++$emptyLine;
			}

			$time = strtotime('-1 month', $time);
		}
	}

	unlink($cookie);
?>
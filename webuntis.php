<?php
	$uriBase = 'https://mese.webuntis.com/WebUntis/';
	$cookie = tempnam ("/tmp", "webuntisCookie");
//	$cookie = './temp/tempWebuntisCookie';
	$userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 12_3_1) AppleWebKit (KHTML, like Gecko) Chrome Safari (compatible; Tursics-Bot; +https://www.tursics.de)';
	$timeout = 5;

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
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
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

	function getTimeTable($school) {
		global $uriBase;
		$uri = $uriBase . '?school=' . $school . '#/basic/timetable';

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

		// IDC_KLASSE
		var_dump($json->data->elementTypeLabel);
		echo('<br><br>');

		foreach($json->data->elements as $element) {
			echo($element->displayname . ' (' . $element->longName . '), Klassenlehrer*in ' . $element->classteacher->longName . ', Kapazität von ' . $element->capacity);
			echo('<br>');
		}
	}

	$uri = getTimeTable('max-planck-gymnasium-berlin');
	sleep(1);
	getPageConfig($uri, 390, '2022-04-25');

	unlink($cookie);
?>
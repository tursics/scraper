<?php
	$cookie = tempnam ("/tmp", "schulverzeichnisCookie");
//	$cookie = './temp/tempCookie';
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

	function step0getSearchForm() {
		$uri = 'https://www.bildung.berlin.de/Schulverzeichnis/';

		$output = fetch($uri);

		echo $output;
	}

	function step1getListOfAllSchools() {
		$base = 'https://www.bildung.berlin.de/Schulverzeichnis/';
		$uri = $base . 'SchulListe.aspx';

		$html = fetch($uri);

		$dom = new DOMDocument;
		@$dom->loadHTML($html);

		$list = $dom->getElementById('DataListSchulen');
		$links = $list->getElementsByTagName('a');

		$ret = array();
		foreach ($links as $link){
			$href = str_replace(' ', '%20', $link->getAttribute('href'));
			$ret[] = $base . $href;
		}

		return $ret;
	}

	function step2getSchoolOverview($uri) {
		$html = fetch($uri);

		$dom = new DOMDocument;
		@$dom->loadHTML($html);

		$nav = $dom->getElementById('portrait_hauptnavi');
		$info = $dom->getElementById('divAllgemein');

		$title = $dom->getElementById('ContentPlaceHolderMenuListe_lblSchulname');
		echo $title->textContent . '<br>';

		$type = $dom->getElementById('ContentPlaceHolderMenuListe_lblSchulart');
		echo $type->textContent . '<br>';

		$street = $dom->getElementById('ContentPlaceHolderMenuListe_lblStrasse');
		echo $street->textContent . '<br>';

		$city = $dom->getElementById('ContentPlaceHolderMenuListe_lblOrt');
		echo $city->textContent . '<br>';

		$liStudents = $dom->getElementById('NaviSchuelerschaft');
		$linkStudents = $liStudents->getElementsByTagName('a');
		echo $linkStudents[0]->getAttribute('href') . '<br>';
	}

	function step3getStudents($referer) {
		$base = 'https://www.bildung.berlin.de/Schulverzeichnis/';
		$uri = $base . 'schuelerschaft.aspx';
//		$uri = $base . 'schuelerschaft.aspx?view=jgs';
//		$uri = $base . 'schuelerschaft.aspx?view=jgs&jahr=2018/19';

		$html = fetch($uri, $referer);

		$dom = new DOMDocument;
		@$dom->loadHTML($html);
		
		echo '<br><br>';
		echo $referer.'<br>';
		echo $uri.'<br>';
		echo '<br><br>';
		echo $html;
	}

//	step0getSearchForm();
	$schools = step1getListOfAllSchools();
	$id = 150;
	sleep(1);
	step2getSchoolOverview($schools[$id]);
	sleep(1);
	step3getStudents($schools[$id]);

	unlink($cookie);
?>
<?php
/*

	Template.php
	200712192324
	201007211800
	201008010000

	(C) eZH 2007,2010

	mailto:emakhrov@gmail.com
	xmpp:ezh@neko.im

*/
class Template {

	var $data;

	function __construct($data) {
		if (strlen($data) < 200 && file_exists($data)) $data = file_get_contents($data);
		$this->data = $data;

	}

	function getData() {

		return $this->data;

	}

	function build($content, $clear = false) {

		// result html
		$result = $this->data;

		// auto create inverted boolean keys
		/*
		foreach ($content as $key => $value) {
			if ($key[0] == '_' && $key[1] != '!') {
				$content['_!' . substr($key, 1)] = !$value;
			}
		}
		*/
		$keys = array_keys($content);
		rsort($keys);

		// process all keys
		//foreach ($content as $key => $value) {
		while($key = array_pop($keys)) {
			$value = $content[$key];
			// while key isin template - process
			while (($start = strpos($result, '{'.$key.'}')) !== FALSE) {
			
				// key is looping key
				if ($key[0] == '+') {

					// find ending tag
					$end = strpos($result, '{+'.$key.'}');

					$loopdatastart = $start + strlen($key) + 2;

					$localTemplate = new Template(substr($result, $loopdatastart, $end - $loopdatastart));

					$localResult = '';
					foreach ($value as $v2) $localResult .= $localTemplate->build($v2);

					$result = substr_replace($result, $localResult, $start,
						$end + 3 + strlen($key) - $start);

				// key is a switched key
				} else if ($key[0] == '_') {
					if (!is_bool($value) && !is_numeric($value)) {
						$end = strpos($result, '{_'.$key.'}');

						$inner = $start + strlen($key) + 2;

						$cases = explode(',', substr($result, $inner, $end - $inner));
						foreach($cases as $case) {
							$casekey = $key . $case;
							array_push($keys, $key . $case);
							$content[$key . $case] = $value == $case;
						}
						$value = false;

						
					} else if ($key[1] != '!') {
						array_push($keys, '_!' . substr($key, 1));
						$content['_!' . substr($key, 1)] = !$value;
					}

					// true - leave contents - clear only key marks
					if ($value) {

						$result = str_replace('{'.$key.'}', '', $result);
						$result = str_replace('{_'.$key.'}', '', $result);

					// clear entire content
					} else {
						$result = substr_replace($result, '', $start,
							strpos($result, '{_'.$key.'}') + 3 + strlen($key) - $start );
					}

				// key is general key
				} else $result = str_replace('{'.$key.'}', $value, $result);

			}


		} // foreach keys
		// clean for unfilled keys, if needed
		if ($clear) {
			$result = preg_replace('/\{[\_\+A-Z]+\}/', '', $result);
		}


		return $result;

	}

}


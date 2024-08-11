<?php

require_once __DIR__.'/../lib/bootstrap.php';

/**
 * Generates compact regex that matches any string from list.
 *
 * Example:
 * 	input  ["is_null", "is_int", "is_integer"]
 * 	output "is_(null|int(eger)?)"
 *
 * @param []string $list of keywords for regex match
 */
function generateCompactRegex(iterable $list): string {
	$buckets = [];
	$optional = false;
	foreach ($list as $string) {
		if (empty($string)) {
			$optional = true;
		} else {
			$buckets[mb_substr($string, 0, 1)][] = mb_substr($string, 1);
		}
	}
	if ($buckets) {
		$patterns = [];
		foreach ($buckets as $key => $value) {
			$patterns[] = $key.generateCompactRegex($value);
		}
		if ($optional) {
			return '('.implode('|', $patterns).')?';
		} elseif (isset($patterns[1])) {
			return '('.implode('|', $patterns).')';
		} else {
			return $patterns[0];
		}
	}

	return '';
}

function processList(iterable $list) {
	usort($list, fn ($a, $b) => unpack('C*', $a) <=> unpack('C*', $b));

	return sprintf(
		'(?i)\b%s\b',
		generateCompactRegex(
			array_map(
				fn ($item) => preg_quote($item),
				$list,
			),
		)
	);
}

$sections = [];
$completions = [];
foreach (new DirectoryIterator(__DIR__.'/docs/en') as $doc) {
	if ($doc->isDot() || $doc->isDir()) {
		continue;
	}

	// if ($doc != 'pdo.getavailabledrivers.html') {
	// 	continue;
	// }

	// if ($doc != 'function.getenv.html') {
	// 	continue;
	// }

	// if ($doc != 'datetime.construct.html') {
	// 	continue;
	// }

	// if (strpos($doc, 'weakref') === false) {
	// 	continue;
	// }

	$dom = new DOMDocument();
	$dom->loadHTMLFile($doc->getPathname(), \LIBXML_NOERROR);

	$xpath = new DOMXpath($dom);

	$section = array_map(
		fn ($href) => explode('.', $href->value)[1],
		array_filter(
			iterator_to_array($xpath->query("//div[@id='breadcrumbs']//a/@href")),
			fn ($href) => str_starts_with($href->value, 'ref.')
		),
	);

	foreach ($xpath->query("//div[@class='refentry']") as $reference) {
		$node = $xpath->query("div[contains(@class, 'description')]", $reference)->item(0);
		$description = $xpath->query("div/p[@class='refpurpose']/span[@class='dc-title']", $reference)->item(0)?->nodeValue;

		foreach ($xpath->query("div[contains(@class, 'dc-description')]", $node) as $synopsis) {
			$name = $xpath->query("span[@class='methodname']", $synopsis)->item(0)->nodeValue;
			// skip namespaced functions for now atleast
			if (strpos($name, '\\')) {
				continue;
			}
			// only include constructor as class completion
			if (strpos($name, '::')) {
				if (!strpos($name, '::__construct')) {
					continue;
				}
				$name = strstr($name, '::', true);
			}

			$signature = preg_replace("/\n\s+|\s{2,}/", ' ', simplexml_import_dom($synopsis)->asXML());

			$completions[strtolower($name)]['name'] ??= $name;
			$completions[strtolower($name)]['signatures'][] = trim(html_entity_decode(strip_tags($signature)));
			$completions[strtolower($name)]['description'] = trim($description);

			if ($section) {
				$sections[reset($section)][] = $name;
			}
		}
	}

	foreach ($xpath->query("//div[@class='reference']") as $reference) {
		$name = $xpath->query("//div[@class='classsynopsis']/div[@class='classsynopsisinfo']/span[@class='ooclass']/strong[@class='classname']")->item(0)?->nodeValue;
		if (!$name || strpos($name, '::') || strpos($name, '\\')) {
			continue;
		}
		// this name is the most correct
		$completions[strtolower($name)]['name'] = $name;
		$completions[strtolower($name)]['signatures'] ??= [];
	}
}

ksort($completions);
ksort($sections);

// print_r(array_map(
// 	fn ($section) => processList($section),
// 	$sections,
// ));
// exit;

foreach ($sections as $section => $functions) {
	echo sprintf(
		<<<XML
		<dict>
			<key>match</key>
			<string>%s</string>
			<key>name</key>
			<string>support.function.%s.php</string>
		</dict>
		XML,
		processList($functions),
		$section,
	);
	echo "\n";
}
// exit;

// foreach ($completions as ['name' => $name]) {
// 	echo "<string>{$name}</string>\n";
// }
// exit;

// function spacesToTabs(string $input, int $length = 4): string {
// 	// double {} around $length because string interpolation eats one set away
// 	return preg_replace("/(?:\G|^) {{$length}}/m", "\t", $input);
// }
//
// $completionsOfInitial = function (string $char) use ($completions) {
// 	foreach ($completions as ['name' => $completion, 'signatures' => $signatures]) {
// 		if ($char === strtolower($completion[0])) {
// 			if (!$signatures) {
// 				continue;
// 			}
// 			yield $completion => $signatures;
// 		}
// 	}
// };
//
// foreach (range('a', 'z') as $initial) {
// 	$completionsJson = json_encode(iterator_to_array($completionsOfInitial($initial)), JSON_PRETTY_PRINT);
// 	file_put_contents(
// 		__DIR__.'/../completions/'.$initial.'.json',
// 		spacesToTabs($completionsJson),
// 	);
// }
//
// $all = [];
// $initial = 'a';
// foreach ($completions as ['name' => $completion, 'signatures' => $signatures]) {
// 	if ($initial === strtolower($completion[0])) {
// 		if (!$signatures) {
// 			continue;
// 		}
// 		$all[$completion] = $signatures;
// 	}
// }
// var_export($all);
// exit;

$all = [];
foreach ($completions as ['name' => $completion, 'signatures' => $signatures]) {
	if (!$signatures) {
		continue;
	}
	$all[$completion] = $signatures;
}

file_put_contents(
	__DIR__.'/../resources/completions.json',
	json_encode($all, \JSON_PRETTY_PRINT),
);

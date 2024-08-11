<?php

// $data = <<<JSON
// 	{
// 		"classes": [
// 			"APCIterator",
// 			"APCUIterator",
// 			"AppendIterator",
// 			"ArrayAccess",
// 			"ArrayIterator",
// 			"ArrayObject"
// 		],
// 		"sections": {
// 			"apc": [
// 				"apc_compile_file",
// 				"apc_inc",
// 				"apc_dec",
// 				"apc_bin_load",
// 				"apc_cas",
// 				"apc_clear_cache",
// 				"apc_store",
// 				"apc_exists",
// 				"apc_cache_info",
// 				"apc_delete_file",
// 				"apc_load_constants",
// 				"apc_delete",
// 				"apc_define_constants",
// 				"apc_bin_loadfile",
// 				"apc_sma_info",
// 				"apc_bin_dump",
// 				"apc_bin_dumpfile",
// 				"apc_add",
// 				"apc_fetch"
// 			]
// 		}
// 	}
// 	JSON;
//
// $data = json_decode($data);

$data = json_decode(file_get_contents(__DIR__.'/functions.json'));

$patterns = [
	'support.class.builtin.php' => processList($data->classes),
	'support.function.alias.php' => processList(['is_int', 'is_integer']),
];

foreach ($data->sections as $section => $functions) {
	$patterns["support.function.{$section}.php"] = processList($functions);
}

$syntaxFilePath = __DIR__.'/../../Syntaxes/PHP.plist';

function patternsToArrayXml($patterns) {
	$outputXml = new \DOMDocument();
	$outputXml->formatOutput = true;
	$new = $outputXml->appendChild($outputXml->createElement('array'));

	foreach ($patterns as $section => $regex) {
		$dict = $outputXml->createElement('dict');
		$dict->appendChild($outputXml->createElement('key', 'match'));
		$dict->appendChild($outputXml->createElement('string', $regex));
		$dict->appendChild($outputXml->createElement('key', 'name'));
		$dict->appendChild($outputXml->createElement('string', $section));
		$new->appendChild($dict);
	}

	$outputXml->appendChild($new);
	$out = preg_replace_callback(
		'/^( +)?<[^\?]/m',
		function ($a) {
			return str_repeat("\t", (int) (strlen($a[1] ?? '') / 2) + 3).'<'.$a[0][-1];
		},
		$outputXml->saveXml()
	);

	return $out;
}

function writePlistAt($dom, string $path, $content): void {
	$path = explode('/', $path);
	$path = implode('/', array_map(fn ($name) => "key[.='{$name}']/following-sibling::dict[1]", $path));
	$path = "/plist/dict/{$path}";
	$path = substr_replace($path, 'array', -7);

	$outputXml = new \DOMDocument();
	$outputXml->loadXml($content);

	$xpath = new \DOMXpath($dom);
	$node = $xpath->query($path)->item(0);
	$node->parentNode->replaceChild(
		$dom->importNode($outputXml->documentElement, true),
		$node,
	);
}

$syntaxXml = new \DOMDocument();
$syntaxXml->load($syntaxFilePath);

writePlistAt($syntaxXml, 'repository/support/patterns', patternsToArrayXml($patterns));

// $support = $xpath->query("/plist/dict[key='repository']/dict/key[.='support']/following-sibling::dict[1]/key[.='patterns']/following-sibling::array")->item(0);
//
// $outputXml = new \DOMDocument;
// $outputXml->loadXml(patternsToArrayXml($patterns));
//
// $support->parentNode->replaceChild($syntaxXml->importNode($outputXml->documentElement, true), $support);

// <dict>
// 	<key>match</key>
// 	<string>(?i)\b(s(trval|e(ttype|rialize))|i(s(set|_(s(calar|tring)|nu(ll|meric)|callable|i(nt(eger)?|terable)|object|double|float|long|array|re(source|al)|bool))|ntval|mport_request_variables)|d(oubleval|ebug_zval_dump)|unse(t|rialize)|print_r|empty|var_(dump|export)|floatval|get(type|_(defined_vars|resource_type))|boolval)\b</string>
// 	<key>name</key>
// 	<string>support.function.var.php</string>
// </dict>

file_put_contents($syntaxFilePath, $syntaxXml->saveXML());

function processList(iterable $list) {
	usort($list, fn ($a, $b) => unpack('C*', $a) <=> unpack('C*', $b));

	return sprintf(
		'(?i)\b%s\b',
		generateCompactRegex(
			fn ($item) => preg_quote($item),
			$list,
		)
	);
}

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

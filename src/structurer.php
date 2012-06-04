#!/usr/bin/php
<?php
// Structurer v0.1
// by Stéphan Zych (monkeymonk.be)
/*
$input = <<<EOT
css/
css/reset.css
css/styles.css
js/
js/lib/
js/lib/jquery.js
js/scripts.js
img/
index.php
EOT;
*/

$input = '';
$fp = fopen('php://stdin', 'r');
$base = getenv('CODA_SITE_LOCAL_PATH') . '/';

while($line = fgets($fp, 1024))	$input .= $line;

fclose($fp);

$input = explode("\n", $input);

$tmp = array();
foreach($input as $key => $val){
	$tmp[$val] = $val;
}

function explodeTree($array, $delimiter = '_', $baseval = false){
	if(!is_array($array))	return false;
	
	$splitRE = '/' . preg_quote($delimiter, '/') . '/';
	$returnArr = array();
	
	foreach($array as $key => $val){
		if(preg_match('/:+(.*)/', $val, $url)){
			$val = str_replace($url[0], '', $val);
			$key = str_replace($url[0], '', $key);
			
			echo var_dump($url);
			
			$url[$key] = $url[1];
			unset($array[$key]);
		}
		
		$parts = preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
		$leafPart = array_pop($parts);
 
		// Build parent structure
		$parentArr = &$returnArr;
		foreach($parts as $part){
			if(!isset($parentArr[$part])){
				$parentArr[$part] = array();
			} elseif(!is_array($parentArr[$part])){
				if($baseval){
					$parentArr[$part] = array('__base_val' => $parentArr[$part]);
				}else{
					$parentArr[$part] = array();
				}
			}
			$parentArr = &$parentArr[$part];
		}
 
		// Add the final part to the structure
		if(empty($parentArr[$leafPart])){
			$parentArr[$leafPart] = $leafPart;
			if(isset($url) && array_key_exists($val, $url)){
				$parentArr[$leafPart] = $url[$val];
			}
		} elseif($baseval && is_array($parentArr[$leafPart])){
			$parentArr[$leafPart]['__base_val'] = $val;
		}
	}
	return $returnArr;
} // explodeTree

function create($arr, $base){
	foreach($arr as $key => $val){
		if(strpos($key, '.')){echo '<br/>' . $base . $key;
			if(is_file($base . $key)){
				$file = fopen($base . $key, 'a+');
				if(strpos($val, '/')){
					$content = curl_init($val);
					curl_setopt($content, CURLOPT_TIMEOUT, 50);
					curl_setopt($content, CURLOPT_FILE, $file);
					curl_setopt($content, CURLOPT_FOLLOWLOCATION, true);
					curl_exec($content);
					curl_close($content);
				}
				fclose($file);
			} else {
				$file = fopen($base . $key, 'w+');
				if(strpos($val, '/')){
					$content = curl_init($val);
					curl_setopt($content, CURLOPT_TIMEOUT, 50);
					curl_setopt($content, CURLOPT_FILE, $file);
					curl_setopt($content, CURLOPT_FOLLOWLOCATION, true);
					curl_exec($content);
					curl_close($content);
				}
				fclose($file);
			}
		} else {echo '<br/>' . $base . $key;
			if(!is_dir($base . $key)){
				mkdir($base . $key);
			}
		}
		if(is_array($val))	create($val, $base . $key . '/');
	}
} // create

$input = explodeTree($tmp, '/');

create($input, $base);
exit();
?>
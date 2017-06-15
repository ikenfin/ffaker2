<?php
	/*
		ffaker.php - runner script for FFaker

		This file is part of ffaker.phar project
	*/
	define('CALL_SCRIPT_NAME', basename($argv[0]));

	$libsPath = Phar::running();

	if(trim($libsPath) == '')
		$libsPath = __DIR__;

	require_once($libsPath . '/autoload.php');
	require_once($libsPath . '/helpers/fs.php');

	$db_config = $structure = $words = null;
	$interactive = false;
	$count = 0;

	$structure_source = 'php://stdin';

	$progressCallback = function(){};

	$structInputFormat = 'JSON';

	$structInputFormats = require_once($libsPath . "/var/ExportFormat.php");

	$short = array(
		'd:',	// database connection config file
		's:',   // php meta structure
		'w:',   // words, that will be used for random content char fields
		'c:',	// count of records to create
		'h::',  // call help menu
		'i',	// interactive - show progress
		'v',	// call version menu
		'f:'	// config format (p - php, s - serialized, j - json) (PHP format cannot be used with STDIN!)
	);

	$options = getopt(implode('', $short), array());

	if(!$options) {
		echo "No valid arguments passed! Please see help screen with " . CALL_SCRIPT_NAME . " -h\n";
		exit(1);
	}

	foreach($options as $option => $value) {

		switch($option) {
			case 'i' :
				$interactive = true;
				break;
			case 'd' :
				$file = resolve_path($value);
				if(is_file($file))
					$db_config = require_once($file);
				break;
			case 's' :
				if($value == '-') {
					break;
				}
				$structure_source = resolve_path($value);				
				break;
			case 'f' :
				if(in_array($value, array_keys($structInputFormats)))
					$structInputFormat = $structInputFormats[$value];
				else
					__error("Wrong input format!\n");
				break;
			case 'w' :
				$file = resolve_path($value);
				if(is_file($file))
					$words = require_once($file);
				break;
			case 'c' :
				$count = $value;
				break;
			case 'h' :
				print_ffaker_help($value, \ffaker\app\FFaker::version());
				exit(0);
			case 'v' :
				echo "FFaker version [" . \ffaker\app\FFaker::version() . "]\n";
				exit(0);
			default:
				echo "Wrong option! Please see help screen with " . CALL_SCRIPT_NAME . " -h\n";
				exit(1);
		}
	}

	if(is_file($structure_source)) {
		$structure = require_once($structure_source);
	}
	elseif($structure_source == 'php://stdin' && $structInputFormat != 'PHP') {
		$structure_dump = file_get_contents($structure_source);

		if($structInputFormat == 'JSON') {
			$structure = json_decode($structure_dump, true);
		}
		elseif($structInputFormat == 'Serialized') {
			$structure = unserialize($structure_dump);
		}
	}

	// packed db_config will be used if was not directly set
	if(isset($structure['__db_config__'])) {
		if(!$db_config) {
			$db_config = $structure['__db_config__'];
		}

		unset($structure['__db_config__']);
	}

	if(!$db_config || !$structure) {
		$msg = <<<MSG
You must specify db_config with -d <database.php> and structure with -s <struct.php>! Or use __db_config__ in structure.

MSG;
		__error($msg);
	}

	if($interactive) {
		$progressCallback = function($item, $max, $saved_status) {
			if($saved_status)
				echo "\rDone $item from $max";
			else
				echo "\rFailed $item";

			if($item == $max)
				echo "\n";
		};
	}

	$ffaker = new \ffaker\app\FFaker($db_config);

	if(!empty($structure) && count($structure) > 0) {

		if(!is_plain_array($structure)) {
			$structure = [$structure];
		}

		foreach($structure as $key => $struct) {
			if($interactive)
				echo "Fill table {" . $struct['__table__'] . "}\n";
			
			$ffaker->run($count, $struct, $words, $progressCallback);
		}
	}
	else {
		__error("Invalid struct definition! See " . CALL_SCRIPT_NAME . "--help structure\n");
	}

	echo "\n";
	exit(0);
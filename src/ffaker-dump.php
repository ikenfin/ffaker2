#!/usr/bin/php
<?php
	/*
		ffaker-dump.php - runner script for FFakerDumper

		This file is part of ffaker.phar project
	*/
	define('CALL_SCRIPT_NAME', $argv[0]);
	
	require_once(__DIR__ . '/autoload.php');
	require_once('helpers/fs.php');

	$export_formats = require_once('var/ExportFormat.php');

	$db_config = null;
	$out_file = "php://stdout";
	$pack_db_config = false;
	$export_tables = [];
	$export_format = 'JSON';

	$short = array(
		'd:', // database config file
		'w:', // write to file (defaults - STDIN)
		'p',  // pack db config to out
		't:', // export only tables (format table1,table2,table3)
		'h',  // show help screen
		'f:', // export format
		'v'	  // prints dumper version
	);

	$options = getopt(implode('', $short), array());

	foreach($options as $option => $value) {

		switch($option) {
			case 'h':
				print_ffaker_dump_help();
				exit(0);
			case 'w':
				$out_file = resolve_path($value);
				break;
			case 'p':
				$pack_db_config = true;
				break;
			case 'f':
				if(in_array($value, array_keys($export_formats))) {
					$export_format = $export_formats[$value];
				}
				else {
					__error("Wrong export format!\n");
				}
				break;
			case 't':
				$export_tables = explode(',', $value);
				break;
			case 'd':
				// $db_config = require_once(resolve_path($value));
				$db_config = ['url' => $value];
				break;
			case 'v':
				echo "FFakerDumper version [" . \ffaker\app\FFakerDumper::version() . "]\n";
				exit(0);
		}
	}

	if(!$db_config) {
		__error("You must specify db_config with -d <database.php>!\n");
	}

	try {
		// run dumper
		$dumper = new ffaker\app\FFakerDumper($db_config, $export_tables);
		$dumper->dump($out_file, $pack_db_config, $export_format);
	}
	catch(Exception $e) {
		__error($e->getMessage() . "\n");
	}

	echo "\n";
	exit(0);
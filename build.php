<?php
	/*
		build.php - builds php archives

		This file is part of ffaker.phar project
	*/

	if(ini_get('phar.readonly') == TRUE) {
		echo "Cannot build phar due to PHP settings!" . PHP_EOL;
		echo "Please check \"phar.readonly\" setting to be \"Off\" in your php.ini!" . PHP_EOL;
		exit(1);
	}

	$PHP_PATH = `which php`;

	if(!$PHP_PATH) {
		echo "[WARN] - path to php not found! Set it to /usr/bin/php\n";
		$PHP_PATH = "/usr/bin/php \n";
	}

	function build_phar($name, $stub) {
		global $PHP_PATH;
		
		echo "Building {" . $name . "} . . . \n";
		
		$phar = new Phar('build/' . $name, 0, $name);
		$phar->buildFromDirectory(dirname(__FILE__) . '/pre_build');
		$defaultStub = $phar->createDefaultStub($stub);

		$stub = '#!' . $PHP_PATH . $defaultStub;

		$phar->setStub($stub);
		echo "Done!\n";
	}

	$target = "ffaker";

	$targets = [
		'ffaker' => [
			'name' => 'ffaker.phar',
			'stub' => 'ffaker.php'
		],
		'ffaker-dump' => [
			'name' => 'ffaker-dump.phar',
			'stub' => 'ffaker-dump.php'
		]
	];

	if(isset($argv[1]))
		$target = $argv[1];

	if($target == 'all') {
		foreach($targets as $target) {
			build_phar($target['name'], $target['stub']);
		}
		exit(0);
	}

	if(in_array($target, array_keys($targets))) {
		build_phar($targets[$target]['name'], $targets[$target]['stub']);
	}

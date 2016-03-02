<?php

	/*
		Helper functions

		This file is part of ffaker.phar project
	*/

	function resolve_path($path) {
		
		$baseName = basename($path);
		if($baseName == '.' || $baseName == '..')
			$baseName = '';
		
		return realpath(dirname($path)) . DIRECTORY_SEPARATOR . $baseName;
	}

	function __error($msg) {
		$stderr = fopen("php://stderr", "a");
		fwrite($stderr, $msg);
		fclose($stderr);
		exit(1);
	}

	function is_plain_array(array $array) {
		return count(array_filter(array_keys($array), 'is_string')) == 0;
	}

	function print_ffaker_help($value = null) {
		echo "FinFaker [" . FinFaker::version() . "] - php util to fill database with test data.\n\n";

		switch($value) {

			case "database" :
				echo <<<DB_HELP
Database config file example:
<?php
	return array('
	    'host' => 'localhost',
		'port' => '3306',
		'db' => 'classified',
		'user' => 'root',
		'pass' => ''
	);

DB_HELP;
				break;

			case "fields":
				echo <<<FIELDS_HELP
Fields:
Field is just an PHP array with structure:

    <field_name> => ['field_type', 'field_size', . . . NAMED OPTIONS . . .]

Exception from the rules - __table__
	__table__ => 'table name' - its just a table name. Its required field!

Field types:

	int - used to represents integer fields from db.
	char - used to represents char/varchar fields from db.
	datetime - used to represents datetime fields from db. Does not require size definition.

Named options are:
	null => boolean
	default => default value

PRIMARY KEY - must have name `pk` (if it auto_increment - set 'auto'=> true in named options)

Relations and dynamic:

You can define related fields, and use them in calculated fields.
To create related field, use 'related' => '<table>.<field>', if relation
must target to same table - use `self` keyword, example:

	'parent_id' => ['int', 11, 'related' => 'self.pk', 'null' => true]

To create calculated field, use 'value' => 'expression', example:

	'level' => ['int', 11, 'value' => 'parent_id.level + 1', 'null' => true, 'default' => 0]

Calculated fields can use values from other fields or from fields of related tables
In example above, we get value of level attribute in related table, and inrement it.

FIELDS_HELP;
				// break; // I think its useful to show structure in same screen.

			case "structure":
				echo <<<STRUCT_HELP
Php db structure example:
	\$struct = [
		'__table__' => 'address_object',
		'pk' => ['id_address_object', 'auto' => true],
		'name' => ['char', 120],
		'parent_id' => ['int', 11, 'related' => 'self.pk', 'null' => true],
		'level' => ['int', 11, 'value' => 'parent_id.level + 1', 'null' => true, 'default' => 0]
	];

STRUCT_HELP;
				break;

			default:
				echo <<<HELP
Options:
	-i -- run program interactive (showing progress)
	-d <filename> -- database connection config file. Type `" . CALL_SCRIPT_NAME . " -h=database` to get more info about
	-s <filename> -- php file with table structure. Type `" . CALL_SCRIPT_NAME . " -h=structure` to get more info about
	-w <filename> -- words file.
	-c <number> -- count of items to create
	-h -- call this menu
		-h=database -- info about database config file
		-h=structure -- info about php metadb structure file
		-h=fields -- info about fields that can be used in structure file
	-v -- show version

HELP;
				break;
		}
	}


	function print_ffaker_dump_help() {
		$script = CALL_SCRIPT_NAME;

		echo "FinFakerDumper [" . FinFakerDumper::version() . "] - php util to dump database to FinFaker format.\n\n";
		echo <<<HELP
Usage:
$script -d <database_config>
FinFakerDumper prints result in stdout, so to save it into file use > operator

	Example: $script -d db.php > struct.php

HELP;

	}
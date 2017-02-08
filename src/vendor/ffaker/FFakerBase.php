<?php

	namespace ffaker;

	// composer autoload file
	require dirname(__FILE__) . '/../autoload.php';

	class FFakerBase {

		const FFVersion = '0.0.1';

		protected $_conn;
		protected $_connParams;

		public function __construct($connParams) {
			$this->_connParams = $connParams;
			$conf = new \Doctrine\DBAL\Configuration();
			$this->_conn = \Doctrine\DBAL\DriverManager::getConnection($connParams, $conf);
		}

		public static function version() {
			return static::FFVersion;
		}

		public function getPrimaryKey($table) {
			$indexes = $this->_conn->getSchemaManager()->listTableIndexes($table);

			foreach($indexes as $index) {
				if($index->isPrimary()) {
					$cols = $index->getColumns();
					if(count($cols) > 0)
						return $cols[0];
				}
			}

			return null;
		}

		public function getFields($table) {
			return $this->_conn->getSchemaManager()->listTableColumns($table);
		}

		public function getTables() {
			return $this->_conn->getSchemaManager()->listTables();
		}

		public function getForeignKeys($table) {
			return $this->_conn->getSchemaManager()->listTableForeignKeys($table);
		}

		/*
			Return default length by field type
			If it cannot get default length - then returns 11
		*/
		public function defaultLength($typeName) {
			$type = \Doctrine\DBAL\Types\Type::getType($typeName);
			$size = $type->getDefaultLength($this->_conn->getDatabasePlatform());

			if($size == null) {
				return 11;
			}
			return $size;
		}

	}

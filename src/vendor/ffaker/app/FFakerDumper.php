<?php
	
	namespace ffaker\app;

	class FFakerDumper extends \ffaker\FFakerBase {

		const FFVersion = '0.0.2';

		protected $_exportTables = false;
		protected $_exportTablesList = [];
		
		protected $outFormat = 'PHP';

		public function __construct($connParams, array $exportTables = []) {
			$this->_exportTablesList = $exportTables;

			if(count($this->_exportTablesList) > 0)
				$this->_exportTables = true;

			parent::__construct($connParams);
		}

		public function dump($outfile, $include_db_config = false, $outFormat = null) {
			$struct = $this->dumpInternal();
			
			if($include_db_config) {
				$struct['__db_config__'] = $this->_connParams;
			}

			if($outFormat != null) {
				$this->outFormat = $outFormat;
			}

			$this->generateFile($outfile, $struct);
		}

		protected function dumpInternal() {
			$tables = $this->getTables();
			$result = [];
			
			foreach($tables as $table) {
				$tName = $table->getName();

				if($this->_exportTables) {
					if(!in_array($tName, $this->_exportTablesList)) {
						continue;
					}
				}

				$struct = [
					'__table__' => $tName
				];

				$pk = $this->getPrimaryKey($tName);
				$fields = $this->getFields($tName);
				$fks = $this->getForeignKeys($tName);
				
				foreach($fields as $data) {
					
					$field = [];

					if($data->getName() == $pk) {
						$struct['pk'] = [$data->getName(), $data->getType()->getName(), 'auto' => $data->getAutoincrement()];
						continue;
					}
					
					$field[0] = $data->getType()->getName();
					
					$length = $data->getLength();
					
					if($length == null) {
						$length = $data->getType()->getDefaultLength($this->_conn->getDatabasePlatform());
					}

					if($length != null) {
						$field[1] = $length;
					}

					if(!$data->getNotNull())
						$field['null'] = true;
					else
						$field['null'] = false;

					if($fks != null && count($fks) > 0) {
						foreach($fks as $fk) {
							$fCols = $fk->getForeignColumns();
							$lCols = $fk->getLocalColumns();
							
							foreach($lCols as $i => $lCol) {
								if($data->getName() == $lCol)
									$field['related'] = $fk->getForeignTableName() . '.' . $fCols[$i];
							}
						}
					}

					$struct[$data->getName()] = $field;
 				}

 				$result[] = $struct;
			}

			return $result;
		}

		public function generateFile($file, $struct) {
			$file = fopen($file, 'w');
			$outMethod = 'generate' . $this->outFormat;
			
			if(method_exists($this, $outMethod))
				return call_user_func_array([$this, $outMethod], [$file, $struct]);
			else $this->generatePHP($file);

			fclose($file);
		}

		public function generatePHP($file, $struct) {
			fwrite($file, '<?php' . "\n");
			fwrite($file, 'return ');
			fwrite($file, var_export($struct, true));
			fwrite($file, "; \n");
		}

		public function generateJSON($file, $struct) {
			fwrite($file, json_encode($struct));
		}

		public function generateSerialized($file, $struct) {
			fwrite($file, serialize($struct));
		}

	}
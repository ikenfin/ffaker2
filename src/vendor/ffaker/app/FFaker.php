<?php
	
	namespace ffaker\app;
	
	class FFaker extends \ffaker\FFakerBase {

		const FFVersion = '0.0.1';

		protected $struct = [];
		protected $binded = [];

		protected $chars = [
			'q','w','e','r','t','y','u','i','o','p','a',
			's','d','f','g','h','j','k','l','z','x','c',
			'v','b','n','m',' '
		];

		protected $__supportedCalculateOperations = ['+', '-', '/', '*'];

		/* data generators */
		public function generateInteger($max) {
			return rand(0, $max);
		}

		public function generateDouble($size) {
			list($max, $after) = explode(",", $size);
			return rand(0, $max) / pow(10, $after);
		}

		public function generateDatetime() {
			$rand_timestamp = $this->generateInt(time() - 86400);
			return date('Y-m-d H:i:s', $rand_timestamp);
		}

		public function generateText($size) {
			return $this->generateString($size);
		}

		public function generateString($size) {
			$result = '';
			
			for($i = 0; $i < $size; $i++) {
				$result .= $this->chars[$this->generateInteger(count($this->chars) - 1)];
			}
			return $result;
		}

		/*
			Take decision which generator can be used
		*/
		public function randomData($type, $size) {
			$method = 'generate' . ucfirst($type);
			
			if(method_exists($this, $method)) {
				return $this->{$method}($size);
			}
			return null;
		}

		/*
			Finds random row in $table
		*/
		public function findRandom($table, $null = false) {
			if($null) {
				$returnNull = rand(0, 1);
				
				if($returnNull)
					return null;
			}

			$related = null;
			$finder = null;

			// if db have RAND function:
			$_qb = $this->_conn->createQueryBuilder();
			try {
				$_qb->select('*')
					->from($table)
					->orderBy("RAND()")
					->setMaxResults(1);
				$q = $this->_conn->executeQuery($_qb->getSql());

				$related = $q->fetch();
			}
			catch(\Doctrine\DBAL\DBALException $e) {
				// hacky solution for databases that doesnt support RAND() function
				$_qb->resetQueryParts();

				$_qb->select('COUNT(*)')
					->from($table);
				$q = $this->_conn->executeQuery($_qb->getSql());
				
				$count = $q->fetchColumn();

				if($count > 0) {
					$_qb->resetQueryParts();
					$_qb->select('*')
						->from($table)
						->setMaxResults(1)
						->setFirstResult(rand(1, $count));

					$q = $this->_conn->executeQuery($_qb->getSql());

					$related = $q->fetch();
				}
			}

			return $related;
		}

		/*
			Fetch related table values
		*/
		public function relatedValue($name, $relation, $null=false) {
			list($table, $field) = explode('.', $relation);
			
			if($table == 'self')
				$table = $this->struct['__table__'];

			if(!isset($this->binded['__related__']))
				$this->binded['__related__'] = [];

			if(!array_key_exists($name, $this->binded['__related__']))
				$this->binded['__related__'][$name] = $this->findRandom($table, $null);

			$value = null;

			if($field == 'pk') {
				$field = $this->getPrimaryKey($table);
			}

			if(isset($this->binded['__related__'][$name][$field])) {
				$value = $this->binded['__related__'][$name][$field];
			}
			else {
				if(!$null)
					throw new \Exception("We cannot find anything to relate and field $name cannot be null!");
			}

			return $value;
		}

		/*
			Parse value for calculated values
			Values can be numbers, or field pointers
		*/
		protected function getValue($field_string, $null = false) {
			if(is_numeric($field_string))
				return $field_string;

			if(strpos($field_string, '.')) {
				list($key, $value) = explode('.', $field_string);

				$struct_item = $this->struct[$key];

				if($struct_item['related']) {
					if(isset($this->binded['__related__'][$key][$value])) {
						return $this->binded['__related__'][$key][$value];
					}
				}
				else {
					return $this->binded[$value];
				}
			}
			elseif(array_key_exists($field_string, $this->binded)) {
				return $this->binded[$field_string];
			}

			return null;
		}

		/*
			Process expression and returns calculated value
		*/
		public function calculatedValue($name, $expression, $null, $default = null) {
			list($a_str, $expr, $b_str) = explode(' ', $expression);

			if(!in_array($expr, $this->__supportedCalculateOperations))
				throw new Exception("{ $expr } is not supported in calculatedValue! Supported operators: [" . implode(',', self::$__supportedCalculateOperations) . "]", 1);
				
			$a_value = $this->getValue($a_str, $null);
			$b_value = $this->getValue($b_str, $null);
			
			if($a_value == null || $b_value == null) {
				if($default !== null)
					return $default;
				if($null)
					return null;

				throw new \Exception("Cannot calculate expression: { $a_value $expr $b_value }");
			}

			$val = eval('return ' . $a_value . $expr . $b_value . ';');

			if($val == null) {
				$val = $default;
			}

			if($val == null) {
				if(!$null)
					throw new \Exception("Cannot calculate {$expression} because its given NULL and $name cannot be null!");
			}

			return $val;
		}

		public static function getArrayValue($array, $key, $default) {
			if(array_key_exists($key, $array))
				return $array[$key];
			return $default;
		}

		/*
			Recognize structure item
		*/
		public function recognize($field, $struct_item) {
			$auto = self::getArrayValue($struct_item, 'auto', false);
			
			if($auto == true)
				return;

			$type = $struct_item[0];
			$size = self::getArrayValue($struct_item, 1, $this->defaultLength($type));
			$null = self::getArrayValue($struct_item, 'null', false);
			$value= self::getArrayValue($struct_item, 'value', null);
			$default = self::getArrayValue($struct_item, 'default', null);
			$related = self::getArrayValue($struct_item, 'related', null);

			if($related) {
				$this->binded[$field] = $this->relatedValue($field, $related, $null);
				return;
			}
			if($value) {
				$this->binded[$field] = $this->calculatedValue($field, $value, $null, $default);
				return;
			}

			$this->binded[$field] = $this->randomData($type, $size);
		}

		public function run($count = 0, $struct = [], $chars = [], $progressCallback = null) {
			$this->struct = $struct;

			if(count($chars) > 0)
				$this->chars = $chars;

			if(empty($struct))
				throw new Exception("Structure is empty!");	

			$table = null;

			for($i = 0; $i < $count; $i++) {
				$this->binded = [];

				foreach($struct as $key => $struct_item) {
					if($key == '__table__') {
						continue;
					}
					$this->recognize($key, $struct_item);
				}

				$result = $this->save($this->binded);

				if(is_callable($progressCallback))
					call_user_func($progressCallback, $i+1, $count, $result);
			}
		}

		/*
			Save generated data
		*/
		public function save($values) {
			$data = $this->binded;

			if(isset($data['__related__']))
				unset($data['__related__']);

			$status = $this->_conn->insert($this->struct['__table__'], $data);

			if(!$status) {
				var_dump($q->errorInfo);
				return false;
			}

			return true;
		}
	}
<?php

    class FFakerDumperTest extends PHPUnit_Framework_TestCase {
        
        public $db_conf;
        public $out_file;

        function setUp() {
            $db_path = realpath(dirname(__FILE__) . '/../test_db.sqlite3');

            if(!is_file($db_path)) {
                $this->markTestSkipped("Test sqlite3 database is missing! Please do 'make test' or copy it manually from test_suite dir");
            }

            $this->db_conf = ['url' => 'sqlite3:///' . $db_path ];
            $this->out_file = realpath(dirname(__FILE__)) . '/tmp_ffaker_dump.tmp';
        }

        function tearDown() {
            if(is_file($this->out_file))
                unlink($this->out_file);
        }

        // test json dumper
        function test_dumper_output_json() {
            $ffaker = new \ffaker\app\FFakerDumper($this->db_conf);
            $ffaker->dump($this->out_file, false, 'JSON');
            
            $data = json_decode(file_get_contents($this->out_file), true);

            $this->assertEquals(count($data), 2);
        }

        function test_dumper_output_json_with_db_config() {
            $ffaker = new \ffaker\app\FFakerDumper($this->db_conf);
            $ffaker->dump($this->out_file, true, 'JSON');
            
            $data = json_decode(file_get_contents($this->out_file), true);

            $this->assertEquals(count($data), 3);
            $this->assertEquals($data['__db_config__'], $this->db_conf);
        }
        
        // test php dumper
        function test_dumper_output_php() {
            $ffaker = new \ffaker\app\FFakerDumper($this->db_conf);
            $ffaker->dump($this->out_file, false, 'PHP');
            
            $data = require($this->out_file);

            $this->assertEquals(count($data), 2);
        }

        function test_dumper_output_php_with_db_config() {
            $ffaker = new \ffaker\app\FFakerDumper($this->db_conf);
            $ffaker->dump($this->out_file, true, 'PHP');
            
            $data = require($this->out_file);

            $this->assertEquals(count($data), 3);
            $this->assertEquals($data['__db_config__'], $this->db_conf);
        }

        // test php serialized dumper
        function test_dumper_output_serialized() {
            $ffaker = new \ffaker\app\FFakerDumper($this->db_conf);
            $ffaker->dump($this->out_file, false, 'Serialized');
            
            $data = unserialize(file_get_contents($this->out_file));

            $this->assertEquals(count($data), 2);
        }

        function test_dumper_output_serialized_with_db_config() {
            $ffaker = new \ffaker\app\FFakerDumper($this->db_conf);
            $ffaker->dump($this->out_file, true, 'Serialized');

            $data = unserialize(file_get_contents($this->out_file));

            $this->assertEquals(count($data), 3);
            $this->assertEquals($data['__db_config__'], $this->db_conf);
        }

    } 

?>
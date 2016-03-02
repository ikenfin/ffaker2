<?php
    require(dirname(__FILE__) . '/../helpers/fs.php');

    class HelpersTest extends PHPUnit_Framework_TestCase {

        function test_is_plain_array() {
            $plain_array = ['a', 'b', 'c'];
            $hashtable = ['a' => 'b', 'c' => 'd'];
            $mixed = ['a', 'b' => 'c'];
            $mixed_num = [0 => 'a', 1 => 'b', 'c' => 'd'];
            $numbered = [0 => 'a', 2 => 'b', 3 => 'c'];

            $this->assertTrue(is_plain_array($plain_array));
            $this->assertFalse(is_plain_array($hashtable));
            $this->assertFalse(is_plain_array($mixed));
            $this->assertFalse(is_plain_array($mixed_num));
            $this->assertTrue(is_plain_array($numbered));
        }

        function test_resolve_path() {
            $path = dirname(__FILE__);
            $file = basename(__FILE__);
            $must_be = realpath($path) . DIRECTORY_SEPARATOR . $file;

            $path_parts = explode('/', $path);
            $test_path_parts = [];

            for($i = count($path_parts) - 1; count($test_path_parts) < 1; $i--) {
                array_unshift($test_path_parts, $path_parts[$i]);
            }
            
            $test_path = implode('/', $test_path_parts) . DIRECTORY_SEPARATOR . $file;

            for($i = count($path_parts) - 1; count($test_path_parts) < 2; $i--) {
                array_unshift($test_path_parts, $path_parts[$i]);
            }

            $test_path2 = implode('/', $test_path_parts) . DIRECTORY_SEPARATOR . $file;
            
            $relative_path = $test_path;
            $relative_path2= './' . $test_path;
            $relative_path3= '../' . $test_path2;
            $relative_path4= './../' . $test_path2;

            $this->assertEquals(resolve_path($relative_path), $must_be);
            $this->assertEquals(resolve_path($relative_path2), $must_be);
            $this->assertEquals(resolve_path($relative_path3), $must_be);
            $this->assertEquals(resolve_path($relative_path4), $must_be);
        }
    } 

?>
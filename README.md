# ffaker2

PHP script to generate fake data in db, with specific rules. 

This version built on Doctrine, so it support databases that Doctrine supports.

## Installation

ffaker is wrapper for FinFaker php class, so you can use FinFaker separately, or you can compile ffaker.phar.

Compile ffaker.phar:

```bash
cd <ffaker_dir>
make # make will generate ffaker.phar in 'build' dir from now you can use it by ./build/ffaker.phar
sudo make install # this command will copy builded ffaker.phar, ffaker-dump.phar to /usr/local/bin, so it will be accesible globally
```

## Additional Makefile commands

* build_ffaker_dumper - creates only ffaker-dump.phar
* build_ffaker - creates only ffaker.phar

* test - creates environment to run PHPUnit tests
* runtests - run PHPUnit tests

## ffaker-dump options

* -d - database connection url (see [doctrine database url](http://docs.doctrine-project.org/projects/doctrine-dbal/en/* latest/reference/configuration.html#connecting-using-a-url) for details)
* -w - file to write dump (by default STDOUT)
* -p - pack database config into out structure
* -t - tables to export (format -t table1,table2,table3)
* -f - export config format (formats: j - JSON (default), s - Serialized php, p - PHP array)
* -h - show help screen
* -v - print version

## ffaker options

* -d - database connection url (is config packed in structure, this option can be passed)
* -s - database structure file (by default STDIN)
* -w - words file
* -c - count of records for each table
* -i - interactive mode (show progress)
* -f - import config format (like -f in dumper)
* -h - show help screen
* -v - print version


#### PHP format
PHP format can be used to easily customize output structure file.

This format cannot be passed via stdin in ffaker.phar. So if this format used, you need pass it like this:

```bash
ffaker-dump.phar -d sqlite3:///test_db.sqlite3 -p -f p -w struct.php && ffaker.phar -f p -s struct.php -c 1
```

## Example

You can use test sqlite3 db (test_suite/) for this example

```bash
ffaker-dump.phar -d sqlite3:///test_db.sqlite3 -p | ffaker.phar -c 10 -i
```

## Advanced usage

See the [USAGE.md](USAGE.md) to get more instructions.

## Contribute

Programmers:

Open to PR

Non-programmers:

As you can see English is not my main language, so if you can help me with documentation.
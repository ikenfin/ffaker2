# ffaker2 advanced usage

You can configure the ffaker2 generator by your own preferences

Lets use test database to learn how we can use ffaker2 more effective

The `ffaker2-dump.phar` by default generates compressed JSON files, and you can decompress it using Alchemy for Chrome, or use PHP output format, that creates more readable PHP file with schema.

# ffaker2 input format

Lets call input object as `structure`.
So, structure can contains database schema and configuration objects. Lets look at them:

### Database config object

Database config is just an object with key `__db_config__` that contain attribute `url` -- DBAL connection string

Example:

```php
	'__db_config__' => array(
		'url' => 'sqlite3:///./test/test_db.sqlite3'
	)
```

### Database schema

**The table.**

Structure database schema represents tables and table fields. Tables stored with numeric indexes, and must contain attribute `__table__` that store table name in database.

**`__table__` is required field!**

```php
array(
	0 => array(
		'__table__' => 'node',
		// table fields below
		// . . .
	)
)
```

**The fields.**

Fields represents table fields (cap.) as key=>value pairs, where key is a table field name, and value is a settings array.

Lets look at this format:

`<key> => ['field_type', 'field_size', . . . NAMED OPTIONS . . .]`

`key` can be name of field in table, or be a shortcode:

For now ffaker2 support only one shortcode - `pk` - that will be replaced with name of PRIMARY KEY of table.

`field_type` must be first in field array. Possibly types are:

`int` - represents INTEGER fields
`char` - represents CHAR/VARCHAR fields
`datetime` - represents DATETIME fields

`field_size` is a field size (for ex.: for VARCHAR(80) field_size will be 80), 

**you can use lower values - field_size used in value generator as value lenth.**

NAMED OPTIONS are:

`null` - if value is true, generator can fill this field with NULL values (relies on rand()). Default is FALSE.

`auto` - if true - field uses AUTO_INCREMENT. Default is FALSE

`default` - is default value, used in calculatable fields

`related` - is relation field (see related section below)

`value` - is calculatable value (see calculatable section below)


So the `structure` contains array of tables (and possibly `__db_config__`), that contains fields. Look at this simple example:

```php
array(
	0 => array(
		'__table__' => 'node',
		'pk' => array(
			0 => 'int',
			1 => 10,
			'auto' => true
		),
		'title' => array(
			0 => 'char',
			1 => 120
		)
	),
	'__db_config__' => array(
		'url' => 'sqlite3:///./test/test_db.sqlite3'
	)
)
```

### Related fields

Random fake data int good for related fields (may compromise the integrity of data), so you can use random **existing** values from these related tables.

Syntax of `related` is simple:

'related' => '&lt;table&gt;.&lt;field_name&gt;'

For self related tables you can use keyword `self` as &lt;table&gt;:
```php
'parent_id' => array(
	// ...
	'related' => 'self.pk'
)
```

See example:

```php
'owner_id' => array(
	0 => 'int',
	1 => 10,
	'null' => true,
	'related' => 'user.user_id'
)
```

### Calculatable fields

With calculatable fields you can add some arithmetic operation with field data. Currently supported operations: +, -, *, /.

For now you can use only one operation in field.

```php
'parent_id' => array(
	0 => 'int', 
	1 => 11, 
	'related' => 'self.pk', 
	'null' => true
),
'level' => array(
	0 => 'int', 
	1 => 11, 
	'value' => 'parent_id.level + 1', 
	'null' => true, 
	'default' => 0
)
```

Calculated fields can use values from other fields or from fields of related tables. In example above, we get value of level attribute in related table, and inrement it.
README
======

What is MySQL Workbench Schema Exporter?
----------------------------------------

[MySQL Workbench](http://www.mysql.com/products/workbench/) Schema Exporter is
a library to transform the MySQL Workbench model (`*.mwb`) to useful another
schemas.

It is inspired by [mysql-workbench-doctrine-plugin](http://code.google.com/p/mysql-workbench-doctrine-plugin/).

Currently, MySQL Workbench Schema Exporter can export the model to the
following schemas:

  * Doctrine 1.0 [YAML Schema](http://docs.doctrine-project.org/projects/doctrine1/en/latest/en/manual/yaml-schema-files.html).
  * Doctrine 2.0 [YAML Schema](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/yaml-mapping.html), [Annotation Classes](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html) or Annotation Classes with [Zend Framework 2](http://framework.zend.com/) [Input Filter support](http://framework.zend.com/manual/2.1/en/modules/zend.input-filter.intro.html).
  * [Zend DbTable](http://framework.zend.com/manual/en/zend.db.table.html).
  * Zend Rest Controller.
  * Sencha ExtJS3 Model.
  * Sencha [ExtJS4 Model](http://www.sencha.com/products/extjs/).
  * Propel [XML Schema](http://www.propelorm.org/reference/schema) and YAML Schema.
  * [Node Sequelize](http://sequelizejs.com/).
  * CakePHP (not implemented yet).

Prerequisites
-------------

  * PHP 5.3+
  * Composer to install the dependencies

Installing Dependencies
-----------------------

To fetch the defined dependencies into your local project, just run the
`install` command of `composer.phar`.

    $ php composer.phar install

Configuring MySQL Workbench Schema Exporter
-------------------------------------------

MySQL Workbench Schema Exporter can be configured at runtime using methods:

  * Setup options.
  * Model comment, either applied to table, column, or foreign key object.

Both methods accept different options, and generally divided as common options
and exporter (formatter) specific options.

### Common Setup Options

General options applied to all formatter.

  * `filename`

    The output filename format, use the following tag `%schema%`, `%table%`, `%entity%`, and `%extension%` to allow
    the filename to be replaced with contextual data.

    Default is `%entity%.%extension%`.

  * `indentation`

    The indentation size for generated code.

  * `useTabs`

    Use tabs for indentation instead of spaces. Setting this option will ignore the `indentation`-option.

  * `eolDelimeter`

    EOL type for generated code. Supported EOLs are `win` and `unix`.

  * `addGeneratorInfoAsComment`

    Add generator information to the generated code as a comment.

    Default is `true`.

  * `skipPluralNameChecking`

    Skip checking the plural name of model and leave as is, useful for non English table names.

    Default is `false`.

  * `backupExistingFile`

    If target already exists create a backup before replacing the content.

    Default is `true`.

  * `enhanceManyToManyDetection`

    If enabled, many to many relations between tables will be added to generated code.

    Default is `true`.

  * `sortTablesAndViews`

    If enabled, sorting of tables and views is performed prior to code generation for each table
    and view. For table, it sorted by table model name and for view sorted by view model name. 

    Default is `true`.

  * `logToConsole`

    If enabled, output the log to console.

    Default is `false`.

  * `logFile`

    If specified, output the log to a file. If this option presence, option `logToConsole` will be ignored instead.

    Default is `empty`.

### Common Model Comment Behavior

  * `{MwbExporter:external}true{/MwbExporter:external}` (applied to Table, View)

    Mark table/view as external to skip table/view code generation. For Doctrine use `{d:external}true{/d:external}` instead.

Formatter Setup Options
-----------------------

### Doctrine

#### Doctrine 1.0 YAML Schema

##### Setup Options

  * `extendTableNameWithSchemaName`

    Include schema name beside the table name.

    Default is `false`.

##### Model Comment Behavior

  * `{d:externalRelations}relation{/d:externalRelations}`

  * `{d:actAs}behavior{/d:actAs}`

    The following example export Doctrine behavior by using Table comment:

        {d:actAs}
          actAs:
            timestampable:
              [..]
        {/d:actAs}

  * Foreign key name

    To replace relations name by the name of the foreign key, start the foreign key name with `d:`.

#### Doctrine 2.0 YAML Schema

##### Setup Options

  * `useAutomaticRepository`

    Automatically generate entity repository class name.

  * `bundleNamespace`

    The global namespace prefix for entity class name.

  * `entityNamespace`

    The entity namespace.

    Default is `Entity`.

  * `repositoryNamespace`

    The namespace prefix for entity repository class name. For this configuration to apply, `useAutomaticRepository` must be set to `true`.

  * `extendTableNameWithSchemaName`

    Include schema name beside the table name.

    Default is `false`. 

#### Doctrine 2.0 Annotation

##### Setup Options

  * `useAnnotationPrefix`

    Doctrine annotation prefix.

    Default is `ORM\`.

  * `useAutomaticRepository`

    See above.

  * `bundleNamespace`

    See above.

  * `entityNamespace`

    See above.

  * `repositoryNamespace`

    See above.

  * `skipGetterAndSetter`

    Don't generate columns getter and setter.

    Default is `false`.

  * `skipColumnWithManyRelation`

    Don't generate columns variable and columns getter and setter which has
    many to relation to other table.

    Default is `false`.

  * `generateEntitySerialization`

    Generate method `__sleep()` to include only real columns when entity is serialized.

    Default is `true`.

  * `generateExtendableEntity`

    Generate two class for each tables in schema, one for base and one other
    for extend class. The extend class would not be generated if it already
    exist. So it is safe to place custom code inside the extend class.

    This option will generate entity using Single Table Inheritance.

    Default is `false`.

  * `quoteIdentifier`

    If this option is enabled, all table names and column names will be quoted
    using backtick (`` ` ``). Usefull when the table name or column name
    contains reserved word.

    Default is `false`.

##### Model Comment Behavior

  * `{d:bundleNamespace}AcmeBundle{/d:bundleNamespace}` (applied to Table)

    Override `bundleNamespace` option.

  * `{d:m2m}false{/d:m2m}` (applied to Table)

    MySQL Workbench Schema Exporter tries to automatically guess which tables
    are many-to-many mapping tables and will not generate entity classes for
    these tables.

    A table is considered a mapping table, if it contains exactly two foreign
    keys to different tables and those tables are not many-to-many mapping tables.

    Sometimes this guessing is incorrect for you. But you can add a hint in the
    comment of the table, to show that it is no mapping table. Just use
    `{d:m2m}false{/d:m2m}` anywhere in the comment of the table.

  * `{d:unidirectional}true{/d:unidirectional}` (applied to ForeignKey)

    All foreign keys will result in a bidirectional relation by default. If you
    only want a unidirectional relation, add a flag to the comment of the
    foreign key.

  * `{d:owningSide}true{/d:owningSide}` (applied to ForeignKey)

    In a bi-directional many-to-many mapping table the owning side of the
    relation is randomly selected. If you add this hint to one foreign key of
    the m2m-table, you can define the owning side for Doctrine.

  * `{d:cascade}persist, merge, remove, detach, all{/d:cascade}` (applied to ForeignKey)

    You can specify Doctrine cascade options as a comment on a foreign key.
    They will be generated into the Annotation. ([Reference](http://doctrine-orm.readthedocs.org/en/latest/reference/working-with-associations.html#transitive-persistence-cascade-operations))

  * `{d:fetch}EAGER{/d:fetch}` (applied to ForeignKey)

    You can specify the fetch type for relations in the comment of a foreign
    key. (EAGER or LAZY, doctrine default is LAZY)

  * `{d:orphanRemoval}true{/d:orphanRemoval}` (applied to ForeignKey)

    Another option you can set in the comments of foreign key. ([Reference](http://doctrine-orm.readthedocs.org/en/latest/reference/working-with-associations.html#orphan-removal))

  * `{d:order}column{/d:order}` (applied to ForeignKey)

    Apply OrderBy annotation to One To Many and Many To Many relation. OrderBy
    annotation can be written in the following format:

        column[,(asc|desc)]

    Multiple columns are supported, separated by line break. Example usage:

        {d:order}
          column1
          column1,desc
        {/d:order}

#### Doctrine 2.0 Annotation with ZF2 Input Filter Classes

Doctrine 2.0 Annotation with ZF2 Input Filter Classes formatter directly
extend Doctrine 2.0 Annotation. The setup options and model comment behavior
exactly the same as Doctrine 2.0 Annotation with the following addons. 

##### Setup Options

  * `generateEntityPopulate`

    Generate `populate()` method for entity class.

    Default is `true`.

  * `generateEntityGetArrayCopy`

    Generate `getArrayCopy()` method for entity class.

    Default is `true`.

### Propel

#### Propel 1.x XML Schema

##### Setup Options

  * `namespace`

    The table namespace.

  * `addVendor`

    Add mysql specific vendor info into the generated content.

    Default is `false`.

#### Propel 1.x YAML Schema

##### Setup Options

  * `generateSimpleColumn`

    If enabled, use simple column definition. Table columns considered as
    simple column are `created_at` and `updated_at`. 

    Default is `false`.

  * `package`

    Model package. 

    Default is `lib.model`.

##### Model Comment Behavior

  * `{propel:allowPkInsert}true{/propel:allowPkInsert}` (applied to Table)

    Allow primary key value insertion if its an auto increment column. 

  * `{propel:propel_behaviors}behavior{/propel:propel_behaviors}` (applied to Table)

    Propel behaviors definition, written in YAML format.

    Example usage:

        {propel:propel_behaviors}
        timestampable:
        {/propel:propel_behaviors}

  * `{propel:behaviors}behavior{/propel:behaviors}` (applied to Table)

    Custom behaviors definition, written in YAML format.

### Zend

#### Zend DbTable

##### Setup Options

  * `tablePrefix`

    Table prefix for generated class.

    Default is `Application_Model_DbTable_`.

  * `parentTable`

    Ancestor class, the class to extend for generated class.

    Default is `Zend_Db_Table_Abstract`.

  * `generateDRI`

    Generate `dependentTables` map.

    Default is `false`.

  * `generateGetterSetter`

    Not implemented yet.

#### Zend Rest Controller

##### Setup Options

  * `tablePrefix`

    See above.

    Default is `empty`.

  * `parentTable`

    See above.

    Default is `Zend_Rest_Controller`.

### Sencha

#### ExtJS3 Model

##### Setup Options

  * `classPrefix`

    Class prefix for generated object.

    Default is `SysX.App`.

  * `parentClass`

    Ancestor object, the class to extend for generated javascript object.

    Default is `SysX.Ui.App`.

#### ExtJS4 Model

##### Setup Options

  * `classPrefix`

    Class prefix for generated object.

    Default is `App.model`.

  * `parentClass`

    Ancestor object, the class to extend for generated javascript object.

    Default is `Ext.data.Model`.

  * `generateValidation`

    Generate columns validation.

    Default is `true`.

  * `generateProxy`

    Generate ajax proxy.

    Default is `true`.

### Node

#### Sequelize Model

Currently, none of specific option can be configured for Sequelize Model. 

Command Line Interface (CLI)
----------------------------

There is a new CLI to simplify the export process named `export.php`, you can look under the `cli` folder.
The CLI has feature to customize export configuration before exporting. By default, CLI application will
use config file `export.json` located in the current directory to supply the parameter if it find it. To
disable this behaviour, see the option below.

The syntax of CLI:

    php cli/export.php [options] FILE [DEST]

Where:

  * `FILE`

    The MySQL Workbench model file to export.

  * `DEST`

    The destination directory (optional), if not specified current directory assumed.

Options:

  * `--export=type`

  Choose the result of the export, supported type can be obtained using
  `--list-exporter`. If this option is omitted and no config file found,
  the CLI will prompt to choose which exporter to use.

  * `--config=file`

  Read export parameters from file (in JSON format).

  * `--saveconfig`

  Save export parameters to file `export.json`, later can be used as value for
  `--config=file`.

  * `--list-exporter`

  Show all available exporter.

  * `--no-auto-config`

  Disable automatic config file lookup.

  * `--zip`

  Compress the result.

  * `--help`

  Show the usage (or suppress any parameters).

Sample usage:

    php cli/export.php --export=doctrine1-yaml example/data/test.mwb ./generated
    php cli/export.php --zip example/data/test.mwb

Sample export paramaters (JSON) for doctrine2-annotation:

    {
        "export": "doctrine2-annotation",
        "zip": false,
        "dir": "temp",
        "params": {
            "backupExistingFile": true,
            "skipPluralNameChecking": false,
            "enhanceManyToManyDetection": true,
            "bundleNamespace": "",
            "entityNamespace": "",
            "repositoryNamespace": "",
            "useAnnotationPrefix": "ORM\\",
            "useAutomaticRepository": true,
            "indentation": 4,
            "filename": "%entity%.%extension%",
            "quoteIdentifier": false
        }
    }

Using MySQL Workbench Schema Exporter as Library
------------------------------------------------

If you want to use MySQL Workbench Schema Exporter as a library for other
project. See the included usage in the `example` folder.

Test Database
-------------

  * [Sakila Sample Database documentation](http://dev.mysql.com/doc/sakila/en/index.html).
  * [Sakila Sample Database download](http://dev.mysql.com/doc/index-other.html).

Links
-----

  * [MySQL Workbench](http://wb.mysql.com/)
  * [Doctrine Project](http://www.doctrine-project.org/)
  * [Symfony Project](http://www.symfony.com/)
  * [Sencha - Open source FAQ](http://www.sencha.com/legal/open-source-faq/)

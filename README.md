# README

What is MySQL Workbench Schema Exporter?
----------------------------------------

[MySQL Workbench](http://www.mysql.com/products/workbench/) Schema Exporter is a library to
transform the MySQL Workbench model (`*.mwb`) to useful another schemas.

It is inspired by
[mysql-workbench-doctrine-plugin](http://code.google.com/p/mysql-workbench-doctrine-plugin/).

Currently, MySQL Workbench Schema Exporter can export the model to various schemas using a formatter plugin:

  * Doctrine 1.0
    [YAML Schema](http://docs.doctrine-project.org/projects/doctrine1/en/latest/en/manual/yaml-schema-files.html).
  * Doctrine 2.0
    [YAML Schema](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/yaml-mapping.html),
    [Annotation Classes](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html)
    or Annotation Classes with [Zend Framework 2](http://framework.zend.com/)
    [Input Filter support](http://framework.zend.com/manual/2.1/en/modules/zend.input-filter.intro.html).
  * [Zend DbTable](http://framework.zend.com/manual/1.12/en/zend.db.table.html).
  * Zend Rest Controller.
  * Sencha ExtJS3 Model.
  * Sencha [ExtJS4 Model](http://www.sencha.com/products/extjs/).
  * Propel [XML Schema](http://www.propelorm.org/reference/schema) and YAML Schema.
  * [Node Sequelize](http://sequelizejs.com/).

The actual conversion to another schema is done using an exporter. These plugins are available in subprojects:
 * [Doctrine1 Exporter](https://github.com/mysql-workbench-schema-exporter/doctrine1-exporter)
 * [Doctrine2 Exporter](https://github.com/mysql-workbench-schema-exporter/doctrine2-exporter)
 * [Propel1 Exporter](https://github.com/mysql-workbench-schema-exporter/propel1-exporter)
 * [Zend Framework 1 Exporter](https://github.com/mysql-workbench-schema-exporter/zend1-exporter)
 * [Node Exporter](https://github.com/mysql-workbench-schema-exporter/node-exporter)

## Prerequisites

  * PHP 5.4+
  * Composer to install the dependencies

## Installation

### Using Composer

  1. In your project directory issue:

    ```
    php composer.phar require --dev mysql-workbench-schema-exporter/mysql-workbench-schema-exporter
    ```

  2. You then can invoke the CLI script using `vendor/bin/mysql-workbench-schema-export`.

  3. You can directly require an exporter for your project:
  ```
  php composer.phar require --dev mysql-workbench-schema-exporter/doctrine2-exporter
  ```


### Stand alone

  1. Get the source code using Git or
  [download](https://github.com/johmue/mysql-workbench-schema-exporter/releases) from Github.
  2. Get [composer](https://getcomposer.org/).
  3. Install dependencies:

    ```
    php composer.phar install
    ```

  4. You then can invoke the CLI script using `bin/mysql-workbench-schema-export`.

## Configuring MySQL Workbench Schema Exporter

MySQL Workbench Schema Exporter can be configured at runtime using methods:

  * Setup options.
  * Model comment, either applied to table, column, or foreign key object.

Both methods accept different options, and generally divided as common options and exporter
(formatter) specific options.

### Common Setup Options

General options applied to all formatter.

  * `filename`

    The output filename format, use the following tag `%schema%`, `%table%`, `%entity%`, and
    `%extension%` to allow the filename to be replaced with contextual data.

    Default is `%entity%.%extension%`.

  * `indentation`

    The indentation size for generated code.

  * `useTabs`

    Use tabs for indentation instead of spaces. Setting this option will ignore the
    `indentation`-option.

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

  * `exportOnlyTableCategorized`

    If specified, only export the tables if its category matched.

  * `logToConsole`

    If enabled, output the log to console.

    Default is `false`.

  * `logFile`

    If specified, output the log to a file. If this option presence, option `logToConsole` will be
    ignored instead.

    Default is `empty`.

### Common Model Comment Behavior

  * `{MwbExporter:external}true{/MwbExporter:external}` (applied to Table, View)

    Mark table/view as external to skip table/view code generation. For Doctrine use
    `{d:external}true{/d:external}` instead.

  * `{MwbExporter:category}mycategory{/MwbExporter:category}` (applied to Table)

    Table category used to groups the table for sorting. This way, generated table
    output can be sorted as you need such as in Propel YAML schema (obviously useful
    for exporter which results in single file output).

## Formatter Setup Options

- [Doctrine 2 Annotation, YAML and ZF2 Input Filter](https://github.com/mysql-workbench-schema-exporter/doctrine2-exporter#formatter-setup-options)
- [Doctrine 1 YAML](https://github.com/mysql-workbench-schema-exporter/doctrine1-exporter#formatter-setup-options)
- [Propel 1 YAML and XML](https://github.com/mysql-workbench-schema-exporter/propel1-exporter#formatter-setup-options)
- [Zend 1 Rest and DbTable](https://github.com/mysql-workbench-schema-exporter/zend1-exporter#formatter-setup-options)
- [NodeJS Sequelize ](https://github.com/mysql-workbench-schema-exporter/node-exporter#formatter-setup-options)
- [Sencha ExtJS3 and ExtJS4](https://github.com/mysql-workbench-schema-exporter/sencha-exporter#formatter-setup-options)


## Command Line Interface (CLI)

The `mysql-workbench-schema-export` command helps export a workbench schema model directly
from command line. It has feature to customize export configuration before exporting.
By default, it will use config file `export.json` located in the current directory to supply
the parameter if it find it. To disable this behaviour, see the option below.

Command usage:

    php bin/mysql-workbench-schema-export [options] FILE [DEST]

Where:

  * `FILE`

    The MySQL Workbench model file to export.

  * `DEST`

    The destination directory (optional), if not specified current directory assumed.

Options:

  * `--export=type`

  Choose the result of the export, supported type can be obtained using `--list-exporter`.
  If this option is omitted and no config file found, the CLI will prompt to choose which exporter
  to use.

  * `--config=file`

  Read export parameters from file (in JSON format).

  * `--saveconfig`

  Save export parameters to file `export.json`, later can be used as value for `--config=file`.

  * `--list-exporter`

  Show all available exporter.

  * `--no-auto-config`

  Disable automatic config file lookup.

  * `--zip`

  Compress the result.

  * `--help`

  Show the usage (or suppress any parameters).

Sample usage:

    php bin/mysql-workbench-schema-export --export=doctrine1-yaml example/data/test.mwb ./generated
    php bin/mysql-workbench-schema-export --zip example/data/test.mwb

Sample export parameters (JSON) for doctrine2-annotation:

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

## Using MySQL Workbench Schema Exporter as Library

If you want to use MySQL Workbench Schema Exporter as a library for other project. See the included usage in the `example` folder.

## Test Database

  * [Sakila Sample Database documentation](http://dev.mysql.com/doc/sakila/en/index.html).
  * [Sakila Sample Database download](http://dev.mysql.com/doc/index-other.html).

## Links

  * [MySQL Workbench](http://wb.mysql.com/)
  * [Doctrine Project](http://www.doctrine-project.org/)
  * [Symfony Project](http://www.symfony.com/)
  * [Sencha - Open source FAQ](http://www.sencha.com/legal/open-source-faq/)

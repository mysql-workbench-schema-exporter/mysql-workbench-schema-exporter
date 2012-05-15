README
======

ATTENTION
---------

This software is EXPERIMENTAL and not ready for production.
It is just a proof of concept.


What is MySQL Workbench schema exporter?
----------------------------------------

The application is intended to create:

  * Doctrine 1.0 YAML Schema
  * Doctrine 2.0 [YAML Schema](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/yaml-mapping.html) and [Annotation Classes](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html)
  * [Zend DbTable](http://framework.zend.com/manual/en/zend.db.table.html)
  * Zend Rest Controller
  * Sencha ExtJS3 Model
  * Propel (not implemented)
  * CakePHP (not implemented)

schema files from MySQL Workbench models (*.mwb).
It is inspired by http://code.google.com/p/mysql-workbench-doctrine-plugin/.

Doctrine behaviours
-------------------

Support for behaviours is implemented for Doctrine1. Use the comment fields in
tables.

    {d:actAs}
      actAs:
        timestampable:
          [..]
    {/d:actAs}

Foreign key name
----------------

To replace relations name by the name of the foreign key, start the foreign key name with "d:".

Command Line Interface (CLI)
----------------------------

There is a new CLI to simplify the export process named `export.php`, you can look under the `cli` folder.
The CLI has feature to customize export configuration before exporting. By default, CLI application will
use config file `export.json` located in the current directory to supply the parameter if it find it. To
disable this behaviour, see the option below.

The syntax of CLI:

    php cli/export.php [options] FILE [DEST]

Where:

  * `options`:
    * `--export=type`, choose the result of the export, currently available types:
      * `doctrine1-yaml`, Doctrine 1.0 YAML schema
      * `doctrine2-yaml`, Doctrine 2.0 YAML schema
      * `doctrine2-annotation`, Doctrine 2.0 Annotation classes (default)
      * `zend-dbtable`, Zend DbTable
      * `zend-rest-controller`, Zend Rest Controller
      * `sencha-extjs3`, Sencha ExtJS3 Model
    * `--config=file`, read export parameters from file (in JSON format)
    * `--saveconfig`, save export parameters to file `export.json`, later can be used as value for `--config=file`
    * `--list-exporter`, show all available exporter
    * `--no-auto-config`, disable automatic config file lookup
    * `--zip`, compress the result
    * `--help`, show the usage (or suppress any parameters)
  * `FILE`, the mwb file to export
  * `DEST`, the destination directory (optional), if not specified current directory assumed

Sample usage:

    php cli/export.php --export=doctrine1 example/data/test.mwb ./generated
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
            "filename": "%entity%.%extension%"
        }
    }

Exporter Options
----------------

### General options

General options applied to all formatter.

  * `indentation`

    The indentation size for generated code.

  * `filename`

    The output filename format, use the following tag `%schema%`, `%table%`, `%entity%`, and `%extension%` to allow
    the filename to be replaced with contextual data. Default is `%entity%.%extension%`.

  * `skipPluralNameChecking`

    Skip checking the plural name of model and leave as is, useful for non English table names. Default is `false`.

  * `backupExistingFile`

    If target already exists create a backup before replacing the content. Default is `true`.

  * `enhanceManyToManyDetection`

    If enabled, many to many relations between tables will be added to generated code. Default is `true`.

### Option list for Doctrine 1.0

  * `extendTableNameWithSchemaName`

    Include schema name beside the table name. Default is `false`.

  * `{d:externalRelations}`
  

### Option list for Doctrine 2.0 YAML

  * `useAutomaticRepository`

    Automatically generate entity repository class name.

  * `bundleNamespace`

    The global namespace prefix for entity class name.

  * `entityNamespace`

    The entity namespace. Default is `Entity`.

  * `repositoryNamespace`

    The namespace prefix for entity repository class name. For this configuration to apply, `useAutomaticRepository` must be set to `true`.

  * `extendTableNameWithSchemaName`

    Include schema name beside the table name. Default is `false`. 


### Option list for Doctrine 2.0 Annotation

  * `useAnnotationPrefix`

    Doctrine annotation prefix. Default is `ORM\`.

  * `useAutomaticRepository`

    See above.

  * `bundleNamespace`

    See above.

  * `entityNamespace`

    See above.

  * `repositoryNamespace`

    See above.

  * `skipGetterAndSetter`

    Don't generate columns getter and setter. Default is `false`.

  * `generateEntitySerialization`

    Generate method `__sleep()` to include only real columns when entity is serialized. Default is `true`.


### Option list for Zend DbTable

  * `tablePrefix`
  * `parentTable`
  * `generateDRI`
  * `generateGetterSetter`

### Option list for Zend Rest Controller

  * `tablePrefix`
  * `parentTable`

### Option list for Sencha ExtJS3 Model

  * `classPrefix`
  * `parentClass`

Requirements
------------

Works with PHP 5.3 and up.

Links
-----
  * [MySQL Workbench](http://wb.mysql.com/)
  * [Doctrine Project](http://www.doctrine-project.org/)
  * [Symfony Project](http://www.symfony.com/)

Test-Database
-------------
  * [Sakila DB *.mwb](http://downloads.mysql.com/docs/sakila-db.zip)
  * [Sakila DB docs](http://dev.mysql.com/doc/sakila/en/index.html)

Example
-------

    <?php

    // enable autoloading of classes
    $libDir = __DIR__.'/lib';
    require_once($libDir.'/MwbExporter/SplClassLoader.php');

    $classLoader = new SplClassLoader();
    $classLoader->setIncludePath($libDir);
    $classLoader->register();

    // create bootstrap
    $bootstrap = new \MwbExporter\Bootstrap();

    // define a formatter and do configuration
    $formatter = $bootstrap->getFormatter('doctrine2-annotation');
    $formatter->setup(array());

    // specify the workbench document to load, output directory, and storage type (zip or file)
    $mwbfile = 'myDatabaseModel.mwb';
    $outDir = getcwd();
    $storage = 'zip';
    // load document and export
    $document = $bootstrap->export($formatter, $mwbfile, $outDir, $storage);

    // show the output
    echo sprintf("Saved to %s.\n\n", $document->getWriter()->getStorage()->getResult());

    ?>

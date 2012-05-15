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
  * Doctrine 2.0 YAML Schema and Annotation Classes
  * Zend DbTable
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
            "enhancedManyToManyDetection": false,
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
    the filename to be replaced with contextual data.

  * `skipPluralNameChecking`

    Skip checking the plural name of model and leave as is, useful for non English table names. Default to `false`.

  * `backupExistingFile`

    If target already exists create a backup before replacing the content. Default `true`.

### Option list for Doctrine 1.0

  * `extendTableNameWithSchemaName`
  * `{d:externalRelations}`

### Option list for Doctrine 2.0 YAML

  * `useAutomaticRepository`
  * `bundleNamespace`
  * `entityNamespace`
  * `repositoryNamespace`
  * `extendTableNameWithSchemaName`

### Option list for Doctrine 2.0 Annotation

  * `useAnnotationPrefix`
  * `useAutomaticRepository`
  * `bundleNamespace`
  * `entityNamespace`
  * `repositoryNamespace`
  * `skipGetterAndSetter`
  * `enhancedManyToManyDetection`

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

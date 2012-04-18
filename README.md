README
======

ATTENTION
---------

This software is EXPERIMENTAL and not ready for production.
It is just a proof of concept.


What is MySQL Workbench schema exporter?
----------------------------------------

The application is intended to create:

  * Doctrine1
  * Doctrine2
  * Zend DbTable
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
The CLI has feature to customize export configuration before exporting.

The syntax of CLI:

    php cli/export.php [options] FILE [DEST]

Where:

  * `options`:
    * `--export=type`, choose the result of the export, currently available types:
      * `doctrine1`, Doctrine 1.0 YAML schema
      * `doctrine2-yml`, Doctrine 2.0 YAML schema
      * `doctrine2-annotation`, Doctrine 2.0 Annotation classes (default)
      * `zend-dbtable`, Zend DbTable
    * `--config=file`, read export parameters from file (in JSON format)
    * `--saveconfig`, save export parameters to file `export.json`, later can be used as value for `--config=file`
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
            "skipPluralNameChecking": false,
            "enhancedManyToManyDetection": false,
            "bundleNamespace": "",
            "entityNamespace": "",
            "repositoryNamespace": "",
            "useAnnotationPrefix": "ORM\\\\",
            "useAutomaticRepository": true,
            "indentation": 4,
            "filename": "%entity%.%extension%"
        }
    }

Exporter Options
----------------

### General options

General options applied to all formatter.

  * `skipPluralNameChecking`, skip checking the plural name of model and leave as is, useful for non English table names.

### Option list for doctrine 1

  * `extendTableNameWithSchemaName`
  * `{d:externalRelations}`

### Option list for doctrine 2

  * `useAnnotationPrefix`
  * `indentation`
  * `useAutomaticRepository`
  * `extendTableNameWithSchemaName`
  * `bundleNamespace`
  * `entityNamespace`
  * `repositoryNamespace`

### Option list for Zend DbTable

  * `tablePrefix`
  * `parentTable`
  * `indentation`

Requirements
------------

Works with PHP 5.3 and up.

Links
-----
  * [Doctrine Project](http://www.doctrine-project.org/)
  * [MySQL Workbench](http://wb.mysql.com/)
  * [Symfony Project](http://www.symfony-project.org/)
  * [MySQL Workbench Doctrine Plugin - google code project](http://code.google.com/p/mysql-workbench-doctrine-plugin/)

Test-Database
-------------
  * [Sakila DB *.mwb](http://downloads.mysql.com/docs/sakila-db.zip)
  * [Sakila DB docs](http://dev.mysql.com/doc/sakila/en/sakila.html)

Example
-------

    <?php

    // enable autoloading of classes
    require_once('lib/MwbExporter/Core/SplClassLoader.php');
    $classLoader = new SplClassLoader();
    $classLoader->setIncludePath('lib');
    $classLoader->register();

    // define a formatter
    $formatter = new \MwbExporter\Formatter\Doctrine2\Annotation\Loader();

    // parse the mwb file
    $mwb = new \MwbExporter\Core\Workbench\Document('myDatabaseModel.mwb', $formatter);

    // show the output
    echo $mwb->display();
    
    // save as zip file in current directory and use .php as file endings
    echo $mwb->zipExport(__DIR__, 'php');
    ?>

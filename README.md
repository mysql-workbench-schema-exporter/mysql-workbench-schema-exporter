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
  * Propel
  * Zend DbTable
  * CakePHP
  * ...

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
-------------

To replace relations name by the name of the foreign key, start the foreign key name with "d:".


Option list for doctrine 1
--------------------------
  * extendTableNameWithSchemaName

Option list for doctrine 2
--------------------------
  * useAnnotationPrefix
  * indentation
  * useAutomaticRepository
  * extendTableNameWithSchemaName
  * bundleNamespace


Requirements
------------

Works with PHP 5.3 and up.

Links
-----
  * [Doctrine Project](http://www.doctrine-project.org/)
  * [MySQL Workbench](http://wb.mysql.com/)
  * [Symfony Project](http://www.symfony-project.org/)
  * [MySQL Workbench Doctrine Plugin - google code project](http://code.google.com/p/mysql-workbench-doctrine-plugin/)

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
    ?>

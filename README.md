README
======

ATTENTION
---------

This software is EXPERIMENTAL and not ready for production use.
It is just a proof of concept.


What is MySQL Workbench schema exporter?
----------------------------------------

The application is intended to create:

  * Doctrine1
  * Doctrine2
  * Propel
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

Requirements
------------

Works with PHP 5.3 and up.

Links
-----

[1]: http://www.doctrine-project.org/
[2]: http://wb.mysql.com/
[3]: http://code.google.com/p/mysql-workbench-doctrine-plugin/

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
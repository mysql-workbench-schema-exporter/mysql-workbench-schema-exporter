![Build Status](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter/actions/workflows/continuous-integration.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter/v/stable.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter)
[![Total Downloads](https://poser.pugx.org/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter/downloads.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter) 
[![License](https://poser.pugx.org/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter/license.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter)

# README

## What is MySQL Workbench Schema Exporter?

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
  * [Node Sequelize](https://sequelize.org).
  * Propel [XML Schema](http://www.propelorm.org/reference/schema) and YAML Schema.
  * Sencha ExtJS3 Model and Sencha [ExtJS4 Model](http://www.sencha.com/products/extjs/).
  * [Zend DbTable](http://framework.zend.com/manual/1.12/en/zend.db.table.html) and Zend Rest Controller.

The actual conversion to another schema is done using an exporter. These plugins are available in subprojects:
 * [Doctrine1 Exporter](https://github.com/mysql-workbench-schema-exporter/doctrine1-exporter)
 * [Doctrine2 Exporter](https://github.com/mysql-workbench-schema-exporter/doctrine2-exporter)
 * [Node Exporter](https://github.com/mysql-workbench-schema-exporter/node-exporter)
 * [Propel1 Exporter](https://github.com/mysql-workbench-schema-exporter/propel1-exporter)
 * [Sencha ExtJS Exporter](https://github.com/mysql-workbench-schema-exporter/sencha-exporter)
 * [Zend Framework 1 Exporter](https://github.com/mysql-workbench-schema-exporter/zend1-exporter)
 
## Prerequisites

  * PHP 7.2+
  * Composer to install the dependencies

## Installation

  1. In your project directory issue:

    composer require --dev mysql-workbench-schema-exporter/mysql-workbench-schema-exporter

  2. You then can invoke the CLI script using `vendor/bin/mysql-workbench-schema-export`.

  3. You can directly require an exporter for your project:

    composer require --dev mysql-workbench-schema-exporter/doctrine2-exporter

## Command Line Interface (CLI)

The `mysql-workbench-schema-export` command helps export a workbench schema model directly
from command line. It has feature to customize export configuration before exporting.
By default, it will use config file `export.json` located in the current directory to supply
the parameter if it find it.

Command usage:

    vendor/bin/mysql-workbench-schema-export [options] FILE [DEST]

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

  * `--save-config`

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

    vendor/bin/mysql-workbench-schema-export --export=doctrine1-yaml example/data/test.mwb ./generated
    vendor/bin/mysql-workbench-schema-export --zip example/data/test.mwb

## Configuring MySQL Workbench Schema Exporter

MySQL Workbench Schema Exporter can be configured at runtime using methods:

  * Configuration files.
  * Model comment, either applied to table, column, or foreign key object.

Refers to exporter project to show detailed information.

 * [Doctrine1 Exporter](https://github.com/mysql-workbench-schema-exporter/doctrine1-exporter#readme)
 * [Doctrine2 Exporter](https://github.com/mysql-workbench-schema-exporter/doctrine2-exporter#readme)
 * [Node Exporter](https://github.com/mysql-workbench-schema-exporter/node-exporter#readme)
 * [Propel1 Exporter](https://github.com/mysql-workbench-schema-exporter/propel1-exporter#readme)
 * [Sencha ExtJS Exporter](https://github.com/mysql-workbench-schema-exporter/sencha-exporter#readme)
 * [Zend Framework 1 Exporter](https://github.com/mysql-workbench-schema-exporter/zend1-exporter#readme)

## Common Model Comment Behavior

  * `{MwbExporter:external}true{/MwbExporter:external}` (applied to Table, View)

    Mark table/view as external to skip table/view code generation. For Doctrine use
    `{d:external}true{/d:external}` instead.

  * `{MwbExporter:category}mycategory{/MwbExporter:category}` (applied to Table)

    Table category used to groups the table for sorting. This way, generated table
    output can be sorted as you need such as in Propel YAML schema (obviously useful
    for exporter which results in single file output).

## Using MySQL Workbench Schema Exporter as Library

If you want to use MySQL Workbench Schema Exporter as a library for other project. See the included usage in the `example` folder.

## Test Database

  * [Sakila Sample Database documentation](http://dev.mysql.com/doc/sakila/en/index.html).
  * [Sakila Sample Database download](http://dev.mysql.com/doc/index-other.html).

## Links

  * [MySQL Workbench](http://wb.mysql.com/)
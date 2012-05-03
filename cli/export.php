<?php

/**
 * Copyright (c) 2012 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

// ----------------------------------------- CONST --------------------------------------------------- //
error_reporting(E_ALL & ~E_NOTICE);
// ----------------------------------------- COMMAND LINE -------------------------------------------- //

function usage()
{
  $self = basename($_SERVER['argv'][0]);
  echo <<<EOF
Usage:
$self [options] FILENAME [DEST]

Options:
--export=type   Export to the following type:
                - doctrine1                Doctrine 1.0 yml schema
                - doctrine2-annotation     Doctrine 2.0 annotation classes
                - doctrine2-yml            Doctrine 2.0 yml schema
                - zend-dbtable             Zend DbTable
--config=file   Read file for export parameters, in JSON format.
--saveconfig    Save export parameters to file.
--zip           Export as zip archive.

EOF;
  die(0);
}

function parseCmdLine($args = array(), &$params = array(), &$options = array(), &$values = array())
{
  $values = array();
  while (true)
  {
    if (!count($args)) break;
    $p        = strpos($args[0], '=');
    $is_opt   = '--' == substr($args[0], 0, 2);
    $is_param = $is_opt && false !== $p;
    $arg      = $is_opt ? ($is_param ? substr($args[0], 2, $p - 2) : substr($args[0], 2)) : $args[0];
    $val      = $is_param ? substr($args[0], $p + 1) : null;
    if ($is_opt)
    {
      $found = false;
      if ($is_param)
      {
        if ($found = array_key_exists($arg, $params))
        {
          $params[$arg] = $val;
        }
      }
      else
      {
        if ($found = array_key_exists($arg, $options))
        {
          $options[$arg] = true;
        }
      }
      if (!$found)
      {
        echo sprintf("Unknown option %s.\n", $arg);
        die(0);
      }
    }
    else
    {
      $values[] = $arg;
    }
    array_shift($args);
  }
}

// ----------------------------------------- FUNCTIONS ----------------------------------------------- //

function getConfigName($config)
{
  return ucfirst(strtolower(preg_replace('/[A-Z]/', ' ${0}', $config)));
}

function getConfigValue($value)
{
  return is_bool($value) ? ($value ? 'yes' : 'no') : $value;
}

function askValue($prompt, &$value, $show = true)
{
  while (true)
  {
    if ($show)
    {
      $message = sprintf('%s [%s]? ', $prompt, getConfigValue($value));
    }
    else
    {
      $message = sprintf('%s? ', $prompt);
    }
    echo $message;
    $input = trim(fgets(STDIN));
    // dont't modify value if just empty (press ENTER)
    if (0 == strlen($input))
    {
      return;
    }
    if (is_bool($value))
    {
      $input = strtolower($input);
      if (in_array($input, array('y', 'n')))
      {
        $value = 'y' == $input ? true : false;
        break;
      }
    }
    elseif (is_int($value))
    {
      if (is_numeric($input))
      {
        $value = (int) $input;
        break;
      }
    }
    else
    {
      $value = $input;
      break;
    }
  }
}

function mergeFormatter(&$setup, $configs)
{
  $keys = array_keys($setup);
  for ($i = 0; $i < count($keys); $i++)
  {
    if (isset($configs[$keys[$i]]))
    {
      $setup[$keys[$i]] = $configs[$keys[$i]];
    }
  }
}

function setupFormatter(&$setup)
{
  $keys = array_keys($setup);
  for ($i = 0; $i < count($keys); $i++)
  {
    $label = getConfigName($keys[$i]);
    $value = $setup[$keys[$i]];
    askValue($label, $value);
    $setup[$keys[$i]] = $value;
  }
}

function main($filename, $dir, $params, $options)
{
  $setup = array();
  $configs = array();
  // check config file
  if ($config = $params['config'])
  {
    if (!is_readable($config))
    {
      echo "Can't read config file $config, using interactive mode.\n\n";
    }
    else
    {
      if (null !== ($data = json_decode(file_get_contents($config), true)))
      {
        echo "Using config file $config for parameters.\n\n";
        if (isset($data['export']))
        {
          $params['export'] = $data['export'];
        }
        if (isset($data['zip']))
        {
          $options['zip'] = (bool) $data['zip'];
        }
        if (isset($data['dir']))
        {
          $dir = $data['dir'];
        }
        if (isset($data['params']))
        {
          $configs = $data['params'];
        }
      }
      else
      {
        echo "Ignoring invalid config file $config.\n\n";
      }
    }
  }
  // main export checking
  switch (strtolower($export = $params['export']))
  {
    case 'doctrine1':
      $title = 'Doctrine 1.0 YAML schema';
      $setup = array(
        'extendTableNameWithSchemaName' => false,
      );
      $formatter_class = '\MwbExporter\Formatter\Doctrine1\Yaml\Loader';
      $extension = 'yml';
      break;

    case 'doctrine2-annotation':
      $title = 'Doctrine 2.0 Annotation Classes';
      $setup = array(
        'enhancedManyToManyDetection'   => false,
        'bundleNamespace'               => '',
        'entityNamespace'               => '',
        'repositoryNamespace'           => '',
        'useAnnotationPrefix'           => 'ORM\\',
        'useAutomaticRepository'        => true,
        'indentation'                   => 4,
        'filename'                      => '%entity%.%extension%',
      );
      $formatter_class = '\MwbExporter\Formatter\Doctrine2\Annotation\Loader';
      $extension = 'php';
      break;

    case 'doctrine2-yml':
      $title = 'Doctrine 2.0 YAML schema';
      $setup = array(
        'extendTableNameWithSchemaName' => false,
        'bundleNamespace'               => '',
        'entityNamespace'               => '',
        'repositoryNamespace'           => '',
        'useAutomaticRepository'        => true,
        'indentation'                   => 4,
        'filename'                      => '%entity%.orm.%extension%',
      );
      $formatter_class = '\MwbExporter\Formatter\Doctrine2\Yaml\Loader';
      $extension = 'yml';
      break;

    case 'zend-dbtable':
      $title = 'Zend DbTable';
      $setup = array(
          'tablePrefix'               => 'Application_Model_DbTable_',
          'parentTable'               => 'Zend_Db_Table_Abstract',
          'generateDRI'               => false,
          'generateGetterSetter'      => false,
          'indentation'               => 4,
          'filename'                  => 'DbTable/%schema%/%entity%.%extension%',
          'generateSchema'            => true,
          'generateName'              => true,
          'generateReferences'        => true,
          'generatePrimary'        => true,
      );
      $formatter_class = '\MwbExporter\Formatter\Zend\DbTable\Loader';
      $extension = 'php';
      break;

    case 'zend-restcontroller':
      $title = 'Zend Rest Controller';
      $setup = array(
          'tablePrefix'               => '',
          'parentTable'               => 'Zend_Rest_Controller',
          'indentation'               => 4,
          'filename'                  => 'RestController/%entity%Controller.%extension%',
      );
      $formatter_class = '\MwbExporter\Formatter\Zend\Controller\Loader';
      $extension = 'php';
      break;


    case 'js-sencha3model':
      $title = 'JS Sencha3Model';
      $setup = array(
          'classPrefix'               => 'SysX.App',
          'parentClass'               => 'SysX.Ui.App',
          'indentation'               => 4,
          'filename'                  => 'JS/%schema%/%entity%.%extension%',
      );
      $formatter_class = '\MwbExporter\Formatter\JS\Sencha3Model\Loader';
      $extension = 'js';
         	break;

    default:
      echo "Unknown export type: $export, use --help to show more option.\n\n";
      die(0);
      break;
  }

  echo sprintf("Exporting %s as %s.\n\n", basename($filename), $title);
  $setup = array_merge(array('skipPluralNameChecking' => false), $setup);
  if (count($setup))
  {
    if (count($configs))
    {
      mergeFormatter($setup, $configs);
    }
    else
    {
      $ask = false;
      askValue('Would you like to change the setup configuration before exporting', $ask);
      if ($ask)
      {
        setupFormatter($setup);
      }
    }
  }
  // save export parameters
  if ($options['saveconfig'])
  {
    file_put_contents('export.json', json_encode(array('export' => $export, 'zip' => $options['zip'], 'dir' => $dir, 'params' => $setup)));
  }

  // lets stop the time
  $start = microtime(true);

  // enable autoloading of classes
  require_once(__DIR__ . '/../lib/MwbExporter/Core/SplClassLoader.php');
  $classLoader = new SplClassLoader();
  $classLoader->setIncludePath(__DIR__ . '/../lib');
  $classLoader->register();

  // create a formatter
  $formatter = new $formatter_class($setup);

  // parse the mwb file
  $mwb = new \MwbExporter\Core\Workbench\Document($filename, $formatter);

  // show the export output of the mwb file
  //echo $mwb->display();

  // save as zip file
  if ($options['zip'])
  {
    $exporter = new \MwbExporter\Core\Helper\ZipFileExporter($dir);
  }
  else
  {
    $exporter = new \MwbExporter\Core\Helper\FileExporter($dir);
  }
  $mwb->export($exporter, $extension);
  echo sprintf("File exported to %s\n\n", $exporter->getFileName());

  // show some information about used memory
  echo (memory_get_peak_usage(true) / 1024 / 1024) . " MB used\n";

  // show the time needed to parse the mwb file
  $end = microtime(true);
  echo sprintf('%0.3f', $end-$start) . " sec needed\n";
  die(1);
}

// ----------------------------------------- MAIN ---------------------------------------------------- //

// default values
$arguments = $_SERVER['argv'];
$options = array(
  'help'          => false,
  'zip'           => false,
  'saveconfig'    => false,
);
$params = array(
  'export'        => 'doctrine2-annotation',
  'config'        => null,
);

array_shift($arguments);
parseCmdLine($arguments, $params, $options, $values);
if ($options['help'] || count($values) < 1)
{
  usage();
}

main($values[0], count($values) > 1 ? $values[1] : getcwd(), $params, $options);

// ----------------------------------------- EOF ----------------------------------------------------- //

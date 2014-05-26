<?php

/*
 * Copyright (c) 2012-2014 Toha <tohenk@yahoo.com>
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

try {
    require_once dirname(__FILE__).'/../lib/autoload.php';
} catch (\Exception $e) {
    echo $e->getMessage()."\n";

    die(1);
}

use MwbExporter\Bootstrap;
use MwbExporter\Formatter\FormatterInterface;

// ----------------------------------------- CONST --------------------------------------------------- //

const CMD_PARAM_EXPORT       = 'export';
const CMD_PARAM_CONFIG       = 'config';
const CMD_OPT_SAVE_CONFIG    = 'saveconfig';
const CMD_OPT_LIST_EXPORTER  = 'list-exporter';
const CMD_OPT_NO_AUTO_CONFIG = 'no-auto-config';
const CMD_OPT_ZIP            = 'zip';
const CMD_OPT_HELP           = 'help';

// ----------------------------------------- COMMAND LINE -------------------------------------------- //

function showTitle()
{
    $version = FormatterInterface::VERSION;
    echo <<<EOF
MySQL Workbench Schema Exporter version $version.
Copyright (c) 2010-2014 Johannes Mueller <circus2@web.de>
Copyright (c) 2012-2014 Toha <tohenk@yahoo.com>


EOF;
}

function usage()
{
    $self = basename($_SERVER['argv'][0]);

    showTitle();

    echo <<<EOF
Usage:
$self [options] FILENAME [DEST]

Options:
--export=type       Select the exporter, for a list of available exportor
                    use --list-exporter option.
--config=file       Read file for export parameters, in JSON format.
--saveconfig        Save export parameters to file.
--list-exporter     List all available exporter.
--no-auto-config    Disable automatic config file lookup in the current
                    directory if no --config option specified. Normally,
                    application will looking config file named export.json.
--zip               Export as zip archive.

EOF;
    die(0);
}

function parseCmdLine($args = array(), &$params = array(), &$options = array(), &$values = array())
{
    $values = array();
    while (true) {
        if (!count($args)) {
            break;
        }
        $p        = strpos($args[0], '=');
        $is_opt   = '--' == substr($args[0], 0, 2);
        $is_param = $is_opt && false !== $p;
        $arg      = $is_opt ? ($is_param ? substr($args[0], 2, $p - 2) : substr($args[0], 2)) : $args[0];
        $val      = $is_param ? substr($args[0], $p + 1) : null;
        if ($is_opt) {
            $found = false;
            if ($is_param) {
                if ($found = array_key_exists($arg, $params)) {
                    $params[$arg] = $val;
                }
            } else {
                if ($found = array_key_exists($arg, $options)) {
                    $options[$arg] = true;
                }
            }
            if (!$found) {
                echo sprintf("Unknown option %s.\n", $arg);
                die(1);
            }
        } else {
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
    while (true) {
        if ($show) {
            $message = sprintf('%s [%s]? ', $prompt, getConfigValue($value));
        } else {
            $message = sprintf('%s? ', $prompt);
        }
        echo $message;
        $input = trim(fgets(STDIN));
        // dont't modify value if just empty (press ENTER)
        if (0 == strlen($input)) {
            return;
        }
        if (is_bool($value)) {
            $input = strtolower($input);
            if (in_array($input, array('y', 'n'))) {
                $value = 'y' == $input ? true : false;
                break;
            }
        } elseif (is_int($value)) {
            if (is_numeric($input)) {
                $value = (int) $input;
                break;
            }
        } else {
            $value = $input;
            break;
        }
    }
}

function mergeFormatter(&$setup, $configs)
{
    $keys = array_keys($setup);
    for ($i = 0; $i < count($keys); $i++) {
        if (isset($configs[$keys[$i]])) {
          $setup[$keys[$i]] = $configs[$keys[$i]];
        }
    }
}

function setupFormatter(&$setup)
{
    $keys = array_keys($setup);
    for ($i = 0; $i < count($keys); $i++) {
        $label = getConfigName($keys[$i]);
        $value = $setup[$keys[$i]];
        askValue($label, $value);
        $setup[$keys[$i]] = $value;
    }
}

function listFormatter($bootstrap, $ordered = false)
{
    $i = 0;
    $formatters = $bootstrap->getFormatters();
    // find the longest formatter name
    $len = max(array_map('strlen', array_keys($formatters))) + 1;
    $nlen = strlen((string) count($formatters));
    foreach ($formatters as $name => $class) {
        $i++;
        $formatter = $bootstrap->getFormatter($name);
        echo sprintf("%s %-${len}s %s\n", $ordered ? str_pad((string) $i, $nlen, ' ', STR_PAD_LEFT).'.' : '-', $name, $formatter->getTitle());
    }

    return $formatters;
}

function chooseFormatter($bootstrap)
{
    $choice = 1;
    $formatters = listFormatter($bootstrap, true);
    while (true) {
        askValue("Enter choice", $choice);
        if ($choice > 0 && $choice <= count($formatters)) {
            break;
        }
    }
    $keys = array_keys($formatters);

    return $keys[$choice - 1];
}

function main($filename, $dir, $params, $options)
{
    try {
        showTitle();

        // check the existance of filename
        if (!is_readable($filename)) {
            echo sprintf("Can't find document %s.\n\n", $filename);
            die(1);
        }

        $setup = array();
        $configs = array();

        // bootstrap
        $bootstrap = new Bootstrap();
        if ($options[CMD_OPT_LIST_EXPORTER]) {
            echo "Supported exporter:\n";
            listFormatter($bootstrap, true);
            die(0);
        }

        // lookup config file export.json
        if (!$options[CMD_OPT_NO_AUTO_CONFIG] && !$params[CMD_PARAM_CONFIG]) {
            $config = getcwd().DIRECTORY_SEPARATOR.'export.json';
            if (is_readable($config)) {
                $params[CMD_PARAM_CONFIG] = $config;
            }
        }

        // check config file
        if ($config = $params[CMD_PARAM_CONFIG]) {
            if (!is_readable($config)) {
                echo sprintf("Can't read config file %s, using interactive mode.\n\n", $config);
            } else {
                if (null !== ($data = json_decode(file_get_contents($config), true))) {
                    echo sprintf("Using config file %s for parameters.\n\n", $config);
                    if (isset($data[CMD_PARAM_EXPORT])) {
                        $params[CMD_PARAM_EXPORT] = $data[CMD_PARAM_EXPORT];
                    }
                    if (isset($data[CMD_OPT_ZIP])) {
                        $options[CMD_OPT_ZIP] = (bool) $data[CMD_OPT_ZIP];
                    }
                    if (isset($data['dir'])) {
                        $dir = $data['dir'];
                    }
                    if (isset($data['params'])) {
                        $configs = $data['params'];
                    }
                } else {
                    echo sprintf("Ignoring invalid config file %s.\n\n", $config);
                }
            }
        }

        // choose exporter to use if not specified
        if (null === $params[CMD_PARAM_EXPORT]) {
            // choose exporter to use
            echo "Choose which exporter to use:\n";
            $params[CMD_PARAM_EXPORT] = chooseFormatter($bootstrap);
            echo "\n";
        }

        // get formatter after getting the parameter export either from command line or config file
        if (!$formatter = $bootstrap->getFormatter($params[CMD_PARAM_EXPORT])) {
            echo sprintf("Unsupported exporter %s. Use --%s option to show all available exporter.", $params[CMD_PARAM_EXPORT], CMD_OPT_LIST_EXPORTER);
            die(1);
        }

        // parameters customization
        echo sprintf("Exporting %s as %s.\n\n", basename($filename), $formatter->getTitle());
        $setup = $formatter->getConfigurations();
        if (count($configs)) {
            mergeFormatter($setup, $configs);
        } else {
            $ask = false;
            askValue('Would you like to change the setup configuration before exporting', $ask);
            if ($ask) {
                setupFormatter($setup);
            }
            echo "\n";
        }

        // save export parameters
        if ($options[CMD_OPT_SAVE_CONFIG]) {
            file_put_contents('export.json', json_encode(
                array(
                    CMD_PARAM_EXPORT => $params[CMD_PARAM_EXPORT], 
                    CMD_OPT_ZIP => $options[CMD_OPT_ZIP], 
                    'dir' => $dir, 'params' => $setup
                )
            ));
        }

        // start time
        $start = microtime(true);

        // parse the mwb file
        $formatter->setup($setup);
        $document = $bootstrap->export($formatter, $filename, $dir, $options[CMD_OPT_ZIP] ? 'zip' : 'file');

        // end time
        $end = microtime(true);

        if ($document) {
            echo sprintf("File exported to %s.\n\n", $document->getWriter()->getStorage()->getResult());
            // show some information about used memory
            // show the time needed to parse the mwb file
            echo sprintf("Done in %0.3f second, %0.3f MB memory used.\n", $end - $start, memory_get_peak_usage(true) / 1024 / 1024);
            die(0);
        } else {
            echo "Export failed, may be there is no storage available.\n\n";
            die(1);
        }
    } catch (\Exception $e) {
        echo "Error:\n";
        echo $e->getMessage();
        die(1);
    }
}

// ----------------------------------------- MAIN ---------------------------------------------------- //

// default values
$arguments = $_SERVER['argv'];
$options = array(
    CMD_OPT_HELP           => false,
    CMD_OPT_ZIP            => false,
    CMD_OPT_SAVE_CONFIG    => false,
    CMD_OPT_LIST_EXPORTER  => false,
    CMD_OPT_NO_AUTO_CONFIG => false,
);
$params = array(
    CMD_PARAM_EXPORT        => null,
    CMD_PARAM_CONFIG        => null,
);

array_shift($arguments);
parseCmdLine($arguments, $params, $options, $values);
if ($options[CMD_OPT_HELP] || (count($values) < 1) && !$options[CMD_OPT_LIST_EXPORTER]) {
    usage();
}

main(count($values) ? $values[0] : null, count($values) > 1 ? $values[1] : getcwd(), $params, $options);

// ----------------------------------------- EOF ----------------------------------------------------- //
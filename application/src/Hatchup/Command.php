<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Hatchup;

use CommandLine;
use Hatchup\App\Config;
use Hatchup\Command\CommandInterface;
use Hatchup\Command\Exception;

/**
 * This class should be be instantiated on its own
 *
 * Class Command
 *
 * @package Hatchup
 */
abstract class Command
{

    /**
     * @param string $commandDir
     *
     * @throws \Exception
     */
    final public static function execute($commandDir)
    {

        if (!file_exists($commandDir)) {
            throw new Exception(sprintf('Cannot read command directory %s', $commandDir));
        }

        $args = CommandLine::parseArgs($_SERVER['argv']);
        $parameters = Config::getInstance();

        $commandList = scandir($commandDir);
        $validCommands = array();

        $startTime = microtime(true);

        foreach ($commandList as $command) {
            if (strpos($command, '.php') !== false
                && !in_array($command, array('Exception.php', 'CommandInterface.php'))
            ) {
                $name = substr($command, 0, -4);

                $validCommands[strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $name))] = $name;
            }
        }
        unset($command);

        if (isset($args[0]) && array_key_exists($args[0], $validCommands)) {


            $commandFile = sprintf('%s/%s.php', $commandDir, $validCommands[$args[0]]);
            include $commandFile;

            $declared = get_declared_classes();
            $commandClass = end($declared);

            /** @var CommandInterface $command */
            $command = new $commandClass();

            if (!$command instanceof CommandInterface) {
                throw new \Exception(sprintf('%s is not a valid command, it needs to implement CommandInterface.',
                    $commandFile));
            }

            if (isset($args['help'])) {
                $helpInfo = (array)$command->help();

                if (empty($helpInfo)) {
                    echo 'No help available for this command' . PHP_EOL;
                } else {

                    if (!isset($helpInfo['description']) || !isset($helpInfo['arguments']) || !is_array($helpInfo['arguments'])) {
                        throw new \Exception(sprintf('%s does not contain a valid help section', $commandFile));
                    }

                    echo PHP_EOL . $helpInfo['description'] . PHP_EOL;
                    echo PHP_EOL . "OPTIONS: " . PHP_EOL;
                    foreach ($helpInfo['arguments'] as $argument => $description) {
                        echo sprintf("   --%s%s%s", str_pad($argument, 15), $description, PHP_EOL);
                    }
                }

                die(0);
            }

            try {
                $args = array_splice($args, 1);
                $command->run($parameters, $args);

                $endTime = microtime(true);

                $totalTime = $endTime - $startTime;

                echo PHP_EOL . sprintf('=> Total runtime: %.2f seconds', $totalTime) . PHP_EOL;

            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                die(1);
            }

            // exit with code 0 upon success
            die(0);

        } else {
            echo 'Error: Invalid command provided' . PHP_EOL;
            echo sprintf('Valid commands:%s %s %s', PHP_EOL, implode(",\n ", array_keys($validCommands)), PHP_EOL);
            die(127);
        }
    }
}
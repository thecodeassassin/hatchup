<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk <stephen@tca0.nl>
 */

namespace Hatchup\Command;

use Hatchup\App\Config;

/**
 * Interface CommandInterface
 *
 * @package Hatchup\Command
 */
interface CommandInterface
{
    /**
     * @param Config  $config
     * @param array  $arguments
     *
     * @return mixed
     */
    public function run(Config $config, array $arguments);

    /**
     * @return array
     */
    public function help();

}

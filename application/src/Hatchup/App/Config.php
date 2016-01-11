<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace Hatchup\App;

use Hatchup\Util;

/**
 * Class Config
 *
 * @package Hatchup\App
 */
class Config implements \ArrayAccess
{
    protected $params = array();

    /**
     * @return Config
     *
     * @throws Exception
     */
    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new Config();

            // store the params
            $instance->params = $instance->processParameters('config.ini');
        }

        return $instance;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->params[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->params[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return null
     */
    public function offsetGet($offset)
    {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }


    /**
     * This method processes the config.ini file and caches it
     *
     * @param string $parametersFile full path to config.ini
     *
     * @return mixed|null
     * @throws Exception
     */
    public function processParameters($parametersFile)
    {
        $return = null;

        $parametersFilePath = CONFIG_DIR . '/' . $parametersFile;
        $cacheFile = $parametersFile . '_cache.php';
        $cacheFilePath = CACHE_DIR . '/' . $cacheFile;

        if (!is_file($parametersFilePath)) {
            throw new Exception(sprintf('Config file %s cannot be found!', $parametersFile));
        }

        if (is_file($cacheFilePath) && (filemtime($parametersFilePath) == filemtime($cacheFilePath))) {
            $return = include $cacheFilePath;
        } else {
            if (!is_writable(CACHE_DIR)) {
                throw new Exception(
                    sprintf(
                        'Cache file %s could not be created at %s, please make sure it is writable.',
                        $cacheFile,
                        CACHE_DIR
                    )
                );
            }


            $parameters = @parse_ini_file($parametersFilePath);

            if (!$parameters) {
                throw new Exception(sprintf('Could not parse config file'));
            }

            // export the config and write it to the file
            $config = var_export($parameters, true);
            $date = date("Y-m-d h:i:s");
            $cacheContents = <<<EOD
    <?php

    /**
     * Generated with \Hatchup\App
     *
     * on {$date}
     */

    return {$config};
EOD;

            // put the parsed config in the cache file and modify the access
            // times on both the config and the cache file
            file_put_contents($cacheFilePath, $cacheContents);
            Util::touchFile($cacheFilePath);

            $return = include $cacheFilePath;
        }
        return $return;
    }

}
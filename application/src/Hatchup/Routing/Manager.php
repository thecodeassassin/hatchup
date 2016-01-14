<?php
/**
 * @author Patrick JL Laso <wld1373@gmail.com>
 * @author Stephen "TheCodeAssassin" Hoogendijk <stephen@tca0.nl>
 *
 * Based on thecodeassassin/slim-routing-manager
 * Adapted to Slim 3 for Hatchup
 */

namespace Hatchup\Routing;

use ReflectionClass;
use ReflectionMethod;
use Hatchup\Util;
use Slim\App;

/**
 * Routing Manager package
 *
 * Class Manager
 *
 * @package Hatchup\Routing
 */
class Manager
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**l
     * @var array
     */
    protected $controllers;

    /**
     * @var null
     */
    protected $routePrefix;

    /**
     * @var
     */
    protected $cacheFileName = 'compiled_routes.php';

    /**
     * @var string
     */
    protected $baseNameSpace = '\Hatchup\Controller';


    /**
     * @var App
     */
    protected $app;

    /**
     * @param App    $app            Instance of \Slim\App
     * @param array  $controllerDirs array of directories to scan for controllers
     * @param string $cacheDir       directory where to store the cached routes
     * @param null   $routePrefix    Prefix to add to every route (except if they have @noPrefix)
     */
    public function __construct(App $app, array $controllerDirs, $cacheDir, $routePrefix = null)
    {

        $this->app = $app;

        // if the directory does not exist, try to create it
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }

        $this->cacheDir = $cacheDir;

        if ($routePrefix) {
            $this->routePrefix = $routePrefix;
        }

        // load the controllers
        if (count($controllerDirs)) {
            foreach ($controllerDirs as $controllerPath) {
                $controllers = $this->readDirectory($controllerPath);
                if (count($controllers)) {
                    $this->controllers = $controllers;
                }
            }
        }

    }


    /**
     * Generates a new compiled routes file and includes it into the application
     *
     * @throws Exception
     *
     */
    public function generateRoutes()
    {
        $content = '';
        $hasChanged = false;

        $modTimes = array();
        $classes = $this->controllers;



        // if the cache file does not exist, update the cache
        if (!is_file($this->cacheFile())) {
            $hasChanged = true;
        } else {
            // include the cache file and get the mod times variable
            include_once $this->cacheFile();

            // if a new controller was added to the list, update the cache
            if (count($modTimes) != count($classes)) {
                $hasChanged = true;
            } else {
                foreach ($classes as $classFile) {
                    // if a controller is modified (any controller) update the cache
                    $modTime = $modTimes[$classFile];
                    $hasChanged = (filemtime($classFile) != $modTime);

                    if ($hasChanged) {
                        break;
                    }
                }
            }
        }


        // update the cache only if the controllers have changed
        if ($hasChanged) {
            foreach ($classes as $classFile) {
                $content .= $this->processClass($classFile);
            }
            include_once $this->writeCache($classes, $content);
        }

    }

    /**
     * gets the full path and the name of cache file
     *
     * @return string
     */
    protected function cacheFile()
    {
        return $this->cacheDir . '/' . $this->cacheFileName;
    }

    /**
     * This method writes the cache content into cache file
     *
     * @param array $classes
     * @param       $content
     *
     * @return string
     */
    protected function writeCache($classes, $content)
    {
        $modTimes = array();
        foreach ($classes as $classFile) {
            $modTimes[$classFile] = filemtime($classFile);
        }
        $modTimes = var_export($modTimes, true);

        $date = date("Y-m-d h:i:s");
        $content = <<<EOD
<?php


/**
 * Generated with \Hatchup\Routing\Manager
 *
 * on {$date}
 */
\$modTimes = {$modTimes};

{$content}

EOD;
        $fileName = $this->cacheFile();
        file_put_contents($fileName, $content);

        return $fileName;
    }

    /**
     * @param $classFile
     *
     * @return string
     * @throws Exception
     */
    protected function processClass($classFile)
    {

        $content = file_get_contents($classFile);
        $result = '';

        preg_match_all('/class\s+(\w*)\s*(extends\s+)?([^{])*/s', $content, $mclass, PREG_SET_ORDER);
        $className = $mclass[0][1];
        if (!$className) {
            throw new Exception(sprintf('Class not found in %s', $classFile));
        }

        $refl = new ReflectionClass($this->baseNameSpace.'\\'.$className);

        $methods = $refl->getMethods();

        /** @var ReflectionMethod $method */
        foreach ($methods as $method) {
            if (strpos($method->getName(), 'Action')) {
                $doc = $method->getDocComment();
                preg_match_all("/\*\s+@([a-zA-z]+)\s*\('([^']*)'\)/s", $doc, $annotations);

                $httpMethods = [];
                $route = '';

                foreach ($annotations[1] as $idx => $annotation) {
                    if (strtolower($annotation) == 'route') {
                        $route = $annotations[2][$idx];
                    } elseif (strtolower($annotation) == 'method') {
                        $httpMethods[] = $annotations[2][$idx];
                    }
                }

                if (count($httpMethods) == 0 || empty($route)) {
                    throw new Exception(
                        sprintf(
                            'Method %s has invalid annotations, route or method invalid.',
                            $method->getName()
                        )
                    );
                }

                $result .= str_replace('\\', '\\\\', sprintf(
                    '$this->app->map(%s, "%s", "%s\%s:%s");' . PHP_EOL,
                    Util::shortExport($httpMethods),
                    $route,
                    $this->baseNameSpace,
                    $className,
                    $method->getName()
                ));
            }
        }

        return $result;
    }


    /**
     * Reads the contents of this dir and returns only dirs
     * that have first letter capitalized
     *
     * @param string $dir Directory name
     *
     * @return array
     */
    protected function readDirectory($dir)
    {
        $entries = array();
        foreach (scandir($dir) as $entry) {
            if (($entry != '.') && ($entry != '..')) {
                $current = "$dir/$entry";
                if ($current != $dir) {
                    if (is_dir($current)) {
                        $aux = $this->readDirectory($current);
                        $entries = array_merge($entries, $aux);
                    } else {
                        if (preg_match("/\w*?Controller.php/", $entry)) {
                            $entries[] = $current;
                        }
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * @return null
     */
    public function getRoutePrefix()
    {
        return $this->routePrefix;
    }

    /**
     * @param null $routePrefix
     */
    public function setRoutePrefix($routePrefix)
    {
        $this->routePrefix = $routePrefix;
    }

    /**
     * @return mixed
     */
    public function getCacheFileName()
    {
        return $this->cacheFileName;
    }

    /**
     * @param mixed $cacheFileName
     */
    public function setCacheFileName($cacheFileName)
    {
        $this->cacheFileName = $cacheFileName;
    }

}

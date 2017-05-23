<?php
/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 5/23/17
 * Time: 1:51 PM
 */
namespace CORE;

use Composer\Autoload\ClassLoader;

/**
 * !!! Simple Config class
 * !!! Simple service locator (Dependency Container)
 * @todo split into Config and DI classes
 * @todo implement FileConfig object instead of $fileLocation arg (Open-Closed & Liskov Subs SOLID principles)
 * @todo replace "parseFile" with FileConfig::parse
 *
 * Class Config
 * @package CORE
 * @property Logger $logger
 * @property \stdClass $raw
 */
class Config extends \stdClass
{
    protected $loader;

    public $root;

    /**
     * @param ClassLoader $loader
     * @param $root
     * @param $path
     */
    public function __construct(ClassLoader $loader, $root, $path)
    {
        $this->loader = $loader;
        $this->root = $root;

        $this->loadConfig($path);
    }

    protected function loadConfig($path)
    {
        $tmp = new \stdClass();

        foreach (json_decode(json_encode(parse_ini_file($path, true)), false) as $k => $v) {
            $tmp->$k = $v;
        }

        $this->raw = $tmp;

        return $this;
    }

    /**
     * !!! Very Simple DI
     * @param $k
     * @return null
     */
    public function __get($k)
    {
        if ('logger' == $k) {
            $v = new Logger(join('/', array(
                $this->root,
                $this->raw->app->{'dir.log'},
                'log.' . date('Y-m-d') . '.log'
            )));
        } else {
            $v = null;
        }

        $this->$k = $v;
        return $v;
    }
}
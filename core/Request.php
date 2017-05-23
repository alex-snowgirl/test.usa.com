<?php
/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 4/13/17
 * Time: 5:32 PM
 */
namespace CORE;

/**
 * !!! Simple Web Request class
 *
 * Class Request
 * @package CORE
 */
class Request extends \stdClass
{
    protected $handlers;
    protected $params;

    public function __construct()
    {
        $this->params = array();
        $this->handlers = array(
            'unknown' => function () {}
        );
    }

    public function __set($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function __get($key)
    {
        return isset($this->$key) ? $this->params[$key] : null;
    }

    public function __isset($key)
    {
        return isset($this->params[$key]);
    }

    public function on($method, $request, $handler)
    {
        list($method, $request) = $this->prepareMethodAndRequest($method, $request);

        if (!isset($this->handlers[$method])) {
            $this->handlers[$method] = array();
        }

        $this->handlers[$method][$request] = $handler;

        return $this;
    }

    public function onUnknown($handler)
    {
        $this->handlers['unknown'] = $handler;
        return $this;
    }

    public function parse()
    {
        $this->iniRequestParams();

        list($method, $request) = $this->prepareMethodAndRequest($this->_method, $this->_request);

        if (!isset($this->handlers[$method])) {
            $this->handlers['unknown'];
        }

        foreach ($this->handlers[$method] as $path => $handler) {
            $keys = array();

            $pathRegexp = preg_replace_callback('#{([^}]+)}#', function ($matches) use (&$keys) {
                if (isset($matches[1])) {
                    $keys[] = $matches[1];
                }

                return '([^/]+)';
            }, $path);

            $pathRegexp = '#^' . addslashes($pathRegexp) . '#';

            if (preg_match($pathRegexp, $request, $values)) {
                array_shift($values);

                foreach (array_combine($keys, $values) as $key => $value) {
                    $this->$key = $value;
                }

                return $handler;
            }
        }

        return $this->handlers['unknown'];
    }

    protected function prepareMethodAndRequest($method, $request)
    {
        return array(
            strtolower($method),
            trim($request, '/')
        );
    }

    public function iniRequestParams()
    {
        $uri = trim($_SERVER['REQUEST_URI'], '/');
        $script = trim($_SERVER['SCRIPT_NAME'], '/');

        if (0 === strpos($uri, $script)) {
            $uri = substr($uri, strlen($script));
        }

        $tmp = explode('?', $uri);
        $uri = trim($tmp[0], '/');
        $pathExplode = explode('/', $uri);

        if (1 == sizeof($pathExplode) % 2) {
            $pathExplode[] = '';
        }

        $pathParams = array();

        for ($i = 0, $l = sizeof($tmp); $i < $l; $i += 2) {
            $pathParams[$pathExplode[$i]] = $pathExplode[$i + 1];
        }

        parse_str(file_get_contents("php://input"), $stream);

        $this->params = $pathParams + $_GET + $_POST + $_FILES + $_REQUEST + $stream;

        $this->params['_method'] = $method = $_SERVER['REQUEST_METHOD'];
        $this->params['_request'] = $uri;

        return $this;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 4/14/17
 * Time: 7:42 PM
 */
namespace CORE;

/**
 * Simple Web Application class
 *
 * Class App
 * @package CORE
 */
class App extends Observable
{
    const EVENT_EXCEPTION = 0;
    const EVENT_PRE_RUN = 1;
    const EVENT_POST_RUN = 2;

    /**
     * Public for simple access interface @todo...
     * @var Config
     */
    public $config;
    /**
     * Public for simple access interface @todo...
     * @var Request
     */
    public $request;
    /**
     * Public for simple access interface @todo...
     * @var Handlers
     */
    public $handlers;
    /**
     * Public for simple access interface @todo...
     * @var Response
     */
    public $response;

    public function __construct(Config $config, Handlers $Handlers, View $view)
    {
        $this->config = $config;
        $this->handlers = $Handlers;

        /**
         * !!! This is not good (Open-Closed principle, but considering simplicity of our app...)
         */
        $this->request = new Request();
        $this->response = new Response($view);
    }

    public function __get($k)
    {
        return $this->config->$k;
    }

    protected function executeHandler($handler)
    {
        if ($handler instanceof \Closure) {
            $handler($this);
        } elseif (is_callable($handler, true)) {
            call_user_func($handler, $this);
        } else {
            //@todo...
        }
    }

    public function run()
    {
        $this->trigger(self::EVENT_PRE_RUN);
        try {
            $this->handlers->bindHandlers($this->request);
            $handler = $this->request->parse();
            $this->executeHandler($handler);
            $this->response->send();
        } catch (Exception $ex) {
            $this->trigger(self::EVENT_EXCEPTION, $ex);
        }

        $this->trigger(self::EVENT_POST_RUN);
    }
}
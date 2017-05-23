<?php
/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 5/23/17
 * Time: 11:47 PM
 */
namespace CORE;

/**
 * !!! Very Simple Router & Controller abstraction (assuming for WEB only...)
 * @todo split...
 * @todo improve...
 *
 * Interface Handlers
 * @package CORE
 */
abstract class Handlers
{
    public function bindHandlers(Request $request)
    {
        $this->bindCustom($request);
        $this->bindDefault($request);
    }

    abstract public function bindCustom(Request $request);

    abstract public function bindDefault(Request $request);
}
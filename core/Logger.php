<?php
/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 5/23/17
 * Time: 3:07 PM
 */
namespace CORE;

/**
 * !!! Very Simple Logger class
 * @todo create logger interface or abstract class and implement it as Disc Logger
 *
 * Class Logger
 * @package CORE
 */
class Logger
{
    protected $file;

    public function __construct($file)
    {
//        var_dump($file);die;
        if (!file_exists($file)) {
            @file_put_contents($file, '');
        }

        if (!is_writable($file)) {
            @chmod($file, 0755);
        }

        if (file_exists($file) && is_writable($file)) {
            $this->file = $file;
            ini_set('error_log', $this->file);
        }
    }

    public function log($msg)
    {
        if ($this->file) {
            error_log(join(' ', array(time(), $msg)) . "\n\n", 3, $this->file);
        }

        return $this;
    }

    public function logException(Exception $ex)
    {
        return $this->log(join("\r\n", array(
            $ex->getMessage(),
            $ex->getTraceAsString()
        )));
    }
}
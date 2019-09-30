<?php
namespace App\Service;


use EasySwoole\Component\Singleton;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * 记录日志
 */
class LogService
{
    use Singleton;

    private $logger;

    public function getLogger($name)
    {
        if($this->logger) {
            return $this->logger;
        }

        $this->logger = new Logger($name);

        $this->logger->pushHandler(new StreamHandler('Log/'.$name.'.log', Logger::DEBUG) );

        return $this->logger;
    }
}
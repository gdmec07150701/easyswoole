<?php
namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use App\Utility\Pool\RedisPool;
use EasySwoole\Socket\Dispatcher;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
//        $redis = RedisPool::defer();
//        $register->add($register::onWorkerStart, function (\swoole_server $server, int $workerId) {
//            \EasySwoole\Component\Timer::getInstance()->loop(2000, function () {
////                $redis ->
//                var_dump(1);
//            });
//        });
        // TODO: Implement mainServerCreate() method.
//        $timerId = \EasySwoole\Component\Timer::getInstance()->loop( 1000, function () {
//            var_dump('2222222');
//        });


//        $conf = new \EasySwoole\Socket\Config();
//        // 设置 Dispatcher 为 WebSocket 模式
//        $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
//        // 设置解析器对象
//        $conf->setParser(new WebSocketParser());
//        // 创建 Dispatcher 对象 并注入 config 对象
//        $dispatch = new Dispatcher($conf);
//        // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
//        $register->set(EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($dispatch) {
//            $dispatch->dispatch($server, $frame->data, $frame);
//        });

    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}
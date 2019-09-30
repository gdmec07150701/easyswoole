<?php


namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;
use App\Utility\Pool\RedisPool;
use App\Utility\Pool\MysqlPool;
use App\HttpController\Base;

class Index extends Base
{
    function index()
    {
        $db = MysqlPool::defer();
        var_dump($db->rawQuery('select * from test'));
        $redis = RedisPool::defer();
//        $redis->set('test', 'test');
        var_dump($redis->get('test'));

    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }
}
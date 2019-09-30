<?php
/**
 * Created by PhpStorm.
 * User: php
 * Date: 2019/3/18
 * Time: 10:32
 */

namespace App\Process;

use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Process;
use App\Utility\Pool\RedisObject;
use App\Utility\Pool\RedisPool;
use App\Utility\Pool\MysqlPool;
use App\Utility\Pool\MysqlObject;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Config;
use App\Model\TemplateSendModel;
use App\Model\TemplateMessageModel;
use App\Model\UserWeixinTokenModel;
use App\Utility\Curl;
use App\Service\WeixinMpService;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Component\Di;

class Consumer extends AbstractProcess
{
    private $isRun = false;
    private $secondIsRun = false;
    public function run($arg)
    {
        // TODO: Implement run() method.
        /*
         * 举例，消费redis中的队列数据
         * 定时500ms检测有没有任务，有的话就while死循环执行
         */
        $this->addTick(1000,function (){
            if(!$this->isRun){
                
                $this->isRun = true;

                while (true){
                    try{
                        $redis = RedisPool::defer();

                        $data = $redis->lPop('manualSend');

                        if($data){
                            Logger::getInstance()->console(json_encode($data)." running-redis");

                            $db = MysqlPool::defer();

                            $TemplateSendModel = new TemplateSendModel($db);

                            $UserWeixinTokenModel = new UserWeixinTokenModel($db);

                            $access_token = $UserWeixinTokenModel->getAccessToken();

                            $instance = \EasySwoole\EasySwoole\Config::getInstance();

                            $WeixinMpService = new WeixinMpService($instance->getConf('WEIXIN.APPID'),$instance->getConf('WEIXIN.SECRET'));

                            $data = unserialize($data);

                            $send = $WeixinMpService->mpTemplateMessage($access_token, $data['openid'], $data['template_id'], $data['url'], $data['tplMsgData']);

                            $send = json_decode($send, true);
                            Logger::getInstance()->console(json_encode($send)." running-send");
                            if ($send['errcode'] == 0) {
                                $tableData['sendStatus'] = 1;
                                $tableData['sendTime'] = time();
                                $tableData['errMessage'] = 'ok';

                                if(isset($data['markNum']) && !empty($data['markNum'])){
                                    $TemplateSendModel->updateByMarkNum($data['markNum'], $tableData);
                                }else{
                                    $TemplateSendModel->update($data['templateSendId'], $tableData);
                                }

                            }elseif(strpos($send['errmsg'],'access_token') !==false){
                                //如果是access token过期,则重置，再发送
                                $UserWeixinTokenModel->update('1',array('expire_time'=>0));

                                $redis->rpush("manualSend",serialize($data));
                                //Di::getInstance()->get("REDIS")->rpush("manualSend",serialize($data));

                            }else{
                                $tableData['sendStatus'] = 3;
                                $tableData['sendTime'] = time();
                                $tableData['errMessage'] = $send['errmsg'];
                                if(isset($data['markNum']) && !empty($data['markNum'])){
                                    $TemplateSendModel->updateByMarkNum($data['markNum'], $tableData);
                                }else{
                                    $TemplateSendModel->update($data['templateSendId'], $tableData);
                                }
                            }
                        }else{
                            break;
                        }

                    }catch (\Throwable $throwable){
                        break;
                    }
                }

                $this->isRun = false;
            }

        });

        $this->addTick(1000,function (){
            if(!$this->secondIsRun){

                $this->secondIsRun = true;

                while (true){
                    try{

                        $redis = RedisPool::defer();

                        $redisData = $redis->lPop('expiredTip');

                        if($redisData){
                            $mysqlObject = MysqlPool::defer();

                            $UserWeixinTokenModel = new UserWeixinTokenModel($mysqlObject);

                            $access_token = $UserWeixinTokenModel->getAccessToken();

                            $instance = \EasySwoole\EasySwoole\Config::getInstance();

                            $TemplateSendModel = new TemplateSendModel($mysqlObject);

                            $WeixinMpService = new WeixinMpService($instance->getConf('WEIXIN.APPID'),$instance->getConf('WEIXIN.SECRET'));

                            $data = unserialize($redisData);

                            if($data['triggerTime']<=time()) {
                                $send = $WeixinMpService->mpTemplateMessage($access_token, $data['openid'], $data['template_id'], $data['url'], $data['tplMsgData']);

                                if (isset($send['errcode']) && $send['errcode'] == 0) {
                                    $tableData['sendStatus'] = 1;
                                    $tableData['sendTime'] = time();
                                    $tableData['errMessage'] = 'ok';
                                    if(isset($data['templateSendId'])){
                                    $TemplateSendModel->update($data['templateSendId'], $tableData);
                                }
                            }elseif(strpos($send['errmsg'],'access_token') !==false){
                                    //如果是access token过期,则重置，再发送
                                    $UserWeixinTokenModel->update('1',array('expire_time'=>0));

                                    $redis->rpush("expiredTip",$redisData);
                                }

                            }else{
                                $redis->rPush('expiredTip',$redisData);
                            }

                        }else{
                            break;
                        }

                    }catch (\Throwable $throwable){
                        break;
                    }
                }

                $this->secondIsRun = false;
            }
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str, ...$args)
    {
        // TODO: Implement onReceive() method.
    }
}
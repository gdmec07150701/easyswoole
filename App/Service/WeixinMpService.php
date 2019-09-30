<?php

namespace App\Service;
use App\Service\CurlToolkit;

/**
 * 微信公众号服务类
 */
class WeixinMpService
{

    private $appid;
    private $secret;

    public function __construct($appid, $secret) {
        $this->appid    = $appid;
        $this->secret   = $secret;
    }

    //获取微信token
    public function getAccessToken():string
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->secret;
        $res = json_decode(CommonService::curlGet($url),true);
        return $res['access_token'];
    }

    
    //微信公众号模板消息发送
    public function mpTemplateMessage($access_token,$touser,$template_id,$url,$data=[])
    {
        $post_url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
        $post_data['touser'] = $touser;
        $post_data['template_id'] = $template_id;
        $post_data['url'] = $url;
        $post_data['data'] = $data;
        $data = CommonService::curlPost($post_url, json_encode($post_data));
        return $data;
    }

    public function sendDialogue($access_token,$sendMessage)
    {
        $json_message=json_encode($sendMessage);

        $url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;

        $res = CommonService::curlPost($url,urldecode($json_message));
        return json_decode($res,true);
    }

    //上传临时素材
    public function uploadMaterial($access_token,$params)
    {
        $URL ='https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type='.$params['type'];

        $postData = array('media'=>'@'.$params['media']);

        $result = CommonService::curlPost($URL,$postData);

        $data = @json_decode($result,true);

        return $data;
    }

    public function setUserActionSetId($params,$access_token)
    {
        $url = "https://api.weixin.qq.com/marketing/user_action_sets/add?version=v1.0&access_token=".$access_token;
        $result = CurlToolkit::request('POST',$url, $params);
        return $result;
    }

    public function getUserActionSetId($params,$access_token)
    {
        $url ="https://api.weixin.qq.com/marketing/user_action_sets/get?version=v1.0&access_token=".$access_token;
        $result = CurlToolkit::request('GET', $url, $params);
        return $result;
    }

    public function sendUserActions($params,$access_token)
    {
        $url = "https://api.weixin.qq.com/marketing/user_actions/add?version=v1.0&access_token=".$access_token;
        $result = CurlToolkit::request('POST', $url, json_encode($params),array('contentType'=>'application/json'),false);
        return $result;
    }
}

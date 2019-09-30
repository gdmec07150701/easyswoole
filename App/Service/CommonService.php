<?php

namespace App\Service;

use EasySwoole\EasySwoole\Config;

/**
 * 全局公共方法类（提供全局静态调用）
 */
class CommonService
{
    /**
     * 解密
     * @param $data
     * @return string
     */
    public static function decryptWithOpenssl($data):string
    {
        $secretKey = Config::getInstance()->getConf('secretKey');
        $key = $secretKey['key'];
        $iv = $secretKey['iv'];
        return openssl_decrypt(base64_decode($data),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv);
    }
    
    /**
     * 加密
     * @param $data
     * @return string
     */
    public static function encryptWithOpenssl($data):string
    {
        $secretKey = Config::getInstance()->getConf('secretKey');
        $key = $secretKey['key'];
        $iv = $secretKey['iv'];
        return base64_encode(openssl_encrypt($data,"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv));
    }
    
    
    /**构建会话加密函数，默认30天超时
     * @param $openid
     * @param int $exptime
     * @return string
     */
    public static function sessionEncrypt($openid, $exptime=2592000):string
    {
        $exptime = time() + $exptime;
        return self::encryptWithOpenssl($openid.'|'.$exptime);
    }
    
    /**
     * 验证会话token是否有效
     * @param $raw
     * @return bool
     */
    public static function sessionCheckToken($raw):int
    {
        
        //如果解密不出文本返回失败
        if(!$data = self::decryptWithOpenssl($raw)){
            return 0;
        }
        $token = explode('|', $data);
        //如果分离出来的openid或者exptime为空 返回失败
        if(!isset($token[0]) || !isset($token[1])){
            return 0;
        }
        //如果时间过期，返回失败
        if( $token[1] < time()){
            return 0;
        }
        return $token[0];
    }
    
    //普通curl_get
    public static function curlGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
     }
    
    //普通curl_post
    public static function curlPost($url,$post_data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
     }


    
}    
 

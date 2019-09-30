<?php
namespace App\Service;

class CurlToolkit
{
    public static function request($method, $url, $params = array(), $conditions = array(),$advance = true)
    {
        $conditions['userAgent']      = isset($conditions['userAgent']) ? $conditions['userAgent'] : '';
        $conditions['connectTimeout'] = isset($conditions['connectTimeout']) ? $conditions['connectTimeout'] : 10;
        $conditions['timeout']        = isset($conditions['timeout']) ? $conditions['timeout'] : 10;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $conditions['userAgent']);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $conditions['connectTimeout']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $conditions['timeout']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } elseif ($method == 'PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ($method == 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ($method == 'PATCH') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        if(isset($conditions['contentType']) && $conditions['contentType'] == 'application/json'){
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: '.$conditions['contentType']
                )
            );
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($curl);
        $curlinfo = curl_getinfo($curl);
        $header = substr($response, 0, $curlinfo['header_size']);
        $body   = substr($response, $curlinfo['header_size']);

        curl_close($curl);

        if($advance){
            if (empty($curlinfo['namelookup_time'])) {
                return array();
            }
        }

        if (isset($conditions['contentType']) && $conditions['contentType'] == 'plain') {
            return $body;
        }

        $body = json_decode($body, true);
        return $body;
    }

    public static function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        $data = json_decode($data, true);
        return $data;
    }

    public static function P($url = '', $param = '',$setting = null) {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);


        if(bpToolKit::is_data($setting)){
            foreach ($setting as $index => $item) {
                if(bpToolKit::is_data($item)){
                    switch (strtolower($index)) {
                        case "contenttype":curl_setopt($ch, CURLOPT_HTTPHEADER, array($item));break;
                    }
                }
            }
        }


        $data = curl_exec($ch);//运行curl
        $err = curl_error($ch);
        curl_close($ch);
        if($data == false){
            return $err;
        }
        return $data;
    }

    public static function request_post_header($url = '', $param = '',$header = null) {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl

        $useHeaderData = 0;

        if($header){
            $useHeaderData = 1;
            $tmp = "";
            foreach ($header as $index => $item) {
                $tmp[] = sprintf("%s:%s",$index,$item);
            }
            $header = $tmp;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        curl_setopt($ch, CURLOPT_HEADER, $useHeaderData);//设置header
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $error = curl_error($ch);
        $data = curl_exec($ch);//运行curl

        $isDebug = false;
        if($isDebug){
            echo curl_getinfo($ch, CURLINFO_HEADER_OUT);
        }
        curl_close($ch);
        $json = json_decode($data, true);
        if(!isset($json)){
            if($data == false){
                return $error;
            }
            return $data;
        }else{
            return $json;
        }
    }
}

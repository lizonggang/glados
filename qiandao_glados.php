<?php

/**
 * glados自动签到脚本
 * lizg
 * 2022-10-26
 * 0 1 * * *       php /home/deploy/shell/qiandao_glados.php
 */

set_time_limit(0);
date_default_timezone_set('PRC');
$iNum = rand(0, 80000);
sleep($iNum);

// 请修改成自己的cookie
$sCookie = '';
if (empty($sCookie)) {
    exit('请填写cookie' . PHP_EOL);
}

// 请求地址
$sUrl = 'https://glados.network/api/user/checkin';
// 头信息
$aHeader = array(
    'accept-language: zh-CN,zh;q=0.9,und;q=0.8,en;q=0.7',
    'cookie: ' . $sCookie,
    'origin: https://glados.network',
    'sec-ch-ua: "Chromium";v="106", "Google Chrome";v="106", "Not;A=Brand";v="99"',
    'sec-ch-ua-mobile: ?0',
    'sec-ch-ua-platform: "macOS"',
    'sec-fetch-dest: empty',
    'sec-fetch-mode: cors',
    'sec-fetch-site: same-origin',
    'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36',
);

$aData = array(
    'token' => 'glados.network'
);

$result = httpRequest($sUrl, 'POST', $aData, $aHeader);
if (empty($result)) {
    _funcLog('请求返回结果为空。');
}

$aResult = json_decode($result, TRUE);
$code = $aResult['code'];
$message = $aResult['message'];
if (0 == $code && 'Checkin! Get 1 Day' == $message) {
    _funcLog('签到成功，得到1天。');
} elseif (1 == $code && 'Please Try Tomorrow' == $message) {
    _funcLog('今天已经签到过，请明天再来。');
} else {
    _funcLog('code: ' . $aResult['code'] . ' message: ' .  $aResult['message']);
}


/**
 * CURL请求
 *
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug 调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method = "GET", $postfields = null, $headers = array(), $timeout = 30, $debug = false)
{
    $method = strtoupper($method);
    $ci     = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    // curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_TIMEOUT, $timeout); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                if (is_string($postfields)) {
                    parse_str($postfields, $output);
                    $tmpdatastr = http_build_query($output);
                } else {
                    $tmpdatastr = http_build_query($postfields);
                }
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($ssl) {
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
        curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    }
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response    = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code   = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
    // return array($http_code, $response,$requestinfo);
}

// 记录过程日志
function _funcLog($message)
{
    // 日志路径/tmp/qiandao_glados.log
    $sLogDir = '/tmp';
    $sLogName = $sLogDir . '/' . 'qiandao_glados' . '.log';

    if (!file_exists($sLogDir)) {
        exit($sLogDir . ' not exist!');
    }

    //打开文件
    if (!@file_exists($sLogName)) {
        $handle = @fopen($sLogName, 'w');
    } else {
        $handle = @fopen($sLogName, 'a');
    }

    //写入数据
    $sTime = date("Y-m-d H:i:s", time());
    $aContent[] = '[' . $sTime . '] ' . '[INFO]: ' . $message . "\r\n";
    $sContent = join("", $aContent);
    @fwrite($handle, $sContent);

    //关闭文件
    @fclose($handle);
}

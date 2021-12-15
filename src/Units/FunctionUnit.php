<?php
/**
 *Author:Syskey
 *Date:2021/11/25
 *Time:15:30
 **/

namespace EasyVod\Units;

class FunctionUnit
{
    /**
     * @param $url
     * @param string $method
     * @param array $data
     * @param array $config
     * @return bool|string
     *http单线程请求
     */
    public static function http_request($url, $method = "get", $data = [], $config = [])
    {
        $conputer_user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
        $mobile_user_agent = "Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //代理
        if (isset($config["proxy"])) {
            $proxy = array_filter(explode(":", $config["proxy"]));
            curl_setopt($ch, CURLOPT_PROXY, $proxy[0]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy[1]);
        }
        //参数为1表示传输数据，为0表示直接输出显示。
        curl_setopt($ch, CURLOPT_TIMEOUT, $config["timeout"] ?? 5);//设置超时时间
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, $conputer_user_agent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $config["header"] ?? []);
        //参数为0表示不带头文件，为1表示带头文件
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if ($method == "post") {
            //设置post方式提交
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * @param array $urlarr
     * @param array $config
     * @return array
     * http并发请求
     */
    public static function http_multi(array $urlarr, array $config = [])
    {
        $res = array();
        $mh = curl_multi_init();//创建多个curl语柄
        $user_agent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36";
        foreach ($urlarr as $k => $url) {
            $conn[$k] = curl_init($url);
            curl_setopt($conn[$k], CURLOPT_TIMEOUT, $config["timeout"] ?? 10);//设置超时时间
            curl_setopt($conn[$k], CURLOPT_USERAGENT, $config["user_agent"] ?? $user_agent);
            curl_setopt($conn[$k], CURLOPT_MAXREDIRS, 7);//HTTp定向级别
            curl_setopt($conn[$k], CURLOPT_NOBODY, 0);
            curl_setopt($conn[$k], CURLOPT_HEADER, 0);//这里不要header，加块效率
            curl_setopt($conn[$k], CURLOPT_FOLLOWLOCATION, 1); // 302 redirect
            curl_setopt($conn[$k], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($conn[$k], CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($conn[$k], CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
            curl_multi_add_handle($mh, $conn[$k]);
        }
        $active = null;
        // 执行批处理句柄
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($active and $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        foreach ($urlarr as $k => $url) {
            $res[$k] = curl_multi_getcontent($conn[$k]);//获得返回信息
            curl_multi_remove_handle($mh, $conn[$k]);
            curl_close($conn[$k]);
        }
        curl_multi_close($mh);
        return $res;
    }

    /**
     * @param $jsonp
     * @param bool $assoc
     * @return mixed|null
     * jsonp解析
     */
    public static function jsonp_decode($jsonp, $assoc = false)
    {
        $pattern = '/\((.*)\)/s';
        if (preg_match($pattern, $jsonp, $matches)) {
            if (!empty($matches['1'])) {
                return json_decode($matches['1'], $assoc);
            }
            return null;
        }
        return null;
    }

    public static function blank($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        return empty($value);
    }


    public static function ParseConfig($typeconfig, $channel, $itemtype = false, $parseitem = false)
    {
        try {
            $item = $typeconfig["channel"][$channel] ?? "";
            if ($parseitem && $itemtype) {
                $config = $typeconfig["type"][$channel];
                $item = $config[$itemtype][$parseitem];
            }
            return $item;
        } catch (\Exception $e) {
            return "";
        }
    }
}
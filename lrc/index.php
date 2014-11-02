<?php
    /**************************************************************************************/
    /* 百度音乐 lrc 歌词获取工具 api                                                      */
    /* 作者 admin@sxb.pw                                                                  */
    /* 博客 http://www.sxb.pw                                                             */
    /* 返回格式: json 或 jsonp 字符串                                                     */
    /* 包含参数:                                                                          */
    /* status，值为 success 或 error                                                      */
    /* errcode, 值为 0,-1,-2,-3,-4,-5                                                     */
    /*          0 无错误                                                                  */
    /*         -1 为服务器没有接收到完整数据                                              */
    /*         -2 为无法正常获取 xml 文件                                                 */
    /*         -3 为找不到该曲目                                                          */
    /*         -4 为未收录 lrc 歌词                                                       */
    /*         -5 为无法正常获取 lrc 歌词                                                 */
    /* result, 当 status=success 时返回 lrc 歌词数组                                      */
    /* 请求参数: name 歌曲名, singer 歌手名, callback 回调函数（可选）, playerid 可选     */
    /**************************************************************************************/
    
    header("Content-type: text/javascript; charset=utf-8");

    
    function curl_get_contents($url) { //封装 curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); //超时五秒
        $output = curl_exec($ch);
        curl_close($ch);
        if ($output===false) {
            return false;
        }
        return $output;
    }
    
    function url_encode_array($val) { //递归处理数组
        if (is_array($val)) {
            foreach ($val as $k=>$v) {
                $val[$k]=url_encode_array($v);
            }
            return $val;
        } else {
            return urlencode($val);
        }
    }
    
    function output($result_array) { //输出函数
        if ($_GET['playerid'] && $result_array['status']=='success') {
            $playerid=htmlspecialchars($_GET['playerid']);
        }
        echo ($_GET['callback']?htmlspecialchars($_GET['callback']).'(':'').urldecode(json_encode(url_encode_array($result_array))).($_GET['callback']?(($playerid?(',\''.(get_magic_quotes_gpc()?$playerid:addslashes($playerid)).'\''):'').');'):'');
        exit();
    }
    
    //function 部分到此结束
    
    $name=rawurldecode($_GET['name']);
    $singer=rawurldecode($_GET['singer']);
    
    /*
    $name='hue';
    $singer='';
    */
    
    
    if ($name=='') {
        output(array('status'=>'error','errcode'=>-1));
    }
    
    $xml_data=curl_get_contents('http://tingapi.ting.baidu.com/v1/restserver/ting?method=baidu.ting.search.common&page_size=1&format=json&query='.rawurlencode($name.' '.$singer));
    
    if (!$xml_data) {
        output(array('status'=>'error','errcode'=>-2));
    }
    
    $_songid=json_decode($xml_data,true);
    
    if (is_array($_songid["song_list"])) {
        $lrc_data=array();
        $_lrc_url=curl_get_contents("http://tingapi.ting.baidu.com/v1/restserver/ting?method=baidu.ting.song.play&format=json&bit=128&songid=".$_songid["song_list"][0]["song_id"]);
        if (!$_lrc_url) {
            output(array('status'=>'error','errcode'=>-5));
        } else {
            $_lrc=json_decode($_lrc_url,true);
            if (!$_lrc) {
                output(array('status'=>'error','errcode'=>-5));
            } else {
                $_lrc_data=curl_get_contents($_lrc["songinfo"]["lrclink"]);
                if (!$_lrc_data) {
                    output(array('status'=>'error','errcode'=>-4,'baidu_errcode'=>$_lrc['error_code']));
                }
                /* $lrc_data[]=iconv("GBK","UTF-8",str_replace(array("\r","\n"),array("","<br />"),$_lrc_data)); */
                $lrc_data[]=str_replace(array("\r","\t","\n"),array("","    ","<br />"),$_lrc_data);
            }
            if (empty($lrc_data)) {
                output(array('status'=>'error','errcode'=>-5));
            } else {
                output(array('status'=>'success','errcode'=>0,'result'=>$lrc_data),$_GET['callback']);
            }
        }
    } else {
        output(array('status'=>'error','errcode'=>-3));
    }
?>
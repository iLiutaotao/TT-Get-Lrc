<?php
    session_start();
    header("Content-type:text/html;charset=utf-8");
    date_default_timezone_set('Asia/Shanghai');
    $pass='037d54d3950a9c311c67a8897cb33065';
    $link = @mysql_connect("localhost",'liu_lab_user','AZVDPdKBqVWctER',true);
    if(!$link) {
        die("Connect Server Failed: " . mysql_error());
    }
    if(!mysql_select_db('liu_lab',$link)) {
        die("Select Database Failed: " . mysql_error($link));
    }
    mysql_query('set names utf8');
    
    function get_music($id=null) {
        $return_value=array();
        $select_query=mysql_query('select * from `music_list`');
        while (($value=mysql_fetch_array($select_query))!==false) {
            $return_tmp=array();
            $return_tmp['name']=$value['name'];
            $return_tmp['url']=$value['url'];
            $return_tmp['singer']=$value['singer'];
            $return_tmp['id']=$value['id'];
            $return_tmp['lrc']=htmlspecialchars($value['lrc']);
            $return_tmp['lrc_data']=str_replace(array("\r","\n"),array("","<br />"),$value['lrc_data']);
            $return_value[]=$return_tmp;
        }
        if ($id==null) {
            return $return_value;
        } else {
            $return_values=$return_value[$id];
            $return_values['length']=count($return_value);
            return $return_values;
        }
    }
    
    function chkid($id) {
        if (!empty($id) && is_numeric($id)) {
            $select_query=mysql_query('select * from `music_list` where `id`=\''.$id.'\'');
            if (mysql_num_rows($select_query)==0) {
                msg('找不到数据');
            }
            return true;
        }
        msg('找不到数据');
    }
    function get_post_data() {
        if (!get_magic_quotes_gpc()) {
            $return_value=array();
            foreach ($_POST as $key=>$value) {
                $return_value[$key]=addslashes($value);
            }
        } else {
            $return_value=$_POST;
        }
        return $return_value;
    }
    function msg($msg) {
        echo '<p>'.$msg.'</p><p><a href="'.$_SERVER['PHP_SELF'].'">返回首页</a></p>';
        echo_footer();
        exit();
    }
    function echo_header() {
        echo '<html><head><meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0"/><title>背景音乐管理中心</title></head><body>';
        return true;
    }
    function echo_footer() {
        echo '</body></html>';
        return true;
    }
    if ($_GET['action']=='get_music') {
        if ($_GET['id']!=='' && is_numeric($_GET['id'])) {
            $output=get_music($_GET['id']);
            preg_match_all("/^(http:\/\/.*\/)(.*?)$/",$output['url'],$outputtmp);
            $output['url']=$outputtmp[1][0].rawurlencode($outputtmp[2][0]);
            foreach ($output as $k=>$v) {
                $output[$k]=urlencode($v);
            }
            echo urldecode(json_encode($output));
        }
        exit();
    } elseif ($_GET['action']=='get_music_list') {
        echo json_encode(get_music());
        exit();
    } elseif ($_GET['action']=='login') {
        if ($_SESSION['admin']=='admin') {
            header('Location: '.$_SERVER['PHP_SELF']);
            exit();
        }
        echo_header();
        if ($_POST) {
            if (md5($_POST['password'])==$pass) {
                $_SESSION['admin']='admin';
                header('Location: '.$_SERVER['PHP_SELF']);
                exit();
            } else {
                echo '<p>密码错误</p>';
            }
        }
        echo '<form action="" method="post"><p><label>请输入密码:<input type="password" name="password" /></label></p><p><input type="submit" name="submit" value="提交" /></p></form>';
        echo_footer();
    } else {
        if ($_SESSION['admin']!=='admin') {
            header('Location: '.$_SERVER['PHP_SELF'].'?action=login');
            exit();
        }
        echo_header();
        if ($_GET['action']=='del') {
            chkid($_GET['id']);
            if ($_POST) {
                $del_query=mysql_query('delete from `music_list` where `id`=\''.$_GET['id'].'\'');
                if ($del_query) {
                    msg('删除成功');
                } else {
                    msg('删除失败');
                }
            } else {
                $select_query=mysql_fetch_array(mysql_query('select * from `music_list` where `id`=\''.$_GET['id'].'\''));
                echo '<form action="" method="post"><p>您确定要删除 '.$select_query['name'].' - '.$select_query['singer'].'么？</p><p><input type="submit" name="submit" value="确定" /><a href="'.$_SERVER['PHP_SELF'].'">取消</a></p></form>';
            }
        } elseif ($_GET['action']=='edit') {
            chkid($_GET['id']);
            if ($_POST) {
                $post_data=get_post_data();
                $update_query=mysql_query('update `music_list` set `name`=\''.$post_data['name'].'\', `url`=\''.$post_data['url'].'\', `singer`=\''.$post_data['singer'].'\', `lrc`=\''.$post_data['lrc'].'\', `lrc_data`=\''.$post_data['lrc_data'].'\' where `id`=\''.$_GET['id'].'\'');
                if ($update_query) {
                    msg('数据插入成功');
                } else {
                    msg('数据插入失败<br />'.mysql_errno().' '.mysql_error());
                }
            } else {
                $select_query=mysql_fetch_array(mysql_query('select * from `music_list` where `id`=\''.$_GET['id'].'\''));
                echo '<form action="" method="post"><p><label>歌&nbsp;&nbsp;&nbsp;&nbsp;名:<input type="text" name="name" value="'.$select_query['name'].'" /></p><p><label>歌&nbsp;&nbsp;&nbsp;&nbsp;手:<input type="text" name="singer" value="'.$select_query['singer'].'" /></p><p><label>下载地址:<input type="text" name="url" value="'.$select_query['url'].'" /></p><p><a href="http://tool.liujiantao.me/upload/" target="_blank">歌曲上传</a></p><p>显示 lrc 歌词: <label><input type="radio" name="lrc" value="1" '.($select_query['lrc']?'checked="checked" ':'').'/>是</label>&nbsp;&nbsp;<label><input type="radio" name="lrc" value="0" '.(!$select_query['lrc']?'checked="checked" ':'').'/>否</label></p><p>lrc 歌词(可选):<br /><textarea name="lrc_data">'.htmlspecialchars($select_query['lrc_data']).'</textarea></p><p><input type="submit" name="submit" value="确定" /><a href="'.$_SERVER['PHP_SELF'].'">取消</a></p></form>';
            }
        } elseif ($_GET['action']=='search') {
            $post_data=get_post_data();
            echo '<form action="?action=search" method="post"><p><input name="search" type="text" value="'.$post_data['search'].'"/></p><p><input type="submit" value="搜索" /></p></form>';
            if ($_POST) {
                $search_query=mysql_query('select * from `music_list` where `name` like \'%'.$post_data['search'].'%\' or `singer` like \'%'.$post_data['search'].'%\' or `url` like \'%'.$post_data['search'].'%\'');
                if (mysql_num_rows($search_query)>0) {
                    echo '<ol>';
                    while (($value=mysql_fetch_array($search_query))!==false) {
                        echo '<li>'.$value['name'].' - '.$value['singer'].'&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$value['id'].'">编辑</a>&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?action=del&id='.$value['id'].'">删除</a></li>';
                    }
                    echo '</ol><a href="'.$_SERVER['PHP_SELF'].'">返回首页</a>';
                } else {
                    echo '<p>无搜索结果</p>';
                }
            }
        } elseif ($_GET['action']=='add') {
            if ($_POST) {
                $post_data=get_post_data();
                $insert_query=mysql_query('insert into `music_list`(`name`,`url`,`singer`,`lrc`,`lrc_data`) values(\''.$post_data['name'].'\',\''.$post_data['url'].'\',\''.$post_data['singer'].'\',\''.$post_data['lrc'].'\',\''.$post_data['lrc_data'].'\')');
                if ($insert_query) {
                    msg('数据插入成功');
                } else {
                    msg('数据插入失败<br />'.mysql_errno().' '.mysql_error());
                }
            } else {
                echo '<form action="" method="post"><p><label>歌&nbsp;&nbsp;&nbsp;&nbsp;名:<input type="text" name="name" /></p><p><label>歌&nbsp;&nbsp;&nbsp;&nbsp;手:<input type="text" name="singer" /></p><p><label>下载地址:<input type="text" name="url" value="http://" /></p><p><a href="http://tool.liujiantao.me/upload/" target="_blank">歌曲上传</a></p><p>显示 lrc 歌词: <label><input type="radio" name="lrc" value="1" />是</label>&nbsp;&nbsp;<label><input type="radio" name="lrc" value="0" checked="checked" />否</label></p><p>lrc 歌词(可选):<br /><textarea name="lrc_data">'.htmlspecialchars($select_query['lrc_data']).'</textarea></p><p><input type="submit" name="submit" value="确定" /><a href="'.$_SERVER['PHP_SELF'].'">取消</a></p></form>';
            }
        } else {
             echo '<form action="?action=search" method="post"><p><input name="search" type="text" /></p><p><input type="submit" value="搜索" /></p></form>';
             $all_list=get_music();
             echo '<ol>';
             foreach ($all_list as $value) {
                 echo '<li>'.$value['name'].' - '.$value['singer'].'&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$value['id'].'">编辑</a>&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?action=del&id='.$value['id'].'">删除</a></li>';
             }
             echo '</ol>';
             echo '<a href="'.$_SERVER['PHP_SELF'].'?action=add">添加新曲目</a>';
        }
        echo_footer();
    }
?>
function get_lrc(name,singer,playerid) {
    var script = document.createElement('script');  
    script.setAttribute('src', "http://lab.liujiantao.me/lrc/?name="+encodeURIComponent(name)+"&singer="+encodeURIComponent(singer)+"&playerid="+encodeURIComponent(playerid)+"&callback=con_lrc");  
    //load javascript  
    document.getElementsByTagName('head')[0].appendChild(script);
    return true;
}

function con_lrc(lrc_data,playerid) {
    var err_msg={"-1":"服务器没有接收到完整数据","-2":"无法正常获取 xml 文件","-3":"找不到该曲目","-4":"未收录 lrc 歌词","-5":"无法正常获取 lrc 歌词"};
    if (lrc_data.status=="error") {
        console.error(err_msg[lrc_data.errcode]);
        return false;
    }
    eval("var "+playerid+" = load_kk_lrc(\""+playerid+"\");");
    for (i=0;i<lrc_data.result.length;i++) {
        var lrcArray=lrc_data.result[i].split("<br />");
        var timeKey=new Object();
        var strArray=new Array();
        for (var i = 0,l = lrcArray.length;i < l;i++) {
            //正则匹配 删除[00:00.00]格式或者 [00:00:00]格式
            //所有的 lrc 都应该 decode 一下，因为各种语言都可能有
            clause = decodeURIComponent(lrcArray[i]).replace(/\[\d*?:\d*?[\.:]\d*?\]/g,'');
            timeRegExpArr = decodeURIComponent(lrcArray[i]).match(/\[(\d*?):(\d*?)[\.:](\d*?)\]/g);
            if (timeRegExpArr!=null) {
                for (var k = 0,h = timeRegExpArr.length;k < h;k++) { //第一遍循环，JSON存储歌词，数组存储时间
                    _timeRegExpArr = timeRegExpArr[k].match(/^\[(\d*?):(\d*?)[\.:](\d*?)\]$/);
                    min = parseFloat(_timeRegExpArr[1]);
                    sec = parseFloat(_timeRegExpArr[2]);
                    msec = parseFloat(_timeRegExpArr[3]);
                    time=min * 60 + sec + msec/100;
                    if (!timeKey[time]) {
                        strArray.push(time);
                        timeKey[time] = clause + '<br />';
                    } else {
                        timeKey[time] += clause + '<br />';
                    }
                }
            }
        }
        strArray.sort(function(a,b) {
            return a - b;
        });
        for (var i = 0,l = strArray.length;i < l;i++) { //第二遍循环，JSON存储时间，数组存储歌词
            var tempIndex = strArray[i],
            tempClause = timeKey[tempIndex];
            console.log([tempIndex,tempClause]);
            eval(playerid+".add_lrc(tempIndex,tempClause);");
        }
    }
    eval(playerid+".init();");
    return true;
}

function add_lrc(lrc_data,playerid) {
    con_lrc({'status':'success','errcode':'0','result':[lrc_data.replace("\n","<br />")]},playerid);
    return true;
}

/*
 * Lyric support by kookxiang(http://ikk.me)
 */
function load_kk_lrc(playerid){
    var kk_lrc = new Object();
    kk_lrc.extra_top = 1;
    kk_lrc.current = -1;
    kk_lrc.current_obj;
    kk_lrc.offset = 0;
    kk_lrc.current_start = -1;
    kk_lrc.next_update_time = -1;
    kk_lrc.showtime = -1;
    kk_lrc.lrc_offset = -1;
    kk_lrc.lrc_height = -1;
    kk_lrc.lrc = [];
    kk_lrc.lrcobj = null;
    kk_lrc.target = 0;
    kk_lrc._target = 0;
    kk_lrc.player_obj = document.getElementById('kk_lrc_' + playerid);
    kk_lrc.lrc_obj = document.getElementById('kk_lrc_' + playerid + '_lrc');

    kk_lrc.scroll_lrc = function () {
        if(typeof kk_lrc.lrc[kk_lrc.current+2] != "undefined"){
            for(id in kk_lrc.lrcobj) kk_lrc.lrcobj[id].className = '';
            kk_lrc.lrcobj[kk_lrc.current+3].className = 'current';
        }
        kk_lrc.current_start = kk_lrc.lrc[kk_lrc.current];
        kk_lrc.current++;
        kk_lrc.current_obj = kk_lrc.lrcobj[kk_lrc.current+2];
        kk_lrc.next_update_time = kk_lrc.lrc[kk_lrc.current];
        kk_lrc.showtime = kk_lrc.next_update_time - kk_lrc.current_start;
        kk_lrc.lrc_offset = kk_lrc.current_obj.offsetTop;
        kk_lrc.lrc_height = kk_lrc.current_obj.offsetHeight;
    };
    kk_lrc.check_lrc_update = function () {
        var curTime = kk_lrc.player_obj.currentTime;
        if(curTime >= kk_lrc.next_update_time - 0.2){
            kk_lrc.scroll_lrc();
            kk_lrc.check_lrc_update();
        }
        if(typeof kk_lrc.lrc[kk_lrc.current-1] != "undefined"){
            kk_lrc.extra_top = (kk_lrc.next_update_time - curTime) / kk_lrc.showtime;
        }
        kk_lrc.target = Math.round(kk_lrc.lrc_offset - (125 - kk_lrc.lrc_height) / 2);
        if(kk_lrc.target < 0) kk_lrc.target = 0;
    };
    kk_lrc.init = function () {
        kk_lrc.add_lrc('999999', '');
        kk_lrc.add_lrc('999999', '');
        kk_lrc.add_lrc('999999', '');
        kk_lrc.current = -1;
        kk_lrc.lrcobj = kk_lrc.lrc_obj.getElementsByTagName('li');
        kk_lrc.current_obj = kk_lrc.lrcobj[0];
        kk_lrc.scroll_lrc();
        kk_lrc.check_lrc_update();
        kk_lrc.player_obj.addEventListener("seeked" ,function(){
            kk_lrc.current = -1;
            kk_lrc.scroll_lrc();
            kk_lrc.check_lrc_update();
        });
        kk_lrc.player_obj.addEventListener("durationchange" ,function(){
            kk_lrc.current = -1;
            kk_lrc.scroll_lrc();
            kk_lrc.check_lrc_update();
        });
        setInterval(function(){
                if(kk_lrc.player_obj.paused) return;
                if(kk_lrc.current_start > kk_lrc.player_obj.currentTime){
                    kk_lrc.current = -1;
                    kk_lrc.scroll_lrc();
                    kk_lrc.check_lrc_update();
                }else{
                    kk_lrc.check_lrc_update();
                }
            }, 100);
        setInterval(function(){
                if(kk_lrc._target == kk_lrc.target) return;
                if(Math.abs(kk_lrc.lrc_obj.scrollTop - kk_lrc._target) > kk_lrc.lrc_height){
                    kk_lrc._target = kk_lrc.lrc_obj.scrollTop;
                }
                if(kk_lrc.player_obj.paused){
                    kk_lrc._target = kk_lrc.fixFloat(kk_lrc._target * 0.8 + kk_lrc.target * 0.2);
                }else{
                    kk_lrc._target = kk_lrc.fixFloat(kk_lrc._target * 0.98 + kk_lrc.target * 0.02);
                }
                kk_lrc.lrc_obj.scrollTop = kk_lrc._target;
            }, 5);
    };
    kk_lrc.add_lrc = function (time, lrc) {
        kk_lrc.lrc.push(parseFloat(time) + kk_lrc.offset);
        var lrc_line = document.createElement("li");
        lrc_line.innerHTML = lrc;
        kk_lrc.lrc_obj.appendChild(lrc_line);
    };
    kk_lrc.get_lrc = function (num) {
        if(typeof kk_lrc.lrc[num] != "undefined"){
            return kk_lrc.lrc[num][1];
        }else{
            return '';
        }
    }
    kk_lrc.setoffset = function (num) {
        kk_lrc.offset = num / 1000;
    }
    kk_lrc.fixFloat = function (num) {
        return Math.ceil(num * 10) / 10;
    }
    return kk_lrc;
}
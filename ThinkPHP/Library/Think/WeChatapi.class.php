<?php
namespace Think;
class WeChatapi{
    var $_APPID = ""; //公众号ID
    var $_APPSECRET = ""; //公众号密码
    var $_TOKEN = "";  // 公众号access_token
    var $_ticket = ""; //js-sdk

    //微信平台接入验证
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    //使用cURL函数库发送（get|post）请求
    private function _request($curl, $https = true, $method = "GET", $data = null){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($https){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        }

        if($method == 'POST'){
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }
        $content = curl_exec($ch);
        return $content;
    }


    //网页获取用户基本信息
    private function _getAccess_Token($code){
        $content = $this->_request("https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->_APPID."secret=".$this->$_APPSECRET."&code=".$code."&grant_type=authorization_code");
        $content = json_decode($content);
        return $content->access_token;
    }
    
    //获取Access Token
    public function _getAccessToken(){ 
        $file = './accesstoken';
        if(file_exists($file)){
                $content = file_get_contents($file);
                $content = json_decode($content);
                if( time() - filemtime( $file ) < $content->expires_in)
                    return $content->access_token;
        }
        $content = $this->_request("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->_APPID."&secret=".$this->_APPSECRET);
        file_put_contents($file, $content); 
        $content = json_decode($content);
        return $content->access_token;
    }


    //获取js-sdk
    public function _jsapi_ticket(){ 
        $file = './jsapi_ticket';
        if(file_exists($file)){
                $content = file_get_contents($file); 
                $content = json_decode($content);
                if( time() - filemtime( $file ) < $content->expires_in) 
                    return $content->ticket;
        }
        $content = $this->_request("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$this->_getAccessToken()."&type=jsapi");
        file_put_contents($file, $content); 
        $content = json_decode($content);
        return $content->ticket;
    }


    //生成二维码
    private function _getTicket($expire_seconds = 604800, $type = 'temp', $scene_id = 0 ){
        $data;
        if($type == 'temp'){
            $data = '{"expire_seconds": '.$expire_seconds.', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';    
        }else{
            $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}' ;
        } 
        $curl = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->_getAccessToken();
        
        $content = $this->_request($curl, true, 'POST', $data);
        $content = json_decode($content); 
        $ticket = $content->ticket;
        return $ticket;
    }

    //用ticket换取二维码
    public function _getQRCode($e,$t,$s){
        $ticket = $this->_getTicket($e,$t,$s);
        echo "<img src='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$ticket."' width=150 >";
    }

    //删除自定义菜单
    public function _deleteMenu(){
        $curl = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$this->_getAccessToken();
        $content = json_decode($this->_request($curl));
        if($content->errcode == 0)
            echo "菜单已成功删除";
    }

    //创建自定义菜单
    public function _createMenu($menu){
        $curl = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->_getAccessToken();
        $content = json_decode($this->_request($curl, true, 'POST', $menu));
        if($content->errcode == 0)
            echo "菜单创建成功";
    }
    
    //获取用户列表
    public function _getUserList(){
        $curl = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->_getAccessToken();
        $content = json_decode($this->_request($curl));
        return( $content->data->openid);        
    }

    //群发文本信息到用户
    public function _send2User(){
        $curl = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->_getAccessToken();
        $users = $this->_getUserList();
        $dataTpl = '{
            "touser":"%s",
            "msgtype":"text",
            "text":
            {
                "content":"%s"
            }
        }';
        for($i=0;$i<count($users);$i++){
            $data = sprintf($dataTpl,$users[$i],'您好');
            echo "发送到".$users[$i].'信息结果：'.$this->_request($curl, true, 'POST', $data)."<br />";
        }
    }

    //响应用户发送的所以信息，统一处理
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        switch($postObj->MsgType){ 
            case "event":
                $this->_doEvent($postObj);
                break;
            case "text":
                $this->_doText($postObj);
                break;
            case "image":
                $this->_doImage($postObj);
                break;
            case "voice":
                $this->_doVoice($postObj);
                break;
            case "video":
                $this->_doVideo($postObj);
                break;
            case "shortvideo":
                $this->_doShortVideo($postObj);
                break;
            case "location":
                $this->_doLocation($postObj);
                break;
            case "link": 
                $this->_doLink($postObj);
                break;  
            default:
            ;   
        }
    }
    
    //事件处理
    private function _doEvent($postObj){
        if(isset($postObj->EventKey)){
            switch($postObj->EventKey){
            case 'NEWS':
                $this->_sendNews($postObj);
            default:
            ;
            }
        }
        if(isset($postObj->Event)){
            switch($postObj->Event){
            case 'subscribe':
                $this->_doSubscribe($postObj);
                break;
            case 'unsubscribe':
                $this->_doUnsubscribe($postObj);
                break;
            default:
            ;
        }
        }
    }

    //新闻推送
    public function _sendNews($postObj){
        $news = array(
            array(
                'title' => '第九道再创历史',
                'desc' => '铜牌！银牌！无论什么牌，都是中国田径值得大书特书的历史性成就。2015年8月29日，若干年之后的岁月磨砺，你才能更加咀嚼出这个日子的不平凡味道，就如同2004年8月28日刘翔创造的奇迹之夜。',
                'picurl'=>'http://k.sinaimg.cn/i0/dy/slidenews/2_img/2015_35/70202_1578342_761150.jpg/w570237.jpg',
                'url' =>'http://sports.sina.com.cn/others/athletics/2015-08-29/doc-ifxhkafa9429475.shtml'
            ),
            array(
                'title' => '巴萨主场险胜',
                'desc' => '北京时间8月30日02:30(西班牙当地时间29日20:30)，2015/16赛季西班牙足球甲级联赛第2轮一场焦点战在诺坎普球场展开争夺，巴塞罗那主场1比0小胜马拉加，巴萨全场围攻难破僵局，维尔马伦绝杀建功。巴萨新赛季以2个1-0取得连胜。',
                'picurl' => 'http://k.sinaimg.cn/n/transform/20150830/ZZdw-fxhkafe6176918.jpg/w57019c.jpg',
                'url' =>'http://sports.sina.com.cn/g/laliga/2015-08-30/doc-ifxhkpcu4847560.shtml'
            ),
            array(
                'title' => '梦百合前瞻常昊再逢朴永训',
                'desc' => '8月30日，第2届Mlily梦百合杯世界围棋公开赛重启战幕，包括常昊、柯洁、周睿羊在内的11名中国棋手，与李世石、朴廷桓领衔的五位韩国棋手将展开16强战的争夺。其中周睿羊VS朴廷桓、常昊VS朴永训、丁浩VS李世石、柁嘉熹VS安成浚、戎毅VS金世东五场中韩对决，将决定本届梦百合杯争冠形势的走向。',
                'picurl' => 'http://k.sinaimg.cn/n/transform/20150828/pbgq-fxhkafe6135532.jpg/w570784.jpg',
                'url' =>'http://sports.sina.com.cn/go/2015-08-28/doc-ifxhkafa9381895.shtml'
            )
        );
        $news_item = '';
        
        foreach($news as $n){
            $news_item .= sprintf($this->Tpl['news_item'],$n['title'], $n['desc'], $n['picurl'], $n['url']);
        }
        $content = sprintf($this->Tpl['news'],$postObj->FromUserName, $postObj->ToUserName,time(),3,$news_item);
        echo $content;        
    }

    //图像处理
    private function _doImage($postObj){
        $contentStr = "您上传的图片位置：".$postObj->PicUrl;
        $content = sprintf($this->Tpl["text"],$postObj->FromUserName, $postObj->ToUserName,time(),'text',$contentStr);
        echo $content;  
        $image = $this->_request($postObj->PicUrl);
        $file = './'.uniqid().'.jpg';
        file_put_contents($file,$image);
    }

    //关注
    public function _doSubscribe($postObj){
        $time = time();
        $msgType = "text";
        $contentStr = '欢迎您关注ITCASTPHP37微信测试号。';
        $resultStr = sprintf($this->Tpl["text"], $postObj->FromUserName, $postObj->ToUserName, $time, $msgType, $contentStr);
        echo $resultStr;   
    }
    
    //取消关注
    public function _doUnsubscribe($postObj){
        $time = time();
        $msgType = "text";
        $contentStr = '感谢您近来对ITCAST PHP37微信测试号的支持。';
        $resultStr = sprintf($this->Tpl["text"],  $postObj->FromUserName,  $postObj->ToUserName, $time, $msgType, $contentStr);
        echo $resultStr;      
    }

    //添加素材
    public function _setMedia($type, $file){
        $curl = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->_getAccessToken().'&type='.$type;
        $data['type'] = $type;     
        $data['media'] = '@'.$file;
        echo $this->_request($curl, true, 'POST', $data);   
    }

    //反馈用户所在经纬度
    private function _doLocation($postObj){
        $resultStr = sprintf($this->Tpl["text"], $postObj->FromUserName, $postObj->ToUserName, time(), 'text', '您所在位置的经度：'.$postObj->Location_Y.' 纬度：'.$postObj->Location_X);
        echo $resultStr;
    }

    //按歌曲名称和歌手搜索歌曲的URL
    private function _getMusicURL($song, $singer){
        if($singer==''){
            $curl = 'http://box.zhangmen.baidu.com/x?op=12&count=1&title='.$song.'$$';
        } else {
            $curl = 'http://box.zhangmen.baidu.com/x?op=12&count=1&title='.$song.'$$'.$singer.'$$$$';
        }
        $content = $this->_request($curl, false);
        $content = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
        $musicUrl = substr($content->url->encode,0,strrpos($content->url->encode,'/')+1).substr($content->url->decode,0,strrpos($content->url->decode,'&'));
        return $musicUrl;
    } 

    //文本处理
    private function _doText($postObj){
        $music = mb_substr(
          $postObj->Content,
          0,
          2, 
          'UTF-8');
         if($music == '歌曲')
             $this->_sendMusic($postObj);
         
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $content = trim($postObj->Content);
        switch(strtolower($postObj->Content)){
            case "php":
                $contentStr ='PHP（外文名:PHP: Hypertext Preprocessor，中文名：“超文本预处理器”）是一种通用开源脚本语言。';
                break;
            case "java":
                $contentStr = '一种编程语言！';
                break;
            default:
                $curl = 'http://www.xiaodoubi.com/bot/chat.php';
                $data = 'chat='.$postObj->Content;
                $contentStr = $this->_request($curl, false, 'POST', $data);
        }
        $time = time();
        $msgType = "text";
        $resultStr = sprintf($this->Tpl["text"], $fromUsername, $toUsername, $time, $msgType, $contentStr);
        echo $resultStr;
    }

    //发送歌曲到微信用户
    private function _sendMusic($postObj){  
        $postObj->Content = mb_substr($postObj->Content, 2, mb_strlen($postObj->Content, 'UTF-8')-2, 'UTF-8');
        $songinfo = explode('@', $postObj->Content);
        $song = trim($songinfo[0]);
        $singer='';
        if(isset($songinfo[1])){
            $singer = trim($songinfo[1]);
        }   
        $musicURL = $this->_getMusicURL($song, $singer);
        $musicTpl = 
            '<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[music]]></MsgType>
                <Music>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[]]></Description>
                    <MusicUrl><![CDATA[%s]]></MusicUrl>
                    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                    <ThumbMediaId>
                    <![CDATA[yTmem0Tipvz3XTaoDM6xt2g_4ECRcx1NCMbZ7KjL51jGvJuvC7DRnFEoJEVJ62Bq]]></ThumbMediaId>
                </Music>
            </xml>';
        $resultStr = sprintf($musicTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $song, $singer, $musicURL, $musicURL);
        echo $resultStr;
        exit;
    }   


}
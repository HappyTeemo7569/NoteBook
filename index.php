<?php
	//声明一个常量定义一个token值, token
	define("TOKEN", "weixin");

	//通过Wechat类， 创建一个对象
	$wechatObj = new Wechat();
	
	//如果没有通过GET收到echostr字符串， 说明不是再使用token验证
	if (!isset($_GET['echostr'])) {
		//调用wecat对象中的方法响应用户消息
   		$wechatObj->responseMsg();
	}else{
		//调用valid()方法，进行token验证
  		$wechatObj->valid();
	}


	//声明一个Wechat的类， 处理接收消息， 接收事件， 响应各种消息， 以及token验证
	class Wechat {
	     
	//验证签名, 手册中原代码改写
		public function valid() {


		//在开发者首次提交验证申请时，微信服务器将发送GET请求到填写的URL上，并且带上四个参数（signature、timestamp、nonce、echostr），开发者通过对签名（即signature）的效验，来判断此条消息的真实性。 
        $echoStr = $_GET["echostr"];    // 随机字符串 
        $signature = $_GET["signature"]; //微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
        $timestamp = $_GET["timestamp"]; //时间戳 
        $nonce = $_GET["nonce"];  // 随机数 
		
		 //上面通过常量声明的token值
		 $token = TOKEN;

		 //将token、timestamp、nonce三个参数进行字典序排序
    	$tmpArr = array($token, $timestamp, $nonce);
	    sort($tmpArr);


		 //将三个参数字符串拼接成一个字符串进行sha1加密
    	$tmpStr = implode($tmpArr); //将数排序过的数组组合成一个字符
    	$tmpStr = sha1($tmpStr);   //使用sha1加密

		//如果公众号上的签名和服务器上的签名是相等的则验正成功
  		if( $tmpStr == $signature ){
        		 return true;
		}else{
        		 return false;
 		}
		
   	 }

    //响应消息处理
    public function responseMsg()
    {
	//接收微新传过来的xml消息数据    
	$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

	//如果接收到了就处理并回复
	if (!empty($postStr)){
	    //将接收到的XML字符串写入日志， 用R标记表示接收消息
	    $this->logger("R \n".$postStr);
	    //将接收的消息处理返回一个对象，将接受到的xml转成字符串
	    $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

	    //从消息对象中获取消息的类型 text  image location voice vodeo link 
            $RX_TYPE = trim($postObj->MsgType);
             
            //消息类型分离, 通过RX_TYPE类型作为判断， 每个方法都需要将对象$postObj传入
            switch ($RX_TYPE)
            {
                case "text":
                    $result = $this->receiveText($postObj);     //接收文本消息
                    break;
                case "image":
                    $result = $this->receiveImage($postObj);   //接收图片消息
                    break;
                case "location":
                    $result = $this->receiveLocation($postObj);  //接收位置消息
                    break;
                case "voice":
                    $result = $this->receiveVoice($postObj);   //接收语音消息
                    break;
                case "video":
                    $result = $this->receiveVideo($postObj);  //接收视频消息
                    break;
                case "link":
                    $result = $this->receiveLink($postObj);  //接收链接消息
                    break;
                default:
                    $result = "unknown msg type: ".$RX_TYPE;   //未知的消息类型
                    break;
	    }
	    //将响应的消息再次写入日志， 使用T标记响应的消息！
            $this->logger("T \n".$result);
	    //输出消息给微新
	    echo $result;
	}else {
	    //如果没有消息则输出空，并退出
            echo "";
            exit;
        }
    }

  

    //接收文本消息
    private function receiveText($object)
    {
	//从接收到的消息中获取用户输入的文本内容， 作为一个查询的关键字， 使用trim()函数去两边的空格
        $keyword = trim($object->Content);
    
            //自动回复模式
             if (strstr($keyword, "文本")){
		     $content = "这是个文本消息";

	     }else if (strstr($keyword, "单图文")){

                $content = array();
		$content[] = array("Title"=>"小规模低性能低流量网站设计原则",  "Description"=>"单图文内容", "PicUrl"=>"http://mmbiz.qpic.cn/mmbiz/2j8mJHm8CogqL5ZSDErOzeiaGyWIibNrwrVibuKUibkqMjicCmjTjNMYic8vwv3zMPNfichUwLQp35apGhiciatcv0j6xwA/0", "Url" =>"http://mp.weixin.qq.com/s?__biz=MjM5NDAxMDEyMg==&mid=201222165&idx=1&sn=68b6c2a79e1e33c5228fff3cb1761587#rd");

            }else if (strstr($keyword, "图文") || strstr($keyword, "多图文")){
                $content = array();
                $content[] = array("Title"=>"多图文1标题", "Description"=>"动手构建站点的时候，不要到处去问别人该用什么，什么熟悉用什么，如果用自己不擅长的技术手段来写网站，等你写完，黄花菜可能都凉了。", "PicUrl"=>"http://mmbiz.qpic.cn/mmbiz/2j8mJHm8CogqL5ZSDErOzeiaGyWIibNrwrVibuKUibkqMjicCmjTjNMYic8vwv3zMPNfichUwLQp35apGhiciatcv0j6xwA/0", "Url" =>"http://mp.weixin.qq.com/s?__biz=MjM5NDAxMDEyMg==&mid=201222165&idx=1&sn=68b6c2a79e1e33c5228fff3cb1761587#rd");
                $content[] = array("Title"=>"多图文2标题", "Description"=>"动手构建站点的时候，不要到处去问别人该用什么，什么熟悉用什么，如果用自己不擅长的技术手段来写网站，等你写完，黄花菜可能都凉了。", "PicUrl"=>"http://mmbiz.qpic.cn/mmbiz/2j8mJHm8CogqL5ZSDErOzeiaGyWIibNrwrVibuKUibkqMjicCmjTjNMYic8vwv3zMPNfichUwLQp35apGhiciatcv0j6xwA/0", "Url" =>"http://mp.weixin.qq.com/s?__biz=MjM5NDAxMDEyMg==&mid=201222165&idx=1&sn=68b6c2a79e1e33c5228fff3cb1761587#rd");
                $content[] = array("Title"=>"多图文3标题", "Description"=>"动手构建站点的时候，不要到处去问别人该用什么，什么熟悉用什么，如果用自己不擅长的技术手段来写网站，等你写完，黄花菜可能都凉了。", "PicUrl"=>"http://mmbiz.qpic.cn/mmbiz/2j8mJHm8CogqL5ZSDErOzeiaGyWIibNrwrVibuKUibkqMjicCmjTjNMYic8vwv3zMPNfichUwLQp35apGhiciatcv0j6xwA/0", "Url" =>"http://mp.weixin.qq.com/s?__biz=MjM5NDAxMDEyMg==&mid=201222165&idx=1&sn=68b6c2a79e1e33c5228fff3cb1761587#rd");
            }else if (strstr($keyword, "音乐")){
                $content = array();
                $content = array("Title"=>"小歌曲你听听", "Description"=>"歌手：不是高洛峰", "MusicUrl"=>"http://wx.buqiu.com/app/hlw.mp3", "HQMusicUrl"=>"http://wx.buqiu.com/app/hlw.mp3");
            }else{
                $content = date("Y-m-d H:i:s",time())."\n技术支持 高洛峰";
            }
            
            if(is_array($content)){
                if (isset($content[0]['PicUrl'])){
                    $result = $this->transmitNews($object, $content);
                }else if (isset($content['MusicUrl'])){
                    $result = $this->transmitMusic($object, $content);
                }
            }else{
                $result = $this->transmitText($object, $content);
            }
     

        return $result;
    }

    //接收图片消息
    private function receiveImage($object)
    {
        $content = array("MediaId"=>$object->MediaId);
        $result = $this->transmitImage($object, $content);
        return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "你刚才说的是：".$object->Recognition;
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }

        return $result;
    }

    //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "Title"=>"this is a test", "Description"=>"pai pai");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
    <MediaId><![CDATA[%s]]></MediaId>
</Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
    <MediaId><![CDATA[%s]]></MediaId>
</Voice>";

        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
    <MediaId><![CDATA[%s]]></MediaId>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
</Video>";

        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['Title'], $videoArray['Description']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        $itemTpl = "<Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }



    //日志记录
    private function logger($log_content)
    {
      
	    $max_size = 100000;   //声明日志的最大尺寸

	    $log_filename = "log.xml";  //日志名称

	    //如果文件存在并且大于了规定的最大尺寸就删除了
	    if(file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size)){
		    unlink($log_filename);
	    }

	    //写入日志，内容前加上时间， 后面加上换行， 以追加的方式写入
	    file_put_contents($log_filename, date('H:i:s')." ".$log_content."\n", FILE_APPEND);
        
    }
}
?>


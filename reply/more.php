<?php
/**
 * wechat 关键词回复语言消息，获取用户基本信息，接收地理位置等
 */

// 官方PHP示例代码：http://mp.weixin.qq.com/mpres/htmledition/res/wx_sample.20140819.zip

// 认证 token
define("TOKEN", "weixin_test");
$wechatObj = new wechatCallbackapiTest();   // 实例化对象
$wechatObj->valid();    // 调用验证方法（此方法内调用回复方法）

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            $this->responseMsg();
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);   // 收到消息的类型
            //不同类型进行不同处理
            switch ($RX_TYPE)
            {
                case 'text':
                    $resultStr = $this->receiveText($postObj);
                    break;
                case 'voice':
                    $resultStr = $this->receiveText($postObj);
                    break;
                case 'event':
                    $resultStr = $this->receiveEvent($postObj);
                    break;
                case 'location':
                    $contentstr = "你的位置为：{$postObj->Label}。";
                    $resultStr = $this->ReplyText($postObj, $contentstr);
                    break;
                default :
                    $contentstr = "你的".$RX_TYPE."信息已经收到";
                    $resultStr = $this->ReplyText($postObj, $contentstr);
                    break;
            }
            echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }

    // 用户事件
    private function receiveEvent($postObj)
    {
        $event = $postObj->Event;
        switch ($event) {
            case 'subscribe':	// 订阅
                $contentstr = "欢迎订阅";
                $resultStr = $this->ReplyText($postObj, $contentstr);
                break;
            case 'unsubscribe':	// 取消订阅
                $tousername = $postObj->FromUserName;
                // 可根据用户名进行删除（更新）用户信息得操作
                $resultStr = '';
                break;
            case 'CLICK':	// 自定义菜单
                $resultStr = $this->receiveText($postObj);	// 菜单点击事件
                break;
            default :
                $contentstr = "unknown";
                $resultStr = $this->ReplyText($postObj, $contentstr);
                break;
        }
        return  $resultStr;
    }

    // 把收到文本消息的回复封装起来
    private function receiveText($postObj)
    {
        $keyword = '';
        // 文本消息
        if (isset($postObj->Content)) {
            $keyword = trim($postObj->Content);
        }
        // 此处把收到的自定义菜单的点击事件也当作文本消息处理
        if (isset($postObj->EventKey)) {
            $keyword = trim($postObj->EventKey);
        }
        // 把语言识别的结果也当作文本处理
        if (isset($postObj->Recognition)) {
            $keyword = trim($postObj->Recognition);
        }

        if(!empty( $keyword ))
        {
            if($keyword == "news"){
                $news = array('title' => "单图文",
                    'description' => "图文描述",
                    'picurl' => "http://static.ukejisong.com/image/service/c5f5f36cf65d48deb59c46b70fd13bd4.jpg",
                    'url' => "http://www.ukejisong.com/",
                );
                $resultStr = $this->ReplyOneNews($postObj, $news);
            }
            elseif($keyword == "news2"){
                $news = array(
                    array(
                        'title' => "多图文1",
                        'picurl' => "http://static.ukejisong.com/image/service/c5f5f36cf65d48deb59c46b70fd13bd4.jpg",
                        'url' => "http://www.ukejisong.com/",
                    ),
                    array(
                        'title' => "多图文2",
                        'picurl' => "http://static.ukejisong.com/image/service/c5f5f36cf65d48deb59c46b70fd13bd4.jpg",
                        'url' => "http://www.ukejisong.com/",
                    ),
                    array(
                        'title' => "多图文2",
                        'picurl' => "http://static.ukejisong.com/image/service/c5f5f36cf65d48deb59c46b70fd13bd4.jpg",
                        'url' => "http://www.ukejisong.com/",
                    ),
                );
                $resultStr = $this->ReplyNews($postObj, $news);
            }
            elseif($keyword == "text"){
                $contentStr = "回复文本消息";
                $resultStr = $this->ReplyText($postObj, $contentStr);
            }
            elseif($keyword == "语音"){
                $contentStr = "语音识别正确";
                $resultStr = $this->ReplyText($postObj, $contentStr);
            }
            else{
                $contentStr = "无匹配关键词";
                $resultStr = $this->ReplyText($postObj, $contentStr);
            }
        }else{
            $resultStr = "Input something...";
        }

        return $resultStr;
    }

    // 在示例代码的基础上封装一下，实现一个回复文本的方法
    private function ReplyText($object, $contentstr)
    {
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $contentstr);
        return $resultStr;
    }

    // 回复格式为图文消息（多条,无Description），传入数组参数
    private function ReplyNews($object, $news)
    {
        $ArticleCount = count($news);	// 图文数量
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>%s</ArticleCount>
					<Articles>";

        for ($i=0; $i < $ArticleCount; $i++) { 		//多条图文消息组合
            $textTpl .= "
					<item>
					<Title><![CDATA[".$news[$i]['title']."]]></Title>
					<PicUrl><![CDATA[".$news[$i]['picurl']."]]></PicUrl>
					<Url><![CDATA[".$news[$i]['url']."]]></Url>
					</item>";
        }

        $textTpl .= "
					</Articles>
					</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $ArticleCount);
        return $resultStr;
    }

    // 回单图文消息
    private function ReplyOneNews($object, $news)
    {
        /*单图文图片大小推荐：360px * 200px*/
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>1</ArticleCount>
					<Articles>
					<item>
					<Title><![CDATA[%s]]></Title>
					<Description><![CDATA[%s]]></Description>
					<PicUrl><![CDATA[%s]]></PicUrl>
					<Url><![CDATA[%s]]></Url>
					</item>
					</Articles>
					</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $news['title'], $news['description'], $news['picurl'], $news['url']);
        return $resultStr;
    }

    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}
// end of more.php
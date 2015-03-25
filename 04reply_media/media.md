此章节介绍如何回复多媒体消息（以图片为例）。

图片（image）、语音（voice）、视频（video）在微信中统称多媒体（现在接口名称更换为素材管理），以 media_id 标识这个资源，这里我们只要在 xml 串中指定 media_id，即可向用户回复这个多媒体。

忽略代码中的其它部分，只关注图片消息回复的代码。
responseMsg() 方法中的 case 'image' 部分即对收到的图片消息做回复，这里直接获取用户发送图片的 media_id 然后回复此 media_id 给用户，用户就会收到自己发送的图片。
从这里可以看出 media_id 是可以复用的。

media_id 是需要上传到微信服务器才能得到。receiveText() 中的 $keyword == "日历" 演示了如何上传一张图片到服务器，获取 media_id 后回复给用户。
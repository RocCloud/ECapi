<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/11/1
 * Time: 12:50
 */

namespace app\api\service;


use think\Exception;

class WxMessage
{
    private $sendUrl;
    private $touser;
    private $color = 'black';

    protected $tplID;
    protected $page;
    protected $formID;
    protected $data;
    protected $emphasisKeyWord;

    function __construct()
    {
        $accessToken = new AccessToken();
        $token = $accessToken->get();
        $this->sendUrl = sprintf(config('wx.send_template_message_url'),$token);
    }

    protected function sendMessage($openID){
        $data = [
            'touser' => $openID,
            'template_id' => $this->tplID,
            'page' => $this->page,
            'form_id' => $this->formID,
            'data' => $this->data,
            //'color' => $this->color,
            '$emphasis_keyword' => $this->emphasisKeyWord
        ];
        $res = curl_post($this->sendUrl,$data);
        $res = json_decode($res,true);
        if($res['errcode'] == 0){
            return true;
        }else{
            throw new Exception('模板消息发送失败,'.$res['errmsg']);
        }
    }
}
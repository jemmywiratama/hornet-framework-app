<?php
/**
 * Created by PhpStorm.
 * User: sven
 * Date: 2017/7/4 0004
 * Time: 下午 7:07
 */

namespace main\app\server\async;

require_once '../../../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

class email
{
    /**
     * 发送邮件
     * @param $json_obj
     * @return array
     */
    function send_by_smtp( $json_obj ){

        if( !isset($json_obj->to)
            || !isset($json_obj->subject)
            || !isset($json_obj->content)
            || !isset($json_obj->config)
        ){
            return [ false , '参数错误' ];
        }
        $to = $json_obj->to;
        $subject = $json_obj->subject;
        $body = $json_obj->content; 
        $config = (object)$json_obj->config; 
	
		//var_dump($to,$subject,$body);
        
        $ret = false;
        $msg = '';

        try {
            $mail = new \PHPMailer(true);
            $mail->IsSMTP();
            $mail->CharSet='UTF-8';
            $mail->SMTPAuth = true;
            $mail->Port = $config->port;
            $mail->SMTPDebug = 2;
            $mail->Host =  $config->host;
            $mail->Username = $config->username;
            $mail->Password = $config->password;
            $mail->Timeout = isset( $config->timeout ) ? $config->timeout :20  ;
            $mail->From = $config->username;
            $mail->FromName =  $config->username;
            if( is_array($to) && !empty($to) ){
                foreach ( $to as $t ){
                    $mail->AddAddress( $t );
                }
            }else{
                $mail->AddAddress($to);
            }

            $mail->Subject = $subject;
            $mail->Body = $body ;
            $mail->AltBody = "To view the message, please use an HTML compatible email viewer!";
            $mail->WordWrap = 80;
            $mail->IsHTML(true);
            $ret =  $mail->Send();
            if( !$ret ) {
                $msg = 'Mailer Error: ' . $mail->ErrorInfo;
            }

        } catch (\phpmailerException $e) {
            $msg =  "邮件发送失败：".$e->errorMessage();
        }

        return [ $ret , $msg ];
    }
	
	public function send_by_api( $json_obj )
    {
        if( !isset($json_obj->to)
            || !isset($json_obj->subject)
            || !isset($json_obj->content) 
        ){
            return [ false , '参数错误' ];
        }
        $to = $json_obj->to;
		if( is_array($to) && !empty($to) ){
			$to = implode(';',$json_obj->to);
		}
        $subject = $json_obj->subject;
        $body = $json_obj->content; 
        $config = (object)$json_obj->config; 

        $url = 'http://api.sendcloud.net/apiv2/mail/send';
        $API_USER = 'weichaoduo_test_Olsbj4';
        $API_KEY = 'tlQhPfPOPgPOexiU';

        //您需要登录SendCloud创建API_USER，使用API_USER和API_KEY才可以进行邮件的发送。
        $param = array(
            'apiUser' => $API_USER,
            'apiKey' => $API_KEY,
            'from' => 'service@sendcloud.im',
            'fromName' => 'SendCloud',
            'to' => $to,
            'subject' => $subject,
            'html' => $body,
            'respEmailId' => 'true');

        $data = http_build_query($param);
		var_dump($param);
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $data
            ));

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
		echo $result."\n\n";
    }
}
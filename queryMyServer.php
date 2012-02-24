<?php
/**
 * queryMyServer will match your Domains IP with your current ip.
 * If they are different, will notice the person in '$noticeMailList'.
 * The tool will be  used monitor the dynamic dns 's ip change.
 * 
 * @author samuel.li (weiyesoft@gmail.com)
 */
require_once('lib/phpmailer/class.phpmailer.php');

// Your Domain
define('DOMAIN','weiyesoft.3322.org');

// Notice Mail List
$noticeMailList = array(
    'weiyesoft@gmail.com'
);

// SMTP INFO (need config)
define("SMTP_HOST",'mail.test.com');
define("SMTP_AUTH_USER",'user');
define("SMTP_AUTH_PASS",'pass');
define("SMTP_FROM",'user@test.com');
define("SMTP_REPLYTO",'otherone@example.com'); // default is same with SMTP_FROM

class SystemTool {
    public static function getIpByDomain($domain) {
        //http://www.ip138.com/ips.asp?ip=weiyesoft.3322.org&action=2
        $htmlResponse = file_get_contents("http://www.ip138.com/ips.asp?ip=".DOMAIN."&action=2");
        $matches = array();
        $pattern = "/".DOMAIN.">>(\d+.\d+.\d+.\d+)/i";
        $htmlResponse=preg_replace("/[\s|\r|\n]+/",'',$htmlResponse);
        preg_match($pattern, $htmlResponse, $matches);
        /**
         * (
         *   [0] => inputtype="text"name="ip"id="ip"value="124.93.249.68"
         *   [1] => 124.93.249.68
         *  )
         */
        if(!empty($matches)) {
            return $matches[1];
        }
        else {
            return gethostbyname($domain);
        }
        
    }
    
    public static function getMyIp() {
        $htmlResponse = file_get_contents("http://www.query-ip.com/");
        $matches = array();
        $htmlResponse=preg_replace("/[\s|\r|\n]+/",'',$htmlResponse);

        preg_match('/inputtype="text"name="ip"id="ip"value="(\d+.\d+.\d+.\d+)"/i', $htmlResponse, $matches);
        /**
         * (
         *   [0] => inputtype="text"name="ip"id="ip"value="124.93.249.68"
         *   [1] => 124.93.249.68
         *  )
         */
        if(!empty($matches)) {
            return $matches[1];
        }
    }
    
    static public function sendmail(array $to, $subject, $altBody, $body) {

        //include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

        if(empty($to) || empty($body)) {
            return ;
        }
        
        if(empty($subject)) {
            $subject = "A letter from DistributeServer Programe.";
        }
        
        $mail             = new PHPMailer();

        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->Host       = SMTP_HOST; // SMTP server
        $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
                                                   // 1 = errors and messages
                                                   // 2 = messages only
        $mail->SMTPAuth   = true;                  // enable SMTP authentication
        $mail->Port       = 25;                    // set the SMTP port for the GMAIL server
        $mail->Username   = SMTP_AUTH_USER; // SMTP account username
        $mail->Password   = SMTP_AUTH_PASS;        // SMTP account password

        $mail->SetFrom(SMTP_FROM, 'QueryMyServer Toolkit');

        $mail->AddReplyTo(SMTP_REPLYTO, 'Administrator');

        $mail->Subject    = $subject;

        $mail->AltBody    = $altBody; // optional, comment out and test

        $mail->MsgHTML($body);

        foreach($to as $address) {
            $mail->AddAddress($address, "");
        }

        if(!$mail->Send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            echo "Message sent!";
        }

    }
    
}

$domainIp = SystemTool::getIpByDomain(DOMAIN);
$nowIp = SystemTool::getMyIp();
if(($domainIp != $nowIp)) {
    $subject = 'NOTICE:'.DOMAIN." dosen't matche its global IP";
    $mailTextBody = "
        Domain: ".DOMAIN."'s IP should be {$domainIp}\n
        But Now IP is  {$nowIp}\n
        ---------------------------------------\n
        Power By weiyesoft@gmail.com\n
    ";

    $mailHtmlBody = "
        Domain: <font color:#0000FF>".DOMAIN."</font> 's IP should be <font color:#0000FF>{$domainIp}</font><br/>\n
        But Now IP is <font color:#FF0000>{$nowIp}</font><br/>\n
        ---------------------------------------<br/>\n
        Power By weiyesoft@gmail.com<br/>\n
    ";

    SystemTool::sendmail($noticeMailList, $subject, $mailTextBody,$mailHtmlBody);
}
else {
    echo "ip is same.\n";
}
?>

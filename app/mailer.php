<?php
function org_send_mail(string $to,string $subject,string $html): bool {
    $host=env('SMTP_HOST'); $port=(int)env('SMTP_PORT','587'); $user=env('SMTP_USER'); $pass=env('SMTP_PASS'); $from=env('MAIL_FROM',$user);
    if(!$host||!$user||!$pass||!filter_var($to,FILTER_VALIDATE_EMAIL)) return false;
    $fp=@stream_socket_client('tcp://'.$host.':'.$port,$errno,$errstr,15);
    if(!$fp) return false;
    stream_set_timeout($fp,15);
    $read=function()use($fp){$out='';while(($line=fgets($fp,515))!==false){$out.=$line;if(strlen($line)<4||$line[3]!=='-')break;}return $out;};
    $cmd=function(string $line,int $expected)use($fp,$read){fwrite($fp,$line."\r\n");return (int)substr($read(),0,3)===$expected;};
    if((int)substr($read(),0,3)!==220||!$cmd('EHLO tssmt.org',250)){$cmd('QUIT',221);fclose($fp);return false;}
    if(strtolower(env('SMTP_ENCRYPTION'))==='tls'&&$port===587){if(!$cmd('STARTTLS',220)||!stream_socket_enable_crypto($fp,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)||!$cmd('EHLO tssmt.org',250)){fclose($fp);return false;}}
    if(!$cmd('AUTH LOGIN',334)||!$cmd(base64_encode($user),334)||!$cmd(base64_encode($pass),235)||!$cmd('MAIL FROM:<'.$from.'>',250)||!$cmd('RCPT TO:<'.$to.'>',250)||!$cmd('DATA',354)){fclose($fp);return false;}
    $safeSubject=str_replace(["\r","\n"],'',$subject); $signature='<p style="margin-top:24px;color:#666;font-size:12px">Powered by <a href="https://ownerp.in" style="color:#666">OwnERP</a></p>'; $body="From: ".setting($GLOBALS['pdo'],'org_name','TSSMT')." <{$from}>\r\nTo: <{$to}>\r\nSubject: {$safeSubject}\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$html}{$signature}\r\n.";
    $ok=$cmd($body,250);$cmd('QUIT',221);fclose($fp);return $ok;
}

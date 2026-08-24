<?php
function org_email_layout(string $title,string $content): string {
    $org=e(setting($GLOBALS['pdo'],'org_name','Talcher Swadhinata Sangrami Trust'));
    $safeTitle=e($title);$content=str_replace('<a ','<a style="display:inline-block;padding:12px 18px;background:#0f766e;color:#ffffff;border-radius:7px;font-weight:700;text-decoration:none" ',$content);
    return '<!doctype html><html><body style="margin:0;padding:24px 12px;background:#f1f5f9;font-family:Arial,sans-serif;color:#172033"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center"><table role="presentation" width="620" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(16,42,82,.18)"><tr><td style="padding:24px 30px;background:#102a52;color:#ffffff"><div style="font-size:20px;font-weight:700">'.$org.'</div><div style="margin-top:5px;font-size:12px;color:#c9d8ef">Member communication</div></td></tr><tr><td style="padding:30px"><h1 style="margin:0 0 16px;font-size:24px;line-height:1.3;color:#172033">'.$safeTitle.'</h1>'.$content.'<p style="margin:28px 0 0;padding-top:18px;border-top:1px solid #e6eaf0;color:#68748a;font-size:12px;line-height:1.6">This is an automated message from '.$org.'. Please do not reply to this email.<br>Powered by <a href="https://ownerp.in" style="color:#0f766e;font-weight:700;text-decoration:none">OwnERP</a></p></td></tr></table></td></tr></table></body></html>';
}

function org_send_mail(string $to,string $subject,string $html): bool {
    $host=env('SMTP_HOST');$port=(int)env('SMTP_PORT','587');$user=env('SMTP_USER');$pass=env('SMTP_PASS');$from=env('MAIL_FROM',$user);
    if(!$host||!$user||!$pass||!filter_var($to,FILTER_VALIDATE_EMAIL))return false;
    $fp=@stream_socket_client('tcp://'.$host.':'.$port,$errno,$errstr,15);if(!$fp)return false;
    stream_set_timeout($fp,15);$read=function()use($fp){$out='';while(($line=fgets($fp,515))!==false){$out.=$line;if(strlen($line)<4||$line[3]!=='-')break;}return $out;};$cmd=function(string $line,int $expected)use($fp,$read){fwrite($fp,$line."\r\n");return(int)substr($read(),0,3)===$expected;};
    if((int)substr($read(),0,3)!==220||!$cmd('EHLO tssmt.org',250)){$cmd('QUIT',221);fclose($fp);return false;}if(strtolower(env('SMTP_ENCRYPTION'))==='tls'&&$port===587){if(!$cmd('STARTTLS',220)||!stream_socket_enable_crypto($fp,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)||!$cmd('EHLO tssmt.org',250)){fclose($fp);return false;}}
    if(!$cmd('AUTH LOGIN',334)||!$cmd(base64_encode($user),334)||!$cmd(base64_encode($pass),235)||!$cmd('MAIL FROM:<'.$from.'>',250)||!$cmd('RCPT TO:<'.$to.'>',250)||!$cmd('DATA',354)){fclose($fp);return false;}
    $safeSubject=str_replace(["\r","\n"],'',$subject);$body="From: ".setting($GLOBALS['pdo'],'org_name','TSSMT')." <{$from}>\r\nTo: <{$to}>\r\nSubject: {$safeSubject}\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n".org_email_layout($safeSubject,$html)."\r\n.";$ok=$cmd($body,250);$cmd('QUIT',221);fclose($fp);return $ok;
}

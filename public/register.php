<?php require_once __DIR__.'/../app/layout.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf(); $name=trim($_POST['full_name']??''); $mobile=preg_replace('/\D/','',$_POST['mobile']??'');
    $email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL); $password=$_POST['password']??'';
    if(strlen($name)<2||!preg_match('/^\d{10,15}$/',$mobile)||!$email||strlen($password)<8){ flash('Enter a valid name, mobile number, email and a password of at least 8 characters.','error'); }
    else { try { $pdo->prepare('INSERT INTO members (full_name,mobile,email,password_hash) VALUES (?,?,?,?)')->execute([$name,$mobile,$email,password_hash($password,PASSWORD_DEFAULT)]); flash('Thank you for your registration. Your membership request has been submitted for approval.'); redirect('/register.php'); } catch(PDOException $e) { flash('That email address or mobile number is already registered.','error'); } }
}
header_html('Membership registration'); ?>
<h1>Join TSSMT</h1><form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><label>Full Name<input name="full_name" required maxlength="150"></label><label>Mobile Number<input name="mobile" inputmode="numeric" required></label><label>Email Address<input type="email" name="email" required></label><label>Create password<input type="password" name="password" minlength="8" required></label><button>Submit registration</button></form><?php footer_html();

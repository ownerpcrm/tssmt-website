<?php
require_once __DIR__.'/../app/layout.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $s=$pdo->prepare("SELECT * FROM members WHERE email=? AND status='active'");
    $s->execute([strtolower(trim($_POST['email']??''))]);
    $m=$s->fetch();
    if($m&&$m['password_hash']&&password_verify($_POST['password']??'',$m['password_hash'])){
        session_regenerate_id(true);
        $_SESSION['member_id']=$m['id'];
        redirect('/member/dashboard.php');
    }
    flash('Invalid login or your account is not active.','error');
}
header_html('Member login');
?>
<style>.login-scene{min-height:600px;padding:54px 24px;background:linear-gradient(90deg,#102a52d9,#102a5299),url('/assets/login-community-bg.png') center/cover;border-radius:18px}.login-scene h1{color:#fff}.login-card{max-width:460px;background:#fffffff2!important;margin:0!important}.login-scene>p{max-width:460px;color:#fff}.login-scene>p a{color:#fff;text-decoration:underline}</style>
<section class="login-scene"><h1>Member login</h1>
<div class="login-card">
<form method="post">
  <input type="hidden" name="csrf" value="<?=csrf()?>">
  <label>Email<input type="email" name="email" required></label>
  <label>Password<input type="password" name="password" required></label>
  <button>Login</button>
</form>
</div><p><a href="/forgot-password.php?type=member">Forgot password?</a></p>
<p>Not a member? <a href="/register.php">Sign up for membership</a>. Your application will be reviewed and approved by an administrator before you can log in.</p></section>
<?php footer_html();

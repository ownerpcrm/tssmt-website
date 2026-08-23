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
<h1>Member login</h1>
<form method="post">
  <input type="hidden" name="csrf" value="<?=csrf()?>">
  <label>Email<input type="email" name="email" required></label>
  <label>Password<input type="password" name="password" required></label>
  <button>Login</button>
</form>
<p><a href="/forgot-password.php?type=member">Forgot password?</a></p>
<p>Not a member? <a href="/register.php">Sign up for membership</a>. Your application will be reviewed and approved by an administrator before you can log in.</p>
<?php footer_html();

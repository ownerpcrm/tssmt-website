<?php
require_once __DIR__.'/bootstrap.php';
require_once __DIR__.'/mailer.php';

function header_html(string $title='TSSMT'): void {
    global $pdo;
    $org=setting($pdo,'org_name','TSSMT');
    $f=consume_flash();
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title)?> | <?=e($org)?></title>
<link rel="stylesheet" href="/assets/style.css">
<style>
.language-picker{display:inline-flex;align-items:center}.goog-te-gadget{font:inherit!important;color:var(--ink)!important}.goog-te-gadget .goog-te-combo{max-width:135px;margin:0!important;padding:5px!important;border:1px solid #d5d9df!important;border-radius:6px!important;background:#fff;color:var(--ink)}
.password-field{position:relative;display:block}.password-field input{padding-right:48px!important}.password-toggle{position:absolute;right:7px;top:50%;transform:translateY(-50%);width:auto!important;margin:0!important;padding:4px 7px!important;border:0!important;background:transparent!important;color:var(--blue)!important;font-size:1.15rem;line-height:1;cursor:pointer}
</style>
</head>
<body>
<header>
  <a class="brand" href="/"><?=e($org)?></a>
  <nav>
    <a href="/about.php">About</a>
    <a href="/activities.php">Activities</a>
    <a href="/notices.php">Notices</a>
    <a href="/register.php">Membership</a>
    <a href="/donate.php">Donation</a>
    <a href="/contact.php">Contact</a>
    <?php if(!empty($_SESSION['admin_id'])): ?>
      <a href="/admin/dashboard.php">Admin panel</a>
    <?php elseif(!empty($_SESSION['member_id'])): ?>
      <a href="/member/dashboard.php">My panel</a>
    <?php else: ?>
      <a href="/login.php">Member Login</a>
      <a href="/admin/login.php">Admin</a>
    <?php endif; ?>
    <div id="google_translate_element" class="language-picker" aria-label="Choose language"></div>
  </nav>
</header>
<main>
  <?php if($f): ?><p class="flash <?=e($f[1])?>"><?=e($f[0])?></p><?php endif; ?>
<?php
}

function footer_html(): void {
    ?>
</main>
<footer>&copy; <?=date('Y')?> TSSMT</footer>
<script>
function googleTranslateElementInit() {
  new google.translate.TranslateElement({
    pageLanguage: 'en',
    includedLanguages: 'en,hi,bn,gu,kn,ml,mr,or,pa,ta,te,ur',
    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
  }, 'google_translate_element');
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('input[type="password"]').forEach(function (input) {
    var wrapper=document.createElement('span');
    wrapper.className='password-field';
    input.parentNode.insertBefore(wrapper,input);
    wrapper.appendChild(input);
    var button=document.createElement('button');
    button.type='button';
    button.className='password-toggle';
    button.setAttribute('aria-label','Show password');
    button.setAttribute('title','Show password');
    button.textContent='👁';
    button.addEventListener('click',function(){
      var show=input.type==='password';
      input.type=show?'text':'password';
      button.textContent=show?'◉':'👁';
      button.setAttribute('aria-label',show?'Hide password':'Show password');
      button.setAttribute('title',show?'Hide password':'Show password');
    });
    wrapper.appendChild(button);
  });
});
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
<?php
}

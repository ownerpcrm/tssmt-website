<?php declare(strict_types=1);

$env = [];
foreach (file('/etc/tssmt.org/database.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if ($line[0] !== '#' && str_contains($line, '=')) {
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

try {
    $pdo = new PDO(
        'mysql:host=' . ($env['DB_HOST'] ?? 'localhost') . ';dbname=' . ($env['DB_NAME'] ?? 'tssmt_db') . ';charset=utf8mb4',
        $env['DB_USER'] ?? '', $env['DB_PASS'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
    );
} catch (Throwable) {
    http_response_code(500); exit('Service configuration is temporarily unavailable.');
}

session_name('tssmt_session');
session_set_cookie_params(['httponly' => true, 'secure' => true, 'samesite' => 'Lax']);
session_start();

function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function check_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Invalid request token.'); } }
function go(string $page): never { header('Location: /portal.php?page=' . rawurlencode($page)); exit; }
function flash(?string $message = null): ?string { if ($message !== null) { $_SESSION['flash'] = $message; return null; } $message = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $message; }
function page(string $title, string $content): never {
    $message = flash();
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . e($title) . ' | TSSMT</title><style>body{margin:0;background:#f6f8fb;color:#172033;font:16px/1.6 system-ui,sans-serif}header{padding:18px 8%;background:#fff;border-bottom:1px solid #e4e8ef;display:flex;justify-content:space-between;gap:18px;align-items:center}.brand{font-weight:900;letter-spacing:.1em;color:#102a52;text-decoration:none}nav{display:flex;gap:15px;flex-wrap:wrap}nav a{color:#345;text-decoration:none}main{max-width:820px;margin:42px auto;padding:0 22px}.card{background:#fff;border:1px solid #e4e8ef;border-radius:14px;padding:28px;margin:20px 0}label{display:block;font-weight:700;margin:14px 0 5px}input{width:100%;padding:11px;border:1px solid #bfc8d6;border-radius:7px;box-sizing:border-box;font:inherit}button{margin-top:18px;padding:11px 16px;border:0;border-radius:7px;background:#102a52;color:#fff;font:inherit;font-weight:700;cursor:pointer}.notice{padding:13px 16px;background:#edf8f1;border-left:4px solid #16834a;border-radius:6px}.error{background:#fff0f0;border-color:#c33}table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #e4e8ef;text-align:left}@media(max-width:650px){header{align-items:flex-start;flex-direction:column}}</style></head><body><header><a class="brand" href="/">TSSMT</a><nav><a href="/portal.php?page=signup">Member signup</a><a href="/portal.php?page=login">Member sign in</a><a href="/portal.php?page=admin">Admin login</a></nav></header><main>' . ($message ? '<p class="notice">' . e($message) . '</p>' : '') . $content . '</main></body></html>';
    exit;
}

$page = $_GET['page'] ?? 'signup';

if ($page === 'logout') { $_SESSION = []; session_destroy(); go('login'); }

if ($page === 'signup') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf(); $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? '')); $password = $_POST['password'] ?? '';
        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) { flash('Enter a name, a valid email address, and a password of at least 8 characters.'); go('signup'); }
        try { $pdo->prepare("INSERT INTO members (full_name,email,password_hash,status) VALUES (?,?,?,'pending')")->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]); flash('Your signup request was submitted. An administrator must approve it before you can sign in.'); go('login'); }
        catch (PDOException) { flash('That email address already has a signup request or account.'); go('signup'); }
    }
    page('Member signup request', '<section class="card"><h1>Member signup request</h1><p>Submit your request. You can sign in after it is approved by an administrator.</p><form method="post"><input type="hidden" name="csrf" value="' . csrf() . '"><label>Full name<input name="name" maxlength="150" autocomplete="name" required></label><label>Email address<input type="email" name="email" autocomplete="email" required></label><label>Create password<input type="password" name="password" minlength="8" autocomplete="new-password" required></label><button>Submit request</button></form></section>');
}

if ($page === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf(); $email = strtolower(trim($_POST['email'] ?? '')); $statement = $pdo->prepare("SELECT * FROM members WHERE email=? AND status='active'"); $statement->execute([$email]); $member = $statement->fetch();
        if ($member && password_verify($_POST['password'] ?? '', $member['password_hash'])) { session_regenerate_id(true); $_SESSION['member_id'] = (int)$member['id']; go('member'); }
        flash('Invalid sign-in details, or your request is still awaiting approval.'); go('login');
    }
    page('Member sign in', '<section class="card"><h1>Member sign in</h1><form method="post"><input type="hidden" name="csrf" value="' . csrf() . '"><label>Email address<input type="email" name="email" autocomplete="email" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button>Sign in</button></form><p><a href="/portal.php?page=signup">Need to submit a signup request?</a></p></section>');
}

if ($page === 'member') {
    $id = (int)($_SESSION['member_id'] ?? 0); $statement = $pdo->prepare("SELECT full_name,email FROM members WHERE id=? AND status='active'"); $statement->execute([$id]); $member = $statement->fetch(); if (!$member) { unset($_SESSION['member_id']); go('login'); }
    page('Member area', '<section class="card"><h1>Welcome, ' . e($member['full_name']) . '</h1><p>Your member sign-in is active.</p><p><a href="/portal.php?page=logout">Sign out</a></p></section>');
}

if ($page === 'admin') {
    if (empty($_SESSION['admin_id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf(); $statement = $pdo->prepare('SELECT * FROM admins WHERE email=? AND is_active=1'); $statement->execute([strtolower(trim($_POST['email'] ?? ''))]); $admin = $statement->fetch();
        if ($admin && password_verify($_POST['password'] ?? '', $admin['password_hash'])) { session_regenerate_id(true); $_SESSION['admin_id'] = (int)$admin['id']; go('admin'); }
        flash('Invalid administrator sign-in details.'); go('admin');
    }
    if (empty($_SESSION['admin_id'])) page('Admin login', '<section class="card"><h1>Administrator login</h1><form method="post"><input type="hidden" name="csrf" value="' . csrf() . '"><label>Email address<input type="email" name="email" autocomplete="email" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button>Sign in</button></form></section>');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { check_csrf(); $status = ($_POST['action'] ?? '') === 'approve' ? 'active' : 'rejected'; $pdo->prepare("UPDATE members SET status=? WHERE id=? AND status='pending'")->execute([$status, (int)($_POST['member_id'] ?? 0)]); flash('Signup request updated.'); go('admin'); }
    $requests = $pdo->query("SELECT id,full_name,email,created_at FROM members WHERE status='pending' ORDER BY created_at ASC")->fetchAll(); $rows = $requests ? '' : '<tr><td colspan="3">No pending signup requests.</td></tr>';
    foreach ($requests as $request) $rows .= '<tr><td>' . e($request['full_name']) . '</td><td>' . e($request['email']) . '</td><td><form method="post"><input type="hidden" name="csrf" value="' . csrf() . '"><input type="hidden" name="member_id" value="' . (int)$request['id'] . '"><button name="action" value="approve">Approve</button><button name="action" value="reject">Reject</button></form></td></tr>';
    page('Admin dashboard', '<section class="card"><h1>Signup requests</h1><table><tr><th>Name</th><th>Email</th><th>Action</th></tr>' . $rows . '</table><p><a href="/portal.php?page=logout">Sign out</a></p></section>');
}

http_response_code(404); page('Not found', '<section class="card"><h1>Not found</h1></section>');

<?php
require_once __DIR__.'/../app/layout.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        $name=trim($_POST['donor_name']??'');
        $mobile=preg_replace('/\D/','',$_POST['donor_mobile']??'');
        $email=filter_var($_POST['donor_email']??'',FILTER_VALIDATE_EMAIL);
        $amount=(float)($_POST['amount']??0);
        $purpose=trim($_POST['purpose']??'');
        $mode=$_POST['payment_mode']??'';
        if(strlen($name)<2||!preg_match('/^\d{10,15}$/',$mobile)||!$email||$amount<=0||$purpose===''||!in_array($mode,['upi','bank','cash'],true)) throw new RuntimeException('Please complete all required donation details.');
        $pdo->prepare('INSERT INTO donations (donor_name,donor_mobile,donor_email,donor_address,donor_state,donor_city,donor_pan,donor_aadhaar,amount,purpose,payment_mode,transaction_no,payment_date,proof_path,remarks) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$name,$mobile,$email,trim($_POST['donor_address']??''),trim($_POST['donor_state']??''),trim($_POST['donor_city']??''),trim($_POST['donor_pan']??'')?:null,trim($_POST['donor_aadhaar']??'')?:null,$amount,$purpose,$mode,trim($_POST['transaction_no']??'')?:null,$_POST['payment_date']??date('Y-m-d'),upload('proof'),trim($_POST['remarks']??'')?:null]);
        flash('Thank you. Your donation details have been submitted for administrator verification.');
        redirect('/donate.php');
    }catch(Throwable $e){flash($e->getMessage(),'error');}
}
header_html('Donate now');
?>
<style>.donation-form{max-width:900px;margin:0 auto}.donation-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0 24px}.donation-grid .wide{grid-column:1/-1}.payment-details{display:grid;grid-template-columns:1fr 240px;gap:24px;align-items:center;margin:26px 0;padding:24px;border:1px solid #d9e5db;border-radius:14px;background:#f4fbf5}.payment-qr{width:100%;height:240px;border:0;background:#fff}@media(max-width:700px){.donation-grid,.payment-details{grid-template-columns:1fr}}</style>
<section class="donation-form">
<h1>Donate Now</h1>
<p>Your contribution helps <?=e(setting($pdo,'org_name','TSSMT'))?> continue its community and freedom-fighter remembrance work. Members may also donate from their dashboard; every donation is verified by an administrator.</p>
<section class="payment-details"><div><p class="section-label">Pay by UPI or bank transfer</p><h2>Payment details</h2><p><strong>Account name:</strong> Talcher Swadhinata Sangrami Memorial Trust<br><strong>Bank:</strong> UCO Bank, Kanihasambal Puri Branch<br><strong>Account number:</strong> 09090110051689<br><strong>IFSC:</strong> UCBA0000909<br><strong>UPI ID:</strong> 9439415894@ucobank</p><p>After payment, enter the UTR/transaction number and upload proof below. An administrator will verify it before approval.</p></div><object class="payment-qr" data="/assets/payment-qr.pdf" type="application/pdf"><a href="/assets/payment-qr.pdf" target="_blank">Open payment QR code</a></object></section>
<h2>Contribute Now</h2>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=csrf()?>">
<div class="donation-grid">
<label>Name *<input name="donor_name" required></label><label>Contact number *<input name="donor_mobile" inputmode="numeric" required></label><label>Email address *<input type="email" name="donor_email" required></label>
<label>Amount (₹) *<input type="number" step="0.01" min="1" name="amount" required></label><label>State<input name="donor_state"></label><label>City<input name="donor_city"></label>
<label class="wide">Address<textarea name="donor_address"></textarea></label>
<label>PAN (optional)<input name="donor_pan" maxlength="20"></label><label>Aadhaar (optional)<input name="donor_aadhaar" inputmode="numeric" maxlength="20"></label><label>Purpose *<input name="purpose" required maxlength="255"></label>
<label>Payment mode *<select name="payment_mode"><option value="upi">UPI / QR</option><option value="bank">Bank transfer</option><option value="cash">Cash</option></select></label><label>UTR / transaction number<input name="transaction_no"></label><label>Payment date *<input type="date" name="payment_date" value="<?=date('Y-m-d')?>" required></label>
<label class="wide">Payment proof (optional)<input type="file" name="proof" accept="image/jpeg,image/png,image/webp,application/pdf"></label><label class="wide">Remarks<textarea name="remarks"></textarea></label>
</div><button>Submit donation</button></form>
<p>Already a member? <a href="/login.php">Member login</a>.</p>
</section>
<?php footer_html();

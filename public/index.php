<?php
require_once __DIR__.'/../app/layout.php';
header_html('Home');
$notices=$pdo->query("SELECT * FROM notices WHERE status='active' AND audience IN ('public','both') AND publish_date<=CURDATE() AND (expiry_date IS NULL OR expiry_date>=CURDATE()) ORDER BY publish_date DESC LIMIT 3")->fetchAll();
$activities=$pdo->query("SELECT * FROM activities WHERE status='active' ORDER BY activity_date DESC LIMIT 3")->fetchAll();
$gallery=$pdo->query("SELECT ai.image_path,a.title FROM activity_images ai JOIN activities a ON a.id=ai.activity_id WHERE a.status='active' ORDER BY a.activity_date DESC,ai.id DESC LIMIT 12")->fetchAll();
?>
<section class="hero hero--tricolour">
  <div class="hero__content">
    <p class="eyebrow">FREEDOM • SERVICE • UNITY</p>
    <h1><?=e(setting($pdo,'org_name','TSSMT'))?></h1>
    <p class="hero__intro"><?=e($pdo->query("SELECT body FROM cms_content WHERE `key`='home_intro'")->fetchColumn()?:'Dedicated to honouring our freedom fighters and serving the nation with pride.')?></p>
    <p class="actions"><a class="button" href="/register.php">Join the mission</a><a class="button button--outline" href="/donate.php">Support the cause</a></p>
  </div>
  <div class="chakra" aria-label="Ashoka Chakra"><span>24</span></div>
</section>

<section class="values">
  <article><span class="value-icon">✦</span><h2>Honour</h2><p>Remembering the courage and sacrifice that gave India her freedom.</p></article>
  <article><span class="value-icon">✦</span><h2>Unity</h2><p>Bringing citizens together in the spirit of our tricolour.</p></article>
  <article><span class="value-icon">✦</span><h2>Service</h2><p>Turning gratitude into meaningful action for our communities.</p></article>
</section>

<section class="grid home-grid">
  <article class="card mission-card"><p class="section-label">Our purpose</p><h2>Our Mission</h2><p><?=e($pdo->query("SELECT body FROM cms_content WHERE `key`='mission'")->fetchColumn()?:'')?></p></article>
  <article class="card contact-card"><p class="section-label">Stay connected</p><h2>Contact</h2><p><?=nl2br(e($pdo->query("SELECT body FROM cms_content WHERE `key`='contact'")->fetchColumn()?:setting($pdo,'email')))?></p><a href="/contact.php">Get in touch →</a></article>
</section>

<?php if($gallery): ?>
<section class="home-section gallery-section">
  <div class="section-heading"><div><p class="section-label">Our moments</p><h2>Photo Gallery</h2></div><a href="/activities.php">View activities →</a></div>
  <div class="gallery-scroll" aria-label="Activity photo gallery">
    <?php foreach($gallery as $photo): ?>
      <figure class="gallery-item"><img src="<?=e($photo['image_path'])?>" alt="<?=e($photo['title'])?>" loading="lazy"><span><?=e($photo['title'])?></span></figure>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="home-section">
  <div class="section-heading"><div><p class="section-label">Stay informed</p><h2>Latest notices</h2></div><a href="/notices.php">View all →</a></div>
  <section class="grid"><?php foreach($notices as $n): ?><article class="card notice-card"><h3><?=e($n['title'])?></h3><p><?=e($n['description'])?></p></article><?php endforeach; ?></section>
</section>

<section class="home-section">
  <div class="section-heading"><div><p class="section-label">In the community</p><h2>Latest activities</h2></div><a href="/activities.php">View all →</a></div>
  <section class="grid"><?php foreach($activities as $a): ?><article class="card activity-card"><h3><?=e($a['title'])?></h3><p class="activity-meta"><?=e($a['activity_date'])?><?= $a['location']?' · '.e($a['location']):''?></p><p><?=e($a['description'])?></p></article><?php endforeach; ?></section>
</section>
<?php footer_html();

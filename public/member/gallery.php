<?php
require_once __DIR__.'/../../app/layout.php';
$m=require_member();

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $id=(int)($_POST['id']??0);
    $action=$_POST['action']??'save';
    $s=$pdo->prepare('SELECT * FROM gallery_images WHERE id=? AND member_id=?');
    $s->execute([$id,$m['id']]);
    $existing=$id?$s->fetch():null;

    if($action==='delete'&&$existing){
        $pdo->prepare('DELETE FROM gallery_images WHERE id=? AND member_id=?')->execute([$id,$m['id']]);
        flash('Your photo submission was removed.');
    }else{
        try{
            $title=trim($_POST['title']??'');
            $image=upload('image')?:($existing['image_path']??null);
            if($title===''||!$image){
                flash('A title and photo are required.','error');
            }elseif($existing){
                $pdo->prepare("UPDATE gallery_images SET title=?,image_path=?,status='pending' WHERE id=? AND member_id=?")->execute([$title,$image,$id,$m['id']]);
                flash('Photo updated and sent for approval.');
            }else{
                $pdo->prepare("INSERT INTO gallery_images (member_id,title,image_path,status) VALUES (?,?,?,'pending')")->execute([$m['id'],$title,$image]);
                flash('Photo submitted for administrator approval.');
            }
        }catch(Throwable $e){ flash($e->getMessage(),'error'); }
    }
    redirect('/member/gallery.php');
}

$edit=['id'=>'','title'=>'','image_path'=>''];
if(isset($_GET['edit'])){
    $s=$pdo->prepare('SELECT * FROM gallery_images WHERE id=? AND member_id=?');
    $s->execute([(int)$_GET['edit'],$m['id']]);
    $edit=$s->fetch()?:$edit;
}
$s=$pdo->prepare('SELECT * FROM gallery_images WHERE member_id=? ORDER BY id DESC');
$s->execute([$m['id']]);
$rows=$s->fetchAll();
header_html('My photo submissions');
?>
<h1>My photo submissions</h1>
<p>Upload photos for the home-page gallery. An administrator must approve each new or edited photo before it becomes public.</p>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=e((string)$edit['id'])?>">
<label>Photo title<input name="title" value="<?=e($edit['title'])?>" required></label>
<label>Photo<input type="file" name="image" accept="image/jpeg,image/png,image/webp" <?=$edit['id']?'':'required'?>></label>
<button><?= $edit['id']?'Update submission':'Submit photo' ?></button>
</form>
<h2>Your submissions</h2>
<table><tr><th>Photo</th><th>Title</th><th>Status</th><th></th></tr><?php foreach($rows as $row): ?><tr><td><img src="<?=e($row['image_path'])?>" alt="" width="90" height="55" style="object-fit:cover"></td><td><?=e($row['title'])?></td><td><?=e(ucfirst($row['status']))?></td><td><a href="?edit=<?=$row['id']?>">Edit</a> <form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$row['id']?>"><button name="action" value="delete">Delete</button></form></td></tr><?php endforeach; ?></table>
<?php footer_html();

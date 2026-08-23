<?php
require_once __DIR__.'/../../app/layout.php';
$a=require_admin();

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $id=(int)($_POST['id']??0);
    $action=$_POST['action']??'save';

    if($action==='delete'&&$id){
        $pdo->prepare('DELETE FROM gallery_images WHERE id=?')->execute([$id]);
        audit($pdo,$a['id'],'gallery_image_deleted','gallery_image',$id);
        flash('Gallery photo removed.');
    }else{
        $title=trim($_POST['title']??'');
        $status=$_POST['status']??'active';
        $sortOrder=max(0,(int)($_POST['sort_order']??0));
        $existing=null;
        if($id){
            $s=$pdo->prepare('SELECT * FROM gallery_images WHERE id=?');
            $s->execute([$id]);
            $existing=$s->fetch();
        }

        try{
            $image=upload('image')?:($existing['image_path']??null);
            if($title===''||!$image){
                flash('A title and photo are required.','error');
            }elseif($id){
                $pdo->prepare('UPDATE gallery_images SET title=?,image_path=?,sort_order=?,status=? WHERE id=?')->execute([$title,$image,$sortOrder,$status,$id]);
                audit($pdo,$a['id'],'gallery_image_updated','gallery_image',$id);
                flash('Gallery photo updated.');
            }else{
                $pdo->prepare('INSERT INTO gallery_images (title,image_path,sort_order,status) VALUES (?,?,?,?)')->execute([$title,$image,$sortOrder,$status]);
                $newId=(int)$pdo->lastInsertId();
                audit($pdo,$a['id'],'gallery_image_created','gallery_image',$newId);
                flash('Gallery photo added.');
            }
        }catch(Throwable $e){
            flash($e->getMessage(),'error');
        }
    }
    redirect('/admin/gallery.php');
}

$edit=['id'=>'','title'=>'','image_path'=>'','sort_order'=>0,'status'=>'active'];
if(isset($_GET['edit'])){
    $s=$pdo->prepare('SELECT * FROM gallery_images WHERE id=?');
    $s->execute([(int)$_GET['edit']]);
    $edit=$s->fetch()?:$edit;
}
$rows=$pdo->query('SELECT * FROM gallery_images ORDER BY sort_order ASC,id DESC')->fetchAll();
header_html('Manage photo gallery');
?>
<h1>Manage photo gallery</h1>
<p>Add photos that will scroll on the home page. Lower display order appears first.</p>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?=csrf()?>">
  <input type="hidden" name="id" value="<?=e((string)$edit['id'])?>">
  <label>Photo title<input name="title" value="<?=e($edit['title'])?>" required></label>
  <label>Photo<input type="file" name="image" accept="image/jpeg,image/png,image/webp" <?=$edit['id']?'':'required'?>></label>
  <?php if($edit['image_path']): ?><p>Current photo: <a href="<?=e($edit['image_path'])?>" target="_blank" rel="noopener">View image</a></p><?php endif; ?>
  <label>Display order<input type="number" min="0" name="sort_order" value="<?=e((string)$edit['sort_order'])?>"></label>
  <label>Status<select name="status"><option value="active" <?=$edit['status']==='active'?'selected':''?>>Visible</option><option value="inactive" <?=$edit['status']==='inactive'?'selected':''?>>Hidden</option></select></label>
  <button><?= $edit['id']?'Update photo':'Add photo' ?></button>
</form>

<h2>Gallery photos</h2>
<table><tr><th>Photo</th><th>Title</th><th>Order</th><th>Status</th><th></th></tr>
<?php foreach($rows as $row): ?>
  <tr><td><img src="<?=e($row['image_path'])?>" alt="" width="90" height="55" style="object-fit:cover"></td><td><?=e($row['title'])?></td><td><?=e((string)$row['sort_order'])?></td><td><?=e($row['status'])?></td><td><a href="?edit=<?=$row['id']?>">Edit</a> <form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$row['id']?>"><button name="action" value="delete">Delete</button></form></td></tr>
<?php endforeach; ?>
</table>
<?php footer_html();

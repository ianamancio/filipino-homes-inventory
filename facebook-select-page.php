<?php
require __DIR__.'/config/bootstrap.php';require_login();
$pages=$_SESSION['fb_available_pages']??[];if(!$pages)redirect('facebook.php');
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$id=(string)($_POST['page_id']??'');foreach($pages as $p){if((string)$p['id']===$id){save_setting('fb_page_id',(string)$p['id']);save_setting('fb_page_name',(string)$p['name']);save_setting('fb_page_token',(string)$p['access_token']);unset($_SESSION['fb_available_pages']);flash('success','Facebook Page connected successfully.');redirect('facebook.php');}}}
require __DIR__.'/includes/header.php';
?>
<div class="page-title mb-2">Choose your Facebook Page</div><p class="text-secondary">Select the Page where your property listings should be published.</p><div class="panel" style="max-width:700px"><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><?php foreach($pages as $p): ?><label class="d-flex gap-3 align-items-center border rounded-3 p-3 mb-2"><input type="radio" name="page_id" value="<?=h((string)$p['id'])?>" required><span><strong><?=h($p['name'])?></strong><small class="d-block text-secondary">Page ID: <?=h((string)$p['id'])?></small></span></label><?php endforeach; ?><button class="btn btn-primary mt-2">Connect Selected Page</button></form></div>
<?php require __DIR__.'/includes/footer.php'; ?>

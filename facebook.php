<?php
require __DIR__.'/config/bootstrap.php';require_login();
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['disconnect'])){verify_csrf();foreach(['fb_page_id','fb_page_name','fb_page_token'] as $k){$st=db()->prepare('DELETE FROM settings WHERE setting_key=?');$st->execute([$k]);}flash('success','Facebook Page disconnected.');redirect('facebook.php');}
$connected=setting('fb_page_id');
require __DIR__.'/includes/header.php';
?>
<div class="mb-4"><div class="page-title">Facebook Integration</div><div class="text-muted2">Connect your Facebook Business Page to publish property listings automatically.</div></div>
<div class="panel" style="max-width:760px"><div class="d-flex align-items-center gap-3 mb-3"><div class="brand-mark" style="background:#1877f2">f</div><div><h5 class="mb-0">Facebook Business Page</h5><div class="small text-secondary">Meta Graph API · <?=h(envv('META_GRAPH_VERSION','v26.0'))?></div></div></div>
<?php if($connected): ?><div class="alert alert-success">Connected to <strong><?=h(setting('fb_page_name')??'Facebook Page')?></strong></div><div class="small text-secondary mb-3">Page ID: <?=h($connected)?></div><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><button class="btn btn-outline-danger" name="disconnect" value="1" data-confirm="Disconnect this Facebook Page?">Disconnect Page</button></form>
<?php else: ?><p class="text-secondary">No Page connected yet. Configure your Meta App credentials in the <code>.env</code> file, then authorize your Facebook account.</p><a class="btn btn-primary" href="facebook-connect.php"><span class="me-1">f</span> Connect Facebook Page</a><?php endif; ?>
<hr><h6>Required Meta permissions</h6><code>pages_show_list</code>, <code>pages_read_engagement</code>, <code>pages_manage_posts</code><p class="small text-secondary mt-2 mb-0">The Facebook user must have Page access that allows creating content. App Review/Advanced Access may be needed for users outside your app roles.</p></div>
<?php require __DIR__.'/includes/footer.php'; ?>

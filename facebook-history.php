<?php
require __DIR__.'/config/bootstrap.php';require_login();
$rows=db()->query("SELECT f.*,p.property_code,p.title FROM facebook_posts f JOIN properties p ON p.id=f.property_id ORDER BY f.created_at DESC LIMIT 200")->fetchAll();
require __DIR__.'/includes/header.php';
?>
<div class="mb-4"><div class="page-title">Facebook Publishing History</div><div class="text-muted2">Review automatic publishing results and errors.</div></div>
<div class="panel"><div class="table-responsive"><table class="table"><thead><tr><th>Property</th><th>Status</th><th>Facebook Post ID</th><th>Date</th><th>Details</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><strong><?=h($r['title'])?></strong><div class="small text-secondary"><?=h($r['property_code'])?></div></td><td><span class="badge text-bg-<?=$r['status']==='Published'?'success':($r['status']==='Failed'?'danger':'warning')?>"><?=h($r['status'])?></span></td><td class="small"><?=h($r['facebook_post_id']??'—')?></td><td><?=h($r['published_at']??$r['created_at'])?></td><td class="small text-danger"><?=h($r['error_message']??'')?></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="5" class="empty-state">No Facebook posts yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php require __DIR__.'/includes/footer.php'; ?>

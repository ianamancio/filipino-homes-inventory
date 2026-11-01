<?php
require __DIR__.'/config/bootstrap.php'; require_login();
$pdo=db();
$stats=[
 'properties'=>$pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn(),
 'available'=>$pdo->query("SELECT COUNT(*) FROM properties WHERE status='Available'")->fetchColumn(),
 'reserved'=>$pdo->query("SELECT COUNT(*) FROM properties WHERE status='Reserved'")->fetchColumn(),
 'sold'=>$pdo->query("SELECT COUNT(*) FROM properties WHERE status='Sold'")->fetchColumn(),
 'clients'=>$pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn(),
 'commission'=>$pdo->query("SELECT COALESCE(SUM(commission),0) FROM transactions")->fetchColumn()
];
$recent=$pdo->query("SELECT p.*, (SELECT image_path FROM property_images i WHERE i.property_id=p.id ORDER BY i.id LIMIT 1) image FROM properties p ORDER BY p.created_at DESC LIMIT 6")->fetchAll();
require __DIR__.'/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><div class="page-title">Dashboard</div><div class="text-muted2">Your real estate business at a glance.</div></div><a class="btn btn-primary" href="add-property.php">+ Add Property</a></div>
<div class="row g-3 mb-4">
<?php foreach([['Total Properties',$stats['properties'],'⌂'],['Available',$stats['available'],'✓'],['Reserved',$stats['reserved'],'◷'],['Sold',$stats['sold'],'▣'],['Clients',$stats['clients'],'♙'],['Commissions','₱'.number_format((float)$stats['commission'],2),'₱']] as $s): ?>
<div class="col-6 col-xl-4"><div class="stat-card"><div class="d-flex justify-content-between"><div class="stat-label"><?=h($s[0])?></div><div class="stat-icon"><?=h($s[2])?></div></div><div class="stat-value"><?=h((string)$s[1])?></div></div></div>
<?php endforeach; ?>
</div>
<div class="panel"><div class="d-flex justify-content-between align-items-center mb-3"><div class="fw-bold">Recently Added Properties</div><a href="properties.php" class="small">View all</a></div><div class="table-responsive"><table class="table"><thead><tr><th>Property</th><th>Location</th><th>Price</th><th>Status</th></tr></thead><tbody>
<?php foreach($recent as $p): ?><tr><td><div class="d-flex align-items-center gap-2"><img class="property-thumb" src="<?=h($p['image'] ?: 'assets/img-placeholder.svg')?>"><div><strong><?=h($p['title'])?></strong><div class="small text-secondary"><?=h($p['property_code'])?></div></div></div></td><td><?=h($p['location'])?></td><td>₱<?=number_format((float)$p['price'],2)?></td><td><span class="badge-status status-<?=strtolower($p['status'])?>"><?=h($p['status'])?></span></td></tr><?php endforeach; ?>
<?php if(!$recent): ?><tr><td colspan="4" class="empty-state">No properties yet. Add your first listing.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require __DIR__.'/includes/footer.php'; ?>

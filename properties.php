<?php
require __DIR__.'/config/bootstrap.php'; require_login();
$q=trim($_GET['q']??''); $status=$_GET['status']??'';
$sql="SELECT p.*, (SELECT image_path FROM property_images i WHERE i.property_id=p.id ORDER BY i.id LIMIT 1) image FROM properties p WHERE (p.title LIKE ? OR p.property_code LIKE ? OR p.location LIKE ?)";
$args=["%$q%","%$q%","%$q%"];
if(in_array($status,['Available','Reserved','Sold','Rented'],true)){ $sql.=" AND p.status=?";$args[]=$status; }
$sql.=" ORDER BY p.created_at DESC";
$stmt=db()->prepare($sql);$stmt->execute($args);$rows=$stmt->fetchAll();
require __DIR__.'/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><div class="page-title">Properties</div><div class="text-muted2">Manage all your property listings.</div></div><a class="btn btn-primary" href="add-property.php">+ Add Property</a></div>
<div class="panel"><form class="row g-2 mb-3"><div class="col-md-6"><input class="form-control" name="q" value="<?=h($q)?>" placeholder="Search title, code or location"></div><div class="col-md-3"><select class="form-select" name="status"><option value="">All statuses</option><?php foreach(['Available','Reserved','Sold','Rented'] as $s): ?><option <?=$status===$s?'selected':''?>><?=$s?></option><?php endforeach; ?></select></div><div class="col-md-3"><button class="btn btn-outline-primary w-100">Search</button></div></form>
<div class="table-responsive"><table class="table"><thead><tr><th>Property</th><th>Type</th><th>Location</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $p): ?><tr><td><div class="d-flex align-items-center gap-2"><img class="property-thumb" src="<?=h($p['image'] ?: 'assets/img-placeholder.svg')?>"><div><strong><?=h($p['title'])?></strong><div class="small text-secondary"><?=h($p['property_code'])?></div></div></div></td><td><?=h($p['property_type'])?> · <?=h($p['listing_type'])?></td><td><?=h($p['location'])?></td><td>₱<?=number_format((float)$p['price'],2)?></td><td><span class="badge-status status-<?=strtolower($p['status'])?>"><?=h($p['status'])?></span></td><td><a class="btn btn-sm btn-outline-primary" href="edit-property.php?id=<?=$p['id']?>">Edit</a> <form class="d-inline" method="post" action="delete-property.php" onsubmit="return confirm('Delete this property?')"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="6" class="empty-state">No matching properties found.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require __DIR__.'/includes/footer.php'; ?>

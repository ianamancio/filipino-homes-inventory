<?php
require __DIR__.'/config/bootstrap.php'; require_login();
$id=(int)($_GET['id']??0); $editing=$id>0;
$p=['property_code'=>'','title'=>'','property_type'=>'House and Lot','listing_type'=>'For Sale','location'=>'','price'=>'','bedrooms'=>0,'bathrooms'=>0,'floor_area'=>'','lot_area'=>'','description'=>'','status'=>'Available','publish_to_facebook'=>1];
if($editing){$st=db()->prepare('SELECT * FROM properties WHERE id=?');$st->execute([$id]);$found=$st->fetch();if(!$found){flash('danger','Property not found.');redirect('properties.php');}$p=$found;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 foreach(['property_code','title','property_type','listing_type','location','price','bedrooms','bathrooms','floor_area','lot_area','description','status'] as $k)$p[$k]=trim($_POST[$k]??'');
 $p['publish_to_facebook']=isset($_POST['publish_to_facebook'])?1:0;
 if($p['property_code']===''||$p['title']===''||$p['location']===''||!is_numeric($p['price'])||$p['price']<0)$error='Please enter a property code, title, location, and valid price.';
 if(!$error){
  try{
   if($editing){$st=db()->prepare('UPDATE properties SET property_code=?,title=?,property_type=?,listing_type=?,location=?,price=?,bedrooms=?,bathrooms=?,floor_area=?,lot_area=?,description=?,status=?,publish_to_facebook=? WHERE id=?');$st->execute([$p['property_code'],$p['title'],$p['property_type'],$p['listing_type'],$p['location'],$p['price'],$p['bedrooms'],$p['bathrooms'],$p['floor_area']?:null,$p['lot_area']?:null,$p['description'],$p['status'],$p['publish_to_facebook'],$id]);$propertyId=$id;}
   else{$st=db()->prepare('INSERT INTO properties(property_code,title,property_type,listing_type,location,price,bedrooms,bathrooms,floor_area,lot_area,description,status,publish_to_facebook,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)');$st->execute([$p['property_code'],$p['title'],$p['property_type'],$p['listing_type'],$p['location'],$p['price'],$p['bedrooms'],$p['bathrooms'],$p['floor_area']?:null,$p['lot_area']?:null,$p['description'],$p['status'],$p['publish_to_facebook'],$_SESSION['user_id']]);$propertyId=(int)db()->lastInsertId();}
   if(!empty($_FILES['photos']['name'][0])){
    $dir=__DIR__.'/uploads/properties/'; if(!is_dir($dir))mkdir($dir,0755,true);
    $finfo=new finfo(FILEINFO_MIME_TYPE); $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif'];
    foreach($_FILES['photos']['tmp_name'] as $i=>$tmp){if($_FILES['photos']['error'][$i]!==UPLOAD_ERR_OK)continue;if($_FILES['photos']['size'][$i]>8*1024*1024)continue;$mime=$finfo->file($tmp);if(!isset($allowed[$mime]))continue;$name=bin2hex(random_bytes(16)).'.'.$allowed[$mime];if(move_uploaded_file($tmp,$dir.$name)){$rel='uploads/properties/'.$name;$ins=db()->prepare('INSERT INTO property_images(property_id,image_path) VALUES(?,?)');$ins->execute([$propertyId,$rel]);}}
   }
   if(!$editing && $p['publish_to_facebook'] && setting('fb_page_id')) {
      require_once __DIR__.'/integrations/facebook-client.php';
      try{$result=fb_publish_property($propertyId);flash('success','Property saved and published to Facebook.');}
      catch(Throwable $e){$ins=db()->prepare("INSERT INTO facebook_posts(property_id,status,message,error_message) VALUES(?,'Failed',?,?)");$ins->execute([$propertyId,'Automatic property post',$e->getMessage()]);flash('warning','Property saved, but Facebook publishing failed. See Facebook History.');}
   } else flash('success',$editing?'Property updated.':'Property saved.');
   redirect('properties.php');
  }catch(Throwable $e){$error='Could not save property. Check that the property code is unique. '.$e->getMessage();}
 }
}
require __DIR__.'/includes/header.php';
?>
<div class="mb-4"><div class="page-title"><?=$editing?'Edit':'Add'?> Property</div><div class="text-muted2">Enter the listing details and upload property photos.</div></div>
<?php if($error): ?><div class="alert alert-danger"><?=h($error)?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form-card"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>">
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Property Code *</label><input class="form-control" name="property_code" value="<?=h($p['property_code'])?>" required placeholder="PROP-001"></div>
<div class="col-md-8"><label class="form-label">Property Title *</label><input class="form-control" name="title" value="<?=h($p['title'])?>" required placeholder="Modern 3-bedroom house and lot"></div>
<div class="col-md-4"><label class="form-label">Property Type</label><select class="form-select" name="property_type"><?php foreach(['House and Lot','Condominium','Townhouse','Lot / Land','Apartment','Commercial Property'] as $v): ?><option <?=$p['property_type']===$v?'selected':''?>><?=$v?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Listing Type</label><select class="form-select" name="listing_type"><?php foreach(['For Sale','For Rent'] as $v): ?><option <?=$p['listing_type']===$v?'selected':''?>><?=$v?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Price (PHP) *</label><input class="form-control" type="number" min="0" step="0.01" name="price" value="<?=h((string)$p['price'])?>" required></div>
<div class="col-md-6"><label class="form-label">Location *</label><input class="form-control" name="location" value="<?=h($p['location'])?>" required placeholder="Barangay, City, Province"></div>
<div class="col-md-2"><label class="form-label">Bedrooms</label><input class="form-control" type="number" min="0" name="bedrooms" value="<?=h((string)$p['bedrooms'])?>"></div>
<div class="col-md-2"><label class="form-label">Bathrooms</label><input class="form-control" type="number" min="0" name="bathrooms" value="<?=h((string)$p['bathrooms'])?>"></div>
<div class="col-md-2"><label class="form-label">Floor Area (sqm)</label><input class="form-control" type="number" step=".01" name="floor_area" value="<?=h((string)$p['floor_area'])?>"></div>
<div class="col-md-3"><label class="form-label">Lot Area (sqm)</label><input class="form-control" type="number" step=".01" name="lot_area" value="<?=h((string)$p['lot_area'])?>"></div>
<div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach(['Available','Reserved','Sold','Rented'] as $v): ?><option <?=$p['status']===$v?'selected':''?>><?=$v?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Property Photos (JPEG/PNG/GIF, max 8MB each)</label><input class="form-control" type="file" name="photos[]" accept="image/jpeg,image/png,image/gif" multiple></div>
<div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="4" name="description"><?=h($p['description'])?></textarea></div>
<div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="publish_to_facebook" id="fb" <?=$p['publish_to_facebook']?'checked':''?>><label class="form-check-label" for="fb">Publish to Facebook automatically after saving (new properties only)</label></div><div class="form-text">Connect your Facebook Page first in Facebook Integration. Property must have at least one photo for photo publishing.</div></div>
<div class="col-12 d-flex gap-2"><button class="btn btn-primary">Save Property</button><a class="btn btn-outline-secondary" href="properties.php">Cancel</a></div>
</div></form>
<?php require __DIR__.'/includes/footer.php'; ?>

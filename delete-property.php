<?php
require __DIR__.'/config/bootstrap.php';require_login();if($_SERVER['REQUEST_METHOD']!=='POST')redirect('properties.php');verify_csrf();
$id=(int)($_POST['id']??0);$st=db()->prepare('SELECT image_path FROM property_images WHERE property_id=?');$st->execute([$id]);foreach($st->fetchAll() as $img){$path=__DIR__.'/'.$img['image_path'];if(is_file($path))unlink($path);}
$st=db()->prepare('DELETE FROM properties WHERE id=?');$st->execute([$id]);flash('success','Property deleted.');redirect('properties.php');

<?php
require_once __DIR__.'/../config/bootstrap.php';

function fb_request(string $method,string $endpoint,array $data=[],bool $multipart=false): array {
    $version=envv('META_GRAPH_VERSION','v26.0');
    $url='https://graph.facebook.com/'.$version.'/'.$endpoint;
    $ch=curl_init($url);
    $opts=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>60,CURLOPT_CUSTOMREQUEST=>$method];
    if($method==='POST')$opts[CURLOPT_POST]=true;
    if($data){$opts[CURLOPT_POSTFIELDS]=$data;}
    curl_setopt_array($ch,$opts);
    $body=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
    if($body===false)throw new RuntimeException('Facebook connection error: '.$err);
    $json=json_decode($body,true);
    if($status<200||$status>=300||isset($json['error']))throw new RuntimeException($json['error']['message']??('Facebook API returned HTTP '.$status));
    return is_array($json)?$json:[];
}
function fb_publish_property(int $propertyId): array {
    $pdo=db();
    $st=$pdo->prepare('SELECT * FROM properties WHERE id=?');$st->execute([$propertyId]);$p=$st->fetch();
    if(!$p)throw new RuntimeException('Property not found.');
    $pageId=setting('fb_page_id');$token=setting('fb_page_token');
    if(!$pageId||!$token)throw new RuntimeException('Facebook Page is not connected.');
    $imgs=$pdo->prepare('SELECT image_path FROM property_images WHERE property_id=? ORDER BY id LIMIT 10');$imgs->execute([$propertyId]);$images=$imgs->fetchAll();
    $message=strtoupper($p['listing_type'])." - ".strtoupper($p['title'])."\n\n".
      "📍 Location: ".$p['location']."\n".
      "💰 Price: ₱".number_format((float)$p['price'],2)."\n".
      "🏠 Type: ".$p['property_type']."\n".
      "🛏 Bedrooms: ".$p['bedrooms']." | 🚿 Bathrooms: ".$p['bathrooms']."\n".
      ($p['floor_area']?"📐 Floor Area: ".$p['floor_area']." sqm\n":"").
      ($p['lot_area']?"🌳 Lot Area: ".$p['lot_area']." sqm\n":"")."\n".
      trim((string)$p['description'])."\n\nMessage us for inquiries!\n#RealEstate #PropertyForSale";
    if(!$images) {
      $result=fb_request('POST',$pageId.'/feed',['message'=>$message,'access_token'=>$token]);
    } else {
      $attached=[];
      foreach($images as $img){
        $path=dirname(__DIR__).'/'.$img['image_path'];
        if(!is_file($path))continue;
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($path);
        if(!in_array($mime,['image/jpeg','image/png','image/gif'],true))continue;
        $photo=fb_request('POST',$pageId.'/photos',[
          'source'=>new CURLFile($path,$mime,basename($path)),
          'published'=>'false','access_token'=>$token
        ],true);
        if(!empty($photo['id']))$attached[]=['media_fbid'=>$photo['id']];
      }
      if(!$attached)throw new RuntimeException('No supported property photo could be uploaded to Facebook.');
      $data=['message'=>$message,'access_token'=>$token];
      foreach($attached as $i=>$media)$data['attached_media['.$i.']']=json_encode($media);
      $result=fb_request('POST',$pageId.'/feed',$data);
    }
    if(empty($result['id']))throw new RuntimeException('Facebook did not return a post ID.');
    $st=$pdo->prepare("INSERT INTO facebook_posts(property_id,facebook_post_id,status,message,published_at) VALUES(?,?,'Published',?,NOW())");
    $st->execute([$propertyId,$result['id'],$message]);
    return $result;
}

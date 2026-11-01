<?php
require __DIR__.'/config/bootstrap.php';require_login();
if(isset($_GET['error'])){flash('danger','Facebook authorization was cancelled or denied.');redirect('facebook.php');}
if(empty($_GET['state'])||!hash_equals($_SESSION['fb_oauth_state']??'',(string)$_GET['state'])||empty($_GET['code'])){flash('danger','Invalid Facebook OAuth response. Please reconnect.');redirect('facebook.php');}
unset($_SESSION['fb_oauth_state']);
$version=envv('META_GRAPH_VERSION','v26.0');
$params=http_build_query(['client_id'=>envv('META_APP_ID'),'client_secret'=>envv('META_APP_SECRET'),'redirect_uri'=>envv('META_REDIRECT_URI'),'code'=>$_GET['code']]);
$url='https://graph.facebook.com/'.$version.'/oauth/access_token?'.$params;
$ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30]);$raw=curl_exec($ch);$err=curl_error($ch);curl_close($ch);
$data=json_decode((string)$raw,true);
if(!$data||empty($data['access_token'])){flash('danger','Could not obtain Facebook access token: '.($data['error']['message']??$err));redirect('facebook.php');}
$userToken=$data['access_token'];
$longUrl='https://graph.facebook.com/'.$version.'/oauth/access_token?'.http_build_query(['grant_type'=>'fb_exchange_token','client_id'=>envv('META_APP_ID'),'client_secret'=>envv('META_APP_SECRET'),'fb_exchange_token'=>$userToken]);
$ch=curl_init($longUrl);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30]);$longRaw=curl_exec($ch);curl_close($ch);$long=json_decode((string)$longRaw,true);
if(!empty($long['access_token']))$userToken=$long['access_token'];
$pagesUrl='https://graph.facebook.com/'.$version.'/me/accounts?'.http_build_query(['fields'=>'id,name,access_token,tasks','limit'=>100,'access_token'=>$userToken]);
$ch=curl_init($pagesUrl);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30]);$pagesRaw=curl_exec($ch);$pagesErr=curl_error($ch);curl_close($ch);$pages=json_decode((string)$pagesRaw,true);
if(!isset($pages['data'])||!is_array($pages['data'])){flash('danger','Could not retrieve Pages: '.($pages['error']['message']??$pagesErr));redirect('facebook.php');}
$eligible=array_values(array_filter($pages['data'],fn($p)=>!empty($p['id'])&&!empty($p['access_token'])&&in_array('CREATE_CONTENT',$p['tasks']??[],true)));
if(!$eligible){flash('warning','No Page with CREATE_CONTENT access was found. Check your Page access and granted permissions.');redirect('facebook.php');}
$_SESSION['fb_available_pages']=$eligible;redirect('facebook-select-page.php');

<?php
require __DIR__.'/config/bootstrap.php';require_login();
$app=envv('META_APP_ID');$redirectUri=envv('META_REDIRECT_URI');
if(!$app||!envv('META_APP_SECRET')){flash('danger','Add META_APP_ID and META_APP_SECRET to your .env file first.');redirect('facebook.php');}
$state=bin2hex(random_bytes(24));$_SESSION['fb_oauth_state']=$state;
$params=http_build_query(['client_id'=>$app,'redirect_uri'=>$redirectUri,'state'=>$state,'scope'=>'pages_show_list,pages_read_engagement,pages_manage_posts','response_type'=>'code']);
redirect('https://www.facebook.com/'.envv('META_GRAPH_VERSION','v26.0').'/dialog/oauth?'.$params);

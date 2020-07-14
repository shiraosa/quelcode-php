<?php
session_start();
require('dbconnect.php');

$retweet = $db->prepare('SELECT * FROM posts WHERE member_id=? AND original_post_id=?');
$retweet->execute(array($_SESSION['id'],$_REQUEST['id']));

if($retweet->fetch()){
    //RT削除
    $del_retweet = $db->prepare('DELETE FROM posts WHERE member_id=? AND original_post_id=?'); 
    $del_retweet->execute(array($_SESSION['id'],$_REQUEST['id']));
}else{
    //RT投稿
    $original = $db->prepare('SELECT * FROM posts WHERE id=?');
    $original->execute(array($_REQUEST['id']));
    $origin = $original->fetch();
    $post_rt = $db->prepare('INSERT INTO posts SET message=?,member_id=?, reply_post_id=?, original_post_id=?,created=NOW()');
    $post_rt->execute(array(
        $origin['message'],
        $_SESSION['id'],
        $origin['reply_post_id'],
        $origin['id']
    ));
}

header('Location: index.php'); exit();

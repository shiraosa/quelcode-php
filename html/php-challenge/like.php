<?php
session_start();
require('dbconnect.php');

$likes = $db->prepare('SELECT * FROM likes WHERE post_id = ? AND member_id=?');
$likes->execute(array($_REQUEST['id'],$_SESSION['id']));

if($likes->fetch()){
    //いいねを削除
    $del_like = $db->prepare('DELETE FROM likes WHERE post_id=? AND member_id=?');
    $del_like->execute(array($_REQUEST['id'],$_SESSION['id']));
}else{
    //いいねを追加
    $add_like = $db->prepare('INSERT INTO likes SET post_id=?, member_id=?');
    $add_like->execute(array($_REQUEST['id'],$_SESSION['id']));
}

header('Location: index.php'); exit();

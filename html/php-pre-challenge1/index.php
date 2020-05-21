<?php
for ($i = 1; $i <= 100; $i++) {
    //ここからコードを書く
    if($i%15===0){
        echo "3の倍数であり、5の倍数"."<br>";
    }else if($i%5===0){
        echo "5の倍数"."<br>";
    }else if($i%3===0){
        echo "3の倍数"."<br>";
    }else{
    echo $i."<br>";
    }
}

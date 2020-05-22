<?php
$array=explode(',', $_GET['array']);

// 修正はここから
$count=count($array);
for ($i=0;$i<$count;$i++) {
    if(count(array_keys($array,min($array)))>1){//最小値が二つ以上ある場合
        $for_count=count(array_keys($array,min($array)));//最小値の個数を変数に代入
        for ($j=0;$j<$for_count;$j++){
            $min_sort[]=min($array);//最小値を代入
        }
        $i+=$j-1;//$iに$jカウントの反映とカウントの重複を-1する
    }else{
        $min_sort[]=min($array);//最小値を代入
    }
    $array=array_diff($array,array(min($array)));//配列から最小値を削除
}
$array=$min_sort;
// 修正はここまで

echo "<pre>";
print_r($array);
echo "</pre>";

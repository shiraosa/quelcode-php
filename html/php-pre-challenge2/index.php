<?php
$array = explode(',', $_GET['array']);

// 修正はここから
$count =count($array);
for ($i = 0; $i < $count; $i++) {
    if(count(array_keys($array,min($array)))>1){//最小値が二つ以上ある場合
        $for_count =$i+count(array_keys($array,min($array)));//カウント上限の変数
        for ($i;$i<$for_count;$i++){
            $min_sort[]=min($array);//最小値を代入
        }
        $i--;//$iのカウント重複を防ぐ
    }else{
    $min_sort[]=min($array);//最小値を代入
    }
    $array =array_diff($array,array(min($array)));//配列から最小値を削除
}
$array=$min_sort;
// 修正はここまで

echo "<pre>";
print_r($array);
echo "</pre>";

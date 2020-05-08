<?php
$array = explode(',', $_GET['array']);

// 修正はここから
for ($i = 0; $i < count($array); $i++) {
    $target = count($array)-$i-1;
    for($j = 0; $j < $target; $j++){
         if($array[$j] > $array[$j+1]){
            $swap = $array[$j];
            $array[$j] = $array[$j+1];
            $array[$j+1] = $swap;
        }
    }
}
// 修正はここまで

echo "<pre>";
print_r($array);
echo "</pre>";

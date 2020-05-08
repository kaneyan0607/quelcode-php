<?php
$array = explode(',', $_GET['array']);

// 修正はここから
for ($i = 0; $i < count($array); $i++) {
    $length = count($array);
    for($j = $i + 1; $j < $length; $j++) {
        if ($array[$i] >= $array[$j]) {
            $tmp = $array[$i];
            $array[$i] = $array[$j];
            $array[$j] = $tmp;
        }
    }
}
// 修正はここまで

echo "<pre>";
print_r($array);
echo "</pre>";

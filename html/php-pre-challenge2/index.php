<?php
$array = explode(',', $_GET['array']);

// 修正はここから
$length = count($array);
for ($i = 0; $i < $length; $i++) {
    for($j = $i + 1; $j < $length; $j++) {
        if ($array[$i] > $array[$j]) {
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

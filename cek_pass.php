<?php
$hash = '$2y$10$qZiE3L8Wr0tuVTedQbG19u4K7aoYBdSw06NG1UvWwfxqy3daZduWC';

$test = ['25Oktober2004', 'password', '123456'];
foreach($test as $p){
    echo "$p : " . (password_verify($p, $hash) ? 'COCOK' : 'tidak cocok') . "<br>";
}
?>
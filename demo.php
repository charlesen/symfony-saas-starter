<?php

// Merge the two arrays using array_merge()

$first = [1, 2, 3];
$second = [4, 5, 6];

echo "<pre>";
// print_r(array_merge($first, $second));
print_r(array_shift($first, $second));
echo "</pre>";

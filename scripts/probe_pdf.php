<?php
$b = file_get_contents(__DIR__ . '/../Dice_Resume_CV_Mohammad_Nazmus_Sakib.pdf');
echo substr($b, 0, 300) . PHP_EOL;
preg_match_all('/\(([^()\\\\]{2,})\)/', $b, $m);
echo 'matches: ' . count($m[1]) . PHP_EOL;
echo substr(implode(' ', array_slice($m[1], 0, 40)), 0, 800);

<?php

if (is_file(__DIR__ . '/../router.php')) {
    include_once __DIR__ . '/../router.php';
} elseif (is_file(__DIR__ . '/../router')) {
    include_once __DIR__ . '/../router';
}

<?php

if (is_file(__DIR__ . '/../router.php')) {
    include_once __DIR__ . '/../router.php';
} else if (is_file(__DIR__ . '/../router')) {
    include_once __DIR__ . '/../router';
}
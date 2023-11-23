<?php

foreach (scandir(__DIR__ . '/abstract') as $file) {
    if ($file !== '.' && $file !== '..') {
        require __DIR__ . '/abstract/' . $file;
    }
}

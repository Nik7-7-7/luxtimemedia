<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('berqCache')) {
    require_once optifer_PATH . '/inc/class-berqcache.php';
}

global $berq_log;
$berq_log->info("Triggered flush cache via API.");

$berqCache = new berqCache();
$berqCache->delete_cache_files();
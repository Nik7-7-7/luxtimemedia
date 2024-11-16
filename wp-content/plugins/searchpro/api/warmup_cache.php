<?php
if (!defined('ABSPATH')) exit;

$slug = $request->get_param('slug');
$is_forced = false;

if (!empty($slug) && bwp_can_warmup_cache($slug)) {
    warmup_cache_by_slug($slug, $is_forced);
    return;
}
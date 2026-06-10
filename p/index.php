<?php
// Redirect /p/slug to /profile.php?slug=slug
$slug = basename($_SERVER['REQUEST_URI']);
$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
if ($slug) {
    header('Location: /profile.php?slug=' . urlencode($slug));
    exit;
}
header('Location: /search.php');
exit;

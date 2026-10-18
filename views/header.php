<?php
// Shared page header: <head>, title bar and one-time flash message
// Expects $pageTitle to be set by the page that includes it.
$flash = pullFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Game Developer Applications') ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <a href="index.php" class="brand">🎮 Emilio Aguinaldo Gaming <span>Game Developer Careers</span></a>
</header>
<main>
<?php if ($flash): ?>
    <div class="flash <?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
<?php endif; ?>

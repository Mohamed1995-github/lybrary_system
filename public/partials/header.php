<body>
<div class="header">
    <div class="logo">
        <div class="logo-icon"><i class="fas fa-book-open"></i></div>
        <span class="logo-text"><?= htmlspecialchars($t['library_system']) ?></span>
    </div>
    <div class="user-info">
        <div class="user">
            <div class="avatar"><i class="fas fa-user"></i></div>
            <span><?= htmlspecialchars($t['hello']) ?>, <?= htmlspecialchars($username) ?></span>
        </div>
        <a href="logout.php" class="logout-btn"><?= htmlspecialchars($t['logout']) ?></a>
    </div>
</div>
<main class="container">

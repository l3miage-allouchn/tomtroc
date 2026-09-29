<?php
// nombre de messages non lus, pour la pastille du menu
$unreadCount = 0;
if (isset($_SESSION['user_id'])) {
    $messageManager = new MessageManager();
    $unreadCount = $messageManager->countUnread($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TomTroc</title>
    <link rel="stylesheet" href="/tomtroc/public/css/style.css">
</head>
<body>
    <header class="site-header">
        <a href="/tomtroc/public/" class="site-header__logo">TomTroc</a>

        <nav class="site-header__nav">
            <a href="/tomtroc/public/">Accueil</a>
            <a href="/tomtroc/public/livres">Nos livres à l'échange</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/tomtroc/public/messagerie">
                    Messagerie
                    <?php if ($unreadCount > 0): ?>
                        <span class="unread-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="/tomtroc/public/mon-compte">Mon compte</a>
                <a href="/tomtroc/public/deconnexion">Déconnexion</a>
            <?php else: ?>
                <a href="/tomtroc/public/connexion">Connexion</a>
                <a href="/tomtroc/public/inscription">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>

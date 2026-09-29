<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <h1>Messagerie</h1>

    <?php if (empty($conversations)): ?>
        <p>Vous n'avez aucun message pour le moment.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($conversations as $conversation): ?>
                <li>
                    <a href="/tomtroc/public/messagerie/<?= $conversation['user']->getId() ?>">
                        <strong><?= htmlspecialchars($conversation['user']->getPseudo()) ?></strong>
                    </a>
                    <?php if ($conversation['unreadCount'] > 0): ?>
                        (<?= $conversation['unreadCount'] ?> non lu<?= $conversation['unreadCount'] > 1 ? 's' : '' ?>)
                    <?php endif; ?>
                    <p><?= htmlspecialchars($conversation['lastMessage']->getContent()) ?></p>
                    <p><?= $conversation['lastMessage']->getCreatedAt()->format('d/m/Y H:i') ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

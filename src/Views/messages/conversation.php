<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <p><a href="/tomtroc/public/messagerie">Retour à la messagerie</a></p>

    <h1>
        Conversation avec
        <a href="/tomtroc/public/profil/<?= $otherUser->getId() ?>"><?= htmlspecialchars($otherUser->getPseudo()) ?></a>
    </h1>

    <?php if (empty($messages)): ?>
        <p>Aucun message. Écrivez le premier !</p>
    <?php else: ?>
        <ul>
            <?php foreach ($messages as $message): ?>
                <li>
                    <strong>
                        <?= $message->getSenderId() === $currentUserId ? 'Moi' : htmlspecialchars($otherUser->getPseudo()) ?>
                    </strong>
                    — <?= $message->getCreatedAt()->format('d/m/Y H:i') ?>
                    <p><?= nl2br(htmlspecialchars($message->getContent())) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <label for="content">Votre message</label>
        <textarea id="content" name="content" required></textarea>
        <button type="submit">Envoyer</button>
    </form>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

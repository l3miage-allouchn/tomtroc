<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <h1><?= htmlspecialchars($user->getPseudo()) ?></h1>

    <?php if ($user->getAvatar() !== null): ?>
        <img src="<?= htmlspecialchars($user->getAvatar()) ?>" alt="Avatar de <?= htmlspecialchars($user->getPseudo()) ?>">
    <?php endif; ?>

    <p>Membre depuis le <?= $user->getCreatedAt()->format('d/m/Y') ?></p>

    <?php if ($user->getBio() !== null): ?>
        <p><?= nl2br(htmlspecialchars($user->getBio())) ?></p>
    <?php endif; ?>

    <h2>Sa bibliothèque</h2>

    <?php if (empty($books)): ?>
        <p>Ce membre n'a encore aucun livre.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($books as $book): ?>
                <li>
                    <a href="/tomtroc/public/livre/<?= $book->getId() ?>">
                        <?= htmlspecialchars($book->getTitle()) ?>
                    </a>
                    — <?= htmlspecialchars($book->getAuthor()) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a href="/tomtroc/public/messagerie/<?= $user->getId() ?>">Écrire un message</a></p>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

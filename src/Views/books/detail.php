<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <h1><?= htmlspecialchars($book->getTitle()) ?></h1>

    <p>par <?= htmlspecialchars($book->getAuthor()) ?></p>

    <img src="/tomtroc/public/<?= htmlspecialchars($book->getImage() ?? 'images/default-book.svg') ?>" alt="Couverture de <?= htmlspecialchars($book->getTitle()) ?>">

    <p><?= $book->getStatus() === 'available' ? 'Disponible à l\'échange' : 'Non disponible' ?></p>

    <h2>Description</h2>
    <p><?= nl2br(htmlspecialchars($book->getDescription() ?? '')) ?></p>

    <?php if ($owner !== null): ?>
        <h2>Propriétaire</h2>
        <p>
            <a href="/tomtroc/public/profil/<?= $owner->getId() ?>">
                <?= htmlspecialchars($owner->getPseudo()) ?>
            </a>
        </p>
        <p><a href="/tomtroc/public/messagerie/<?= $owner->getId() ?>">Envoyer un message</a></p>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

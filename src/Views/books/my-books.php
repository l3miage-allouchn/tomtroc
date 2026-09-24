<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <h1>Mon compte</h1>

    <p><a href="/tomtroc/public/livre/ajouter">Ajouter un livre</a></p>

    <?php if (empty($books)): ?>
        <p>Vous n'avez ajouté aucun livre pour le moment.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($books as $book): ?>
                <li>
                    <strong><?= htmlspecialchars($book->getTitle()) ?></strong>
                    — <?= htmlspecialchars($book->getAuthor()) ?>
                    (<?= htmlspecialchars($book->getStatus()) ?>)
                    <a href="/tomtroc/public/livre/<?= $book->getId() ?>/modifier">Modifier</a>
                    <form method="post" action="/tomtroc/public/livre/<?= $book->getId() ?>/supprimer">
                        <button type="submit">Supprimer</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

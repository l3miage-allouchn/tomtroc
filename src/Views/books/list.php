<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <h1>Nos livres à l'échange</h1>

    <form method="get">
        <label for="q">Rechercher un titre</label>
        <input type="text" id="q" name="q" value="<?= htmlspecialchars($search ?? '') ?>">
        <button type="submit">Rechercher</button>
    </form>

    <?php if (empty($books)): ?>
        <p>Aucun livre trouvé.</p>
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
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

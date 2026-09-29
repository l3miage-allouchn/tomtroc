<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <h1>Mon compte</h1>

    <p><a href="/tomtroc/public/profil/<?= $user->getId() ?>">Voir mon profil public</a></p>

    <h2>Mes informations</h2>

    <?php if ($error !== null): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="pseudo">Pseudo</label>
        <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($user->getPseudo()) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>" required>

        <label for="password">Nouveau mot de passe (laisser vide pour ne pas le changer)</label>
        <input type="password" id="password" name="password">

        <label for="bio">Bio</label>
        <textarea id="bio" name="bio"><?= htmlspecialchars($user->getBio() ?? '') ?></textarea>

        <button type="submit">Enregistrer</button>
    </form>

    <h2>Ma bibliothèque</h2>

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

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <h1><?= $book->getId() === null ? 'Ajouter un livre' : 'Modifier le livre' ?></h1>

    <form method="post">
        <label for="title">Titre</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($book->getTitle()) ?>" required>

        <label for="author">Auteur</label>
        <input type="text" id="author" name="author" value="<?= htmlspecialchars($book->getAuthor()) ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description"><?= htmlspecialchars($book->getDescription() ?? '') ?></textarea>

        <label for="image">Image (URL)</label>
        <input type="text" id="image" name="image" value="<?= htmlspecialchars($book->getImage() ?? '') ?>">

        <label for="status">Disponibilité</label>
        <select id="status" name="status">
            <option value="available" <?= $book->getStatus() === 'available' ? 'selected' : '' ?>>Disponible à l'échange</option>
            <option value="unavailable" <?= $book->getStatus() === 'unavailable' ? 'selected' : '' ?>>Non disponible</option>
        </select>

        <button type="submit">Enregistrer</button>
    </form>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <h1><?= $book->getId() === null ? 'Ajouter un livre' : 'Modifier le livre' ?></h1>

    <?php if ($error !== null): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="image">Photo</label>
        <img src="/tomtroc/public/<?= htmlspecialchars($book->getImage() ?? 'images/default-book.svg') ?>" alt="Couverture actuelle du livre">
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">

        <label for="title">Titre</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($book->getTitle()) ?>" required>

        <label for="author">Auteur</label>
        <input type="text" id="author" name="author" value="<?= htmlspecialchars($book->getAuthor()) ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description"><?= htmlspecialchars($book->getDescription() ?? '') ?></textarea>

        <label for="status">Disponibilité</label>
        <select id="status" name="status">
            <option value="available" <?= $book->getStatus() === 'available' ? 'selected' : '' ?>>Disponible à l'échange</option>
            <option value="unavailable" <?= $book->getStatus() === 'unavailable' ? 'selected' : '' ?>>Non disponible</option>
        </select>

        <button type="submit">Enregistrer</button>
    </form>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

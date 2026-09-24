<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <h1>Inscription</h1>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="/tomtroc/public/inscription">
        <label for="pseudo">Pseudo</label>
        <input type="text" id="pseudo" name="pseudo" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">S'inscrire</button>
    </form>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

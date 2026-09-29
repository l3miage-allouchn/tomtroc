<?php

class UserController
{
    public function show(int $id): void
    {
        $userManager = new UserManager();
        $user = $userManager->findById($id);

        if ($user === null) {
            header('Location: /tomtroc/public/livres');
            return;
        }

        $bookManager = new BookManager();
        $books = $bookManager->findByUserId($user->getId());

        require __DIR__ . '/../Views/users/show.php';
    }

    public function account(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /tomtroc/public/connexion');
            return;
        }

        $userManager = new UserManager();
        $user = $userManager->findById($_SESSION['user_id']);

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // l'email doit rester unique : s'il appartient à quelqu'un d'autre, on refuse
            $existingUser = $userManager->findByEmail($_POST['email']);

            if ($existingUser !== null && $existingUser->getId() !== $user->getId()) {
                $error = 'Cet email est déjà utilisé.';
            } else {
                try {
                    // l'avatar en premier : s'il est refusé, rien n'est modifié
                    $imageUploader = new ImageUploader();
                    $newAvatar = $imageUploader->upload($_FILES['avatar'] ?? null, 'avatars');

                    if ($newAvatar !== null) {
                        $imageUploader->delete($user->getAvatar());
                        $user->setAvatar($newAvatar);
                    }

                    $user->setPseudo($_POST['pseudo']);
                    $user->setEmail($_POST['email']);
                    $user->setBio($_POST['bio'] !== '' ? $_POST['bio'] : null);

                    // mot de passe vide = on garde l'ancien hash
                    if ($_POST['password'] !== '') {
                        $user->setPassword(password_hash($_POST['password'], PASSWORD_DEFAULT));
                    }

                    $userManager->update($user);

                    // le pseudo est aussi gardé en session : on le met à jour
                    $_SESSION['user_pseudo'] = $user->getPseudo();

                    header('Location: /tomtroc/public/mon-compte');
                    return;
                } catch (RuntimeException $exception) {
                    $error = $exception->getMessage();
                }
            }
        }

        $bookManager = new BookManager();
        $books = $bookManager->findByUserId($user->getId());

        require __DIR__ . '/../Views/users/account.php';
    }
}

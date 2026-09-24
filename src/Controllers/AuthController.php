<?php

class AuthController
{
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userManager = new UserManager();

            //Vérifier que l'email n'est pas déjà pris

            if ($userManager->findByEmail($_POST['email']) !== null) {
                $error = 'Cet email est déjà utilisé.';
                require __DIR__ . '/../Views/auth/register.php';
                return;
            }

            //Création du nouvel utilisateur

            $user = new User();
            $user->setPseudo($_POST['pseudo']);
            $user->setEmail($_POST['email']);
            $user->setPassword(password_hash($_POST['password'], PASSWORD_DEFAULT));

            $id = $userManager->create($user);
            $user->setId($id);

            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_pseudo'] = $user->getPseudo();

            header('Location: /tomtroc/public/');
            return;
        }

        require __DIR__ . '/../Views/auth/register.php';
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userManager = new UserManager();
            $user = $userManager->findByEmail($_POST['email']);

            if ($user === null || !password_verify($_POST['password'], $user->getPassword())) {
                $error = 'Email ou mot de passe incorrect.';
                require __DIR__ . '/../Views/auth/login.php';
                return;
            }

            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_pseudo'] = $user->getPseudo();

            header('Location: /tomtroc/public/');
            return;
        }

        require __DIR__ . '/../Views/auth/login.php';
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /tomtroc/public/');
    }
}

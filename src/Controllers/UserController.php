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
}

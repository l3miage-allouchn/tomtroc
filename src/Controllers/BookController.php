<?php

class BookController
{
    // public function myBooks(): void
    // {
    //     if (!isset($_SESSION['user_id'])) {
    //         header('Location: /tomtroc/public/connexion');
    //         return;
    //     }

    //     $bookManager = new BookManager();
    //     $books = $bookManager->findByUserId($_SESSION['user_id']);

    //     require __DIR__ . '/../Views/books/my-books.php';
    // }

    public function add(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /tomtroc/public/connexion');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $book = new Book();
            $book->setUserId($_SESSION['user_id']);
            $book->setTitle($_POST['title']);
            $book->setAuthor($_POST['author']);
            $book->setDescription($_POST['description']);
            $book->setImage($_POST['image'] !== '' ? $_POST['image'] : null);
            $book->setStatus($_POST['status']);

            $bookManager = new BookManager();
            $bookManager->create($book);

            header('Location: /tomtroc/public/mon-compte');
            return;
        }

        $book = new Book();
        require __DIR__ . '/../Views/books/form.php';
    }

    public function edit(int $id): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /tomtroc/public/connexion');
            return;
        }

        $bookManager = new BookManager();
        $book = $bookManager->findById($id);

        if ($book === null || $book->getUserId() !== $_SESSION['user_id']) {
            header('Location: /tomtroc/public/mon-compte');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $book->setTitle($_POST['title']);
            $book->setAuthor($_POST['author']);
            $book->setDescription($_POST['description']);
            $book->setImage($_POST['image'] !== '' ? $_POST['image'] : null);
            $book->setStatus($_POST['status']);

            $bookManager->update($book);

            header('Location: /tomtroc/public/mon-compte');
            return;
        }

        require __DIR__ . '/../Views/books/form.php';
    }

    public function delete(int $id): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /tomtroc/public/connexion');
            return;
        }

        $bookManager = new BookManager();
        $book = $bookManager->findById($id);

        if ($book !== null && $book->getUserId() === $_SESSION['user_id']) {
            $bookManager->delete($id);
        }

        header('Location: /tomtroc/public/mon-compte');
    }


    public function list(): void
    {
    $search = $_GET['q'] ?? null;

    $bookManager = new BookManager();
    $books = $bookManager->findAvailable($search);

    require __DIR__ . '/../Views/books/list.php';
    }

    public function detail(int $id): void
    {
        $bookManager = new BookManager();
        $book = $bookManager->findById($id);

        if ($book === null) {
            header('Location: /tomtroc/public/livres');
            return;
        }

        // le livre ne connaît que l'id de son propriétaire : on va chercher le User complet
        $userManager = new UserManager();
        $owner = $userManager->findById($book->getUserId());

        require __DIR__ . '/../Views/books/detail.php';
    }

}

<?php

class MessageController
{
    public function inbox(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /tomtroc/public/connexion');
            return;
        }

        $currentUserId = $_SESSION['user_id'];

        $messageManager = new MessageManager();
        $userManager = new UserManager();
        $messages = $messageManager->findByUserId($currentUserId);

        // on regroupe les messages par interlocuteur
        $conversations = [];
        foreach ($messages as $message) {
            // l'interlocuteur, c'est "l'autre" : celui qui n'est pas moi
            $otherUserId = $message->getSenderId() === $currentUserId
                ? $message->getReceiverId()
                : $message->getSenderId();

            // les messages sont triés du plus récent au plus ancien :
            // le premier trouvé pour un interlocuteur est donc le dernier message de la conversation
            if (!isset($conversations[$otherUserId])) {
                $conversations[$otherUserId] = [
                    'user' => $userManager->findById($otherUserId),
                    'lastMessage' => $message,
                    'unreadCount' => 0,
                ];
            }

            // un message non lu que j'ai reçu
            if ($message->getReceiverId() === $currentUserId && !$message->isRead()) {
                $conversations[$otherUserId]['unreadCount']++;
            }
        }

        require __DIR__ . '/../Views/messages/inbox.php';
    }

    public function conversation(int $otherUserId): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /tomtroc/public/connexion');
            return;
        }

        $currentUserId = $_SESSION['user_id'];

        $userManager = new UserManager();
        $otherUser = $userManager->findById($otherUserId);

        // interlocuteur inexistant, ou on essaie de s'écrire à soi-même
        if ($otherUser === null || $otherUserId === $currentUserId) {
            header('Location: /tomtroc/public/messagerie');
            return;
        }

        $messageManager = new MessageManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['content']);

            if ($content !== '') {
                $message = new Message();
                $message->setSenderId($currentUserId);
                $message->setReceiverId($otherUserId);
                $message->setContent($content);

                $messageManager->create($message);
            }

            header('Location: /tomtroc/public/messagerie/' . $otherUserId);
            return;
        }

        // j'ouvre le fil : les messages qu'il m'a envoyés sont maintenant lus
        $messageManager->markAsRead($otherUserId, $currentUserId);

        $messages = $messageManager->findConversation($currentUserId, $otherUserId);

        require __DIR__ . '/../Views/messages/conversation.php';
    }
}

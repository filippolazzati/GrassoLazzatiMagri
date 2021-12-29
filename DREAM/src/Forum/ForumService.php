<?php

namespace App\Forum;

use App\Entity\Farmer;
use App\Entity\Forum\Message;
use App\Entity\Forum\Thread;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Service\Attribute\Required;

class ForumService
{
    #[Required] public Security $security;

    public function createThread(Farmer $author, string $title, string $message): Thread
    {
        $thread = new Thread($author, $title);
        $msg = new Message($author, $message);
        $thread->addMessage($msg);
        return $thread;
    }
}
<?php

namespace App\Controller;

use App\Entity\Farmer;
use App\Entity\Forum\Message;
use App\Entity\Forum\Thread;
use App\Form\Forum\NewMessageType;
use App\Form\Forum\NewThreadType;
use App\Forum\ForumService;
use AssertionError;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/forum', name: 'forum_')]
class ForumController extends AbstractController
{
    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $threadQuery = $this->em->getRepository(Thread::class)
            ->createQueryBuilder('thread')
            ->orderBy('thread.createdAt', 'DESC');
        $pagination = $this->paginator->paginate($threadQuery, $request->query->getInt('page', 1), 25);

        return $this->render('forum/index.html.twig', ['pagination' => $pagination]);
    }

    #[Route('/thread/create', name: 'thread_create', methods: ['GET', 'POST'])]
    public function threadCreate(Request $request, ForumService $forumService): Response
    {
        $form = $this->createForm(NewThreadType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!($user instanceof Farmer)) {
                throw new AssertionError();
            }
            /** @var array{title: string, message: string} $formData */
            $formData = $form->getData();
            $thread = $forumService->createThread($user, $formData['title'], $formData['message']);
            $this->em->persist($thread);
            $this->em->flush();

            return $this->redirectToRoute('forum_index');
        }

        return $this->render('forum/thread/new_thread.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/thread/{thread}', name: 'thread_view', methods: ['GET', 'POST'])]
    public function threadView(Thread $thread, Request $request): Response
    {
        $messagesPerPage = 10;
        $newMessageForm = $this->createForm(NewMessageType::class);
        $newMessageForm->handleRequest($request);
        if ($newMessageForm->isSubmitted() && $newMessageForm->isValid()) {
            $user = $this->getUser();
            if (!($user instanceof Farmer)) {
                throw new LogicException('User is not a farmer');
            }

            $message = new Message($user, $newMessageForm->getData()['content']);
            $thread->addMessage($message);
            $this->em->persist($message);
            $this->em->flush();

            $this->addFlash('success', 'Message sent!');
            $count = $this->em->getRepository(Message::class)->getMessageCount($thread);
            return $this->redirectToRoute('forum_thread_view', [
                'thread' => $thread->getId(),
                'page' => ceil($count / $messagesPerPage)
            ]);
        }

        $messagesQuery = $this->em->getRepository(Message::class)
            ->createQueryBuilder('message')
            ->andWhere('message.thread = :thread')
            ->setParameter('thread', $thread)
            ->orderBy('message.createdAt', 'ASC');
        $pagination = $this->paginator->paginate($messagesQuery, $request->query->getInt('page', 1), $messagesPerPage);

        return $this->render('forum/thread/view_thread.html.twig', [
            'thread' => $thread,
            'pagination' => $pagination,
            'totalPages' => ceil($pagination->getTotalItemCount() / $messagesPerPage),
            'newMessageForm' => $newMessageForm->createView(),
        ]);
    }
}
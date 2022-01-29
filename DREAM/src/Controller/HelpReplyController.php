<?php

namespace App\Controller;

use App\Entity\Agronomist;
use App\Entity\Farmer;
use App\Entity\HelpReply;
use App\Entity\HelpRequest;
use App\Form\HelpRequests\InsertReplyType;
use AssertionError;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/my_replies', name: 'my_replies_')]
class HelpReplyController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;

    #[Route('/view/{help_request?}', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, ?HelpRequest $help_request): \Symfony\Component\HttpFoundation\Response
    {
        // if the user is not a farmer or agronomist, error
        $user = $this->getUser();
        if (!($user instanceof Farmer || $user instanceof Agronomist)) {
            throw new AssertionError();
        }

        // retrieve help requests sent to the user, paginating them in group of 20
        $helpRequestsQuery = $this->em->getRepository(HelpRequest::class)->getHelpRequestsToUserQuery($user);
        $pagination = $this->paginator->paginate($helpRequestsQuery, $request->query->getInt('page', 1), 20);

        // if no help request selected (parameter $help_request = null), show as a default the most recent one
        // if the user has no help requests, help_request is left to null
        if (is_null($help_request) && !$user->getReceivedRequests()->isEmpty()) {
            $help_request = $this->em->getRepository(HelpRequest::class)->getMostRecentHelpRequestToUser($user);
        }

        $renderParameters = ['pagination' => $pagination, 'help_request' => $help_request];

        // form for inserting the reply to the selected help request
        // (if it has not been replied yet)
        if (!is_null($help_request) && $help_request->needsReply()) {
            $form = $this->createForm(InsertReplyType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                $this->em->getRepository(HelpReply::class)->createHelpReply($formData['reply'], $help_request);
                return $this->redirectToRoute('my_replies_confirmation_insert_help_reply', ['help_request' => $help_request->getId()]);
            }
            $renderParameters += array('form' => $form->createView());
        }

        return $this->render('myreplies/index.html.twig', $renderParameters);
    }

    #[Route('/confirmation_insert_help_reply{help_request}', name: 'confirmation_insert_help_reply', methods: ['GET'])]
    public function getConfirmPageForInsertReply(HelpRequest $help_request) : \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('myreplies/confirm_insert_reply.html.twig', ['help_request' => $help_request]);
    }
}
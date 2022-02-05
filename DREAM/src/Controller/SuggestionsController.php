<?php


namespace App\Controller;

use App\Entity\Farmer;
use App\Form\SuggestionsType;
use App\Suggestions\SuggestionService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Class SuggestionsController
 * @package App\Controller
 *
 * This controller receives the request of the user to receive a suggestion, parse it, creates
 * a suggestion and returns it to the user.
 */
#[Route('/suggestions', name: 'suggestions_')]
class SuggestionsController extends AbstractController
{
    #[Required] public KernelInterface $kernel;
    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;

    #[Route('/', name: 'view', methods: ['GET'])]
    public function view(Request $request, SuggestionService $suggestionService): Response
    {
        // create the form on top of the page
        $form = $this->createForm(SuggestionsType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false,
        ]);

        $pagination = null;

        // handle the request and check if the form has been submitted and is valid
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // retrieve data from the form
            $type = $form->getData()['type'];
            $crop = $form->getData()['crop'];
            $area = $form->getData()['area'];

            /** @var $user Farmer */
            $user = $this->getUser();
            $city = $user->getFarm()?->getCity();

            $suggestions = $suggestionService->getSuggestions($type, $city, $type === 'fertilizer' ? $crop : $area);

            // fill the paginator object with the results of the neural network
            $pagination = $this->paginator->paginate($suggestions, $request->query->getInt('page', 1), 25);
        }

        // render the view passing the pagination object and the form object
        return $this->render('suggestions/view.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }
}
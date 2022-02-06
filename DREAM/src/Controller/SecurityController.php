<?php

namespace App\Controller;

use App\Entity\Farm;
use App\Entity\Farmer;
use App\Form\ProfileType;
use App\Form\RegisterType;
use App\Security\EmailVerificationService;
use AssertionError;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Service\Attribute\Required;

class SecurityController extends AbstractController
{
    #[Required] public EntityManagerInterface $em;

    private const REGISTER_SUCCESS_SESSION_KEY = 'register_success';

    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('index');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new AssertionError('Handled by Symfony security');
    }

    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(
        Request                     $request,
        SessionInterface            $session,
        UserPasswordHasherInterface $passwordHasher,
        EmailVerificationService    $emailVerificationService
    ): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('index');
        }

        $form = $this->createForm(RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Farmer $farmer */
            $farmer = $form->getData();
            $farmer->setPassword($passwordHasher->hashPassword($farmer, $form->get('password')->getData()));

            $farm = (new Farm())
                ->setArea($form->get('farmArea')->getData())
                ->setCity($form->get('farmCity')->getData())
                ->setStreet($form->get('farmStreet')->getData());

            $farmer->setFarm($farm);

            $this->em->persist($farmer);
            $this->em->flush();

            try {
                $emailVerificationService->sendVerificationEmail($farmer);
            } catch (TransportExceptionInterface) {
                $this->addFlash('danger', 'An error occurred while sending the verification email.');
                return $this->redirectToRoute('register');
            }

            $session->set(self::REGISTER_SUCCESS_SESSION_KEY, true);
            return $this->redirectToRoute('register_success');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register/success', name: 'register_success', methods: ['GET'])]
    public function registerSuccess(SessionInterface $session): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('index');
        }

        // Disallow direct access to this page unless the user has just registered
        if (!$session->remove(self::REGISTER_SUCCESS_SESSION_KEY)) {
            return $this->redirectToRoute('register');
        }

        return $this->render('security/register_success.html.twig', [
            'email' => $this->getUser()?->getEmail(),
        ]);
    }

    #[Route('/register/verify-email', name: 'register_verify_email', methods: ['GET'])]
    public function registerVerifyEmail(Request $request, EmailVerificationService $emailVerificationService): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('index');
        }

        if (null === $token = $request->query->get('token')) {
            return $this->redirectToRoute('register');
        }

        try {
            $emailVerificationService->verifyEmail($token);
        } catch (InvalidArgumentException) {
            return $this->redirectToRoute('register');
        }

        $this->addFlash('success', 'Your email address has been verified! Please login to continue.');
        return $this->redirectToRoute('login');
    }

    #[Route('/profile', name: 'profile', methods: ['GET', 'POST'])]
    public function profile(Request $request): Response
    {
        $form = $this->createForm(ProfileType::class, $this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $currentUser = $this->getUser();

            if ($currentUser instanceof Farmer) {
                /** @var Farmer $farmer */
                $farmer = $this->getUser();

                if (null === $farmer->getFarm()) {
                    $farmer->setFarm(new Farm());
                }

                $farmer->getFarm()
                    ->setArea($form->get('farmArea')->getData())
                    ->setCity($form->get('farmCity')->getData())
                    ->setStreet($form->get('farmStreet')->getData());
            }
            $this->em->flush();
            $this->addFlash('success', 'Profile updated');

            return $this->redirectToRoute('profile');
        }
        return $this->render('security/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
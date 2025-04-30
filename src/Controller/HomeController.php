<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\User;

use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Entity\Evenement;
use App\Entity\Reservation;
use App\Entity\Cotisation;
use App\Form\PaymentFormType;
use App\Repository\EvenementRepository;
use App\Repository\ReservationRepository;
use App\Service\StripePaymentService;

use App\Form\UserProfileType;
use App\Form\CotisationType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;



class HomeController extends AbstractController
{
   
    private $security;
    private $passwordEncoder;

    // Injecter le service Security et UserPasswordEncoderInterface dans le constructeur
    public function __construct(Security $security, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->security = $security;
        $this->passwordEncoder = $passwordEncoder;
    }
    /**
     * @Route("/", name="app_home")
     */

    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

     /**
     * @Route("/login", name="app_login")
     */

     public function login(AuthenticationUtils $authenticationUtils): Response
     {
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupère le dernier nom d'utilisateur utilisé
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('connexion/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
     }

     /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        // Cette méthode peut être vide, Symfony s'occupe de la déconnexion
    }

    /**
     * @Route("/user", name="app_connected")
     */
    public function connected(EvenementRepository $eventRepository): Response
    {
        $user = $this->getUser();
        $events = $eventRepository->findAll();
        $eventCount = count($events);
        return $this->render('anciens/acceuil.html.twig', [
            'user' => $user,
            'events' => $events,
            'eventCount' => $eventCount,
        ]);
    }

    
 /**
     * @Route("/profile/update", name="user_update_profile")
     */
    public function updateProfile(Request $request, Security $security): Response
    {
       // Récupérer l'utilisateur connecté
       $user = $this->getUser();

       if (!$user) {
           // Si l'utilisateur n'est pas connecté, on redirige
           return $this->redirectToRoute('app_login');
       }

       // Vérifier si le formulaire a été soumis
       if ($request->isMethod('POST')) {
           $user->setNom($request->get('nom'));
           $user->setPrenom($request->get('prenom'));
           $user->setTelephone($request->get('telephone'));
           $user->setEmail($request->get('email'));
           $user->setPromotion($request->get('promotion'));
           $user->setProfession($request->get('profession'));
           $user->setAdresse($request->get('adresse'));

           // Si un mot de passe est fourni, on met à jour le mot de passe
           $password = $request->get('password');
           if ($password) {
               // Encoder le mot de passe
               $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
           }

           // Sauvegarder l'utilisateur mis à jour en base de données
           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($user);
           $entityManager->flush();

           // Retourner une réponse ou rediriger
           $this->addFlash('success', 'Profil mis à jour avec succès');
           return $this->redirectToRoute('user_update_profile');
       }

       // Si la requête n'est pas en POST, on retourne la vue de mise à jour
       return $this->render('anciens/acceuil.html.twig', [
           'user' => $user
       ]);
 
    
    }

     /**
     * Afficher le profil de l'utilisateur
     * @Route("/user/profile", name="user_profile")
     */
    public function profile(Request $request, Security $security): Response
    {
        // Récupérer l'utilisateur connecté
       $user = $this->getUser();

       if (!$user) {
           // Si l'utilisateur n'est pas connecté, on redirige
           return $this->redirectToRoute('app_login');
       }

       // Vérifier si le formulaire a été soumis
       if ($request->isMethod('POST')) {
           $user->setNom($request->get('nom'));
           $user->setPrenom($request->get('prenom'));
           $user->setTelephone($request->get('telephone'));
           $user->setEmail($request->get('email'));
           $user->setPromotion($request->get('promotion'));
           $user->setProfession($request->get('profession'));
           $user->setAdresse($request->get('adresse'));

           // Si un mot de passe est fourni, on met à jour le mot de passe
           $password = $request->get('password');
           if ($password) {
               // Encoder le mot de passe
               $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
           }

           // Sauvegarder l'utilisateur mis à jour en base de données
           $entityManager = $this->getDoctrine()->getManager();
           $entityManager->persist($user);
           $entityManager->flush();

           // Retourner une réponse ou rediriger
           $this->addFlash('success', 'Profil mis à jour avec succès');
           return $this->redirectToRoute('user_update_profile');
       }

       // Si la requête n'est pas en POST, on retourne la vue de mise à jour
       return $this->render('anciens/acceuil.html.twig', [
           'user' => $user
       ]);
    }

    /**
     * Afficher les événements disponibles pour l'utilisateur
     * @Route("/user/events", name="user_events")
     */
    public function events(EvenementRepository $eventRepository,EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $events = $eventRepository->findAll(); // ou une logique plus complexe selon tes besoins
        
        // Récupérer les réservations de l'utilisateur pour chaque événement
        $reservations = $em->getRepository(Reservation::class)
                           ->findBy(['utilisateur' => $user]);
        
    
        // Mapper les réservations par événement
        $reservationsByEventCount = [];
        foreach ($reservations as $reservation) {
            $eventId = $reservation->getEvent()->getId();
            if (!isset($reservationsByEventCount[$eventId])) {
                $reservationsByEventCount[$eventId] = 0;
            }
            $reservationsByEventCount[$eventId]++;
        }
        //dd($reservationsByEventCount);
        $eventCount = count($events);
    
        return $this->render('anciens/events.html.twig', [
            'user' => $user,
            'events' => $events,
            'eventCount' => $eventCount,
            'reservationsByEventCount' => $reservationsByEventCount,  // Passer les réservations par événement
        ]);
    }

    /**
     * Réserver un événement
     * @Route("/user/event/{id}/reserve", name="user_event_reservation")
     */
    public function reserveEvent(int $id, EvenementRepository $eventRepository, EntityManagerInterface $em): Response
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            $this->addFlash('error', 'Événement non trouvé.');
            return $this->redirectToRoute('user_events');
        }

        $reservation = new Reservation();
        $reservation->setUtilisateur($this->getUser());
        $reservation->setEvent($event);
        $reservation->setDateReservation(new \DateTime()); 
        $reservation->setStatut('En cours'); 
        $em->persist($reservation);
        $em->flush();

        $this->addFlash('success', 'Réservation effectuée avec succès.');

        return $this->redirectToRoute('user_events');
    }

    /**
     * Afficher les réservations de l'utilisateur
     * @Route("/user/reservations", name="user_reservations")
     */
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findBy(['user' => $this->getUser()]);
        return $this->render('user/reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Gérer le paiement de la réservation
     * @Route("/user/payment/{reservationId}", name="user_event_payment")
     */
    public function payment(int $reservationId, ReservationRepository $reservationRepository, StripePaymentService $stripeService, Request $request): Response
    {
        $reservation = $reservationRepository->find($reservationId);
        if (!$reservation || $reservation->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Réservation non trouvée ou accès non autorisé.');
            return $this->redirectToRoute('user_reservations');
        }

        // Logique de paiement avec Stripe (par exemple)
        $amount = $reservation->getEvent()->getTarif(); // récupérer le tarif de l'événement

        if ($request->isMethod('POST')) {
            // Processus de paiement via Stripe
            try {
                $paymentSuccess = $stripeService->processPayment($amount, $request->get('stripeToken'));
                if ($paymentSuccess) {
                    $reservation->setPaid(true);
                    $this->getDoctrine()->getManager()->flush();

                    $this->addFlash('success', 'Paiement effectué avec succès.');
                } else {
                    $this->addFlash('error', 'Le paiement a échoué.');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors du traitement du paiement.');
            }
        }

        return $this->render('user/payment.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * @Route("/user/cotisations", name="user_cotisations")
     */
    public function cotisations(EntityManagerInterface $entityManager, Request $request)
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer toutes les cotisations de l'utilisateur
        $cotisations = $entityManager->getRepository(Cotisation::class)->findBy(['utilisateur' => $user]);

        // Créer un formulaire pour la nouvelle cotisation
        $cotisation = new Cotisation();
        $form = $this->createForm(CotisationType::class, $cotisation);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Assigner l'utilisateur connecté à la cotisation
            $cotisation->setUtilisateur($user);
            $cotisation->setStatut('Enregistré');
            $entityManager->persist($cotisation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre cotisation a été enregistrée.');

            return $this->redirectToRoute('user_cotisations');
        }

        return $this->render('anciens/cotisations.html.twig', [
            'user' => $user,
            'cotisations' => $cotisations,
            'form' => $form->createView(),
        ]);
    }
    
    
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe
            $user->setPassword($hasher->hashPassword($user, $user->getPassword()));
            
            // Passer un tableau pour les rôles
            $user->setRoles(['ROLE_USER']);  // Utilisation d'un tableau pour les rôles

            // Sauvegarde de l'utilisateur
            $em->persist($user);
            $em->flush();

            // Message flash et redirection
            $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('connexion/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

     
}

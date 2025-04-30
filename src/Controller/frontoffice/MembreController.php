<?php

namespace App\Controller\frontoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Repository\UserRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use App\Entity\User;
use App\Repository\CotisationRepository;
use App\Entity\Evenement;
use App\Repository\ReservationRepository;

class MembreController extends AbstractController
{
   /**
     * @Route("/admin/membres", name="admin_membres")
     */
    public function membres(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/membre.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/admin/membres", name="member_list")
     */
    public function membresList(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/membre.html.twig', [
            'users' => $users,
        ]);
    }

   /**
     * @Route("/admin/user/{id}/cotisations", name="admin_user_cotisations")
     */
    public function cotisations(User $user, CotisationRepository $cotisationRepository, EntityManagerInterface $em, Request $request): Response
    {
        $cotisations = $cotisationRepository->findBy(['utilisateur' => $user]);

        if ($request->isMethod('POST')) {
            foreach ($cotisations as $cotisation) {
                $checkboxName = 'validate_' . $cotisation->getId();
                if ($request->request->get($checkboxName)) {
                    $cotisation->setStatut('validé');
                } else {
                    $cotisation->setStatut('enregistré');
                }
            }
            $em->flush();
            $this->addFlash('success', 'Statuts mis à jour.');
            return $this->redirectToRoute('admin_user_cotisations', ['id' => $user->getId()]);
        }

        return $this->render('admin/cotisation/user_cotisations.html.twig', [
            'user' => $user,
            'cotisations' => $cotisations,
        ]);
    }

    /**
     * @Route("/admin/event/{id}/reservations", name="admin_event_reservations")
     */
    public function showReservations(
        Request $request,
        Evenement $event,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em
    ): Response {
        if ($request->isMethod('POST')) {
            $validatedUserIds = $request->request->all('validate');
    
            if ($validatedUserIds) {
                foreach ($validatedUserIds as $userId) {
                    $reservations = $reservationRepository->findBy([
                        'event' => $event,
                        'utilisateur' => $userId
                    ]);
    
                    foreach ($reservations as $reservation) {
                        $reservation->setStatut('validé');
                    }
                }
                $em->flush();
    
                $this->addFlash('success', 'Réservations validées avec succès.');
            }
        }
    
        $reservationsGrouped = $reservationRepository->getGroupedReservationsByEvent($event);
        $reservations = $reservationRepository->findBy(['event' => $event]);

        $totalPlacesReservées = count($reservations);
        $nbPlacesTotales = $event->getNbPlaces();
         //dd($nbPlacesTotales);
    
        return $this->render('admin/event/reservations.html.twig', [
            'event' => $event,
            'reservations' => $reservationsGrouped,
            'totalPlacesReservées' => $totalPlacesReservées,
            'nbPlacesTotales' => $nbPlacesTotales,
            
        ]);
    }
    
    /**
     * @Route("/admin/membre/{id}/carte", name="member_carte_pdf")
     */
    public function generateMemberCard(UserRepository $userRepository,$id): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $user = $userRepository->find($id);
        $photoFilename = $user->getPhoto();
        $photoPath = $this->getParameter('kernel.project_dir').'/public/uploads/photos/'.$photoFilename;
    
        if (!file_exists($photoPath)) {
            $photoPath = $this->getParameter('kernel.project_dir').'/public/assets/default-avatar.jpg'; // fallback
        }
        $photoData = base64_encode(file_get_contents($photoPath));
        $photo = 'data:image/jpeg;base64,'.$photoData;
    
        $urlQR="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://example.com";
        $footer="http://127.0.0.1:8001/assets/footcard.png";
    
        $html = $this->renderView('admin/pdf/carte_membre.html.twig', [
            'user' => $user,
            'photo' => $photo,
            'urlQR' => $urlQR,
            'footer' => $footer,
            //'qrCode' => $qrCodeDataUri // Pass the Data URI directly to the Twig template
        ]);
    
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
    
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="carte_membre_'.$user->getId().'.pdf"'
        ]);
    }
    
    /**
 * @Route("/admin/membre/{id}/edit", name="member_edit")
 */
public function edit(Request $request, UserRepository $userRepository, SluggerInterface $slugger, $id, EntityManagerInterface $em): Response
{
    $user = $userRepository->find($id);

    $form = $this->createFormBuilder($user)
        ->add('photo', FileType::class, [
            'label' => 'Photo d\'identité',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png'],
                    'mimeTypesMessage' => 'Veuillez uploader une image JPG ou PNG',
                ])
            ],
        ])
        ->add('nom', TextType::class)
        ->add('prenom', TextType::class)
        ->add('email', EmailType::class)
        ->add('telephone', TextType::class)
        ->add('adresse', TextType::class)
        ->add('promotion', TextType::class)
        ->add('anneeDebut', TextType::class)
        ->add('anneeFin', TextType::class)
        ->add('save', SubmitType::class, [
            'label' => 'Mettre à jour',
            'attr' => ['class' => 'btn btn-primary']
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $photoFile = $form->get('photo')->getData();

        if ($photoFile) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

            try {
                $photoFile->move(
                    $this->getParameter('photos_directory'), // <- définie dans services.yaml
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('danger', 'Erreur lors de l\'upload de la photo.');
            }

            $user->setPhoto($newFilename); // Met à jour le champ dans la base
        }

        $em->flush();
        $this->addFlash('success', 'Membre mis à jour avec succès !');
        return $this->redirectToRoute('member_list');
    }

    return $this->render('admin/membre/edit.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
    ]);
}
}

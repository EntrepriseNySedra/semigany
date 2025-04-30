<?php

namespace App\Controller\frontoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Repository\EvenementRepository;
use App\Form\EventType;
use App\Entity\Evenement;
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


class EvenementController extends AbstractController
{
    /**
     * @Route("/admin/events", name="admin_events")
     */
    public function index(EvenementRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('admin/event/index.html.twig', [
            'events' => $events,
        ]);
    }

     /**
     * @Route("/admin/event/create", name="admin_event_create")
     */
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Evenement();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Événement créé avec succès');

            return $this->redirectToRoute('admin_events');
        }

        return $this->render('admin/event/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/event/{id}/edit", name="admin_event_edit")
     */
    public function edit(Request $request, Evenement $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Événement modifié avec succès');

            return $this->redirectToRoute('admin_events');
        }

        return $this->render('admin/event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    /**
     * @Route("/admin/event/{id}/delete", name="admin_event_delete")
     */
    public function delete(Evenement $event, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash('success', 'Événement supprimé avec succès');

        return $this->redirectToRoute('admin_events');
    }
}
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Repository\UserRepository;


class AdminController extends AbstractController
{
  
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function index(AuthorizationCheckerInterface $authChecker)
    {
        // Vérifie si l'utilisateur a le rôle 'ROLE_ADMIN'
        if (!$authChecker->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit !');
        }

        // Si l'utilisateur est un admin, il peut accéder à cette page
        return $this->render('admin/index.html.twig');
    }
    

    
}

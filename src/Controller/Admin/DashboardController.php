<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[AdminDashboard(routePath: '/', routeName: 'admin')]

class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirect('/user');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->disableDarkMode()->setTitle('Simple CRUD');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Users', 'fas fa-list', User::class);
    }
}

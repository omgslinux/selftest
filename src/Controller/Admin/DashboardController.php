<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use App\Controller\Admin\CategoryCrudController;
use App\Controller\Admin\TopicCrudController;
use App\Controller\Admin\LevelCrudController;
use App\Controller\Admin\QuizCrudController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirectToRoute('admin_category_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Selftest')
            ->setTranslationDomain('EasyAdminBundle')
            ->setLocales(['es']);
    }

    public function configureMenuItems(): array
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::linkTo(CategoryCrudController::class, 'Category', 'fa fa-list'),
            MenuItem::linkTo(TopicCrudController::class, 'Topic', 'fa fa-list'),
            MenuItem::linkTo(LevelCrudController::class, 'Level', 'fa fa-list'),
            MenuItem::linkTo(QuizCrudController::class, 'Quiz', 'fa fa-list'),
        ];
    }
}

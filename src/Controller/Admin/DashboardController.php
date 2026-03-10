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
use App\Controller\Admin\QuizQuestionCrudController;
use App\Controller\Admin\UserCrudController;
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
            MenuItem::linkToUrl('Ir a Tests', 'fa fa-brain', '/'),
            MenuItem::linkTo(CategoryCrudController::class, 'Categorías', 'fa fa-folder'),
            MenuItem::linkTo(TopicCrudController::class, 'Temas', 'fa fa-tags'),
            MenuItem::linkTo(LevelCrudController::class, 'Niveles', 'fa fa-signal'),
            MenuItem::linkTo(QuizCrudController::class, 'Cuestionarios', 'fa fa-clipboard-list'),
            MenuItem::linkTo(QuizQuestionCrudController::class, 'Preguntas', 'fa fa-question-circle'),
            MenuItem::linkTo(UserCrudController::class, 'Usuarios', 'fa fa-users'),
        ];
    }
}

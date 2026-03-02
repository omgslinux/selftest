<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category', name: 'app_category_')]
final class CategoryController extends AbstractController
{
    private const PREFIX = 'app_category_';
    private const TDIR = 'category';

    #[Route(name: 'index', methods: ['GET', 'POST'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render(self::TDIR . '/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'PREFIX' => self::PREFIX,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $action = $this->generateUrl(self::PREFIX . 'new');

        $form = $this->createForm(
            CategoryType::class,
            $category,
            [
                'action' => $action,
            ]
        );
        $form->handleRequest($request);

        $render = [
            'template' => self::TDIR . '/_form.html.twig',
            'args' => [
                'form' => $form,
                'PREFIX' => self::PREFIX,
                'title' => 'Create Category',
                'action' => $action,
                'method' => 'POST',
            ]
        ];



        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($category);
                $entityManager->flush();

                $redirectUrl = $this->generateUrl(self::PREFIX . 'index');

                if ($request->isXmlHttpRequest()) {
                    return $this->json([
                        'success' => true,
                        'redirectUrl' => $redirectUrl,
                    ]);
                }

                return $this->redirect($redirectUrl);
                //return $this->redirectToRoute(self::PREFIX . 'index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render(
                $render['template'],
                $render['args'],
                new Response(null, 422)
            );
        }
            return $this->render(
                $render['template'],
                $render['args']
            );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render(self::TDIR . '/show.html.twig', [
            'category' => $category,
            'PREFIX' => self::PREFIX,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $action = $this->generateUrl(self::PREFIX . 'edit', ['id' => $category->getId()]);

        $form = $this->createForm(
            CategoryType::class,
            $category,
            [
                'action' => $action,
            ]
        );
        $form->handleRequest($request);

        $render = [
            'template' => self::TDIR . '/_form.html.twig',
            'args' => [
                'form' => $form,
                'category' => $category,
                'PREFIX' => self::PREFIX,
                'title' => 'Edit Category',
            ]
        ];

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->flush();

                $redirectUrl = $this->generateUrl(self::PREFIX . 'index');

                if ($request->isXmlHttpRequest()) {
                    return $this->json([
                        'success' => true,
                        'redirectUrl' => $redirectUrl,
                    ]);
                }

                return $this->redirect($redirectUrl);
            }

            return $this->render(
                $render['template'],
                $render['args'],
                new Response(null, 422)
            );
        }

        return $this->render(
            $render['template'],
            $render['args']
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute(self::PREFIX . 'index', [], Response::HTTP_SEE_OTHER);
    }
}

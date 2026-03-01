<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Form\TopicType;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/topic', name: 'app_topic_')]
final class TopicController extends AbstractController
{
    private const PREFIX = 'app_topic_';
    private const TDIR = 'topic';

    #[Route(name: 'index', methods: ['GET'])]
    public function index(TopicRepository $topicRepository): Response
    {
        return $this->render(self::TDIR . '/index.html.twig', [
            'topics' => $topicRepository->findAll(),
            'PREFIX' => self::PREFIX,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $topic = new Topic();
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($topic);
            $entityManager->flush();

            return $this->redirectToRoute(self::PREFIX . 'index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render(self::TDIR . '/new.html.twig', [
            'topic' => $topic,
            'form' => $form,
            'PREFIX' => self::PREFIX,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Topic $topic): Response
    {
        return $this->render(self::TDIR . '/show.html.twig', [
            'topic' => $topic,
            'PREFIX' => self::PREFIX,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Topic $topic, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute(self::PREFIX . 'index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render(self::TDIR . '/edit.html.twig', [
            'topic' => $topic,
            'form' => $form,
            'PREFIX' => self::PREFIX,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Topic $topic, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$topic->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($topic);
            $entityManager->flush();
        }

        return $this->redirectToRoute(self::PREFIX . 'index', [], Response::HTTP_SEE_OTHER);
    }
}

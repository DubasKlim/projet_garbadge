<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/utilisateur')]
final class UtilisateurController extends AbstractController
{
    #[Route(name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])] 
    public function new(Request $request, UtilisateurRepository $utilisateurRepository, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine): Response 
    { 
        $utilisateur = new Utilisateur(); 
        $form = $this->createForm(UtilisateurType::class, $utilisateur); 
        $form->handleRequest($request); 
        if ($form->isSubmitted() && $form->isValid()) { 
            $plaintextPassword = $utilisateur->getPassword(); 
            $hashedPassword = $passwordHasher->hashPassword(
                $utilisateur,
                $plaintextPassword 
            ); 
            $utilisateur->setPassword($hashedPassword); 
            $entityManager = $doctrine -> getManager();
            $entityManager->persist($utilisateur);
            $entityManager->flush();
            //$utilisateurRepository->save($utilisateur, true); 
            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER); 
        } 
        return $this->render('utilisateur/new.html.twig', [ 
            'utilisateur' => $utilisateur, 
            'form' => $form->createView(), 
        ]); 
    }

    #[Route('/{id}', name: 'app_utilisateur_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }
}
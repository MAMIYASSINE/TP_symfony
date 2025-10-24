<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/author')]
final class AuthorController extends AbstractController
{
    // #[Route('/author', name: 'app_author')]
    // public function index(AuthorRepository $authorRepository): Response
    // {
    //     return $this->render('author/index.html.twig', [
    //         'controller_name' => 'AuthorController',
    //     ]);
    // }
    // 4) Afficher la liste des auteurs
    #[Route('/', name: 'app_author_index')]
    public function index(AuthorRepository $repo): Response
    {
        $authors = $repo->findAll();

        return $this->render('author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    // 5) Ajouter un auteur statique
    #[Route('/add-static', name: 'app_author_add_static')]
    public function addStatic(EntityManagerInterface $em): Response
    {
        $author = new Author();
        $author->setUsername('Jean Dupont');
        $author->setEmail('jean.dupont@example.com');

        $em->persist($author);
        $em->flush();

        $this->addFlash('success', 'Auteur ajouté avec succès.');

        return $this->redirectToRoute('app_author_index');
    }

    // 7) Ajouter un auteur avec formulaire
    #[Route('/new', name: 'app_author_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($author);
            $em->flush();

            $this->addFlash('success', 'Auteur ajouté avec succès.');
            return $this->redirectToRoute('app_author_index');
        }

        return $this->render('author/new.html.twig', [
            'form' => $form->createView(),
           //'formAuth' => $form,
        ]);
    }

    // 9) Modifier un auteur
    #[Route('/edit/{id}', name: 'app_author_edit')]
    public function edit(
        int $id,
        Request $request,
        AuthorRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $author = $repo->find($id);
        if (!$author) {
            throw $this->createNotFoundException('Auteur non trouvé.');
        }

        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Auteur modifié avec succès.');
            return $this->redirectToRoute('app_author_index');
        }

        return $this->render('author/edit.html.twig', [
            'form' => $form->createView(),
            'author' => $author,
        ]);
    }

    // 10) Supprimer un auteur
    #[Route('/delete/{id}', name: 'app_author_delete')]
    public function delete(int $id, AuthorRepository $repo, EntityManagerInterface $em): Response
    {
        $author = $repo->find($id);
        if (!$author) {
            throw $this->createNotFoundException('Auteur non trouvé.');
        }

        $em->remove($author);
        $em->flush();

        $this->addFlash('success', 'Auteur supprimé avec succès.');

        return $this->redirectToRoute('app_author_index');
    }
}

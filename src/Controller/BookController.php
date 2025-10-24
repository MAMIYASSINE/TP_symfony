<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Form\BookType;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/book')]
final class BookController extends AbstractController
{
    // B1 - Afficher tous les livres publiés
    #[Route('/', name: 'app_book_index')]
    public function index(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findBy(['enabled' => true]);
        $nbPublished = $bookRepository->count(['enabled' => true]);
        $nbNotPublished = $bookRepository->count(['enabled' => false]);

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'nbPublished' => $nbPublished,
            'nbNotPublished' => $nbNotPublished,
        ]);
    }

    // A2 - Ajouter un livre avec formulaire
    #[Route('/new', name: 'app_book_new')]
    public function new(Request $request, EntityManagerInterface $em, AuthorRepository $authorRepo): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // published = true
            $book->setEnabled(true);

            // Incrémentation du nb_books de l’auteur
            $author = $book->getAuthor();
            $author->setNbBooks($author->getNbBooks() + 1);

            $em->persist($book);
            $em->flush();

            $this->addFlash('success', 'Livre ajouté avec succès !');
            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('book/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // C - Modifier un livre
    #[Route('/edit/{id}', name: 'app_book_edit')]
    public function edit(int $id, Request $request, BookRepository $repo, EntityManagerInterface $em): Response
    {
        $book = $repo->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Livre non trouvé.');
        }

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Livre modifié avec succès.');
            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('book/edit.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
        ]);
    }

    // D - Supprimer un livre
    #[Route('/delete/{id}', name: 'app_book_delete')]
    public function delete(int $id, BookRepository $repo, EntityManagerInterface $em): Response
    {
        $book = $repo->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Livre non trouvé.');
        }

        $author = $book->getAuthor();

        $em->remove($book);
        $em->flush();

        // Décrémenter le nb_books de l’auteur
        $author->setNbBooks($author->getNbBooks() - 1);

        // Supprimer les auteurs sans livres
        if ($author->getNbBooks() <= 0) {
            $em->remove($author);
        }

        $em->flush();

        $this->addFlash('success', 'Livre supprimé avec succès.');
        return $this->redirectToRoute('app_book_index');
    }

    // E - Détails d’un livre
    #[Route('/show/{id}', name: 'app_book_show')]
    public function show(int $id, BookRepository $repo): Response
    {
        $book = $repo->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Livre non trouvé.');
        }

        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }
    #[Route('/romance-count', name: 'app_book_romance_count')]
    public function romanceCount(BookRepository $bookRepository): Response
    {
        $count = $bookRepository->countRomanceBooks();

        return $this->render('book/romance_count.html.twig', [
            'count' => $count,
        ]);
    }
    #[Route('/by-date-range', name: 'app_book_by_date_range')]
    public function booksByDateRange(BookRepository $bookRepository): Response
    {
        $startDate = new \DateTime('2014-01-01');
        $endDate = new \DateTime('2018-12-31');

        $books = $bookRepository->findBooksByDateRange($startDate, $endDate);

        return $this->render('book/by_date_range.html.twig', [
            'books' => $books,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}

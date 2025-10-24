<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }
    public function findAuthorsByBookRange(?int $minBooks, ?int $maxBooks): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($minBooks !== null) {
            $qb->andWhere('a.nb_books >= :minBooks')
                ->setParameter('minBooks', $minBooks);
        }

        if ($maxBooks !== null) {
            $qb->andWhere('a.nb_books <= :maxBooks')
                ->setParameter('maxBooks', $maxBooks);
        }

        return $qb->orderBy('a.nb_books', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function deleteAuthorsWithNoBooks(): int
{
    return $this->createQueryBuilder('a')
        ->delete()
        ->where('a.nb_books = 0')
        ->getQuery()
        ->execute();
}
    //    /**
    //     * @return Author[] Returns an array of Author objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Author
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

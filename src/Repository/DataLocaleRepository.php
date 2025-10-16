<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DataLocale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DataLocale>
 */
class DataLocaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataLocale::class);
    }

    public function findByDomainOrDefault(string $domain): ?DataLocale
    {
        return $this->createQueryBuilder('l')
            ->where('l.domain = :domain')
            ->orWhere('l.isDefault = true')
            ->orderBy('l.domain', 'DESC') // Prioritize exact domain match
            ->setParameter('domain', $domain)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

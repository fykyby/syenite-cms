<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DataLocale;
use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function listPagesOfLocale(int $localeId): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.path, p.published, p.updatedAt')
            ->where('p.locale = :localeId')
            ->setParameter('localeId', $localeId)
            ->getQuery()
            ->getArrayResult();
    }

    public function findOneByPathAndLocaleWithLayoutData(
        string $path,
        DataLocale $locale,
    ): ?Page {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.layoutData', 'l')
            ->addSelect('l')
            ->where('p.path = :path')
            ->andWhere('p.published = true')
            ->andWhere('p.locale = :locale')
            ->setParameter('path', $path)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

<?php

namespace TwinElements\MenuBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use TwinElements\MenuBundle\Entity\Menu;
use function Doctrine\ORM\QueryBuilder;

class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function findIndexListItems(int $category)
    {
        $qb = $this->createQueryBuilder('menu');

        $qb
            ->select(['menu', 'menu_translations'])
            ->leftJoin('menu.translations', 'menu_translations')
            ->andWhere(
                $qb->expr()->eq('menu.category', $category)
            )
            ->orderBy('menu.position', 'asc');
        return $qb->getQuery()->getResult();
    }

    public function findByCategory(int $category, string $locale)
    {
        $qb = $this->createQueryBuilder('menu');
        $qb
            ->select(['menu', 'menu_translations'])
            ->join('menu.translations', 'menu_translations')
            ->where(
                $qb->expr()->eq('menu.category', ':category')
            )
            ->andWhere(
                $qb->expr()->eq('menu_translations.locale', ':locale')
            )
            ->setParameter(':category', $category)
            ->setParameter(':locale', $locale)
            ->orderBy('menu.position', 'asc');

        return $qb->getQuery()->getResult();
    }

}

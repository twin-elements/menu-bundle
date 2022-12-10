<?php

namespace TwinElements\MenuBundle;

use Knp\Menu\FactoryInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use TwinElements\FormExtensions\Component\UrlBuilder\ModuleUrlBuilder;
use TwinElements\MenuBundle\Entity\Menu;
use TwinElements\MenuBundle\Repository\MenuCategoryRepository;
use TwinElements\MenuBundle\Repository\MenuRepository;

class Builder
{
    private $menu;
    private $factory;
    private $menuRepository;
    private $categoryRepository;
    private $locale;
    private $cache;
    /**
     * @var ModuleUrlBuilder $urlBuilder
     */
    private $urlBuilder;

    public function __construct(
        FactoryInterface $factory,
        MenuCategoryRepository $categoryRepository,
        MenuRepository $menuRepository,
        RequestStack $requestStack,
        ModuleUrlBuilder $urlBuilder,
        AdapterInterface $cache
    )
    {
        $this->factory = $factory;
        $this->locale = $requestStack->getCurrentRequest()->getLocale();
        $this->urlBuilder = $urlBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->menuRepository = $menuRepository;
        $this->cache = $cache;
    }

    public function mainMenu(array $options)
    {
        if (!array_key_exists('category', $options)) {
            throw new \Exception('No category code');
        }

        $code = $options['category'];

        $menuCategory = $this->categoryRepository->findOneByCode($code);

        if ($menuCategory->isCached()) {
            $menuCache = $this->cache->getItem(MenuCacheUtilities::getCacheName($menuCategory->getId(), $this->locale));
            if (!$menuCache->isHit()) {
                $menuItems = $this->getItemsFromDB($menuCategory->getId());

                $this->cache->save($menuCache->set($menuItems));
            } else {
                $menuItems = $menuCache->get();
            }
        } else {
            $menuItems = $this->getItemsFromDB($menuCategory->getId());
        }

        $this->menu = $this->factory->createItem('menu');
        if (count($menuItems) > 0) {
            /**
             * @var Menu $menuItem
             */
            foreach ($menuItems[0] as $menuId => $menuItem) {
                $this->menu->addChild(
                    $menuItem['title'],
                    $menuItem['url']
                );

                if ($menuItem['isInNewTab']) {
                    $this->menu->getChild($menuItem['title'])->setLinkAttribute('target', '_blank');
                }

                if($menuItem['isMegaMenu']){
                    $this->menu->getChild($menuItem['title'])->setAttribute('class', 'megamenu');
                }

                $this->recursiveGeneratePositions($menuItems, $menuId, $menuItem);

            }
        }

        return $this->menu;
    }

    private function getItemsFromDB(int $category_id)
    {
        $menuList = $this->menuRepository->findByCategory($category_id, $this->locale);
        $menuItems = [];

        /**
         * @var Menu $menuItem
         */
        foreach ($menuList as $itemId => $menuItem) {
            $menuItems[($menuItem->getParent() ? $menuItem->getParent()->getId() : 0)][$menuItem->getId()] = [
                'title' => $menuItem->getTitle(),
                'isInNewTab' => $menuItem->isInNewTab(),
                'url' => $this->returnRoute($menuItem->getRoute()),
                'isMegaMenu' => $menuItem->isMegamenu()
            ];
        }

        return $menuItems;
    }

    private function returnRoute(?string $route)
    {
        if ($route) {
            return [
                'uri' => $this->urlBuilder->generateUrl($route)
            ];
        }

        return [];
    }

    private function recursiveGeneratePositions($menuResult, $menuId, $menuItem)
    {
        if (isset($menuResult[$menuId])) {
            if (count($menuResult[$menuId]) > 0) {
                /**
                 * @var Menu $childMenuItem
                 */
                foreach ($menuResult[$menuId] as $childId => $childMenuItem) {
                    $this->menu[$menuItem['title']]->addChild(
                        $childMenuItem['title'],
                        $childMenuItem['url']
                    );

                    if ($childMenuItem['isInNewTab']) {
                        $this->menu->getChild($menuItem['title'])->getChild($childMenuItem['title'])->setLinkAttribute('target', '_blank');
                    }
                    if($childMenuItem['isMegaMenu']){
                        $this->menu->getChild($menuItem['title'])->getChild($childMenuItem['title'])->setAttribute('class', 'megamenu');
                    }

                    if (isset($menuResult[$childId]) && count($menuResult[$childId]) > 0) {
                        foreach ($menuResult[$childId] as $lvlTwoId => $lvlTwoItem) {
                            $this->menu[$menuItem['title']][$childMenuItem['title']]->addChild(
                                $lvlTwoItem['title'],
                                $lvlTwoItem['url']
                            );
                        }
                    }
                }
            }
        }
    }
}

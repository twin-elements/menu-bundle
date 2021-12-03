<?php

namespace TwinElements\MenuBundle\Controller;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use TwinElements\AdminBundle\Entity\Traits\PositionInterface;
use TwinElements\AdminBundle\Helper\Breadcrumbs;
use TwinElements\AdminBundle\Helper\CrudLoggerMessage;
use TwinElements\AdminBundle\Model\CrudControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use TwinElements\AdminBundle\Role\AdminUserRole;
use TwinElements\AdminBundle\Service\AdminTranslator;
use TwinElements\Component\Flashes\Flashes;
use TwinElements\MenuBundle\Entity\Menu;
use TwinElements\MenuBundle\Entity\MenuCategory;
use TwinElements\MenuBundle\Form\MenuType;
use TwinElements\MenuBundle\MenuCacheUtilities;
use TwinElements\MenuBundle\Repository\MenuCategoryRepository;
use TwinElements\MenuBundle\Repository\MenuRepository;

/**
 * @Route("menu")
 */
class MenuController extends AbstractController
{
    /**
     * @var AdapterInterface $cache
     */
    private $cache;

    use CrudControllerTrait {
        CrudControllerTrait::__construct as private __crudConstruct;
    }

    public function __construct(Breadcrumbs $breadcrumbs, Flashes $flashes, CrudLoggerMessage $crudLogger, AdminTranslator $translator, AdapterInterface $cache)
    {
        $this->__crudConstruct($breadcrumbs, $flashes, $crudLogger, $translator);
        $this->cache = $cache;
    }

    /**
     * @Route("/", name="menu_index", methods={"GET"})
     */
    public function indexAction(Request $request, MenuRepository $menuRepository, MenuCategoryRepository $menuCategoryRepository)
    {
        if (!$request->query->has('category')) {
            throw new \Exception('Category ID not found');
        }

        $categoryId = $request->query->getInt('category');

        $menuItems = $menuRepository->findIndexListItems($categoryId);
        $menuCategory = $menuCategoryRepository->find($categoryId);

        $this->breadcrumbs->setItems([
            'menu_category.menu_categories' => $this->generateUrl('menucategory_index'),
            $menuCategory->getTitle() => null
        ]);

        $responseParameters = [
            'menus' => $menuItems,
            'menu_category' => $menuCategory
        ];

        if ((new \ReflectionClass(Menu::class))->implementsInterface(PositionInterface::class)) {
            $responseParameters['sortable'] = Menu::class;
        }

        return $this->render('@TwinElementsMenu/menu/index.html.twig', $responseParameters);
    }

    /**
     * @Route("/new", name="menu_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, AdapterInterface $cache)
    {
        $this->denyAccessUnlessGranted(AdminUserRole::ROLE_ADMIN);

        $categoryId = (int)$request->get('category');
        $menuCategory = $this->getDoctrine()->getRepository(MenuCategory::class)->find($categoryId);

        $menuCategoryDefaultTitle = $menuCategory->getTitle();

        $menu = new Menu();
        $menu->setCurrentLocale($request->getLocale());
        $form = $this->createForm(MenuType::class, $menu, ['category' => $categoryId]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {

                $menu->setCategory($menuCategory);

                $em = $this->getDoctrine()->getManager();

                $em->persist($menu);
                $menu->mergeNewTranslations();

                $em->flush();

                if ($menuCategory->isCached()) {
                    $this->removeCache($menuCategory->getId(), $request->getLocale());
                }

                $this->flashes->successMessage($this->adminTranslator->translate('admin.success_operation'));;
                $this->crudLogger->createLog($menu->getId(), $menu->getTitle());

            } catch (\Exception $exception) {
                $this->flashes->errorMessage($exception->getMessage());
                return $this->redirectToRoute('menu_index', array('category' => $menuCategory->getId()));
            }

            if ('save' === $form->getClickedButton()->getName()) {
                return $this->redirectToRoute('menu_edit', array('id' => $menu->getId(), 'category' => $menuCategory->getId()));
            } else {
                return $this->redirectToRoute('menu_index', array('category' => $menuCategory->getId()));
            }
        }


        $this->breadcrumbs->setItems([
            'menu_category.menu_categories' => $this->generateUrl('menucategory_index'),
            $menuCategoryDefaultTitle => $this->generateUrl('menu_index', [
                'category' => $categoryId
            ]),
            'Dodawanie nowej pozycji' => null
        ]);

        return $this->render('@TwinElementsMenu/menu/new.html.twig', array(
            'menu' => $menu,
            'menu_category' => $menuCategory,
            'form' => $form->createView(),
            'menu_category_default_locale_title' => $menuCategoryDefaultTitle
        ));
    }

    /**
     * @Route("/{id}/edit", name="menu_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Menu $menu, AdapterInterface $cache)
    {
        $this->denyAccessUnlessGranted(AdminUserRole::ROLE_ADMIN);

        $categoryId = (int)$request->get('category');

        $menuCategory = $menu->getCategory();

        $deleteForm = $this->createDeleteForm($menu);
        $editForm = $this->createForm(MenuType::class, $menu, ['category' => $categoryId]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            try {
                $menu->mergeNewTranslations();

                $this->getDoctrine()->getManager()->flush();

                if ($menuCategory->isCached()) {
                    $this->removeCache($menuCategory->getId(), $request->getLocale());
                }

                $this->flashes->successMessage($this->adminTranslator->translate('admin.success_operation'));;
                $this->crudLogger->createLog($menu->getId(), $menu->getTitle());

            } catch (\Exception $exception) {
                $this->flashes->errorMessage($exception->getMessage());
            }

            if ('save' === $editForm->getClickedButton()->getName()) {
                return $this->redirectToRoute('menu_edit', array('id' => $menu->getId(), 'category' => $menuCategory->getId()));
            } else {
                return $this->redirectToRoute('menu_index', array('category' => $menuCategory->getId()));

            }
        }

        $this->breadcrumbs->setItems([
            'menu_category.menu_categories' => $this->generateUrl('menucategory_index'),
            $menu->getCategory()->getTitle() => $this->generateUrl('menu_index', [
                'category' => $categoryId
            ]),
            $menu->getTitle() => null
        ]);

        return $this->render('@TwinElementsMenu/menu/edit.html.twig', array(
            'entity' => $menu,
            'menu_category' => $menuCategory,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ));
    }

    /**
     * Deletes a menu entity.
     *
     * @Route("/{id}", name="menu_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Menu $menu)
    {
        $this->denyAccessUnlessGranted(AdminUserRole::ROLE_ADMIN);

        $form = $this->createDeleteForm($menu);
        $form->handleRequest($request);

        $category = $menu->getCategory()->getId();

        if ($form->isSubmitted() && $form->isValid()) {

            try {

                $id = $menu->getId();
                $title = $menu->getTitle();

                $em = $this->getDoctrine()->getManager();
                $em->remove($menu);
                $em->flush();

                $this->removeCache($category, $request->getLocale());

                $this->crudLogger->createLog($id, $title);
                $this->flashes->successMessage($this->adminTranslator->translate('menu.the_menu_item_has_been_deleted'));
            } catch (\Exception $exception) {
                $this->flashes->errorMessage($exception->getMessage());
            }
        }

        return $this->redirectToRoute('menu_index', ['category' => $category]);
    }

    /**
     * Creates a form to delete a menu entity.
     *
     * @param Menu $menu The menu entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Menu $menu)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('menu_delete', array('id' => $menu->getId(), 'category' => $menu->getCategory()->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    private function removeCache(int $id, string $locale)
    {
        if ($this->cache->hasItem(MenuCacheUtilities::getCacheName($id, $locale))) {
            $this->cache->deleteItem(MenuCacheUtilities::getCacheName($id, $locale));
        }
    }
}

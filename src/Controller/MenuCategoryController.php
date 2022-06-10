<?php

namespace TwinElements\MenuBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use TwinElements\AdminBundle\Model\CrudControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use TwinElements\AdminBundle\Role\AdminUserRole;
use TwinElements\Component\CrudLogger\CrudLogger;
use TwinElements\MenuBundle\Entity\MenuCategory;
use TwinElements\MenuBundle\Form\MenuCategoryType;
use TwinElements\MenuBundle\Repository\MenuCategoryRepository;

/**
 * @Route("menucategory")
 */
class MenuCategoryController extends AbstractController
{

    use CrudControllerTrait;

    /**
     * @Route("/", name="menucategory_index", methods={"GET"})
     */
    public function indexAction(MenuCategoryRepository $menuCategoryRepository)
    {
        $menuCategories = $menuCategoryRepository->findAll();

	    $this->breadcrumbs->setItems([
            'menu_category.menu_categories' => null
        ]);

        return $this->render('@TwinElementsMenu/menucategory/index.html.twig', [
            'menuCategories' => $menuCategories,
        ]);
    }

    /**
     * @Route("/new", name="menucategory_new", methods={"GET", "POST"})
     */
    public function newAction(
        ManagerRegistry $managerRegistry,
        Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");
        $menuCategory = new Menucategory();
        $form = $this->createForm(MenuCategoryType::class, $menuCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        	try {
		        $em = $managerRegistry->getManager();
		        $em->persist($menuCategory);
		        $em->flush();

		        $this->flashes->successMessage($this->adminTranslator->translate('admin.success_operation'));;
		        $this->crudLogger->createLog(MenuCategory::class, CrudLogger::CreateAction, $menuCategory->getId());

	        } catch (\Exception $exception) {
        		$this->flashes->errorMessage($exception->getMessage());
        		return $this->redirectToRoute('menucategory_index');
	        }

            if ('save' === $form->getClickedButton()->getName()) {
                return $this->redirectToRoute('menucategory_edit', array('id' => $menuCategory->getId()));
            } else {
                return $this->redirectToRoute('menucategory_index');
            }
        }

	    $this->breadcrumbs->setItems([
            'menu_category.menu_categories' => $this->generateUrl('menucategory_index'),
            'menu_category.add_new_menu' => null
        ]);

        return $this->render('@TwinElementsMenu/menucategory/new.html.twig', array(
            'menuCategory' => $menuCategory,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", name="menucategory_edit", methods={"GET", "POST"})
     */
    public function editAction(ManagerRegistry $managerRegistry, Request $request, MenuCategory $menuCategory)
    {
        $this->denyAccessUnlessGranted(AdminUserRole::ROLE_ADMIN);
        $deleteForm = $this->createDeleteForm($menuCategory);
        $editForm = $this->createForm(MenuCategoryType::class, $menuCategory);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

        	try {
		        $managerRegistry->getManager()->flush();

		        $this->flashes->successMessage($this->adminTranslator->translate('admin.success_operation'));;
		        $this->crudLogger->createLog(MenuCategory::class, CrudLogger::EditAction, $menuCategory->getId());

	        } catch (\Exception $exception) {
        		$this->flashes->errorMessage($exception->getMessage());
	        }

            if ('save' === $editForm->getClickedButton()->getName()) {
                return $this->redirectToRoute('menucategory_edit', array('id' => $menuCategory->getId()));
            } else {
                return $this->redirectToRoute('menucategory_index');
            }
        }

	    $this->breadcrumbs->setItems([
            'menu_category.menu_categories' => $this->generateUrl('menucategory_index'),
            $menuCategory->getTitle() => null
        ]);

        return $this->render('@TwinElementsMenu/menucategory/edit.html.twig', array(
            'entity' => $menuCategory,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a menuCategory entity.
     *
     * @Route("/{id}", name="menucategory_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, MenuCategory $menuCategory, ManagerRegistry $managerRegistry)
    {
        $this->denyAccessUnlessGranted(AdminUserRole::ROLE_ADMIN);

        $form = $this->createDeleteForm($menuCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        	try {

        		$id = $menuCategory->getId();

		        $em = $managerRegistry->getManager();
		        $em->remove($menuCategory);
		        $em->flush();

		        $this->flashes->successMessage($this->adminTranslator->translate('admin.success_operation'));;
		        $this->crudLogger->createLog(MenuCategory::class, CrudLogger::DeleteAction, $id);

	        } catch (\Exception $exception){
        		$this->flashes->errorMessage($exception->getMessage());
	        }
        }

        return $this->redirectToRoute('menucategory_index');
    }

    /**
     * @param MenuCategory $menuCategory The menuCategory entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(MenuCategory $menuCategory)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('menucategory_delete', array('id' => $menuCategory->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}

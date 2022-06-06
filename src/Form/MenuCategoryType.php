<?php

namespace TwinElements\MenuBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use TwinElements\Component\AdminTranslator\AdminTranslator;
use TwinElements\FormExtensions\Type\SaveButtonsType;
use TwinElements\FormExtensions\Type\ToggleChoiceType;
use TwinElements\MenuBundle\Entity\MenuCategory;

class MenuCategoryType extends AbstractType
{
    /**
     * @var AdminTranslator $translator
     */
    private $translator;

    /**
     * @var Security $security
     */
    private $security;

    /**
     * @param AdminTranslator $translator
     */
    public function __construct(AdminTranslator $translator, Security $security)
    {
        $this->translator = $translator;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->translate('menu_category.title'),
            ]);

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $builder
                ->add('code', TextType::class, [
                    'label' => $this->translator->translate('menu_category.code')
                ]);
        }

        $builder
            ->add('isCached', ToggleChoiceType::class, [
                'label' => $this->translator->translate('menu_category.isCached')
            ])
            ->add('buttons', SaveButtonsType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MenuCategory::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'menu_category';
    }
}

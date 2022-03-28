<?php

namespace TwinElements\MenuBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TwinElements\AdminBundle\Service\AdminTranslator;
use TwinElements\FormExtensions\Type\SaveButtonsType;
use TwinElements\FormExtensions\Type\TEChooseLinkType;
use TwinElements\FormExtensions\Type\ToggleChoiceType;
use TwinElements\MenuBundle\Entity\Menu;

class MenuType extends AbstractType
{
    /**
     * @var AdminTranslator $translator
     */
    private $translator;

    public function __construct(AdminTranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->translate('admin.title')
            ])
            ->add('route', TEChooseLinkType::class, [
                'label' => $this->translator->translate('menu.redirect')
            ])
            ->add('parent', EntityType::class, [
                'class' => Menu::class,
                'query_builder' => function (EntityRepository $entityRepository) use ($options) {
                    $qb = $entityRepository->createQueryBuilder('m');
                    $qb
                        ->select(['m', 'm_translations'])
                        ->join('m.translations', 'm_translations');

                    if (is_numeric($options['category'])) {
                        $qb
                            ->where('m.category = :category')
                            ->setParameter('category', $options['category']);
                    }

                    return $qb;
                },
                'label' => $this->translator->translate('menu.parent'),
                'label_attr' => ['class' => 'col-md-3 col-lg-2'],
                'attr' => [
                    'class' => 'col-md-4 input'
                ],
                'placeholder' => $this->translator->translate('menu.choose_parent'),
                'required' => false
            ])
            ->add('isInNewTab', ToggleChoiceType::class, [
                'label' => $this->translator->translate('menu.open_in_a_new_tab'),
                'help' => $this->translator->translate('menu.open_in_a_new_tab_help')
            ])
            ->add('isMegamenu', ToggleChoiceType::class,[
                'label' => $this->translator->translate('menu.is_mega_menu')
            ])
            ->add('buttons', SaveButtonsType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Menu::class,
            'category' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_menu';
    }
}

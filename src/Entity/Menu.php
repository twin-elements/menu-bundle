<?php

namespace TwinElements\MenuBundle\Entity;

use TwinElements\AdminBundle\Entity\Traits\IdTrait;
use TwinElements\AdminBundle\Entity\Traits\PositionInterface;
use TwinElements\AdminBundle\Entity\Traits\PositionTrait;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;
use Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

/**
 * @ORM\Entity(repositoryClass="TwinElements\MenuBundle\Repository\MenuRepository")
 * @ORM\Table(name="menu")
 */
class Menu implements TranslatableInterface, BlameableInterface, TimestampableInterface, LoggableInterface, PositionInterface
{
    use
        IdTrait,
        PositionTrait,
        TranslatableTrait,
        BlameableTrait,
        TimestampableTrait,
        LoggableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="TwinElements\MenuBundle\Entity\MenuCategory")
     * @ORM\JoinColumn(name="category_id",referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="TwinElements\MenuBundle\Entity\Menu", mappedBy="parent", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="TwinElements\MenuBundle\Entity\Menu", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isInNewTab = false;

    public function __toString()
    {
        $title = $this->translate(null, false)->getTitle();

        if ($title) {
            return $title;
        } else {
            return 'no translation';
        }
    }

    public function getTitle(): ?string
    {
        return $this->translate(null, false)->getTitle();
    }

    public function setTitle(string $title): void
    {
        $this->translate(null, false)->setTitle($title);
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category): void
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children): void
    {
        $this->children = $children;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent): void
    {
        $this->parent = $parent;
    }


    /**
     * @return bool
     */
    public function isInNewTab(): bool
    {
        return $this->isInNewTab;
    }

    /**
     * @param bool $isInNewTab
     */
    public function setIsInNewTab(bool $isInNewTab): void
    {
        $this->isInNewTab = $isInNewTab;
    }


    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->translate(null, false)->getRoute();
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route): void
    {
        $this->translate(null, false)->setRoute($route);
    }
}

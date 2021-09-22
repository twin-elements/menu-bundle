<?php

namespace TwinElements\MenuBundle\Entity;

use TwinElements\AdminBundle\Entity\Traits\IdTrait;
use TwinElements\AdminBundle\Entity\Traits\TitleInterface;
use TwinElements\AdminBundle\Entity\Traits\TitleTrait;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\Table(name="menu_category")
 * @ORM\Entity(repositoryClass="TwinElements\MenuBundle\Repository\MenuCategoryRepository")
 */
class MenuCategory implements TitleInterface, BlameableInterface, TimestampableInterface
{
    use IdTrait,
        TitleTrait,
        BlameableTrait,
        TimestampableTrait;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=10)
     */
    private $code;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isCached = false;

    public function __toString()
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function isCached(): bool
    {
        return $this->isCached;
    }

    /**
     * @param bool $isCached
     */
    public function setIsCached(bool $isCached): void
    {
        $this->isCached = $isCached;
    }
}

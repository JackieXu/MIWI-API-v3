<?php


namespace AppBundle\Entity;

/**
 * Class Interest
 *
 * @package AppBundle\Entity
 */
class Interest
{
    protected $title;
    protected $isDefault;

    public function __construct($title, $isDefault = false)
    {
        $this->title = $title;
        $this->isDefault = $isDefault;
    }
}
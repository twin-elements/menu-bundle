<?php

namespace TwinElements\MenuBundle;

use TwinElements\AdminBundle\Menu\AdminMenuInterface;
use TwinElements\AdminBundle\Menu\MenuItem;

class AdminMenu implements AdminMenuInterface
{
    public function getItems()
    {
        return [
            MenuItem::newInstance('cms.menu', 'menucategory_index', [], 15),
        ];
    }
}

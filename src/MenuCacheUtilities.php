<?php

namespace TwinElements\MenuBundle;

class MenuCacheUtilities
{
    public static function getCacheName(int $id, string $locale)
    {
        return 'menu_category_' . $id . '_' . $locale;
    }
}

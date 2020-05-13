<?php

namespace YWA\Helpers\Core;

class TemplateLoader
{
    public static $VIEWS_PATH;

    public static $BLOCKS_PATH;


    public function __construct(PathFinder $locator)
    {
        self::$VIEWS_PATH = $locator->getViewsPath();
        self::$BLOCKS_PATH = $locator->getBlocksPath();
    }

    public function display($view, array $args)
    {
        extract($args);

        include self::$VIEWS_PATH . $view . '.php';
    }
}

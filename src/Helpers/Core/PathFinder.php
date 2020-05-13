<?php

namespace YWA\Helpers\Core;



class PathFinder
{
    private $viewPath;

    private $srcPath;

    private $blocksPath;

    private $assetsUrl;

    public function __construct()
    {
        $this->viewPath = plugin_dir_path(dirname(__FILE__, 2)) . 'Views/';
        $this->srcPath = plugin_dir_path(dirname(__FILE__, 2));
        $this->blocksPath = plugin_dir_path(dirname(__FILE__, 2)) . 'Views/Blocks/';
        $this->assetsUrl = plugin_dir_url(dirname(__FILE__, 3)) . 'assets/';
    }

    /**
     * @return string
     */
    public function getViewsPath()
    {
        return $this->viewPath;
    }

    /**
     * @return string
     */
    public function getSrcPath()
    {
        return $this->srcPath;
    }

    /**
     * @return mixed
     */
    public function getBlocksPath()
    {
        return $this->blocksPath;
    }
    /**
     * @return mixed
     */
    public function getAssetsUrl()
    {
        return $this->assetsUrl;
    }
}

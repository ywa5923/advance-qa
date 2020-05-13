<?php
namespace YWA\Helpers\Core;
use YWA\Helpers\Core\TemplateLoader;
use YWA\Helpers\Core\PathFinder;

class BaseAction
{

    public $template;
    public function __construct()
    {
        $this->template=new TemplateLoader(new PathFinder);
    }


}

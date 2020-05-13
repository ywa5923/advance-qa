<?php

namespace YWA\Helpers\Core;

use YWA\Helpers\File\ScanDirectory;
use ReflectionClass;
use YWA\Actions\ActionInterface;

class ActionsLoader
{

    public function loadAllActions()
    {

        $actionsDir = plugin_dir_path(dirname(__FILE__, 2)) . 'Actions';

        $scan = new ScanDirectory($actionsDir);

        foreach ($scan->by('php') as $file) {

            if (strpos($file, 'Interface') === FALSE) {

                $obj = $this->makeObject($file);

                if ($obj instanceof ActionInterface) {
                    $obj->init();
                }
            }
        }
    }

    /**
     * @param string $classString A string which contains class name with namespace
     * @return ActionInterface
     */
    public function makeObject($classString)
    {
        $fullClassName = 'YWA\Actions\\' . $classString;

        return new $fullClassName();
    }
}

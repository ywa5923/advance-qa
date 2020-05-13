<?php

namespace YWA\Helpers\File;

use FilesystemIterator;
use RegexIterator;

class ScanDirectory
{

    protected $dirPath;
    public function __construct($dirPath)
    {
        $this->dirPath = $dirPath;
    }
    public function by(string $extension)
    {

        $splFiles = new FilesystemIterator($this->dirPath);

        $splFiles = new RegexIterator($splFiles, "/\.({$extension})$/");

        $retArray = [];

        foreach ($splFiles as $splFile) {
            //yield $splFile->getBasename('.' . $extension);
            $retArray[] = $splFile->getBasename('.' . $extension);
        }

        return $retArray;
    }
}

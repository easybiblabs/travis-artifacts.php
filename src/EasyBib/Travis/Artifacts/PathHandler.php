<?php
namespace EasyBib\Travis\Artifacts;

class PathHandler
{
    private $paths;
    private $root;

    public function __construct($root, array $paths)
    {
        $this->root = $root;
        $this->paths = $paths;
    }

    public function getPaths()
    {
        foreach ($this->paths as &$path) {

            $path = $this->fixTrailingSlash($path);
            if (!empty($this->root)) {
                $path = $this->fixTrailingSlash($this->root) . $path;
            }
        }

        return $this->paths;
    }

    public function transform($target, $relativePath)
    {
        $key = $this->fixTrailingSlash($target) . $relativePath;

        // strip to make sure
        $key = str_replace('//', '/', $key);
        return $key;
    }

    private function fixTrailingSlash($path)
    {
        return rtrim($path, '/') . '/';
    }
}

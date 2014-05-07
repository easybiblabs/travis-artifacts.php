<?php
namespace EasyBib\Travis\Artifacts;

use Aws\S3;
use Symfony\Component\Console\Output;
use Symfony\Component\Finder;

class Uploader
{
    /**
     * @var Output\OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $root;

    /**
     * @var S3\S3Client
     */
    private $s3;

    /**
     * @param S3\S3Client            $s3
     * @param Output\OutputInterface $output
     * @param string                 $root
     *
     * @return self
     */
    public function __construct(S3\S3Client $s3, Output\OutputInterface $output, $root)
    {
        $this->s3 = $s3;
        $this->output = $output;
        $this->root = $root;
    }

    /**
     * Upload paths to target.
     *
     * @param array $paths
     * @param       $target
     */
    public function doUpload(array $paths, $target)
    {
        $finder = new Finder\Finder();

        foreach ($paths as $path) {

            $path = rtrim($path, '/') . '/';
            $this->output->writeln("<info>Trying to upload from: {$path}</info>");

            $finder->files()->in($path);

            /** @var Finder\SplFileInfo $file */
            foreach ($finder as $file) {

                $key = $this->getKey($target, $file->getRealPath());
                var_dump($key);
                continue;

                $this->s3->putObject([
                        'Acl' => 'private',
                        'Bucket' => getenv('ARTIFACTS_S3_BUCKET'),
                        'Key' => $target . $file->getAb(),
                        'SourceFile' => $file->getRealPath(),
                    ]);
                $this->output->write(".");
            }

            $this->output->writeln("");
        }
    }

    private function getKey($target, $absolutePathToFile)
    {
        $key = $target . $absolutePathToFile;
        if (!empty($this->root)) {
            $key = str_replace($this->root, '', $key);
        }
        $key = str_replace('//', '/', $key);
        return $key;
    }
}

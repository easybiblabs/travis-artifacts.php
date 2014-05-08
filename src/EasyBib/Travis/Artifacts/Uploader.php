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
     * @var string Prepended for relative paths.
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
    public function __construct(S3\S3Client $s3, Output\OutputInterface $output, $root = '')
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
        $handler = new PathHandler($this->root, $paths);

        foreach ($handler->getPaths() as $path) {

            $path = rtrim($path, '/') . '/';
            $this->output->writeln("<info>Trying to upload from: {$path}</info>");

            $finder->files()->in($path);

            /** @var Finder\SplFileInfo $file */
            foreach ($finder as $file) {

                $objectKey = $handler->transform($target, $file->getRelativePathname());
                //var_dump($objectKey);

                $result = $this->s3->putObject([
                    'Acl' => 'private',
                    'Bucket' => getenv('ARTIFACTS_S3_BUCKET'),
                    'Key' => $objectKey,
                    'SourceFile' => $file->getRealPath(),
                ]);

                //var_dump($result->toArray()['ObjectURL']);

                $this->output->write(".");
            }

            $this->output->writeln("");
        }
    }
}

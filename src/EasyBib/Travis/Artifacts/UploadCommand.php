<?php
namespace EasyBib\Travis\Artifacts;

use Aws\S3;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder;

class UploadCommand extends Command
{
    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this
            ->setName('upload')
            ->setDescription('Upload artifacts to Amazon S3.')
            ->addOption(
                'target-path',
                't',
                Input\InputOption::VALUE_OPTIONAL,
                'Default: artifacts'
            )
            ->addOption(
                'path',
                'p',
                Input\InputOption::VALUE_REQUIRED | Input\InputOption::VALUE_IS_ARRAY,
                'Paths to upload from: --path foo --path bar --path foobar'
            )
        ;
    }

    protected function execute(Input\InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getOption('path');
        $target = $input->getOption('target-path');
        if (empty($target)) {
            $target = 'artifacts/'; // default
        } else {
            $target = rtrim($target, '/') . '/';
        }

        $this->checkSetUp($paths);

        $this->output = $output;

        $this->output->writeln("<info>Trying to start upload.</info>");

        $this->upload($paths, $target);
    }

    /**
     * Checks if environment variables are set and if paths and so on are valid.
     *
     * @throws LogicException
     */
    private function checkSetUp(array $paths)
    {
        foreach ($paths as $path) {
            if (!file_exists($path) || !is_readable($path)) {
                throw new LogicException("{$path} does not exist or is not readable.");
            }
        }

        $envs = [
            'ARTIFACTS_S3_BUCKET',
            'ARTIFACTS_AWS_REGION', //us-east-1
            'ARTIFACTS_AWS_ACCESS_KEY_ID',
            'ARTIFACTS_AWS_SECRET_ACCESS_KEY',
        ];

        foreach ($envs as $env) {
            if (false === getenv($env)) {
                if ('ARTIFACTS_AWS_REGION' === $env) {
                    continue;
                }
                throw new LogicException("$env environment variable is not set, but required.");
            }
        }
    }

    /**
     * Upload files using the AWS PHP SDK.
     * 
     * @param array $paths
     * @param string $target
     *
     * @throws LogicException
     */
    private function upload($paths, $target)
    {
        $region = getenv('ARTIFACTS_AWS_REGION');
        if (empty($region)) {
            $region = 'us-east-1';
        }

        // Instantiate the client.
        $s3 = S3\S3Client::factory([
            'key' => getenv('ARTIFACTS_AWS_ACCESS_KEY_ID'),
            'region' => $region,
            'secret' => getenv('ARTIFACTS_AWS_SECRET_ACCESS_KEY'),
        ]);

        $finder = new Finder\Finder();

        foreach ($paths as $path) {

            $path = rtrim($path, '/') . '/';
            $this->output->writeln("<info>Trying to upload from: {$path}</info>");

            $finder->files()->in($path);

            /** @var Finder\SplFileInfo $file */
            foreach ($finder as $file) {
                $s3->putObject([
                    'Acl' => 'private',
                    'Bucket' => getenv('ARTIFACTS_S3_BUCKET'),
                    'Key' => $target . $path . $file->getRelativePathname(),
                    'SourceFile' => $file->getRealPath(),
                ]);
                $this->output->write(".");
            }

            $this->output->writeln("");
        }
    }
}

<?php
namespace EasyBib\Travis\Artifacts;

use Aws\S3;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->addOption(
                'root',
                'r',
                Input\InputOption::VALUE_OPTIONAL,
                'Strip prefix from uploaded file'
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

        $root = $input->getOption('root');

        $validator = new Validator();
        $validator->validatePaths($paths);
        $validator->validateEnvironment();

        $this->output = $output;

        $this->output->writeln("<info>Trying to start upload.</info>");

        $this->upload($paths, $target, $root);
    }

    /**
     * Upload files using the AWS PHP SDK.
     *
     * @param array  $paths
     * @param string $target
     * @param string $root
     *
     * @throws LogicException
     */
    private function upload($paths, $target, $root)
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

        $uploader = new Uploader($s3, $this->output, $root);
        $uploader->doUpload($paths, $target);


    }
}

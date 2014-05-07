<?php
namespace EasyBib\Travis\Artifacts;

use LogicException;

class Validator
{
    /**
     * Checks if paths are valid.
     *
     * @param array $paths
     *
     * @throws \LogicException
     */
    public function validatePaths(array $paths)
    {
        foreach ($paths as $path) {
            if (!file_exists($path) || !is_readable($path)) {
                throw new LogicException("{$path} does not exist or is not readable.");
            }
        }
    }

    /**
     * Checks if environment variables are set.
     *
     * @throws LogicException
     */
    public function validateEnvironment()
    {
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
}

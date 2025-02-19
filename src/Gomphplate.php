<?php

declare(strict_types=1);

namespace Arc\Gomphplate;

use Arc\Gomphplate\Exceptions\GomplateExecutionException;
use Arc\Gomphplate\Exceptions\GomplateNotFoundException;
use Arc\Gomphplate\Exceptions\InvalidDataException;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Gomphplate
{
    /**
     * Render a provided yaml template with the provided json data
     *
     * @param string $template The yaml template string
     * @param string $data The json data string
     * @return string The rendered yaml string
     * @throws Exception|GomplateExecutionException|GomplateNotFoundException|InvalidDataException
     */
    public static function renderYaml(string $template, string $data): string
    {
        if (!self::isJsonValid($data)) {
            throw new InvalidDataException();
        }

        $gomplate = self::getGomplateBinary();

        $filesystem = new Filesystem();

        try {
            $templateFile = $filesystem->tempnam(sys_get_temp_dir(), 'laravel_gomplate_', '.yaml');
            $filesystem->dumpFile($templateFile, $template);

            $dataFile = $filesystem->tempnam(sys_get_temp_dir(), 'laravel_gomplate_', '.json');
            $filesystem->dumpFile($dataFile, $data);

            $process = new Process([$gomplate, '-c', ".=$dataFile", '-f', $templateFile]);
            $process->run();
        } finally {
            $filesystem->remove($templateFile);
            $filesystem->remove($dataFile);
        }

        if ($process->getExitCode() !== 0) {
            throw new GomplateExecutionException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * Get the path to the gomplate binary
     *
     * @throws GomplateNotFoundException
     */
    private static function getGomplateBinary(): string
    {
        $path = getenv('GOMPLATE_PATH');

        if ($path && file_exists($path)) {
            return $path;
        }

        $process = new Process(['which', 'gomplate']);
        $process->run();

        if ($process->getExitCode() !== 0) {
            throw new GomplateNotFoundException();
        }

        return $process->getOutput();
    }

    private static function isJsonValid(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

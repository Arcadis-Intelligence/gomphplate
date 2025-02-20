<?php

declare(strict_types=1);

namespace ArcadisIntelligence\Gomphplate;

use ArcadisIntelligence\Gomphplate\Exceptions\GomplateExecutionException;
use ArcadisIntelligence\Gomphplate\Exceptions\GomplateNotFoundException;
use ArcadisIntelligence\Gomphplate\Exceptions\InvalidDataException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Gomphplate
{
    private const FILE_PREFIX = 'laravel_gomplate_';

    /**
     * Render a provided yaml template with the provided json data
     *
     * @param string $template The yaml template string
     * @param array<mixed> $json The json data array to render the template with
     * @return string The rendered yaml string
     * @throws GomplateExecutionException
     * @throws GomplateNotFoundException
     * @throws InvalidDataException
     */
    public static function renderYamlFromString(string $template, array $json): string
    {
        $data = json_encode($json);

        if ($data === false) {
            throw new InvalidDataException();
        }

        if (!self::isJsonValid($data)) {
            throw new InvalidDataException();
        }

        $gomplate = self::getGomplateBinary();
        $filesystem = new Filesystem();

        try {
            $templateFile = self::createTempFile($template, '.yaml');
            $dataFile = self::createTempFile($data, '.json');

            $process = new Process([$gomplate, '-c', ".=$dataFile", '-f', $templateFile]);
            $process->run();
        } finally {
            if (isset($templateFile)) {
                $filesystem->remove($templateFile);
            }

            if (isset($dataFile)) {
                $filesystem->remove($dataFile);
            }
        }

        if ($process->getExitCode() !== 0) {
            throw new GomplateExecutionException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * @param string $filePath The path to the yaml template file
     * @param array<mixed> $json The json data array to render the template with
     * @throws InvalidDataException
     * @throws GomplateExecutionException
     * @throws GomplateNotFoundException
     */
    public static function renderYamlFromFile(string $filePath, array $json): string
    {
        $data = json_encode($json);

        if ($data === false) {
            throw new InvalidDataException();
        }

        if (!self::isJsonValid($data)) {
            throw new InvalidDataException();
        }

        $gomplate = self::getGomplateBinary();

        try {
            $dataFile = self::createTempFile($data, '.json');

            $process = new Process([$gomplate, '-c', ".=$dataFile", '-f', $filePath]);
            $process->run();
        } finally {
            if (isset($dataFile)) {
                $filesystem = new Filesystem();
                $filesystem->remove($dataFile);
            }
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

    private static function createTempFile(string $contents, string $suffix): string
    {
        $filesystem = new Filesystem();
        $file = $filesystem->tempnam(sys_get_temp_dir(), self::FILE_PREFIX, $suffix);
        $filesystem->dumpFile($file, $contents);

        return $file;
    }
}

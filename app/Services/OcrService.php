<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OcrService
{
    public function extraireTexte(string $cheminImage): string
    {
        $process = new Process([
            'tesseract',
            $cheminImage,
            'stdout',
            '-l', 'fra+eng',
        ]);

        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }
}
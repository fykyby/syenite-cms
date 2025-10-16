<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private string $targetDirectory;
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->targetDirectory = ROOT_DIR . '/public/media/uploads';
        $this->filesystem = new Filesystem();
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo(
            $file->getClientOriginalName(),
            PATHINFO_FILENAME,
        );
        $safeFilename = preg_replace(
            '/[^a-zA-Z0-9_-]/',
            '_',
            $originalFilename,
        );

        $newFilename =
            $safeFilename .
            '-' .
            uniqid() .
            '.' .
            $file->getClientOriginalExtension();

        if (!is_dir($this->targetDirectory)) {
            mkdir($this->targetDirectory, 0777, true);
        }

        $file->move($this->targetDirectory, $newFilename);

        return $newFilename;
    }

    public function delete(string $filename): void
    {
        $filePath = "{$this->targetDirectory}/{$filename}";

        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }
    }
}

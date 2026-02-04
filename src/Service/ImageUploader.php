<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImageUploader
{
    private string $targetDirectory;
    private CacheManager $cacheManager;
    private FilterManager $filterManager;
    private LoaderInterface $loader;
    private Filesystem $filesystem;

    public function __construct(
        string $targetDirectory,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        LoaderInterface $loader,
    ) {
        $this->targetDirectory = $targetDirectory;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->loader = $loader;
        $this->filesystem = new Filesystem();
    }

    private function cacheFormats(string $filename, array $filters): void
    {
        $binary = $this->loader->find($filename);

        foreach ($filters as $filter) {
            $filtered = $this->filterManager->applyFilter($binary, $filter);

            $this->cacheManager->store($filtered, $filename, $filter);
        }
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
        $newFilename = $safeFilename . '-' . uniqid() . '.webp';

        $fullPath = "{$this->targetDirectory}/{$newFilename}";

        if (!is_dir($this->targetDirectory)) {
            mkdir($this->targetDirectory, 0777, true);
        }

        $this->convertAndSave(
            $file->getPathname(),
            $fullPath,
            $file->getClientOriginalExtension(),
        );

        $this->cacheFormats($newFilename, ['thumbnail', 'medium', 'large']);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        return $newFilename;
    }

    public function delete(string $filename): void
    {
        $filePath = "{$this->targetDirectory}/{$filename}";

        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }

        try {
            $this->cacheManager->remove($filename);
        } catch (\Exception $e) {
        }
    }

    private function convertAndSave(
        string $inputPath,
        string $outputPath,
        string $extension,
    ): void {
        $ext = strtolower($extension);

        if ($ext === 'webp') {
            if (!copy($inputPath, $outputPath)) {
                throw new \Exception('Failed to copy file');
            }
            return;
        }

        $image = null;
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($inputPath);
                break;
            case 'png':
                $image = imagecreatefrompng($inputPath);

                if (!imageistruecolor($image)) {
                    $trueColor = imagecreatetruecolor(
                        imagesx($image),
                        imagesy($image),
                    );

                    imagealphablending($trueColor, false);
                    imagesavealpha($trueColor, true);

                    $transparent = imagecolorallocatealpha(
                        $trueColor,
                        0,
                        0,
                        0,
                        127,
                    );

                    imagefill($trueColor, 0, 0, $transparent);

                    imagecopy(
                        $trueColor,
                        $image,
                        0,
                        0,
                        0,
                        0,
                        imagesx($image),
                        imagesy($image),
                    );

                    imagedestroy($image);
                    $image = $trueColor;
                }

                imagealphablending($image, false);
                imagesavealpha($image, true);
                break;
            default:
                throw new \Exception("Unsupported format: $ext");
        }

        if (!imagewebp($image, $outputPath, 80)) {
            throw new \Exception('Failed to convert image to WebP');
        }

        imagedestroy($image);
    }
}

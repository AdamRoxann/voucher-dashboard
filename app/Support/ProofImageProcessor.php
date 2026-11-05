<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Checks uploaded proof photos by their contents and stores them as optimized
 * JPEGs so that browsers can display them consistently.
 */
class ProofImageProcessor
{
    private const ALLOWED_FORMATS = [
        'JPEG', 'JPG', 'PNG', 'WEBP', 'GIF', 'BMP', 'TIFF', 'TIF',
        'JP2', 'J2K', 'TGA', 'ICO', 'PNM', 'PPM', 'PGM', 'PBM', 'PCX',
    ];

    private const HEIF_MIME_TYPES = [
        'image/heic',
        'image/heif',
        'image/avif',
        'image/heic-sequence',
        'image/heif-sequence',
        'image/avif-sequence',
        'image/x-heic',
        'image/x-heif',
    ];

    private const HEIF_EXTENSIONS = ['heic', 'heif', 'avif'];

    public function store(UploadedFile $file): string
    {
        $inputPath = $file->getRealPath();

        if (!$inputPath || !is_file($inputPath) || !$file->isValid()) {
            $this->reject();
        }

        $temporaryBase = tempnam(sys_get_temp_dir(), 'claim-proof-');

        if ($temporaryBase === false) {
            $this->reject();
        }

        $temporarySource = $temporaryBase . '-source.jpg';
        $temporaryJpeg = $temporaryBase . '-final.jpg';
        $storedPath = 'voucher-proofs/' . Str::uuid() . '.jpg';

        try {
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($inputPath) ?: '';
            $extension = strtolower($file->getClientOriginalExtension());
            $isHeif = in_array($mime, self::HEIF_MIME_TYPES, true)
                || in_array($extension, self::HEIF_EXTENSIONS, true);

            $sourcePath = $inputPath;

            if ($isHeif) {
                // libheif handles iPhone HEIC/HEIF photos and AVIF files.
                $decode = new Process([
                    'heif-convert',
                    '-q',
                    '95',
                    $inputPath,
                    $temporarySource,
                ]);
                $decode->setTimeout(45);
                $decode->run();

                if (!$decode->isSuccessful() || !is_file($temporarySource) || filesize($temporarySource) < 1) {
                    $this->reject();
                }

                $sourcePath = $temporarySource;
            }

            // Inspect the decoded content, not the filename or its extension.
            $identify = new Process([
                'identify',
                '-ping',
                '-format',
                '%m %w %h',
                $sourcePath . '[0]',
            ]);
            $identify->setTimeout(20);
            $identify->run();

            if (!$identify->isSuccessful()) {
                $this->reject();
            }

            $metadata = preg_split('/\s+/', trim($identify->getOutput()));
            $format = strtoupper($metadata[0] ?? '');
            $width = (int) ($metadata[1] ?? 0);
            $height = (int) ($metadata[2] ?? 0);

            $allowedFormats = $isHeif ? ['JPEG', 'JPG'] : self::ALLOWED_FORMATS;

            if (
                !in_array($format, $allowedFormats, true)
                || $width < 1
                || $height < 1
                || $width > 12000
                || $height > 12000
                || ($width * $height) > 60000000
            ) {
                $this->reject();
            }

            // Animated inputs are reduced to their first frame for proof display.
            $convert = new Process([
                'convert',
                '-limit', 'memory', '192MiB',
                '-limit', 'map', '384MiB',
                $sourcePath . '[0]',
                '-auto-orient',
                '-thumbnail', '2400x2400>',
                '-strip',
                '-quality', '88',
                'jpeg:' . $temporaryJpeg,
            ]);
            $convert->setTimeout(45);
            $convert->run();

            if (!$convert->isSuccessful() || !is_file($temporaryJpeg) || filesize($temporaryJpeg) < 1) {
                $this->reject();
            }

            // Verify the generated file itself before writing it to public storage.
            $outputCheck = new Process([
                'identify',
                '-ping',
                '-format',
                '%m %w %h',
                $temporaryJpeg . '[0]',
            ]);
            $outputCheck->setTimeout(15);
            $outputCheck->run();

            $outputMetadata = preg_split('/\s+/', trim($outputCheck->getOutput()));
            $outputWidth = (int) ($outputMetadata[1] ?? 0);
            $outputHeight = (int) ($outputMetadata[2] ?? 0);

            if (
                !$outputCheck->isSuccessful()
                || strtoupper($outputMetadata[0] ?? '') !== 'JPEG'
                || $outputWidth < 1
                || $outputHeight < 1
                || $outputWidth > 2400
                || $outputHeight > 2400
            ) {
                $this->reject();
            }

            $contents = file_get_contents($temporaryJpeg);

            if ($contents === false || !Storage::disk('public')->put($storedPath, $contents)) {
                $this->reject();
            }

            return $storedPath;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->reject();
        } finally {
            foreach ([$temporaryBase, $temporarySource, $temporaryJpeg] as $temporaryFile) {
                if (is_file($temporaryFile)) {
                    @unlink($temporaryFile);
                }
            }
        }
    }

    private function reject(): never
    {
        throw ValidationException::withMessages([
            'proof_image' => 'The proof image could not be processed. Please upload a valid photo in a common image format (JPG, PNG, WEBP, GIF, BMP, TIFF, AVIF or HEIC/HEIF).',
        ]);
    }
}

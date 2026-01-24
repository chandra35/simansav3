<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;

class ImageCompressionHelper
{
    /**
     * Kompresi gambar jika melebihi ukuran maksimal
     * 
     * @param UploadedFile $file
     * @param float $maxSizeMB Ukuran maksimal dalam MB (default: 2)
     * @return UploadedFile File original atau compressed
     */
    public static function compressImage(UploadedFile $file, $maxSizeMB = null)
    {
        // Get config
        $config = config('simansa.dokumen_compression', []);
        $enabled = $config['enabled'] ?? true;
        
        // Jika compression disabled, return original
        if (!$enabled) {
            return $file;
        }
        
        $maxSizeMB = $maxSizeMB ?? ($config['max_size_mb'] ?? 2);
        $quality = $config['image_quality'] ?? 85;
        $maxWidth = $config['max_width'] ?? 1920;
        $maxHeight = $config['max_height'] ?? 1920;
        $convertPngToJpg = $config['convert_png_to_jpg'] ?? true;
        
        // Cek apakah perlu dikompresi
        if (!self::shouldCompress($file, $maxSizeMB)) {
            return $file;
        }
        
        try {
            // Load image
            $image = Image::read($file->getRealPath());
            
            // Get original dimensions
            $width = $image->width();
            $height = $image->height();
            
            // Resize jika terlalu besar (maintain aspect ratio)
            if ($width > $maxWidth || $height > $maxHeight) {
                $image->scale(width: $maxWidth, height: $maxHeight);
                Log::info("Image resized from {$width}x{$height} to {$image->width()}x{$image->height()}");
            }
            
            // Tentukan format output
            $extension = strtolower($file->getClientOriginalExtension());
            $outputFormat = $extension;
            
            // Konversi PNG ke JPG jika file besar (PNG biasanya lebih besar)
            if ($convertPngToJpg && $extension === 'png' && $file->getSize() > (1024 * 1024)) {
                $outputFormat = 'jpg';
                Log::info("Converting PNG to JPG for better compression");
            }
            
            // Generate temporary file path
            $tempPath = sys_get_temp_dir() . '/' . uniqid('compressed_') . '.' . $outputFormat;
            
            // Save compressed image
            if ($outputFormat === 'jpg' || $outputFormat === 'jpeg') {
                $image->toJpeg($quality)->save($tempPath);
            } elseif ($outputFormat === 'png') {
                // PNG menggunakan compression level 0-9 (9 = best compression)
                $pngQuality = (int) round(($quality / 100) * 9);
                $image->toPng()->save($tempPath);
            } else {
                // Format lain (webp, gif, dll)
                $image->save($tempPath, $quality);
            }
            
            // Get file info
            $originalSize = $file->getSize();
            $compressedSize = filesize($tempPath);
            $savedPercentage = round((($originalSize - $compressedSize) / $originalSize) * 100, 2);
            
            Log::info("Image compressed", [
                'original_size' => self::formatBytes($originalSize),
                'compressed_size' => self::formatBytes($compressedSize),
                'saved' => $savedPercentage . '%',
                'format' => $outputFormat,
                'quality' => $quality
            ]);
            
            // Create new UploadedFile dari compressed image
            $compressedFile = new UploadedFile(
                $tempPath,
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                null,
                true // test mode (tidak cek file exists)
            );
            
            return $compressedFile;
            
        } catch (\Exception $e) {
            // Jika error, return original file
            Log::error("Image compression failed: " . $e->getMessage());
            return $file;
        }
    }
    
    /**
     * Cek apakah file perlu dikompresi
     * 
     * @param UploadedFile $file
     * @param float $maxSizeMB
     * @return bool
     */
    public static function shouldCompress(UploadedFile $file, $maxSizeMB = 2): bool
    {
        // Cek ukuran file
        $fileSizeMB = $file->getSize() / (1024 * 1024);
        if ($fileSizeMB <= $maxSizeMB) {
            return false;
        }
        
        // Cek apakah file adalah image
        $mimeType = $file->getMimeType();
        $imageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($mimeType, $imageTypes)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Format bytes ke human readable
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Get info kompresi untuk monitoring
     * 
     * @return array
     */
    public static function getCompressionInfo(): array
    {
        $config = config('simansa.dokumen_compression', []);
        
        return [
            'enabled' => $config['enabled'] ?? true,
            'max_size_mb' => $config['max_size_mb'] ?? 2,
            'image_quality' => $config['image_quality'] ?? 85,
            'max_dimensions' => ($config['max_width'] ?? 1920) . 'x' . ($config['max_height'] ?? 1920),
            'convert_png_to_jpg' => $config['convert_png_to_jpg'] ?? true,
        ];
    }
}

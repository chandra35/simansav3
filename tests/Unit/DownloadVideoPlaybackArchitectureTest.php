<?php

namespace Tests\Unit;

use App\Models\Download;
use PHPUnit\Framework\TestCase;

class DownloadVideoPlaybackArchitectureTest extends TestCase
{
    public function test_supported_video_files_are_detected_for_public_playback(): void
    {
        $video = new Download(['file_extension' => 'mp4', 'mime_type' => 'video/mp4']);
        $document = new Download(['file_extension' => 'pdf', 'mime_type' => 'application/pdf']);

        $this->assertTrue($video->is_video);
        $this->assertFalse($document->is_video);
    }

    public function test_public_routes_include_play_and_local_stream_endpoints(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');

        $this->assertStringContainsString("Route::get('/play/{download:slug}'", $routes);
        $this->assertStringContainsString("->name('downloads.play')", $routes);
        $this->assertStringContainsString("->name('downloads.stream')", $routes);
    }
}

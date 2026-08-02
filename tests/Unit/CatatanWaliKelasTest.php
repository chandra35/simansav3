<?php

namespace Tests\Unit;

use App\Models\CatatanWaliKelas;
use PHPUnit\Framework\TestCase;

class CatatanWaliKelasTest extends TestCase
{
    public function test_visual_note_sanitizer_keeps_safe_formatting_and_unicode(): void
    {
        $content = '<p style="color:red" onclick="alert(1)"><strong>Hebat 👏</strong> ✅</p>'
            .'<script>alert("xss")</script><img src=x onerror=alert(2)>';

        $sanitized = CatatanWaliKelas::sanitizeContent($content);

        $this->assertStringContainsString('<p><strong>Hebat 👏</strong> ✅</p>', $sanitized);
        $this->assertStringNotContainsString('onclick', $sanitized);
        $this->assertStringNotContainsString('<script', $sanitized);
        $this->assertStringNotContainsString('<img', $sanitized);
    }

    public function test_visual_note_sanitizer_supports_lists_and_symbols(): void
    {
        $sanitized = CatatanWaliKelas::sanitizeContent('<ul class="list"><li>Disiplin ✓</li><li>Prestasi ★</li></ul>');

        $this->assertSame('<ul><li>Disiplin ✓</li><li>Prestasi ★</li></ul>', $sanitized);
    }

    public function test_plain_legacy_note_is_escaped_and_keeps_line_breaks(): void
    {
        $note = new CatatanWaliKelas(['catatan' => "Baris satu & aman\nBaris dua"]);

        $this->assertSame("Baris satu &amp; aman<br />\nBaris dua", $note->catatan_html);
    }
}

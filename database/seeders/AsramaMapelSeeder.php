<?php

namespace Database\Seeders;

use App\Models\AsramaMapel;
use Illuminate\Database\Seeder;

class AsramaMapelSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['Bahasa Arab', 'اللغة العربية'],
            ['Muhadatsah', 'المحادثة'],
            ['B. Arab Syafahi', 'اللغة العربية الشفهية'],
            ["Al-Imla'", 'الإملاء العربي'],
            ["Al-Insya'", 'الإنشاء العربي'],
            ['Kosakata B. Arab', 'المفردات العربية'],
            ['Nahwu', 'النحو'],
            ['Sorof', 'الصرف'],
            ['Mutholaah', 'المطالعة'],
            ['Mahfudzot', 'المحفوظات'],
            ['Tauhid/Aqidah', 'التوحيد / العقيدة'],
            ['Tafsir', 'التفسير'],
            ['Hadits', 'الحديث'],
            ['Fiqih', 'الفقه'],
            ['Fiqih Syafahi', 'الفقه الشفهي'],
            ['Ushul Fiqih', 'علم أصول الفقه'],
            ['English Lesson', 'اللغة الإنجليزية'],
            ['Conversation', 'المحادثة الإنجليزية'],
            ['Oral Examination', 'اللغة الإنجليزية الشفهية'],
            ['Vocabularies', 'المفردات الإنجليزية'],
            ["Al-Qur'an/Tajwid", 'القرآن والتجويد'],
            ["Tahfizhul Qur'an", 'تحفيظ القرآن'],
            ["Tarjamatul Qur'an", 'ترجمة القرآن'],
            ['Pidato B. Arab', 'الخطابة العربية'],
            ['Pidato B. Indonesia', 'الخطابة الإندونيسية'],
            ['English Speech', 'الخطابة الإنجليزية'],
        ];

        foreach ($subjects as $index => [$latin, $arabic]) {
            $subject = AsramaMapel::withTrashed()->firstOrNew([
                'kode' => 'ASR-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            ]);
            if ($subject->trashed()) {
                $subject->restore();
            }
            $subject->fill([
                'asrama_id' => null,
                'nama_latin' => $latin,
                'nama_arab' => $arabic,
                'kategori' => 'Pelajaran Asrama',
                'skala_maksimum' => 10,
                'nilai_minimum' => 6,
                'urutan' => $index + 1,
                'is_active' => true,
            ])->save();
        }
    }
}

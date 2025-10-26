<?php

if (!function_exists('renderKopSurat')) {
    /**
     * Render Kop Surat untuk PDF/Print
     * 
     * @param bool $showLogos - Tampilkan logo kemenag dan sekolah
     * @return string HTML kop surat
     */
    function renderKopSurat($showLogos = true)
    {
        $setting = \App\Models\AppSetting::getInstance();
        
        if (!$setting) {
            return '<!-- No setting found -->';
        }
        
        // Mode Custom Upload
        if ($setting->kop_mode === 'custom' && $setting->kop_surat_custom_path) {
            $imagePath = public_path('storage/' . $setting->kop_surat_custom_path);
            
            if (file_exists($imagePath)) {
                return '<img src="' . $imagePath . '" style="width:100%; max-height: ' . $setting->kop_height . 'mm;">';
            }
        }
        
        // Mode Builder - Generate dari JSON config
        $config = $setting->kop_surat_config;
        
        if (!$config || !isset($config['elements'])) {
            return '<!-- No kop config found -->';
        }
        
        $html = '<table width="100%" style="margin-bottom: 10px; font-family: Arial, sans-serif;">';
        
        if ($showLogos) {
            $html .= '<tr>';
            
            // Logo Kemenag (Kiri)
            $html .= '<td width="15%" align="center" valign="top">';
            if ($setting->logo_kemenag_path) {
                $logoKemenagPath = public_path('storage/' . $setting->logo_kemenag_path);
                if (file_exists($logoKemenagPath)) {
                    $html .= '<img src="' . $logoKemenagPath . '" style="height: 80px;">';
                }
            }
            $html .= '</td>';
            
            // Content (Tengah)
            $html .= '<td width="70%" align="center" valign="top">';
        } else {
            $html .= '<tr><td align="center" valign="top">';
        }
        
        // Render elements dari JSON config (sorted by order)
        $elements = collect($config['elements'])->sortBy('order');
        
        foreach ($elements as $element) {
            if ($element['type'] === 'text') {
                $style = $element['style'];
                $html .= '<div style="';
                $html .= 'font-size: ' . ($style['fontSize'] ?? '14') . 'px; ';
                $html .= 'font-weight: ' . ($style['fontWeight'] ?? 'normal') . '; ';
                $html .= 'text-align: ' . ($style['textAlign'] ?? 'center') . '; ';
                $html .= 'margin-bottom: ' . ($style['marginBottom'] ?? '2') . 'px; ';
                $html .= 'line-height: 1.4;';
                $html .= '">';
                $html .= htmlspecialchars($element['content']);
                $html .= '</div>';
            } elseif ($element['type'] === 'divider') {
                $style = $element['style'];
                $html .= '<hr style="';
                $html .= 'border: none; ';
                $html .= 'border-top-style: ' . ($style['borderStyle'] ?? 'solid') . '; ';
                $html .= 'border-top-width: ' . ($style['borderWidth'] ?? '1') . 'px; ';
                $html .= 'border-top-color: ' . ($style['borderColor'] ?? '#000000') . '; ';
                $html .= 'margin-top: ' . ($style['marginTop'] ?? '5') . 'px; ';
                $html .= 'margin-bottom: ' . ($style['marginBottom'] ?? '5') . 'px;';
                $html .= '">';
            }
        }
        
        if ($showLogos) {
            $html .= '</td>';
            
            // Logo Sekolah (Kanan)
            $html .= '<td width="15%" align="center" valign="top">';
            if ($setting->logo_sekolah_path) {
                $logoSekolahPath = public_path('storage/' . $setting->logo_sekolah_path);
                if (file_exists($logoSekolahPath)) {
                    $html .= '<img src="' . $logoSekolahPath . '" style="height: 80px;">';
                }
            }
            $html .= '</td>';
        }
        
        $html .= '</tr></table>';
        
        return $html;
    }
}

if (!function_exists('renderTTDKepalaSekolah')) {
    /**
     * Render TTD Kepala Sekolah untuk surat
     * 
     * @param string|null $tanggal - Tanggal surat (default: hari ini)
     * @param string|null $kota - Nama kota (default: dari setting)
     * @return string HTML TTD
     */
    function renderTTDKepalaSekolah($tanggal = null, $kota = null)
    {
        $setting = \App\Models\AppSetting::getInstance();
        $kepalaSekolah = $setting->getKepalaSekolah();
        
        if (!$tanggal) {
            $tanggal = now()->locale('id')->isoFormat('D MMMM Y');
        }
        
        if (!$kota) {
            $kota = $setting->kota?->name ?? '';
        }
        
        $html = '<table width="100%" style="margin-top: 30px; font-family: Arial, sans-serif;">';
        $html .= '<tr>';
        $html .= '<td width="50%"></td>';
        $html .= '<td width="50%" align="center">';
        $html .= '<p style="margin-bottom: 5px;">' . $kota . ', ' . $tanggal . '</p>';
        $html .= '<p style="margin-bottom: 5px; font-weight: bold;">Kepala Sekolah,</p>';
        $html .= '<br><br><br>'; // Space for signature
        
        if ($kepalaSekolah) {
            $html .= '<p style="margin-bottom: 2px; font-weight: bold;"><u>' . $kepalaSekolah->name . '</u></p>';
            $html .= '<p style="margin-bottom: 0;">NIP. ' . ($kepalaSekolah->gtk->nip ?? '-') . '</p>';
        } else {
            $html .= '<p style="margin-bottom: 2px; font-weight: bold;"><u>( ................................ )</u></p>';
            $html .= '<p style="margin-bottom: 0;">NIP. ................................</p>';
        }
        
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        return $html;
    }
}

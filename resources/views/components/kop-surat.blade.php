{{-- 
    Kop Surat Component
    Usage: <x-kop-surat :show-logos="true" />
--}}

@php
    $setting = \App\Models\AppSetting::getInstance();
    $config = $setting->kop_surat_config ?? [];
@endphp

@if($setting->kop_mode === 'custom' && $setting->kop_surat_custom_path)
    {{-- Mode Custom Upload --}}
    <div class="kop-surat-custom">
        <img src="{{ $setting->kop_surat_custom_url }}" alt="Kop Surat" style="width: 100%; max-height: {{ $setting->kop_height }}mm;">
    </div>
@else
    {{-- Mode Builder --}}
    <div class="kop-surat-builder">
        <table width="100%" style="margin-bottom: 10px; font-family: Arial, sans-serif;">
            <tr>
                @if($showLogos ?? true)
                    {{-- Logo Kemenag (Kiri) --}}
                    <td width="15%" align="center" valign="top">
                        @if($setting->logo_kemenag_path)
                            <img src="{{ $setting->logo_kemenag_url }}" alt="Logo Kemenag" style="height: 80px;">
                        @endif
                    </td>
                    
                    {{-- Content (Tengah) --}}
                    <td width="70%" align="center" valign="top">
                @else
                    <td align="center" valign="top">
                @endif
                
                {{-- Render elements dari JSON --}}
                @if(isset($config['elements']))
                    @foreach(collect($config['elements'])->sortBy('order') as $element)
                        @if($element['type'] === 'text')
                            <div style="
                                font-size: {{ $element['style']['fontSize'] ?? 14 }}px;
                                font-weight: {{ $element['style']['fontWeight'] ?? 'normal' }};
                                text-align: {{ $element['style']['textAlign'] ?? 'center' }};
                                margin-bottom: {{ $element['style']['marginBottom'] ?? 2 }}px;
                                line-height: 1.4;
                            ">
                                {{ $element['content'] }}
                            </div>
                        @elseif($element['type'] === 'divider')
                            <hr style="
                                border: none;
                                border-top-style: {{ $element['style']['borderStyle'] ?? 'solid' }};
                                border-top-width: {{ $element['style']['borderWidth'] ?? 1 }}px;
                                border-top-color: {{ $element['style']['borderColor'] ?? '#000000' }};
                                margin-top: {{ $element['style']['marginTop'] ?? 5 }}px;
                                margin-bottom: {{ $element['style']['marginBottom'] ?? 5 }}px;
                            ">
                        @endif
                    @endforeach
                @endif
                
                @if($showLogos ?? true)
                    </td>
                    
                    {{-- Logo Sekolah (Kanan) --}}
                    <td width="15%" align="center" valign="top">
                        @if($setting->logo_sekolah_path)
                            <img src="{{ $setting->logo_sekolah_url }}" alt="Logo Sekolah" style="height: 80px;">
                        @endif
                    </td>
                @else
                    </td>
                @endif
            </tr>
        </table>
    </div>
@endif

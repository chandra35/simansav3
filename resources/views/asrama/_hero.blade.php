<section class="asrama-hero">
    <div>
        <span class="asrama-hero__eyebrow"><i class="fas fa-mosque"></i> Modul Mandiri SIMANSA</span>
        <h2>{{ $heroTitle }}</h2>
        <p>{{ $heroDescription }}</p>
    </div>
    @isset($heroAction)
        <div class="asrama-hero__action">{!! $heroAction !!}</div>
    @endisset
</section>

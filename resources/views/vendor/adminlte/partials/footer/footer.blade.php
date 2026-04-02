<footer class="main-footer simansa-footer">
    <div class="simansa-footer__inner">
        <div class="simansa-footer__brand">
            <strong>SIMANSA</strong>
            <span>Sistem Informasi MAN 1 Metro</span>
        </div>
        <div class="simansa-footer__meta">
            <span>&copy; 2026 TIM IT MAN 1 Metro</span>
        </div>
    </div>

    @hasSection('footer')
        <div class="simansa-footer__extra">
            @yield('footer')
        </div>
    @endif
</footer>

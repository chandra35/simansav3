<style>
    .simansa-role-form-hero { border-radius: .8rem; box-shadow: 0 12px 28px rgba(37, 99, 235, .14); }
    .simansa-role-form-hero__eyebrow { margin: 0 0 .35rem; font-size: .74rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; opacity: .85; }
    .simansa-role-form-hero__title { margin: 0 0 .35rem; font-size: 1.55rem; font-weight: 700; }
    .simansa-role-form-hero__stat { display: grid; gap: .15rem; padding: .8rem .95rem; border: 1px solid rgba(255, 255, 255, .34); border-radius: .65rem; background: rgba(255, 255, 255, .13); }
    .simansa-role-form-hero__stat span { font-size: .7rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; opacity: .84; }
    .simansa-role-form-hero__stat strong { font-size: 1.65rem; line-height: 1; }
    .simansa-role-permission-accordion { display: grid; gap: .65rem; }
    .simansa-role-permission-module { margin: 0; border: 1px solid #dce5f2; border-radius: .75rem; box-shadow: none; overflow: hidden; }
    .simansa-role-permission-module__header { display: flex; align-items: center; gap: .65rem; padding: .45rem .65rem .45rem .75rem; background: #fff; }
    .simansa-role-permission-module__trigger { display: flex; align-items: center; min-width: 0; flex: 1 1 auto; gap: .7rem; padding: .15rem 0; color: #172036; text-align: left; text-decoration: none; }
    .simansa-role-permission-module__trigger:hover, .simansa-role-permission-module__trigger:focus { color: #1d4ed8; text-decoration: none; }
    .simansa-role-permission-module__icon { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: .55rem; background: #f1f5fb; flex: 0 0 34px; }
    .simansa-role-permission-module__title { display: grid; min-width: 0; gap: .1rem; }
    .simansa-role-permission-module__title strong { font-size: .9rem; }
    .simansa-role-permission-module__title small { color: #71809a; font-size: .75rem; line-height: 1.35; }
    .simansa-role-permission-module__count { margin-left: auto; color: #61708a; font-size: .72rem; font-weight: 700; white-space: nowrap; }
    .simansa-role-permission-module__chevron { color: #7c8ba1; font-size: .72rem; transition: transform .18s ease; }
    .simansa-role-permission-module__trigger[aria-expanded="true"] .simansa-role-permission-module__chevron { transform: rotate(180deg); }
    .simansa-role-permission-module__toggle { flex: 0 0 auto; min-height: 34px; white-space: nowrap; }
    .simansa-role-permission-module__body { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .35rem .5rem; padding: .5rem .65rem .6rem; border-top: 1px solid #e8eef6; background: #f8fbff; }
    .simansa-role-permission-row { min-width: 0; padding: .45rem .55rem .45rem 2rem; border-radius: .45rem; }
    .simansa-role-permission-row:hover, .simansa-role-permission-row.is-checked { background: #eef4ff; }
    .simansa-role-permission-row .custom-control-label { display: grid; gap: .12rem; width: 100%; cursor: pointer; }
    .simansa-role-permission-row .custom-control-label small { color: #71809a; font-family: monospace; font-size: .7rem; }
    @media (max-width: 1199.98px) {
        .simansa-role-permission-module__body { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .simansa-role-permission-module__body { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575.98px) {
        .simansa-role-form-hero__title { font-size: 1.35rem; }
        .simansa-role-form-hero__stat { width: fit-content; min-width: 145px; }
        .simansa-role-permission-module__header { align-items: flex-start; }
        .simansa-role-permission-module__count { display: none; }
        .simansa-role-permission-module__toggle { min-height: 40px; }
        .simansa-role-permission-module__title small { font-size: .71rem; }
        .simansa-role-permission-module__body { grid-template-columns: 1fr; }
    }
</style>

<style>
    .simansa-role-permission-accordion { display: grid; gap: .65rem; }
    .simansa-role-permission-module { margin: 0; border: 1px solid #dce5f2; border-radius: .75rem; box-shadow: none; overflow: hidden; }
    .simansa-role-permission-module__header { display: flex; align-items: center; gap: .75rem; padding: .55rem .7rem .55rem .85rem; background: #fff; }
    .simansa-role-permission-module__trigger { display: flex; align-items: center; min-width: 0; flex: 1 1 auto; gap: .7rem; padding: .15rem 0; color: #172036; text-align: left; text-decoration: none; }
    .simansa-role-permission-module__trigger:hover, .simansa-role-permission-module__trigger:focus { color: #1d4ed8; text-decoration: none; }
    .simansa-role-permission-module__icon { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: .55rem; background: #f1f5fb; flex: 0 0 34px; }
    .simansa-role-permission-module__title { display: grid; min-width: 0; gap: .1rem; }
    .simansa-role-permission-module__title strong { font-size: .9rem; }
    .simansa-role-permission-module__title small { color: #71809a; font-size: .75rem; line-height: 1.35; }
    .simansa-role-permission-module__count { margin-left: auto; color: #61708a; font-size: .72rem; font-weight: 700; white-space: nowrap; }
    .simansa-role-permission-module__toggle { flex: 0 0 auto; min-height: 34px; white-space: nowrap; }
    .simansa-role-permission-module__body { display: grid; gap: .15rem; padding: .55rem .7rem .7rem; border-top: 1px solid #e8eef6; background: #f8fbff; }
    .simansa-role-permission-row { padding: .55rem .65rem .55rem 2.2rem; border-radius: .45rem; }
    .simansa-role-permission-row:hover, .simansa-role-permission-row.is-checked { background: #eef4ff; }
    .simansa-role-permission-row .custom-control-label { display: grid; gap: .12rem; width: 100%; cursor: pointer; }
    .simansa-role-permission-row .custom-control-label small { color: #71809a; font-family: monospace; font-size: .7rem; }
    @media (max-width: 575.98px) {
        .simansa-role-permission-module__header { align-items: flex-start; }
        .simansa-role-permission-module__count { display: none; }
        .simansa-role-permission-module__toggle { min-height: 40px; }
        .simansa-role-permission-module__title small { font-size: .71rem; }
    }
</style>

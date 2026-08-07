@if(($pendingPollingNotice ?? null) && !request()->routeIs('*.polling.show'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notice = @json($pendingPollingNotice);
            const sessionKey = 'simansa_polling_reminder_' + notice.id;
            const showReminder = function () {
                try {
                    if (sessionStorage.getItem(sessionKey) === 'shown') return;
                } catch (error) {}
                if (typeof Swal === 'undefined') return;
                try { sessionStorage.setItem(sessionKey, 'shown'); } catch (error) {}

                Swal.fire({
                icon: 'info',
                title: notice.title,
                text: notice.description || 'Ada polling aktif yang memerlukan respons Anda.',
                footer: '<span class="text-muted"><i class="far fa-clock mr-1"></i>Batas pengisian: ' + notice.ends_at + ' WIB</span>',
                confirmButtonText: '<i class="fas fa-pen mr-1"></i> Isi Sekarang',
                cancelButtonText: 'Ingatkan Nanti',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                backdrop: 'rgba(15, 23, 42, .38)',
                allowOutsideClick: true,
                }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = notice.url;
                    return;
                }

                const csrf = document.querySelector('meta[name="csrf-token"]');
                if (! csrf) return;

                fetch(notice.snooze_url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf.content,
                    },
                    body: JSON.stringify({ snooze: true }),
                }).catch(function () {});
                });
            };

            const electionOverlay = document.getElementById('studentElectionOverlay');
            if (electionOverlay && !electionOverlay.hidden) {
                window.addEventListener('simansa:osis-notice-dismissed', function () {
                    window.setTimeout(showReminder, 500);
                }, { once: true });
                return;
            }

            showReminder();
        });
    </script>
@endif

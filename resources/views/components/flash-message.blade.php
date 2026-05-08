<div class="flash-stack">
    @if (session('success'))
        <div class="flash-alert" data-alert data-tone="success">
            <div class="flash-alert-head">
                <div>
                    <h3>Success</h3>
                    <p>{{ session('success') }}</p>
                </div>
                <button type="button" class="flash-dismiss" data-dismiss-alert aria-label="Tutup notifikasi">&times;</button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="flash-alert" data-alert data-tone="error">
            <div class="flash-alert-head">
                <div>
                    <h3>Warning</h3>
                    <p>{{ session('error') }}</p>
                </div>
                <button type="button" class="flash-dismiss" data-dismiss-alert aria-label="Tutup notifikasi">&times;</button>
            </div>
        </div>
    @endif
</div>

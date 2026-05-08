@props(['message'])

<div class="empty-state">
    <div class="empty-state-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 9.75h4.5m-4.5 4.5h4.5M5.25 6.75h13.5A2.25 2.25 0 0121 9v9a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18V9a2.25 2.25 0 012.25-2.25z" />
        </svg>
    </div>
    <p>{{ $message }}</p>
</div>

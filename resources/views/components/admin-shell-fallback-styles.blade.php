<style>
    :root {
        --panel-teal: #0D9488;
        --panel-teal-dark: #134E4A;
        --panel-mint: #F0FDF4;
        --panel-surface: rgba(255, 255, 255, 0.84);
        --panel-surface-strong: rgba(255, 255, 255, 0.96);
        --panel-border: rgba(13, 148, 136, 0.14);
        --panel-text: #134E4A;
        --panel-text-soft: #64807B;
        --panel-shadow: 0 24px 55px rgba(13, 148, 136, 0.12);
    }

    * {
        box-sizing: border-box;
    }

    body.app-body {
        margin: 0;
        min-height: 100vh;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--panel-text);
        background:
            radial-gradient(circle at top left, rgba(13, 148, 136, 0.14), transparent 26rem),
            radial-gradient(circle at bottom right, rgba(13, 148, 136, 0.12), transparent 28rem),
            var(--panel-mint);
    }

    .app-bg {
        position: fixed;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(rgba(13, 148, 136, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(13, 148, 136, 0.05) 1px, transparent 1px);
        background-size: 44px 44px;
        mask-image: linear-gradient(180deg, rgba(255,255,255,0.55), transparent 85%);
        opacity: 0.5;
    }

    .app-shell {
        position: relative;
        z-index: 1;
        display: flex;
        min-height: 100vh;
    }

    .app-sidebar {
        display: none;
    }

    .app-sidebar-panel,
    .app-header-card,
    .page-hero,
    .surface-card,
    .surface-table,
    .surface-form,
    .surface-panel,
    .stat-card,
    .flash-alert,
    .empty-state {
        background: var(--panel-surface);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255,255,255,0.8);
        box-shadow: var(--panel-shadow);
    }

    .app-sidebar-panel {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 24px);
        border-radius: 30px;
        overflow: hidden;
    }

    .app-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 22px 20px 18px;
        border-bottom: 1px solid rgba(13, 148, 136, 0.08);
    }

    .app-brand-mark {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        font-weight: 800;
        letter-spacing: 0.08em;
    }

    .app-eyebrow,
    .app-header-kicker,
    .page-shell label,
    .stat-card-label,
    .mini-panel span,
    .hero-chip strong {
        font-size: 11px;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--panel-teal);
        font-weight: 800;
    }

    .app-brand-copy h1,
    .app-header h2,
    .page-hero h1,
    .surface-card h2,
    .surface-form h2,
    .surface-panel h2 {
        margin: 0;
        color: var(--panel-text);
    }

    .app-brand-copy p,
    .app-sidebar-copy span,
    .app-header p,
    .section-note,
    .stat-card-sub,
    .empty-state p,
    .hero-chip,
    .data-stack span,
    .mini-panel strong {
        color: var(--panel-text-soft);
    }

    .app-brand-copy h1 {
        margin-top: 4px;
        font-size: 18px;
    }

    .app-brand-copy p {
        margin-top: 4px;
        font-size: 14px;
        line-height: 1.6;
    }

    .icon-button,
    .btn-secondary,
    .btn-primary,
    .btn-danger,
    .btn-success,
    .btn-warning,
    .btn-ghost {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 46px;
        padding: 0 16px;
        border-radius: 15px;
        text-decoration: none;
        border: 1px solid transparent;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .icon-button,
    .btn-secondary,
    .btn-ghost {
        background: rgba(255,255,255,0.76);
        border-color: var(--panel-border);
        color: var(--panel-text);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--panel-teal), #0B7A6F);
        color: #fff;
        box-shadow: 0 14px 28px rgba(13, 148, 136, 0.18);
    }

    .btn-success {
        background: linear-gradient(135deg, #22C55E, #16A34A);
        color: #fff;
    }

    .btn-danger {
        background: linear-gradient(135deg, #EF4444, #DC2626);
        color: #fff;
    }

    .btn-warning {
        background: linear-gradient(135deg, #F59E0B, #D97706);
        color: #fff;
    }

    .app-sidebar-copy,
    .app-sidebar-footer-card,
    .surface-list-item,
    .mini-panel,
    .hero-chip {
        background: var(--panel-surface-strong);
        border: 1px solid var(--panel-border);
        border-radius: 22px;
    }

    .app-sidebar-copy {
        margin: 14px 16px 0;
        padding: 16px;
    }

    .app-sidebar-copy strong,
    .app-sidebar-footer-card strong,
    .app-nav-text strong,
    .data-stack strong {
        color: var(--panel-text);
    }

    .app-sidebar-copy strong {
        display: block;
        font-size: 15px;
    }

    .app-sidebar-copy span,
    .app-sidebar-footer-card span {
        display: block;
        margin-top: 6px;
        font-size: 13px;
        line-height: 1.7;
    }

    .app-nav {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    .app-nav-list {
        display: grid;
        gap: 10px;
    }

    .app-nav-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px;
        border-radius: 18px;
        text-decoration: none;
        color: var(--panel-text);
        border: 1px solid transparent;
    }

    .app-nav-link:hover,
    .app-nav-link.is-active {
        background: rgba(255,255,255,0.88);
        border-color: rgba(13, 148, 136, 0.14);
        box-shadow: 0 12px 24px rgba(13, 148, 136, 0.08);
    }

    .app-nav-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: #DFF7F2;
        color: var(--panel-teal-dark);
    }

    .app-nav-link.is-active .app-nav-icon {
        background: linear-gradient(135deg, var(--panel-teal), var(--panel-teal-dark));
        color: #fff;
    }

    .app-nav-text strong {
        display: block;
        font-size: 15px;
    }

    .app-nav-text span {
        display: block;
        margin-top: 4px;
        font-size: 13px;
        color: var(--panel-text-soft);
    }

    .app-sidebar-footer {
        padding: 0 16px 16px;
    }

    .app-sidebar-footer-card {
        padding: 16px;
    }

    .app-main {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        width: 100%;
        padding-bottom: 88px;
    }

    .app-header {
        position: sticky;
        top: 0;
        z-index: 30;
        padding: 12px 12px 0;
    }

    .app-header-card {
        border-radius: 26px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .app-header-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .app-header h2 {
        font-size: clamp(24px, 2vw, 28px);
        font-weight: 800;
    }

    .app-header p {
        margin: 6px 0 0;
        font-size: 14px;
        line-height: 1.7;
    }

    .app-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .app-main-content {
        flex: 1;
        padding: 16px 12px 28px;
    }

    .page-shell {
        width: min(100%, 1500px);
        margin: 0 auto;
    }

    .page-hero,
    .surface-card,
    .surface-table,
    .surface-form,
    .surface-panel,
    .stat-card,
    .flash-alert,
    .empty-state {
        border-radius: 30px;
        /* Removed overflow: hidden — it was cutting off ::before decorative blur orbs */
    }

    .page-hero {
        padding: 24px;
    }

    .page-hero-grid,
    .metric-grid,
    .mini-grid,
    .surface-list {
        display: grid;
        gap: 16px;
    }

    .page-hero h1 {
        font-size: clamp(30px, 4vw, 42px);
        line-height: 1.08;
        font-weight: 800;
    }

    .page-hero p {
        margin: 12px 0 0;
        max-width: 780px;
        color: var(--panel-text-soft);
        line-height: 1.8;
    }

    .page-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 18px;
    }

    .hero-chip {
        min-height: 52px;
        padding: 12px 16px;
        display: inline-flex;
        align-items: center;
    }

    .hero-chip span {
        display: block;
        margin-top: 4px;
        color: var(--panel-text);
        font-weight: 700;
    }

    .surface-card,
    .surface-form,
    .surface-panel {
        padding: 22px;
    }

    .surface-card h2,
    .surface-form h2,
    .surface-panel h2 {
        font-size: 24px;
        font-weight: 800;
    }

    .section-note {
        margin-top: 8px;
        font-size: 14px;
        line-height: 1.7;
    }

    .metric-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .mini-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    .mini-panel {
        padding: 16px;
    }

    .mini-panel strong {
        display: block;
        margin-top: 8px;
        font-size: 20px;
        color: var(--panel-text);
    }

    .stat-card {
        padding: 20px;
    }

    .stat-card-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .stat-card-label span:last-child {
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: #DFF7F2;
        color: var(--panel-teal-dark);
    }

    .stat-card-value {
        margin-top: 18px;
        font-size: clamp(28px, 4vw, 40px);
        font-weight: 800;
        line-height: 1.05;
        color: var(--panel-text);
        word-break: break-word;
    }

    .stat-card-sub {
        margin-top: 8px;
        font-size: 14px;
    }

    .surface-table-wrap {
        overflow-x: auto;
    }

    .surface-table table,
    .page-shell table {
        width: 100%;
        border-collapse: collapse;
    }

    .surface-table thead,
    .page-shell thead {
        background: rgba(223, 247, 242, 0.85);
    }

    .surface-table th,
    .page-shell th {
        padding: 16px 18px;
        text-align: left;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--panel-teal-dark);
        white-space: nowrap;
    }

    .surface-table td,
    .page-shell td {
        padding: 16px 18px;
        border-top: 1px solid rgba(13, 148, 136, 0.08);
        color: var(--panel-text);
        vertical-align: top;
    }

    .surface-table tbody tr:hover,
    .page-shell tbody tr:hover {
        background: rgba(240, 253, 244, 0.64);
    }

    .surface-list {
        margin-top: 18px;
    }

    .surface-list-item {
        padding: 16px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .status-pill::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: currentColor;
    }

    .status-pill--green { background: #DCFCE7; color: #166534; border-color: #BBF7D0; }
    .status-pill--amber { background: #FEF3C7; color: #92400E; border-color: #FCD34D; }
    .status-pill--red { background: #FEE2E2; color: #B91C1C; border-color: #FCA5A5; }
    .status-pill--blue { background: #DBEAFE; color: #1D4ED8; border-color: #93C5FD; }
    .status-pill--teal { background: #CCFBF1; color: #0F766E; border-color: #5EEAD4; }
    .status-pill--gray { background: #F3F4F6; color: #4B5563; border-color: #D1D5DB; }

    .flash-stack {
        display: grid;
        gap: 14px;
    }

    .flash-alert {
        padding: 16px 18px;
    }

    .flash-alert[data-tone='success'] { border-left: 4px solid #16A34A; }
    .flash-alert[data-tone='error'] { border-left: 4px solid #DC2626; }

    .flash-alert-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .flash-alert h3 {
        margin: 0;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--panel-teal);
    }

    .flash-alert p {
        margin: 8px 0 0;
        color: var(--panel-text);
        line-height: 1.7;
    }

    .flash-dismiss {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 1px solid var(--panel-border);
        background: rgba(255,255,255,0.72);
        color: var(--panel-text);
        cursor: pointer;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 14px;
        padding: 28px 20px;
        text-align: center;
    }

    .empty-state-icon {
        width: 56px;
        height: 56px;
        display: grid;
        place-items: center;
        border-radius: 18px;
        background: #DFF7F2;
        color: var(--panel-teal-dark);
    }

    .page-shell label {
        display: block;
        margin-bottom: 8px;
    }

    .page-shell input,
    .page-shell select,
    .page-shell textarea {
        width: 100%;
        border: 1px solid rgba(13, 148, 136, 0.18);
        border-radius: 16px;
        background: rgba(240, 253, 244, 0.72);
        padding: 13px 15px;
        font: inherit;
        color: var(--panel-text);
        outline: none;
    }

    .page-shell input:focus,
    .page-shell select:focus,
    .page-shell textarea:focus {
        border-color: var(--panel-teal);
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.12);
        background: #fff;
    }

    .helper-text { margin-top: 8px; font-size: 12px; color: var(--panel-text-soft); line-height: 1.6; }
    .error-text { margin-top: 8px; font-size: 12px; color: #DC2626; }

    .data-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 800;
        flex-shrink: 0;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid rgba(226, 232, 240, 0.6);
    }

    .data-stack {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .data-stack strong {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: var(--panel-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .data-stack span {
        display: block;
        margin-top: 4px;
        font-size: 11px;
        font-weight: 500;
        color: var(--panel-text-soft);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .mobile-dock {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 35;
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 6px;
        padding: 8px 10px;
        background: rgba(240, 253, 244, 0.88);
        backdrop-filter: blur(14px);
        border-top: 1px solid rgba(13, 148, 136, 0.1);
    }

    .mobile-dock-link {
        min-height: 52px;
        border-radius: 14px;
        background: rgba(255,255,255,0.82);
        border: 1px solid var(--panel-border);
        color: var(--panel-text-soft);
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 6px 4px;
    }

    .mobile-dock-link svg { width: 17px; height: 17px; }
    .mobile-dock-link span {
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }
    .mobile-dock-link.is-active {
        background: linear-gradient(135deg, var(--panel-teal), var(--panel-teal-dark));
        color: #fff;
        border-color: transparent;
    }

    @media (min-width: 1024px) {
        .app-sidebar {
            display: block;
            position: sticky;
            top: 0;
            transform: none;
            width: 312px;
            padding-right: 0;
        }

        .mobile-dock {
            display: none;
        }
    }

    @media (max-width: 900px) {
        .app-header-card,
        .app-header-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .app-main {
            padding-bottom: 72px;
        }

        .app-header {
            padding: 8px 8px 0;
        }

        .app-header-card {
            padding: 14px;
            border-radius: 22px;
            gap: 12px;
        }

        .app-main-content {
            padding: 12px 8px 18px;
        }

        .page-hero,
        .surface-card,
        .surface-table,
        .surface-form,
        .surface-panel,
        .stat-card {
            border-radius: 24px;
        }

        .page-hero,
        .surface-card,
        .surface-form,
        .surface-panel,
        .stat-card {
            padding: 18px;
        }
    }
</style>

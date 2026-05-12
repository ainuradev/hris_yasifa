<style>
    @page {
        size: A4;
        margin: 12mm;
    }

    @media print {
        html,
        body {
            width: 210mm;
            min-height: 297mm;
            background: #ffffff !important;
            color: #0f172a !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body * {
            visibility: hidden !important;
        }

        #slip-gaji,
        #slip-gaji * {
            visibility: visible !important;
        }

        #slip-gaji {
            position: absolute !important;
            inset: 0 auto auto 0 !important;
            width: 186mm !important;
            min-height: 273mm !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: #ffffff !important;
        }

        aside,
        header,
        nav,
        .no-print {
            display: none !important;
            visibility: hidden !important;
        }

        main,
        .page-content,
        .mx-auto,
        .max-w-4xl,
        .max-w-6xl,
        .max-w-7xl {
            width: auto !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            background: #ffffff !important;
        }

        .print-grid-2 {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 16px !important;
        }

        .print-section {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>

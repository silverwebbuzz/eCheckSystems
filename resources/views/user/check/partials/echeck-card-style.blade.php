{{--
    Check-look styling for the Create / Edit check screens (Send Payment + Receive Payment).
    Matches the generated PDF design in user/check_formate/index.blade.php.
    Everything is scoped to `.echeck-card .card-body > .row` so the Add Payor / Add Payee
    modals that live inside the same card (and reuse some input IDs) keep their normal style.
--}}
<style>
    @font-face {
        font-family: 'MICRCheckPrixa';
        src: url('{{ asset('font/MICRCheckPrixa.woff2') }}') format('woff2'),
            url('{{ asset('font/MICRCheckPrixa.woff') }}') format('woff'),
            url('{{ asset('font/MICRCheckPrixa.ttf') }}') format('truetype');
        font-display: swap;
    }

    .echeck-card {
        --echeck-accent: #7367f0;
        --echeck-accent-dark: #6258cc;
        --echeck-paper: #f9f9fe;
        --echeck-band: #f4f3fe;
        --echeck-label: #68757a;
        --echeck-muted: #9aa6aa;
        --echeck-text: #1d2a2e;
        --echeck-line: #aeb9bd;

        background: var(--echeck-paper) !important;
        border: 1px solid #d3d8db;
        border-top: 6px solid var(--echeck-accent);
        border-radius: 6px;
        box-shadow: 0 4px 14px rgba(29, 42, 46, 0.08);
        color: var(--echeck-text);
    }

    .echeck-card .card-header {
        background: #fff;
        border-bottom: 1px solid #e6e9eb;
        margin-bottom: 0 !important;
        padding-top: 14px;
        padding-bottom: 14px;
    }

    .echeck-card .card-body {
        padding: 24px 28px 0 28px;
    }

    .echeck-card .card-body > .row + .row {
        margin-top: 10px;
    }

    /* Small uppercase captions printed on the check */
    .echeck-label {
        display: block;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.12em;
        line-height: 1.35;
        text-transform: uppercase;
        color: var(--echeck-label);
    }

    .echeck-void {
        font-size: 9px;
        color: var(--echeck-muted);
        margin-top: 4px;
    }

    .echeck-logo {
        flex: 0 0 auto;
        width: 110px;
        height: auto;
        margin-right: 10px;
    }

    /* Inputs and selects on the check: underline only, like a printed line */
    .echeck-card .card-body > .row input.form-control,
    .echeck-card .card-body > .row select.form-control {
        border: 0 !important;
        border-bottom: 1px solid var(--echeck-line) !important;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        padding-left: 4px;
        color: var(--echeck-text);
    }

    .echeck-card .card-body > .row input.form-control:focus,
    .echeck-card .card-body > .row select.form-control:focus {
        border-bottom: 2px solid var(--echeck-accent) !important;
    }

    .echeck-card .card-body > .row .select2-container--default .select2-selection--single {
        border: 0 !important;
        border-bottom: 1px solid var(--echeck-line) !important;
        border-radius: 0;
        background: transparent;
    }

    .echeck-card .card-body > .row .select2-container--default .select2-selection--single .select2-selection__rendered {
        font-weight: 600;
        color: var(--echeck-text);
    }

    /* Payor address lines look like printed text, not boxes */
    .echeck-card .card-body > .row #address,
    .echeck-card .card-body > .row #city,
    .echeck-card .card-body > .row #state,
    .echeck-card .card-body > .row #zip {
        border-bottom-color: transparent !important;
        font-size: 12px;
        color: var(--echeck-label);
        padding-top: 2px;
        padding-bottom: 2px;
        height: auto;
    }

    /* Check number + date (top right) */
    .echeck-card .card-body > .row #check_number {
        font-family: 'DejaVu Sans Mono', Menlo, Consolas, monospace;
        font-weight: 700;
        color: var(--echeck-accent-dark);
        text-align: right;
    }

    /* Send Payment: "EC" prefix + number read as one value (EC1000), like on the PDF */
    .echeck-card .card-body > .row .input-group #check_number {
        text-align: left;
        padding-left: 2px;
    }

    .echeck-card .card-body > .row .input-group-text {
        border: 0 !important;
        background: transparent !important;
        color: var(--echeck-accent-dark) !important;
        font-weight: 700;
        font-family: 'DejaVu Sans Mono', Menlo, Consolas, monospace;
        padding-right: 0;
    }

    .echeck-card .card-body > .row #check_date {
        text-align: center;
        font-weight: 700;
    }

    /* Pay to the order of / amount box */
    .echeck-card .card-body > .row .echeck-payto {
        padding-top: 2px;
    }

    .echeck-dollar {
        font-size: 22px;
        font-weight: 700;
        color: var(--echeck-accent-dark);
        text-align: right;
        padding-right: 4px;
    }

    .echeck-card .card-body > .row #amount {
        border: 1.5px solid var(--echeck-accent) !important;
        border-radius: 2px;
        background: #fff;
        text-align: center;
        font-weight: 700;
        font-size: 16px;
    }

    /* Amount in words line */
    .echeck-words {
        flex: 1 1 auto;
        min-height: 30px;
        border-bottom: 1px solid var(--echeck-line);
        padding: 4px 8px 6px 4px;
        font-size: 14px;
        font-weight: 700;
    }

    /* Signature area */
    .echeck-sign-line {
        border-bottom: 1px solid #7d8a8f;
        margin-top: 6px;
    }

    .echeck-sign-caption {
        font-size: 9px;
        letter-spacing: 0.1em;
        color: var(--echeck-muted);
        margin-top: 4px;
        text-align: center;
    }

    .echeck-sign-not-required {
        border-bottom: 1px solid #7d8a8f;
        font-size: 9px;
        line-height: 1.25;
        color: var(--echeck-label);
        padding: 2px 4px 4px 4px;
        text-align: left;
    }

    /* MICR band at the bottom (check number / routing / account) */
    .echeck-card .card-body > .row:has(#verify_check_number) {
        background: var(--echeck-band);
        border-top: 1px solid #dde3e5;
        margin: 28px -28px 0 -28px !important;
        padding: 12px 28px;
        border-radius: 0 0 6px 6px;
    }

    .echeck-card .card-body > .row #verify_check_number,
    .echeck-card .card-body > .row #routing_number,
    .echeck-card .card-body > .row #account_number {
        border: 0 !important;
        background: transparent !important;
        text-align: center;
        font-family: 'MICRCheckPrixa', 'DejaVu Sans Mono', monospace;
        font-size: 22px;
        letter-spacing: 0.08em;
        color: #7d8a8f;
    }

    .echeck-card .card-body > .row #verify_check_number::placeholder,
    .echeck-card .card-body > .row #routing_number::placeholder,
    .echeck-card .card-body > .row #account_number::placeholder {
        font-family: 'Public Sans', sans-serif;
        font-size: 12px;
        letter-spacing: 0;
    }
</style>

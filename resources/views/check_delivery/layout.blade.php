<!DOCTYPE html>
{{-- Standalone layout for the Deluxe-style check delivery pages (no admin menus for payees). --}}
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Your check') · eCheckSystems</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --ec-accent: #7367f0; --ec-accent-dark: #6258cc; --ec-text: #1d2a2e; --ec-muted: #68757a; }
        body { background: #f4f3fe; color: var(--ec-text); font-family: "Public Sans", -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
        .ec-top { background: #fff; border-bottom: 1px solid #e6e9eb; }
        .ec-top img { height: 42px; }
        .ec-card { background: #fff; border: 1px solid #e3e1fb; border-top: 6px solid var(--ec-accent); border-radius: 10px; box-shadow: 0 6px 20px rgba(29, 42, 46, .07); }
        .ec-label { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: var(--ec-muted); font-weight: 600; }
        .ec-amount { font-size: 40px; font-weight: 800; color: var(--ec-accent-dark); line-height: 1.1; }
        .btn-ec { background: var(--ec-accent); border-color: var(--ec-accent); color: #fff; font-weight: 600; }
        .btn-ec:hover, .btn-ec:focus { background: var(--ec-accent-dark); border-color: var(--ec-accent-dark); color: #fff; }
        .btn-ec:disabled { background: #b9b3f7; border-color: #b9b3f7; }
        .btn-outline-ec { border-color: var(--ec-accent); color: var(--ec-accent-dark); font-weight: 600; }
        .btn-outline-ec:hover { background: var(--ec-accent); color: #fff; }
        .ec-option { border: 1px solid #e6e9eb; border-radius: 10px; height: 100%; }
        .ec-option h5 { font-weight: 700; }
        .ec-footer { color: var(--ec-muted); font-size: 13px; }
    </style>
</head>
<body>
    <div class="ec-top">
        <div class="container py-3 d-flex align-items-center justify-content-between" style="max-width: 920px;">
            <img src="https://echecksystems.com/wp-content/uploads/elementor/thumbs/echeck-systems-logo-r3qzixzultt1pr9mur1kl8ksvlbpbynxzcx5fso11c.png" alt="eCheckSystems">
            @yield('top-right')
        </div>
    </div>

    <main class="container py-4 py-md-5" style="max-width: 920px;">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')

        <p class="ec-footer text-center mt-5 mb-0">
            Secure check delivery by eCheckSystems · Need help? Email
            <a href="mailto:{{ config('check_delivery.support_email') }}">{{ config('check_delivery.support_email') }}</a>
        </p>
    </main>

    @yield('scripts')
</body>
</html>

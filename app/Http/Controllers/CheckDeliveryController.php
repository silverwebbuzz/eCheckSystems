<?php

namespace App\Http\Controllers;

use App\Models\CheckDelivery;
use App\Models\Checks;
use App\Models\PayeeAccount;
use App\Models\Payors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Deluxe-style check delivery (client Point 5).
 *
 * Public:  /get-check/{token}            secure page from the "Get Check" email button
 *          POST /get-check/{token}/print  guest: confirm checkbox, then open the check PDF to print
 *          POST /get-check/{token}/account  create a payee account and keep the check
 * Payee:   /payee/login, /payee/checks, /payee/checks/{id}/print, /payee/logout
 */
class CheckDeliveryController extends Controller
{
    // ---------------------------------------------------------------- public (link)

    public function show(string $token)
    {
        [$delivery, $check, $error] = $this->resolve($token);
        if ($error) {
            return $error;
        }

        $delivery->markViewed();

        return view('check_delivery.show', [
            'token' => $token,
            'delivery' => $delivery,
            'details' => $this->details($check, $delivery),
            'hasAccount' => PayeeAccount::whereRaw('LOWER(email) = ?', [strtolower($delivery->payee_email)])->exists(),
            'payeeLoggedIn' => Auth::guard('payee')->check(),
        ]);
    }

    /** Guest path: the checkbox must be ticked, then the PDF opens (new tab) for printing. */
    public function guestPrint(Request $request, string $token)
    {
        [$delivery, $check, $error] = $this->resolve($token);
        if ($error) {
            return $error;
        }

        $request->validate(
            ['confirm' => 'accepted'],
            ['confirm.accepted' => 'Please tick the box to confirm before printing.']
        );

        return $this->streamPdf($delivery, $check, $request);
    }

    /** "Create an account to get your payment": email is fixed to the address the check was sent to. */
    public function createAccount(Request $request, string $token)
    {
        [$delivery, $check, $error] = $this->resolve($token);
        if ($error) {
            return $error;
        }

        $email = strtolower($delivery->payee_email);
        if (PayeeAccount::whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return redirect()->route('payee.login', ['email' => $delivery->payee_email])
                ->with('info', 'You already have an account. Please log in to see your check.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $account = PayeeAccount::create([
            'name' => $validated['name'],
            'email' => $delivery->payee_email,
            'password' => $validated['password'], // hashed by the model cast
            'last_login_at' => now(),
        ]);

        $delivery->update(['payee_account_id' => $account->id]);

        Auth::guard('payee')->login($account);
        $request->session()->regenerate();

        return redirect()->route('payee.checks')
            ->with('success', 'Your account is ready. Your check is saved below.');
    }

    // ---------------------------------------------------------------- payee account area

    public function loginForm(Request $request)
    {
        if (Auth::guard('payee')->check()) {
            return redirect()->route('payee.checks');
        }

        return view('check_delivery.payee_login', ['email' => $request->query('email', '')]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $account = PayeeAccount::whereRaw('LOWER(email) = ?', [strtolower($credentials['email'])])->first();
        if (!$account || !Hash::check($credentials['password'], $account->password)) {
            return back()->withErrors(['email' => 'Email or password is incorrect.'])->withInput($request->only('email'));
        }

        Auth::guard('payee')->login($account, $request->boolean('remember'));
        $request->session()->regenerate();
        $account->update(['last_login_at' => now()]);

        return redirect()->route('payee.checks');
    }

    // ---------------------------------------------------------------- payee "Forgot password"

    public function forgotForm()
    {
        return view('check_delivery.payee_forgot');
    }

    /** Always shows the same message, so nobody can test which emails have an account. */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            Password::broker('payees')->sendResetLink(['email' => strtolower(trim($request->email))]);
        } catch (\Throwable $e) {
            Log::error('Payee password reset email failed', ['error' => $e->getMessage()]);
        }

        return back()->with('success', 'If an account exists for that email, we have sent a link to reset the password. Please check your inbox.');
    }

    public function resetForm(Request $request, string $token)
    {
        return view('check_delivery.payee_reset', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::broker('payees')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (PayeeAccount $account, string $password) {
                $account->password = $password; // hashed by the model cast
                $account->setRememberToken(Str::random(60));
                $account->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This reset link is invalid or has expired. Please request a new one.']);
        }

        return redirect()->route('payee.login', ['email' => $request->email])
            ->with('success', 'Your password has been reset. Please log in.');
    }

    public function logout(Request $request)
    {
        Auth::guard('payee')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('payee.login')->with('success', 'You have been logged out.');
    }

    public function checks()
    {
        $account = Auth::guard('payee')->user();
        if (!$account) {
            return redirect()->route('payee.login');
        }

        $rows = [];
        foreach ($account->deliveries()->get() as $delivery) {
            $check = Checks::find($delivery->CheckID);
            if (!$check) {
                continue;
            }
            $rows[] = ['delivery' => $delivery, 'details' => $this->details($check, $delivery)];
        }

        return view('check_delivery.payee_checks', compact('account', 'rows'));
    }

    public function payeePrint(Request $request, int $id)
    {
        $account = Auth::guard('payee')->user();
        if (!$account) {
            return redirect()->route('payee.login');
        }

        // Only checks sent to this account's email address.
        $delivery = $account->deliveries()->where('id', $id)->first();
        $check = $delivery ? Checks::find($delivery->CheckID) : null;
        if (!$delivery || !$check || empty($check->CheckPDF)) {
            return redirect()->route('payee.checks')->with('error', 'This check is not available.');
        }
        if ($delivery->isExpired()) {
            return redirect()->route('payee.checks')->with('error', 'This check expired on ' . $delivery->expires_at->format('m/d/Y') . '. Please contact the sender.');
        }

        return $this->streamPdf($delivery, $check, $request);
    }

    // ---------------------------------------------------------------- helpers

    /**
     * @return array{0: ?CheckDelivery, 1: ?Checks, 2: mixed} error response in [2] when the link is not usable
     */
    private function resolve(string $token): array
    {
        $delivery = strlen($token) >= 32 ? CheckDelivery::findByToken($token) : null;
        if (!$delivery) {
            return [null, null, response()->view('check_delivery.message', [
                'title' => 'Link not valid',
                'message' => 'This check link is not valid or has been replaced by a newer email. Please use the latest email you received, or contact the sender.',
            ], 404)];
        }

        if ($delivery->isExpired()) {
            return [null, null, response()->view('check_delivery.message', [
                'title' => 'Link expired',
                'message' => 'This check link expired on ' . $delivery->expires_at->format('m/d/Y') . '. Please contact the sender to send it again.',
            ], 410)];
        }

        $check = Checks::find($delivery->CheckID);
        if (!$check || empty($check->CheckPDF) || !File::exists(public_path('checks/' . $check->CheckPDF))) {
            return [null, null, response()->view('check_delivery.message', [
                'title' => 'Check not available',
                'message' => 'This check is no longer available. Please contact the sender.',
            ], 404)];
        }

        return [$delivery, $check, null];
    }

    private function details(Checks $check, ?CheckDelivery $delivery = null): array
    {
        $payor = Payors::withTrashed()->find($check->PayorID);
        $payee = Payors::withTrashed()->find($check->PayeeID);

        return [
            'sender' => ($delivery->sender_name ?? null) ?: ($payor->Name ?? 'Sender'),
            'payee' => $payee->Name ?? '',
            'amount' => number_format((float) $check->Amount, 2),
            'check_number' => 'EC' . $check->CheckNumber,
            'memo' => (string) $check->Memo,
            'date' => $check->ExpiryDate ? \Carbon\Carbon::parse($check->ExpiryDate)->format('m/d/Y') : '',
        ];
    }

    /** Open the check PDF inline (browser PDF viewer prints it letter size, right way up) and log the print. */
    private function streamPdf(CheckDelivery $delivery, Checks $check, Request $request)
    {
        $path = public_path('checks/' . $check->CheckPDF);
        if (!File::exists($path)) {
            abort(404);
        }

        $delivery->markPrinted($request->ip());

        $response = response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Check-EC' . $check->CheckNumber . '.pdf"',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
        // The check holds bank details: never let shared caches/proxies keep a copy.
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }
}

@if (session()->has(\App\Http\Controllers\ImpersonationController::SESSION_KEY))
    <div
        style="position:sticky;top:0;z-index:50;display:flex;align-items:center;justify-content:center;gap:.75rem;
               padding:.5rem 1rem;font-size:.875rem;font-weight:500;
               background:#b45309;color:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);"
    >
        <span>
            Mode "Login sebagai" &mdash; Anda sedang masuk sebagai
            <strong>{{ auth()->user()?->name }}</strong>.
        </span>

        <form method="POST" action="{{ route('impersonate.stop') }}">
            @csrf
            <button
                type="submit"
                style="text-decoration:underline;font-weight:600;background:none;border:0;color:#fff;cursor:pointer;"
            >
                Kembali ke akun saya
            </button>
        </form>
    </div>
@endif

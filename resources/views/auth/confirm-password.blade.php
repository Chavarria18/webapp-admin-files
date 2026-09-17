<form method="POST" action="{{ route('auth.confirm-forgot-password') }}">
    @csrf

    <input
        type="text"
        name="code"
        placeholder="Verification code"
        required
    >

    <input
        type="password"
        name="password"
        placeholder="New password"
        required
    >

    <input
        type="password"
        name="password_confirmation"
        placeholder="Confirm new password"
        required
    >

    <button type="submit">
        Reset password
    </button>
</form>
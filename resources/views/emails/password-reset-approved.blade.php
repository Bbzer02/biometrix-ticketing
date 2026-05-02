<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: Inter, Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 2rem; }
  .card { background: #fff; border-radius: 1rem; max-width: 480px; margin: 0 auto; padding: 2rem 2.5rem; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
  .logo { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem; }
  h1 { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem; }
  p { color: #475569; font-size: 0.9375rem; line-height: 1.6; margin: 0.5rem 0; }
  .btn { display: inline-block; margin: 1.5rem 0 1rem; padding: 0.75rem 2rem; background: #2563eb; color: #fff !important; border-radius: 0.75rem; text-decoration: none; font-weight: 600; font-size: 0.9375rem; }
  .note { font-size: 0.8125rem; color: #94a3b8; margin-top: 1rem; }
  .divider { border: none; border-top: 1px solid #e2e8f0; margin: 1.5rem 0; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">🎫 IT Helpdesk</div>
  <h1>Password reset approved</h1>
  <p>Hi <strong>{{ $userName }}</strong>,</p>
  <p>Your request to reset your password has been approved by the administrator. Click the button below to set a new password.</p>
  <a href="{{ $resetUrl }}" class="btn">Reset my password</a>
  <hr class="divider">
  <p class="note">This link expires at <strong>{{ $expiresAt?->format('M d, Y H:i') ?? 'N/A' }}</strong>. If you did not request this, please contact your administrator.</p>
  <p class="note">If the button doesn't work, copy this link: {{ $resetUrl }}</p>
</div>
</body>
</html>

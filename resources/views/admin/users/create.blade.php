@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Add Student Manually</h2>
  <p style="color:var(--muted);margin-bottom:20px;">Register a new student user account.</p>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <form method="POST" action="{{ route('admin.users.register') }}" style="max-width:480px;">
    @csrf
    <div style="margin-bottom:16px;">
      <label style="display:block;font-weight:600;margin-bottom:4px;">Full Name</label>
      <input type="text" name="name" placeholder="Full Name" required style="padding:8px;border-radius:6px;border:1px solid #ddd;width:100%;">
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-weight:600;margin-bottom:4px;">Email</label>
      <input type="email" name="email" placeholder="Email" required style="padding:8px;border-radius:6px;border:1px solid #ddd;width:100%;">
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-weight:600;margin-bottom:4px;">Password</label>
      <input type="password" name="password" placeholder="Password" required style="padding:8px;border-radius:6px;border:1px solid #ddd;width:100%;">
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-weight:600;margin-bottom:4px;">Password Confirmation</label>
      <input type="password" name="password_confirmation" placeholder="Confirm Password" required style="padding:8px;border-radius:6px;border:1px solid #ddd;width:100%;">
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-weight:600;margin-bottom:4px;">Council</label>
      <select name="council" style="padding:8px;border-radius:6px;border:1px solid #ddd;width:100%;">
        <option value="">Select Council</option>
        <option value="HBM">HBM</option>
        <option value="CSC">CSC</option>
        <option value="BIT">BIT</option>
        <option value="EDUC">EDUC</option>
        <option value="Unaffiliated">Unaffiliated</option>
      </select>
    </div>
    <input type="hidden" name="role" value="Student">
    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn btn-primary">Register Student</button>
      <a href="{{ route('admin.index') }}" class="btn btn-secondary">Back to Admin Panel</a>
    </div>
  </form>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
  <div class="page-header">
    <h2>Analytics Dashboard</h2>
    <span style="color:var(--muted); font-size:0.85rem;">Real-time platform KPIs</span>
  </div>

  {{-- KPI Summary Cards --}}
  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-value">{{ $metrics['total_requests'] }}</div>
      <div class="stat-label">Total Requests</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $metrics['total_providers'] }}</div>
      <div class="stat-label">Active Providers</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $metrics['total_matches'] }}</div>
      <div class="stat-label">Total Matches</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ number_format($metrics['transaction_completion_rate'], 1) }}%</div>
      <div class="stat-label">Transaction Completion Rate</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $metrics['active_provider_count'] }}</div>
      <div class="stat-label">Active Providers (30d)</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ count($metrics['top_rated_providers']) }}</div>
      <div class="stat-label">Top-Rated Providers</div>
    </div>
  </div>

  {{-- Charts --}}
  <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(480px, 1fr)); gap:24px; margin-top:24px;">
    <div class="card">
      <h3 style="margin-top:0;">Monthly Request Volume (12 months)</h3>
      <canvas id="requestVolumeChart" height="120"></canvas>
    </div>
    <div class="card">
      <h3 style="margin-top:0;">Monthly Match Volume (12 months)</h3>
      <canvas id="matchVolumeChart" height="120"></canvas>
    </div>
    <div class="card">
      <h3 style="margin-top:0;">Completion Rate by Category</h3>
      <canvas id="categoryChart" height="120"></canvas>
    </div>
    <div class="card">
      <h3 style="margin-top:0;">Average User Rating Trend (12 months)</h3>
      <canvas id="ratingTrendChart" height="120"></canvas>
    </div>
  </div>

  {{-- Top-Rated Providers Table --}}
  <div class="card" style="margin-top:24px;">
    <h3 style="margin-top:0;">Top-Rated Providers</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Provider</th><th>Rating</th><th>Completed</th><th>Verified</th><th>Skills</th><th>Categories</th></tr>
        </thead>
        <tbody>
          @forelse ($metrics['top_rated_providers'] as $provider)
            <tr>
              <td>{{ $provider->Full_Name }}</td>
              <td>{{ number_format((float) $provider->Avg_Rating, 2) }}/5</td>
              <td>{{ $provider->Total_Completed }}</td>
              <td>{{ $provider->Is_Verified ? 'Yes' : 'No' }}</td>
              <td>{{ $provider->skills->pluck('Skill_Title')->join(', ') }}</td>
              <td>{{ $provider->skills->pluck('Category')->filter()->unique()->join(', ') }}</td>
            </tr>
          @empty
            <tr><td colspan="6" style="text-align:center; color:var(--muted);">No top-rated providers found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Most Active Providers --}}
  <div class="card" style="margin-top:24px;">
    <h3 style="margin-top:0;">Most Active Service Providers</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Provider</th><th>Completed</th><th>Rating</th><th>Verified</th><th>Skills</th></tr>
        </thead>
        <tbody>
          @forelse ($metrics['most_active_providers'] as $provider)
            <tr>
              <td>{{ $provider->Full_Name }}</td>
              <td>{{ $provider->Total_Completed }}</td>
              <td>{{ number_format((float) $provider->Avg_Rating, 2) }}/5</td>
              <td>{{ $provider->Is_Verified ? 'Yes' : 'No' }}</td>
              <td>{{ $provider->skills->pluck('Skill_Title')->join(', ') }}</td>
            </tr>
          @empty
            <tr><td colspan="5" style="text-align:center; color:var(--muted);">No active providers found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Most Requested Categories --}}
  <div class="card" style="margin-top:24px;">
    <h3 style="margin-top:0;">Most Requested Skill Categories</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Category</th><th>Requests</th></tr>
        </thead>
        <tbody>
          @forelse ($metrics['most_requested_categories'] as $cat)
            <tr>
              <td>{{ $cat->Category }}</td>
              <td>{{ $cat->request_count }}</td>
            </tr>
          @empty
            <tr><td colspan="2" style="text-align:center; color:var(--muted);">No requests yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Average Rating Trend Chart --}}
  <div class="card" style="margin-top:24px;">
    <h3 style="margin-top:0;">Average User Rating Trend (12 months)</h3>
    <canvas id="ratingTrendChart" height="120"></canvas>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
  var ctx1 = document.getElementById('requestVolumeChart').getContext('2d');
  new Chart(ctx1, {
    type: 'bar',
    data: {
      labels: @json($metrics['monthly_request_volume']['labels']),
      datasets: [{
        label: 'Requests',
        data: @json($metrics['monthly_request_volume']['data']),
        backgroundColor: 'rgba(79, 70, 229, 0.7)',
        borderColor: 'rgb(79, 70, 229)',
        borderWidth: 1,
      }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });

  var ctx2 = document.getElementById('matchVolumeChart').getContext('2d');
  new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: @json($metrics['monthly_match_volume']['labels']),
      data: @json($metrics['monthly_match_volume']['data']),
      datasets: [{
        label: 'Matches',
        data: @json($metrics['monthly_match_volume']['data']),
        backgroundColor: 'rgba(22, 163, 74, 0.7)',
        borderColor: 'rgb(22, 163, 74)',
        borderWidth: 1,
      }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });

  var ctx3 = document.getElementById('categoryChart').getContext('2d');
  new Chart(ctx3, {
    type: 'bar',
    data: {
      labels: @json($metrics['completion_rate_by_category']['labels']),
      datasets: [{
        label: 'Completion Rate (%)',
        data: @json($metrics['completion_rate_by_category']['data']),
        backgroundColor: 'rgba(245, 158, 11, 0.7)',
        borderColor: 'rgb(245, 158, 11)',
        borderWidth: 1,
      }]
    },
    options: { responsive: true, indexAxis: 'y', scales: { x: { beginAtZero: true, max: 100 } } }
  });

  var ctx4 = document.getElementById('ratingTrendChart').getContext('2d');
  new Chart(ctx4, {
    type: 'line',
    data: {
      labels: @json($metrics['average_rating_trend']['labels']),
      datasets: [{
        label: 'Avg Rating',
        data: @json($metrics['average_rating_trend']['data']),
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        fill: true,
        tension: 0.3,
        pointRadius: 4,
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: false, min: 0, max: 5, ticks: { stepSize: 1 } },
        x: { ticks: { maxRotation: 45, minRotation: 0 } }
      }
    }
  });
})();
</script>
@endsection

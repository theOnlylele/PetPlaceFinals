<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetPlace Admin Dashboard</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f4f7fb; color: #263445; }

        .sidebar { position: fixed; width: 240px; height: 100vh; background: #173b35; color: white; padding: 30px 20px; }
        .sidebar h2 { margin-bottom: 40px; }
        .sidebar a { display: block; padding: 14px; margin-top: 15px; background: #28564b; border-radius: 8px; color: white; text-decoration: none; }
        .sidebar a:hover { background: #397261; }

        .main { margin-left: 240px; padding: 40px; }
        .header { margin-bottom: 26px; }
        .header h1 { margin-bottom: 8px; }
        .header p { color: #64748b; }

        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 18px; margin-bottom: 28px; }
        .card { background: white; padding: 22px; border-radius: 12px; box-shadow: 0 2px 10px #0000000a; }
        .card h3 { color: #64748b; font-size: 13px; margin-bottom: 12px; font-weight: 600; }
        .card .num { font-size: 30px; font-weight: bold; }
        .card .hint { font-size: 12px; color: #94a3b8; margin-top: 4px; }
        .card a { display: inline-block; margin-top: 10px; color: #16834b; text-decoration: none; font-weight: bold; font-size: 14px; }
        .card.alert { border-left: 4px solid #f59e0b; }

        h2.section { font-size: 18px; margin: 6px 0 14px; }
        .charts { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px; }
        .chart-card { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 10px #0000000a; }
        .chart-card h3 { font-size: 15px; margin-bottom: 4px; }
        .chart-card .sub { font-size: 12px; color: #94a3b8; margin-bottom: 18px; }

        /* vertical bars */
        .bars { display: flex; align-items: flex-end; gap: 14px; height: 190px; padding-top: 10px; border-bottom: 1px solid #e2e8f0; }
        .bar-col { flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; }
        .bar { width: 100%; max-width: 44px; background: #16834b; border-radius: 6px 6px 0 0; min-height: 3px; }
        .bar-val { font-size: 12px; font-weight: bold; margin-bottom: 4px; }
        .bar-labels { display: flex; gap: 14px; margin-top: 8px; }
        .bar-labels span { flex: 1; text-align: center; font-size: 12px; color: #64748b; }

        /* horizontal bars */
        .hrow { margin-bottom: 14px; }
        .hrow .top { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px; }
        .track { background: #eef2f7; border-radius: 20px; height: 10px; overflow: hidden; }
        .fill { background: #3b82f6; height: 100%; border-radius: 20px; }

        /* donut */
        .donut-wrap { display: flex; align-items: center; gap: 26px; flex-wrap: wrap; }
        .donut { width: 150px; height: 150px; border-radius: 50%; position: relative; flex: none; }
        .donut::after { content: ''; position: absolute; inset: 30px; background: white; border-radius: 50%; }
        .donut .center { position: absolute; inset: 0; display: grid; place-items: center; z-index: 1; text-align: center; }
        .donut .center b { font-size: 24px; display: block; line-height: 1; }
        .donut .center small { font-size: 11px; color: #94a3b8; }
        .legend { list-style: none; display: grid; gap: 9px; font-size: 13px; }
        .legend li { display: flex; align-items: center; gap: 9px; }
        .dot { width: 11px; height: 11px; border-radius: 50%; flex: none; }
        .legend .val { margin-left: auto; padding-left: 14px; font-weight: bold; }
        .empty { color: #94a3b8; font-size: 14px; }
        
            button {
            padding: 11px 16px;
            margin-top: 14px;
            border: none;
            border-radius: 7px;
            background: #ffffffff;
            color: black;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

                button:hover {
            background: #12563e;
        }

        .toggle-button {
            margin-top: 0;
            background: #e8f5ee;
            color: #176b4d;
        }

        .toggle-button:hover {
            background: #d2ebdd;
        }

        .logout { margin-top: 40px; }
        .logout button { padding: 10px 16px; border: none; border-radius: 6px; cursor: pointer; }

        @media (max-width: 860px) {
            .sidebar { position: static; width: 100%; height: auto; }
            .main { margin-left: 0; padding: 24px; }
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <h2>PetPlace</h2>

        <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
        <a href="{{ route('admin.staff-applications') }}">Staff Applications</a>
        <a href="{{ route('admin.vaccines') }}">Manage Vaccines</a>

        <form class="logout" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log Out</button>
        </form>
    </aside>

    <main class="main">

        <div class="header">
            <h1>Welcome, Administrator!</h1>
            <p>Manage your PetPlace platform. Signed in as {{ auth()->user()->name }}.</p>
        </div>

        {{-- TOP STAT CARDS --}}
        <div class="cards">
            <div class="card">
                <h3>Pet Owners</h3>
                <div class="num">{{ $stats['owners'] }}</div>
                <div class="hint">Registered accounts</div>
            </div>

            <div class="card">
                <h3>Veterinarians</h3>
                <div class="num">{{ $stats['vets'] }}</div>
                <div class="hint">Approved staff</div>
            </div>

            <div class="card">
                <h3>Groomers</h3>
                <div class="num">{{ $stats['groomers'] }}</div>
                <div class="hint">Approved staff</div>
            </div>

            <div class="card">
                <h3>Registered Pets</h3>
                <div class="num">{{ $stats['pets'] }}</div>
                <div class="hint">All pet profiles</div>
            </div>

            <div class="card">
                <h3>Appointments Today</h3>
                <div class="num">{{ $stats['today'] }}</div>
                <div class="hint">{{ now()->format('M j, Y') }}</div>
            </div>

            <div class="card">
                <h3>Pending Bookings</h3>
                <div class="num">{{ $stats['pending'] }}</div>
                <div class="hint">Waiting for a reply</div>
            </div>

            <div class="card">
                <h3>Vaccinations Due</h3>
                <div class="num">{{ $stats['vaccinesDue'] }}</div>
                <div class="hint">Next 30 days</div>
            </div>

            <div class="card {{ $stats['applications'] > 0 ? 'alert' : '' }}">
                <h3>Pending Staff Applications</h3>
                <div class="num">{{ $stats['applications'] }}</div>
                <a href="{{ route('admin.staff-applications') }}">Review Applications →</a>
            </div>
        </div>

        {{-- CHARTS AND REPORTS --}}
        <h2 class="section">Charts and reports</h2>

        <div class="charts">

            {{-- Appointments per month --}}
            @php $maxMonth = max(1, max(array_column($monthly, 'value'))); @endphp
            <div class="chart-card">
                <h3>Appointments per month</h3>
                <p class="sub">Last 6 months</p>
                <div class="bars">
                    @foreach ($monthly as $m)
                        <div class="bar-col">
                            <div class="bar-val">{{ $m['value'] }}</div>
                            <div class="bar" @style(['height: ' . ($m['value'] > 0 ? max(4, round($m['value'] / $maxMonth * 80)) : 0) . '%'])></div>
                        </div>
                    @endforeach
                </div>
                <div class="bar-labels">
                    @foreach ($monthly as $m)<span>{{ $m['label'] }}</span>@endforeach
                </div>
            </div>

            {{-- Appointments by status --}}
            <div class="chart-card">
                <h3>Appointments by status</h3>
                <p class="sub">All bookings</p>
                @if ($status['total'] > 0)
                    <div class="donut-wrap">
                        <div class="donut" @style(['background: ' . $status['gradient']])>
                            <div class="center"><div><b>{{ $status['total'] }}</b><small>total</small></div></div>
                        </div>
                        <ul class="legend">
                            @foreach ($status['items'] as $item)
                                <li><span class="dot" @style(['background: ' . $item['color']])></span>{{ $item['label'] }}<span class="val">{{ $item['value'] }} ({{ $item['percent'] }}%)</span></li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="empty">No appointments yet.</p>
                @endif
            </div>

            {{-- Most requested services --}}
            @php $maxService = max(1, max(array_column($services, 'value') ?: [0])); @endphp
            <div class="chart-card">
                <h3>Most requested services</h3>
                <p class="sub">By number of bookings</p>
                @forelse ($services as $s)
                    <div class="hrow">
                        <div class="top"><span>{{ $s['label'] }}</span><strong>{{ $s['value'] }}</strong></div>
                        <div class="track"><div class="fill" @style(['width: ' . round($s['value'] / $maxService * 100) . '%'])></div></div>
                    </div>
                @empty
                    <p class="empty">No appointments yet.</p>
                @endforelse
            </div>

            {{-- Pets by type --}}
            <div class="chart-card">
                <h3>Pets by type</h3>
                <p class="sub">All registered pets</p>
                @if ($petTypes['total'] > 0)
                    <div class="donut-wrap">
                        <div class="donut" @style(['background: ' . $petTypes['gradient']])>
                            <div class="center"><div><b>{{ $petTypes['total'] }}</b><small>pets</small></div></div>
                        </div>
                        <ul class="legend">
                            @foreach ($petTypes['items'] as $item)
                                <li><span class="dot" @style(['background: ' . $item['color']])></span>{{ $item['label'] }}<span class="val">{{ $item['value'] }} ({{ $item['percent'] }}%)</span></li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="empty">No pets registered yet.</p>
                @endif
            </div>

        </div>
    </main>

</body>
</html>

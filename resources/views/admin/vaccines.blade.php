
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vaccines | PetPlace</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #263445;
        }

        h1, h2, h3, p {
            margin-top: 0;
        }

        .muted {
            color: #64748b;
            font-size: 13px;
        }

      
        .sidebar { position: fixed; width: 240px; height: 100vh; background: #173b35; color: white; padding: 30px 20px; }
        .sidebar h2 { margin-bottom: 40px; }
        .sidebar a { display: block; padding: 14px; margin-top: 15px; background: #28564b; border-radius: 8px; color: white; text-decoration: none; }
        .sidebar a:hover { background: #397261; }

        .main {
            max-width: 1500px;
            margin-left: 240px;
            padding: 35px;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin-bottom: 8px;
        }

        .panel,
        .vaccine {
            padding: 25px;
            margin-bottom: 22px;
            background: white;
            border: 1px solid #e5eaf0;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .panel h2 {
            margin-bottom: 20px;
        }

        .vaccine h3 {
            margin-bottom: 8px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }

        label {
            display: block;
            margin: 12px 0 7px;
            font-size: 13px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: white;
            color: #263445;
            font-family: inherit;
            font-size: 14px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #176b4d;
            box-shadow: 0 0 0 3px rgba(23, 107, 77, 0.1);
        }

        textarea {
            resize: vertical;
        }

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

        .status {
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #e7f7ed;
            color: #176b4d;
            font-size: 14px;
        }

        .error {
            background: #fff1f0;
            color: #b42318;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .vaccine-info {
            margin-bottom: 18px;
        }

        .inactive {
            color: #b42318;
            font-weight: bold;
        }

        .active {
            color: #176b4d;
            font-weight: bold;
        }

        .pricing {
            padding: 14px;
            margin: 15px 0;
            border-radius: 8px;
            background: #f4f7fb;
            font-size: 14px;
            line-height: 1.8;
        }

        details {
            padding-top: 15px;
            border-top: 1px solid #e5eaf0;
        }

        summary {
            color: #176b4d;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        details form {
            margin-top: 15px;
        }

        .logout { 
            margin-top: 40px; 
        }

        .logout button { 
            padding: 10px 16px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
        }

        @media (max-width: 700px) {
            .sidebar {
                position: static;
                width: 100%;
                padding: 18px;
            }

            .sidebar h2 {
                margin-bottom: 15px;
            }

            .sidebar a {
                display: inline-block;
                margin: 5px 3px;
                padding: 10px;
                font-size: 13px;
            }

            .main {
                margin-left: 0;
                padding: 18px;
            }

            .panel,
            .vaccine {
                padding: 18px;
            }

            .row {
                align-items: flex-start;
            }
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
        <header class="page-header">
            <h1>Manage Vaccines & Prices</h1>
            <p class="muted">
                Manage vaccine information and pricing.
                All prices are in Philippine Peso (₱).
            </p>
        </header>

        @if(session('status'))
            <div class="status">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="status error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="panel">
            <h2>Add New Vaccine</h2>

            <form method="POST" action="{{ route('admin.vaccines.store') }}">
                @csrf

                <div class="grid">
                    <div>
                        <label>Vaccine Name</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Enter vaccine name"
                            required
                        >
                    </div>

                    <div>
                        <label>Animal Type</label>
                        <select name="animal_type" required>
                            <option value="dog">Dog</option>
                            <option value="cat">Cat</option>
                            <option value="bird">Bird</option>
                            <option value="rabbit">Rabbit</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label>Pricing Method</label>
                        <select name="pricing_method">
                            <option value="fixed">Fixed Price</option>
                            <option value="per_dose">Price Per Dose</option>
                        </select>
                    </div>

                    <div>
                        <label>Fixed Price (₱)</label>
                        <input
                            type="number"
                            name="fixed_price"
                            min="0"
                            step="0.01"
                            value="0"
                        >
                    </div>
                </div>

                <label>Dose Prices</label>

                <textarea
                    name="dose_prices"
                    rows="4"
                    placeholder="1=500&#10;2=350&#10;3=300"
                ></textarea>

                <p class="muted">
                    Only needed for per-dose pricing.
                    Enter one dose per line using the format: dose number=price.
                </p>

                <button type="submit">Add Vaccine</button>
            </form>
        </section>

        <section>
            <h2>Vaccine Catalog</h2>

            @forelse($vaccines as $vaccine)
                <article class="vaccine">
                    <div class="row">
                        <div class="vaccine-info">
                            <h3>
                                {{ $vaccine->name }}
                                <small class="muted">
                                    ({{ ucfirst($vaccine->animal_type) }})
                                </small>
                            </h3>

                            <p class="muted">
                                Key: {{ $vaccine->vaccine_key }}
                                ·
                                <span class="{{ $vaccine->is_active ? 'active' : 'inactive' }}">
                                    {{ $vaccine->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('admin.vaccines.toggle', $vaccine) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <button type="submit" class="toggle-button">
                                {{ $vaccine->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>

                    <div class="pricing">
                        <strong>Current Pricing</strong>

                        @if($vaccine->pricing_method === 'fixed')
                            <div>
                                Fixed Price:
                                <strong>
                                    ₱{{ number_format((float) $vaccine->fixed_price, 2) }}
                                </strong>
                            </div>
                        @else
                            <div>
                                <strong>Per-Dose Pricing:</strong>
                            </div>

                            @forelse($vaccine->doses as $dose)
                                <div>
                                    Dose {{ $dose->dose_number }}:
                                    ₱{{ number_format((float) $dose->price, 2) }}
                                </div>
                            @empty
                                <div class="muted">
                                    No dose prices configured.
                                </div>
                            @endforelse
                        @endif
                    </div>

                    <details>
                        <summary>Edit Vaccine and Prices</summary>

                        <form
                            method="POST"
                            action="{{ route('admin.vaccines.update', $vaccine) }}"
                        >
                            @csrf
                            @method('PUT')

                            <div class="grid">
                                <div>
                                    <label>Vaccine Name</label>
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ $vaccine->name }}"
                                        required
                                    >
                                </div>

                                <div>
                                    <label>Animal Type</label>
                                    <select name="animal_type">
                                        <option value="dog" @selected($vaccine->animal_type === 'dog')>Dog</option>
                                        <option value="cat" @selected($vaccine->animal_type === 'cat')>Cat</option>
                                        <option value="bird" @selected($vaccine->animal_type === 'bird')>Bird</option>
                                        <option value="rabbit" @selected($vaccine->animal_type === 'rabbit')>Rabbit</option>
                                        <option value="other" @selected($vaccine->animal_type === 'other')>Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label>Pricing Method</label>
                                    <select name="pricing_method">
                                        <option value="fixed" @selected($vaccine->pricing_method === 'fixed')>Fixed Price</option>
                                        <option value="per_dose" @selected($vaccine->pricing_method === 'per_dose')>Price Per Dose</option>
                                    </select>
                                </div>

                                <div>
                                    <label>Fixed Price (₱)</label>
                                    <input
                                        type="number"
                                        name="fixed_price"
                                        min="0"
                                        step="0.01"
                                        value="{{ $vaccine->fixed_price }}"
                                    >
                                </div>
                            </div>

                            <label>Dose Prices</label>

                            <textarea name="dose_prices" rows="4">@foreach($vaccine->doses as $dose){{ $dose->dose_number }}={{ number_format((float) $dose->price, 2, '.', '') }}{{ !$loop->last ? "\n" : '' }}@endforeach</textarea>

                            <p class="muted">
                                Format: dose number=price.
                                Example: 1=500
                            </p>

                            <button type="submit">Save Changes</button>
                        </form>
                    </details>
                </article>
            @empty
                <div class="panel">
                    <p class="muted">
                        No vaccines found. Add a new vaccine to get started.
                    </p>
                </div>
            @endforelse
        </section>
    </main>
</body>
</html>

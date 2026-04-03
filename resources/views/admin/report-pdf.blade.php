<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rental Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h1, h2, h3 {
            margin-bottom: 10px;
        }

        .meta, .summary {
            margin-bottom: 20px;
        }

        .summary p, .meta p {
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #999;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 30px; ">
    <img src="{{ public_path('images/hfhmn.jpg') }}" style="height: 120px;"><br>
    <h2>Soleil Laluna Boutique</h2>
    <h3>Rental Report</h3>
    </div>

    <div class="meta">
        <p><strong>Generated at:</strong> {{ now()->format('F d, Y h:i A') }}</p>
        <p><strong>Period:</strong> {{ $from ? $from : 'Beginning' }} to {{ $to ? $to : 'Present' }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <p><strong>Confirmed Rentals:</strong> {{ $confirmed }}</p>
        <p><strong>Pending:</strong> {{ $pending }}</p>
        <p><strong>Cancelled:</strong> {{ $cancelled }}</p>
        <p><strong>Returned:</strong> {{ $returned }}</p>
        <p><strong>Total Income:</strong> ₱{{ number_format($totalIncome, 2) }}</p>
    </div>

    <h3>Monthly Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Reservations</th>
                <th>Income</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthly as $row)
                <tr>
                    <td>{{ $row->month }}</td>
                    <td>{{ $row->reservations }}</td>
                    <td>₱{{ number_format($row->income, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Most Rented Items</h3>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Times Rented</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mostRentedItems as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['total'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
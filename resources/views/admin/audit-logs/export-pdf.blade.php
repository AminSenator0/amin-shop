<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs Export</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; direction: rtl; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .severity-info { color: #059669; }
        .severity-warning { color: #d97706; }
        .severity-high { color: #ea580c; }
        .severity-critical { color: #dc2626; }
    </style>
</head>
<body>
    <h2>📋 گزارش لاگ‌های سیستم</h2>
    <p>تاریخ تولید: {{ verta(now())->format('Y/m/d H:i') }}</p>
    <p>تعداد رکورد: {{ $logs->count() }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Severity</th>
                <th>Category</th>
                <th>Action</th>
                <th>User</th>
                <th>IP</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ verta($log->created_at)->format('Y/m/d H:i') }}</td>
                <td class="severity-{{ $log->severity->value }}">{{ $log->severity->label() }}</td>
                <td>{{ $log->category->label() }}</td>
                <td>{{ $log->action->label() }}</td>
                <td>{{ $log->user?->name ?? 'Guest' }}</td>
                <td>{{ $log->ip_address }}</td>
                <td>{{ $log->description ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
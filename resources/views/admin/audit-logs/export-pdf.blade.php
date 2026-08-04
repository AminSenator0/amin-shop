<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs Export</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', 'Vazirmatn', 'Tahoma', sans-serif; 
            font-size: 11px; 
            direction: rtl; 
            color: #334155;
            line-height: 1.6;
            margin: 0;
            padding: 24px;
        }

        /* Header */
        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
            margin: 0 0 8px 0;
        }
        .header-meta {
            font-size: 10px;
            color: #64748b;
        }
        .header-meta span {
            margin-left: 16px;
        }

        /* Table */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 12px;
            font-size: 10px;
        }
        thead { 
            display: table-header-group;
        }
        th { 
            background-color: #f1f5f9; 
            font-weight: 700;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 8px 6px;
            text-align: right;
            white-space: nowrap;
        }
        td { 
            border: 1px solid #e2e8f0; 
            padding: 7px 6px; 
            text-align: right;
            vertical-align: top;
        }
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        tbody tr:hover {
            background-color: #f1f5f9;
        }

        /* Severity colors */
        .severity-info { 
            color: #059669; 
            font-weight: 600;
        }
        .severity-warning { 
            color: #d97706; 
            font-weight: 600;
        }
        .severity-high { 
            color: #ea580c; 
            font-weight: 600;
        }
        .severity-critical { 
            color: #dc2626; 
            font-weight: 700;
        }

        /* Columns widths */
        .col-id { width: 40px; text-align: center; }
        .col-date { width: 100px; white-space: nowrap; }
        .col-severity { width: 70px; text-align: center; }
        .col-category { width: 90px; }
        .col-action { width: 100px; }
        .col-user { width: 110px; }
        .col-ip { width: 100px; direction: ltr; text-align: left; }
        .col-desc { min-width: 150px; }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }

        /* Page break */
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">گزارش لاگ‌های سیستم</div>
        <div class="header-meta">
            <span>تاریخ تولید: {{ verta(now())->format('Y/m/d H:i') }}</span>
            <span>تعداد رکورد: {{ $logs->count() }}</span>
            <span>فرمت: PDF</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-id">#</th>
                <th class="col-date">تاریخ</th>
                <th class="col-severity">Severity</th>
                <th class="col-category">دسته‌بندی</th>
                <th class="col-action">Action</th>
                <th class="col-user">کاربر</th>
                <th class="col-ip">IP</th>
                <th class="col-desc">توضیحات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td class="col-id">{{ $log->id }}</td>
                <td class="col-date">{{ verta($log->created_at)->format('Y/m/d H:i') }}</td>
                <td class="col-severity severity-{{ $log->severity->value }}">{{ $log->severity->label() }}</td>
                <td class="col-category">{{ $log->category->label() }}</td>
                <td class="col-action">{{ $log->action->label() }}</td>
                <td class="col-user">{{ $log->user?->name ?? 'Guest' }}</td>
                <td class="col-ip">{{ $log->ip_address }}</td>
                <td class="col-desc">{{ $log->description ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        گزارش تولید شده توسط سیستم مدیریت لاگ‌ها | {{ config('app.name') }}
    </div>
</body>
</html>
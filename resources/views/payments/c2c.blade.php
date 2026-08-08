@extends('layouts.shop')

@section('title', 'پرداخت کارت به کارت - سفارش #' . $order->order_number)

@push('head')
<style>
    .c2c-container { max-width: 680px; margin: 40px auto; background: #fff; border-radius: 28px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08); }
    .c2c-header { text-align: center; margin-bottom: 30px; }
    .c2c-timer { display: inline-flex; align-items: center; gap: 8px; background: #fff1f2; color: #e11d48; padding: 8px 20px; border-radius: 100px; font-weight: 800; font-size: 14px; margin-bottom: 15px; }
    .c2c-timer svg { animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
    .c2c-title { font-size: 24px; font-weight: 900; margin-bottom: 8px; }
    .c2c-subtitle { color: #64748b; font-size: 14px; }
    .c2c-summary { background: #f8fafc; border-radius: 16px; padding: 20px; margin: 25px 0; border: 1px solid #f1f5f9; }
    .c2c-summary-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 14px; border-bottom: 1px dashed #e2e8f0; }
    .c2c-summary-row:last-child { border: none; font-weight: 800; color: #0f172a; }
    .c2c-alert { background: #fffbeb; border: 1px solid #fde68a; padding: 16px; border-radius: 14px; display: flex; gap: 12px; margin-bottom: 25px; font-size: 13.5px; color: #92400e; line-height: 1.7; }
    .c2c-alert strong { color: #78350f; }
    .c2c-price-box { border: 2px dashed #0f172a; border-radius: 24px; padding: 30px; text-align: center; margin-bottom: 25px; position: relative; }
    .c2c-price-label { font-size: 13px; color: #64748b; font-weight: 700; margin-bottom: 12px; }
    .c2c-price-wrap { display: flex; justify-content: center; align-items: center; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
    .c2c-price-amount { font-size: 42px; font-weight: 900; font-family: monospace; letter-spacing: -1px; }
    .c2c-price-currency { font-size: 18px; font-weight: 800; }
    .c2c-copy-btn { background: #0f172a; color: #fff; border: none; padding: 10px 18px; border-radius: 100px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
    .c2c-copy-btn:hover { background: #1e293b; }
    .c2c-toman { display: inline-block; background: #f8fafc; padding: 6px 16px; border-radius: 100px; font-size: 13px; color: #475569; border: 1px solid #e2e8f0; margin-top: 5px; }
    .c2c-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 30px; }
    .c2c-card { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-radius: 16px; padding: 20px; position: relative; overflow: hidden; }
    .c2c-card-header { display: flex; justify-content: space-between; margin-bottom: 18px; font-weight: 900; font-size: 14px; }
    .c2c-card-number { font-family: monospace; font-size: 18px; font-weight: 800; letter-spacing: 2px; direction: ltr; text-align: left; margin-bottom: 12px; }
    .c2c-card-footer { display: flex; justify-content: space-between; align-items: flex-end; }
    .c2c-card-name { font-size: 13px; font-weight: 800; }
    .c2c-card-copy { background: #fff; border: 1px solid rgba(0,0,0,0.08); padding: 6px 12px; border-radius: 100px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .c2c-action { margin-top: 25px; }
    .c2c-btn-main { width: 100%; background: #0f172a; color: #fff; border: none; padding: 18px; border-radius: 16px; font-size: 16px; font-weight: 800; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; }
    .c2c-btn-main:disabled { background: #94a3b8; cursor: not-allowed; }
    .c2c-spinner { border: 3px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; width: 20px; height: 20px; animation: spin 0.8s linear infinite; display: none; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .c2c-status-msg { text-align: center; color: #0284c7; font-size: 13px; font-weight: 700; margin-top: 12px; display: none; }
    .c2c-manual { text-align: center; margin-top: 16px; font-size: 13px; color: #64748b; cursor: pointer; text-decoration: underline; font-weight: 600; }
    .c2c-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 20px; }
    .c2c-modal-overlay.active { display: flex; }
    .c2c-modal-content { background: #fff; width: 100%; max-width: 440px; border-radius: 24px; padding: 30px; position: relative; animation: pop 0.3s ease; }
    @keyframes pop { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .c2c-modal-close { position: absolute; top: 15px; left: 15px; background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 18px; }
    .c2c-upload-box { border: 2px dashed #cbd5e1; border-radius: 16px; padding: 30px; text-align: center; cursor: pointer; background: #f8fafc; margin: 20px 0; }
    .c2c-upload-box:hover { border-color: #3b82f6; background: #eff6ff; }
    .c2c-btn-modal { width: 100%; background: #0f172a; color: #fff; border: none; padding: 14px; border-radius: 12px; font-weight: 700; cursor: pointer; }
    .c2c-btn-modal:disabled { background: #94a3b8; }
    @media (max-width: 768px) {
        .c2c-container { margin: 15px; padding: 25px 20px; }
        .c2c-price-amount { font-size: 34px; }
    }
</style>
@endpush

@section('content')
<div class="c2c-container">
    <div class="c2c-header">
        <div class="c2c-timer">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="countdown">15:00</span>
        </div>
        <h1 class="c2c-title">درگاه پرداخت کارت به کارت</h1>
        <p class="c2c-subtitle">تأیید خودکار از طریق شبکه بانکی شتاب</p>
    </div>

    <div class="c2c-summary">
        <div class="c2c-summary-row">
            <span>شماره سفارش</span>
            <strong>#{{ $order->id }}</strong>
        </div>
        <div class="c2c-summary-row">
            <span>مبلغ سفارش</span>
            <strong>{{ number_format($order->total) }} تومان</strong>
        </div>
    </div>

    <div class="c2c-alert">
        <span style="font-size:22px">⚠️</span>
        <p><strong>توجه:</strong> برای تأیید خودکار، دقیقاً مبلغ <strong>{{ number_format($c2c->exact_rial) }} ریال</strong> را واریز کنید. هرگونه مغایرت باعث تأخیر می‌شود.</p>
    </div>

    <div class="c2c-price-box">
        <div class="c2c-price-label">مبلغ دقیق جهت واریز</div>
        <div class="c2c-price-wrap">
            <div class="c2c-price-amount" id="exact-rial">{{ number_format($c2c->exact_rial) }}</div>
            <span class="c2c-price-currency">ریال</span>
            <button class="c2c-copy-btn" onclick="copyToClipboard('{{ $c2c->exact_rial }}', this)">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                کپی
            </button>
        </div>
        <div class="c2c-toman">معادل {{ number_format($order->total) }} تومان</div>
    </div>

    <div class="c2c-cards">
        <div class="c2c-card">
            <div class="c2c-card-header">
                <span>بانک ملت</span>
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0"/></svg>
            </div>
            <div class="c2c-card-number">6104 3373 6026 0150</div>
            <div class="c2c-card-footer">
                <div class="c2c-card-name">محمدرضا برجی</div>
                <button class="c2c-card-copy" onclick="copyToClipboard('6104337360260150', this)">کپی کارت</button>
            </div>
        </div>
    </div>

    <div class="c2c-action">
        <button class="c2c-btn-main" id="verify-btn" onclick="startChecking()">
            <div class="c2c-spinner" id="spinner"></div>
            <span id="btn-text">من پرداخت کردم (بررسی تراکنش)</span>
        </button>
        <div class="c2c-status-msg" id="status-msg">در حال بررسی ارتباط با بانک...</div>
        <div class="c2c-manual" onclick="openModal()">مبلغ را واریز کردم ولی تأیید نشد</div>
    </div>
</div>

<div class="c2c-modal-overlay" id="receiptModal">
    <div class="c2c-modal-content">
        <button class="c2c-modal-close" onclick="closeModal()">&times;</button>
        <h3 style="margin-bottom:15px">بارگذاری رسید واریزی</h3>
        <p style="font-size:13px; color:#475569; margin-bottom:15px">اگر بررسی خودکار ناموفق بود، رسید خود را آپلود کنید تا کارشناسان بررسی کنند.</p>
        
        <div class="c2c-upload-box" onclick="document.getElementById('receiptFile').click()">
            <svg width="40" height="40" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <div style="margin-top:8px; font-weight:700">انتخاب تصویر رسید</div>
            <input type="file" id="receiptFile" style="display:none" accept="image/*" onchange="fileSelected(this)">
        </div>
        <div id="fileName" style="font-size:12px; color:#10b981; margin-bottom:10px"></div>
        
        <button class="c2c-btn-modal" id="submitReceiptBtn" onclick="submitReceipt()">ارسال رسید</button>
    </div>
</div>

<script>
    const orderId = {{ $order->id }};
    let checkInterval;
    let timeLeft = {{ max(0, now()->diffInSeconds($c2c->expires_at, false)) }};

    function updateTimer() {
        if (timeLeft <= 0) {
            document.getElementById('countdown').innerText = "00:00";
            document.getElementById('verify-btn').disabled = true;
            document.getElementById('btn-text').innerText = "زمان پرداخت به پایان رسید";
            clearInterval(checkInterval);
            return;
        }
        const m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const s = (timeLeft % 60).toString().padStart(2, '0');
        document.getElementById('countdown').innerText = m + ":" + s;
        timeLeft--;
    }
    setInterval(updateTimer, 1000);
    updateTimer();

    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '✔ کپی شد';
            btn.style.background = '#10b981';
            setTimeout(() => { btn.innerHTML = original; btn.style.background = ''; }, 2000);
        });
    }

    function startChecking() {
        const btn = document.getElementById('verify-btn');
        const spinner = document.getElementById('spinner');
        const text = document.getElementById('btn-text');
        const status = document.getElementById('status-msg');

        btn.disabled = true;
        spinner.style.display = 'block';
        text.innerText = 'در حال بررسی...';
        status.style.display = 'block';

        checkInterval = setInterval(() => {
            fetch('{{ route("c2c.check", $order) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.verified) {
                    clearInterval(checkInterval);
                    text.innerText = '✔ پرداخت تأیید شد!';
                    btn.style.background = '#10b981';
                    status.style.color = '#10b981';
                    status.innerText = 'در حال انتقال...';
                    setTimeout(() => window.location.href = data.redirect_url, 1500);
                } else if (data.expired) {
                    clearInterval(checkInterval);
                    text.innerText = 'زمان به پایان رسید';
                    status.innerText = 'لطفاً سفارش جدید ثبت کنید.';
                }
            });
        }, 5000);
    }

    function openModal() { document.getElementById('receiptModal').classList.add('active'); }
    function closeModal() { document.getElementById('receiptModal').classList.remove('active'); }
    function fileSelected(input) {
        if (input.files[0]) {
            document.getElementById('fileName').innerText = 'فایل: ' + input.files[0].name;
        }
    }

    function submitReceipt() {
        const file = document.getElementById('receiptFile').files[0];
        if (!file) { alert('لطفاً یک تصویر انتخاب کنید'); return; }

        const formData = new FormData();
        formData.append('receipt', file);

        const btn = document.getElementById('submitReceiptBtn');
        btn.disabled = true;
        btn.innerText = 'در حال آپلود...';

        fetch('{{ route("c2c.receipt", $order) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeModal();
                document.querySelector('.c2c-container').innerHTML = `
                    <div style="text-align:center; padding:50px 20px;">
                        <svg width="80" height="80" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h2 style="margin-top:20px; line-height:1.6">رسید شما ارسال شد.<br>لطفاً تا بررسی صبوری کنید.</h2>
                    </div>
                `;
            } else {
                alert(data.message || 'خطا در آپلود');
                btn.disabled = false;
                btn.innerText = 'ارسال رسید';
            }
        });
    }
</script>
@endsection
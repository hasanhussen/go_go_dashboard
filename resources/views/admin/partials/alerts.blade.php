{{-- resources/views/components/alerts.blade.php --}}
<style>
    .custom-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        border-radius: 10px;
        padding: 15px 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
        transition: all 0.3s ease-in-out;
    }
    .custom-alert .icon {
        font-size: 24px;
        margin-right: 12px;
    }
    .custom-alert.success { border-left: 6px solid #4caf50; }
    .custom-alert.error { border-left: 6px solid #f44336; }
    .custom-alert.warning { border-left: 6px solid #ff9800; }
    .custom-alert.info { border-left: 6px solid #2196f3; }
    .custom-alert strong { font-size: 16px; display: block; }
    .custom-alert small { color: #555; }
    .custom-alert button {
        background: transparent;
        border: none;
        font-size: 18px;
        color: #888;
    }
    .custom-alert button:hover {
        color: #000;
    }
</style>

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="custom-alert error">
            <div class="d-flex align-items-center">
                <div class="icon">❌</div>
                <div>
                    <strong>Error</strong>
                    <small>{{ $error }}</small>
                </div>
            </div>
            <button onclick="this.parentElement.remove()">×</button>
        </div>
    @endforeach
@endif

@if (session('success'))
    <div class="custom-alert success">
        <div class="d-flex align-items-center">
            <div class="icon">✅</div>
            <div>
                <strong>Success</strong>
                <small>{{ session('success') }}</small>
            </div>
        </div>
        <button onclick="this.parentElement.remove()">×</button>
    </div>
@endif

@if (session('error'))
    <div class="custom-alert error">
        <div class="d-flex align-items-center">
            <div class="icon">❌</div>
            <div>
                <strong>Error</strong>
                <small>{{ session('error') }}</small>
            </div>
        </div>
        <button onclick="this.parentElement.remove()">×</button>
    </div>
@endif

@if (session('warning'))
    <div class="custom-alert warning">
        <div class="d-flex align-items-center">
            <div class="icon">⚠️</div>
            <div>
                <strong>Warning</strong>
                <small>{{ session('warning') }}</small>
            </div>
        </div>
        <button onclick="this.parentElement.remove()">×</button>
    </div>
@endif

@if (session('info'))
    <div class="custom-alert info">
        <div class="d-flex align-items-center">
            <div class="icon">ℹ️</div>
            <div>
                <strong>Info</strong>
                <small>{{ session('info') }}</small>
            </div>
        </div>
        <button onclick="this.parentElement.remove()">×</button>
    </div>
@endif

<script>
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.custom-alert').forEach(alert => alert.remove());
    }, 5000);
</script>

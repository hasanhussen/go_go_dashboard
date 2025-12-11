<div class="mt-4 p-3 border rounded bg-white shadow-sm" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-2">
        
        <!-- العنوان على اليمين -->
        <h6 class="fw-bold text-primary mb-0" style="font-size: 1rem;">
            🕒 أوقات العمل
        </h6>

        <!-- حالة المتجر على اليسار -->
        @if ($store->isOpenNow())
            <span class="badge bg-success d-inline-flex align-items-center" style="font-size: 0.9rem;">
                <span class="ms-2">مفتوح الآن</span>
                <span style="width:10px;height:10px;background:#28a745;border-radius:50%;display:inline-block"></span>
            </span>
        @else
            <span class="badge bg-danger d-inline-flex align-items-center" style="font-size: 0.9rem;">
                <span class="ms-2">مغلق الآن</span>
                <span style="width:10px;height:10px;background:#dc3545;border-radius:50%;display:inline-block"></span>
            </span>
        @endif

    </div>

    <div class="table-responsive">
        <table class="table table-bordered mb-0" style="font-size: 0.95rem; color: #333;">
            <thead class="table-light" style="background-color: #f8f9fc; font-weight: 600;">
                <tr>
                    <th style="width:34%" class="text-end">اليوم</th>
                    <th style="width:33%" class="text-center">من</th>
                    <th style="width:33%" class="text-center">إلى</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($store->workingHours as $workingHour)
                    <tr>
                        <td class="fw-semibold text-end text-capitalize">{{ $workingHour->day }}</td>

                        <td class="text-center">
                            {{ $workingHour->open_at 
                                ? \Carbon\Carbon::parse($workingHour->open_at)->format('H:i') 
                                : '-' }}
                        </td>

                        <td class="text-center">
                            {{ $workingHour->close_at 
                                ? \Carbon\Carbon::parse($workingHour->close_at)->format('H:i') 
                                : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

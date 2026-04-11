const API = '../../../api/dashboard.php';
let currentType = 'daily';

$(document).ready(function () {
    $('.report-tab').on('click', function () {
        $('.report-tab').removeClass('active');
        $(this).addClass('active');
        currentType = $(this).data('type');
    });
    loadReport();
});

function fmtDatetime(v) {
    if (!v) return '—';
    return String(v).substring(0, 16).replace('T', ' ');
}

function fmt(n) {
    return '₱' + parseFloat(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

function loadReport() {
    const period = $('#reportDate').val();
    if (!period) { alert('Please select a date.'); return; }

    $('#reportBody').html('<tr><td colspan="12" class="text-center py-4">' +
        '<div class="spinner-border spinner-border-sm text-secondary"></div> Loading...</td></tr>');

    $.get(API, { action: 'reports', type: currentType, period }, function (raw) {
        const resp = typeof raw === 'string' ? JSON.parse(raw) : raw;
        if (resp.status !== 'success') {
            $('#reportBody').html('<tr><td colspan="12" class="text-center py-4 text-danger">Failed to load report.</td></tr>');
            return;
        }

        const { bookings, summary } = resp.data;
        const { start, end, type }  = resp.period;

        $('#reportPeriodLabel').text('Period: ' + (type === 'daily' ? start : start + ' to ' + end));

        $('#sumTotal').text(summary.total_bookings);
        $('#sumConfirmed').text(summary.confirmed);
        $('#sumCancelled').text(summary.cancelled);
        $('#sumRefunded').text(fmt(summary.total_refunded));
        $('#sumGross').text(fmt(summary.gross_revenue));
        $('#sumRevenue').text(fmt(summary.total_revenue));
        $('#rowCount').text(bookings.length + ' record' + (bookings.length !== 1 ? 's' : ''));

        if (!bookings.length) {
            $('#reportBody').html('<tr><td colspan="12" class="text-center py-5 text-muted">No bookings for this period.</td></tr>');
            return;
        }

        const pillClass = { pending:'pill-pending', confirmed:'pill-confirmed', cancelled:'pill-cancelled', completed:'pill-completed' };
        const payLabel  = { downpayment_only:'Partial', full_payment:'Full' };

        let html = '';
        bookings.forEach(b => {
            const pill        = pillClass[b.status] || 'pill-pending';
            const isCancelled = b.status === 'cancelled';
            const rowStyle    = isCancelled ? 'opacity:0.6;' : '';

            const dpAmt  = parseFloat(b.downpayment_amount || 0);
            const remAmt = parseFloat(b.remaining_balance  || 0);
            const totAmt = parseFloat(b.total_amount       || 0);

            const dpCell  = (!isCancelled && b.downpayment_status === 'confirmed')
                ? fmt(dpAmt)
                : '<span class="text-muted">—</span>';
            const remCell = (!isCancelled && b.remaining_status === 'confirmed')
                ? fmt(remAmt)
                : '<span class="text-muted">—</span>';
            const totCell = isCancelled
                ? `<s class="text-muted">${fmt(totAmt)}</s>`
                : (totAmt ? fmt(totAmt) : '—');

            const refundCell = (b.cancellation_status === 'refunded')
                ? `<span class="text-danger fw-semibold">${fmt(b.refund_amount)}</span>`
                : (b.cancellation_status === 'pending' ? '<span class="text-warning small">Pending</span>' : '—');

            const pay = b.payment_type
                ? `<span class="status-pill" style="background:#e0e7ff;color:#3730a3;">${payLabel[b.payment_type] || '—'}</span>`
                : '—';

            html += `<tr style="${rowStyle}">
                <td class="text-muted">#${b.booking_id}</td>
                <td class="fw-semibold">${b.fullname || '—'}</td>
                <td>${b.room_name || '—'}</td>
                <td>${b.room_type || '—'}</td>
                <td>${fmtDatetime(b.check_in)}</td>
                <td>${fmtDatetime(b.check_out)}</td>
                <td><span class="status-pill ${pill}">${b.status}</span></td>
                <td>${dpCell}</td>
                <td>${remCell}</td>
                <td class="fw-semibold">${totCell}</td>
                <td>${refundCell}</td>
                <td>${pay}</td>
            </tr>`;
        });

        $('#reportBody').html(html);
    }, 'text');
}

@php
    $statusDisplay = [
        'pending' => [
            'label' => '○ Pending transfer',
            'class' => 'status-pending',
        ],
        'approved' => [
            'label' => '✓ Approved',
            'class' => 'status-approved',
        ],
        'transferred' => [
            'label' => '✓ Transferred',
            'class' => 'status-done',
        ],
        'rejected' => [
            'label' => '✕ Rejected',
            'class' => 'status-rejected',
        ],
    ];

    $currentStatus = $statusDisplay[$claim->transfer_status] ?? [
        'label' => 'Unknown',
        'class' => 'status-pending',
    ];
@endphp

<span class="status-badge {{ $currentStatus['class'] }}">
    {{ $currentStatus['label'] }}
</span>
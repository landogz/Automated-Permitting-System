@props([
    'status' => 'draft',
    'label' => null,
])

@php
    $key = strtolower(str_replace([' ', '-'], '_', (string) $status));
    $labels = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_evaluation' => 'Under Evaluation',
        'for_inspection' => 'For Inspection',
        'for_compliance' => 'For Compliance',
        'for_payment' => 'For Payment',
        'released' => 'Released',
        'disapproved' => 'Disapproved',
    ];
    $text = $label ?? ($labels[$key] ?? str_replace('_', ' ', (string) $status));
@endphp

<span {{ $attributes->class(['apics-status', 'apics-status--'.$key]) }}>{{ $text }}</span>

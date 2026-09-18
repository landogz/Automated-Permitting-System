@extends('layouts.velzon.policy')

@php($title = __('Frequently Asked Questions'))

@section('policy-body')
<div class="accordion" id="gwtFaqs">
    <div class="accordion-item">
        <h2 class="accordion-header" id="faq1-h">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">{{ __('Who can register in APICS?') }}</button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse show" aria-labelledby="faq1-h" data-bs-parent="#gwtFaqs">
            <div class="accordion-body">{{ __('Applicants (owners / authorized representatives) may self-register. An OCBO administrator must approve the account before first sign-in.') }}</div>
        </div>
    </div>
    <div class="accordion-item">
        <h2 class="accordion-header" id="faq2-h">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">{{ __('How do I track my building permit?') }}</button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" aria-labelledby="faq2-h" data-bs-parent="#gwtFaqs">
            <div class="accordion-body">{{ __('After sign-in, open Applications. Status labels follow Draft → Submitted → Evaluation → Inspection → Payment → Release.') }}</div>
        </div>
    </div>
    <div class="accordion-item">
        <h2 class="accordion-header" id="faq3-h">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">{{ __('Is APICS the official OCBO channel?') }}</button>
        </h2>
        <div id="faq3" class="accordion-collapse collapse" aria-labelledby="faq3-h" data-bs-parent="#gwtFaqs">
            <div class="accordion-body">{{ __('APICS is the Phase I Automated Permitting, Inspection, and Compliance System for the City of San Fernando OCBO. Confirm current acceptance status with OCBO if you need certified guidance.') }}</div>
        </div>
    </div>
</div>
@endsection

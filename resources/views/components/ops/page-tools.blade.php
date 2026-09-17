<button type="button" class="btn btn-soft-secondary btn-sm" id="btn-browse-routing-templates">
    <i class="ri-route-line align-bottom me-1"></i>{{ __('Routing templates') }}
</button>
<a
    href="{{ route('admin.routing-templates') }}"
    class="btn btn-soft-primary btn-sm d-none"
    data-nav-roles="admin"
    data-nav-permissions="workflow.manage"
    title="{{ __('Manage routing templates') }}"
>
    <i class="ri-settings-3-line align-bottom"></i>
    <span class="d-none d-md-inline ms-1">{{ __('Manage') }}</span>
</a>

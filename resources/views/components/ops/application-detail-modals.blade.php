{{-- Shared staff application detail viewer (available on every Velzon admin page) --}}
<div class="modal fade" id="modal-ops-application-detail" tabindex="-1" aria-labelledby="modal-ops-application-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable apics-modal apics-app-detail-dialog">
        <div class="modal-content">
            <div class="modal-header apics-app-detail__header flex-wrap gap-2 align-items-start">
                <div class="min-w-0 flex-grow-1 pe-lg-2">
                    <h5 class="modal-title mb-1" id="modal-ops-application-detail-label">Application details</h5>
                    <div id="ops-application-detail-header-meta" class="apics-app-detail__header-meta"></div>
                </div>
                <div id="ops-application-detail-actions" class="apics-app-detail__actions d-flex flex-wrap gap-1 justify-content-end"></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="ops-application-detail-tabnav" class="apics-app-detail__tabnav border-bottom px-3 pt-2 bg-body"></div>
            <div class="modal-body apics-app-detail__body" id="ops-application-detail-body">
                <div class="text-center text-muted py-4">Loading…</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-line" aria-hidden="true"></i>
                        <span class="btn-label">Close</span>
                    </button>
            </div>
        </div>
    </div>
</div>

{{-- Expanded GIS map for zoning / site inspection --}}
<div class="modal fade" id="modal-ops-map-expand" tabindex="-1" aria-labelledby="modal-ops-map-expand-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl apics-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-ops-map-expand-label">Project site map</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="ops-map-expand-body">
                <div class="text-muted text-center py-5">Loading map…</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn apics-btn-cancel" data-bs-dismiss="modal">
                        <i class="ri-close-line" aria-hidden="true"></i>
                        <span class="btn-label">Close</span>
                    </button>
            </div>
        </div>
    </div>
</div>

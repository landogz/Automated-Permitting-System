import { escapeHtml } from '../../../utils/bootstrap-modal';
import { openGovernmentPrint } from '../../../utils/print';
import { confirmAction, confirmWithReason, toastError, toastSuccess } from '../../../utils/toast';
import { humanizeKey } from '../../../utils/ops-ui';
import { normalizeSections } from '../../applications/dynamic-fields';
import { formatFieldValue, formatPhpCurrency, formatAreaSqm } from './formatters';
import type { StaffApplicationDetail } from './types';

export async function printQms36Summary(app: StaffApplicationDetail): Promise<void> {
    const payload = (app.payload || {}) as Record<string, unknown>;
    const sections = normalizeSections(app.form?.schema || null);

    const bodyParts = sections
        .map((section) => {
            const rows = section.fields
                .map((field) => {
                    const raw = payload[field.name];
                    if (raw === null || raw === undefined || raw === '') return '';
                    const display = formatFieldValue(field, raw).replace(/<[^>]+>/g, '');
                    return `<tr><th>${escapeHtml(field.label)}</th><td>${escapeHtml(display)}</td></tr>`;
                })
                .filter(Boolean)
                .join('');
            if (!rows) return '';
            return `<h3 style="font-size:13px;margin:14px 0 6px;text-transform:uppercase;letter-spacing:.04em;color:#64748b">${escapeHtml(section.title)}</h3>
                <table class="meta" style="width:100%">${rows}</table>`;
        })
        .filter(Boolean)
        .join('');

    openGovernmentPrint({
        formCode: app.form?.code || 'QMS-36',
        formTitle: app.form?.title || 'Unified Application Form',
        documentNo: app.application_no || app.uuid,
        subtitle: app.project_title || undefined,
        meta: [
            { label: 'Status', value: humanizeKey(app.status || '—') },
            { label: 'Classification', value: humanizeKey(app.classification || 'Unclassified') },
            { label: 'Applicant', value: String(payload.owner_name || app.applicant?.name || '—') },
            { label: 'Location', value: app.project_location || '—' },
            {
                label: 'Estimated cost',
                value:
                    payload.estimated_cost != null && payload.estimated_cost !== ''
                        ? formatPhpCurrency(payload.estimated_cost).replace(/<[^>]+>/g, '')
                        : '—',
            },
            {
                label: 'Floor area',
                value:
                    payload.floor_area != null && payload.floor_area !== ''
                        ? formatAreaSqm(payload.floor_area)
                        : '—',
            },
        ],
        bodyHtml: bodyParts || '<p>No form answers recorded.</p>',
        signatures: [
            { role: 'Prepared / Printed by' },
            { role: 'Reviewed by (Evaluator)' },
            { role: 'Noted by (Building Official)' },
        ],
        windowTitle: `${app.form?.code || 'QMS-36'} · ${app.application_no || ''}`,
    });
}

export async function generateRoutingSlip(app: StaffApplicationDetail): Promise<StaffApplicationDetail | null> {
    if (!app.classification) {
        toastError('Classify the application before generating a routing slip.');
        return null;
    }
    if (
        !(await confirmAction(
            'Generate routing slip?',
            'Creates department review steps from the matching QMS-61/62 routing template.',
        ))
    ) {
        return null;
    }

    try {
        const { data: res } = await window.axios.post(`/api/v1/staff/applications/${app.uuid}/routing-slip`);
        toastSuccess(`Routing slip ${res.data?.slip_no || ''} generated`);
        const refreshed = await window.axios.get(`/api/v1/staff/applications/${app.uuid}`);
        return refreshed.data.data as StaffApplicationDetail;
    } catch (error: any) {
        toastError(error?.response?.data?.message || 'Routing failed');
        return null;
    }
}

export async function startEvaluation(app: StaffApplicationDetail): Promise<void> {
    if (
        !(await confirmAction(
            'Start evaluation?',
            'Creates an evaluation sheet and starts the statutory processing timer for this filing.',
        ))
    ) {
        return;
    }

    try {
        await window.axios.post(`/api/v1/staff/applications/${app.uuid}/timer/start`).catch(() => null);
        const { data: created } = await window.axios.post(`/api/v1/staff/applications/${app.uuid}/evaluations`, {
            findings: [{ item: 'Completeness', status: 'ok' }],
            remarks: 'Evaluation started from application detail modal',
        });
        toastSuccess(`Evaluation ${created.data?.uuid ? 'started' : 'created'}`);
    } catch (error: any) {
        toastError(error?.response?.data?.message || 'Unable to start evaluation');
    }
}

export async function startTimer(app: StaffApplicationDetail): Promise<void> {
    try {
        await window.axios.post(`/api/v1/staff/applications/${app.uuid}/timer/start`);
        toastSuccess('Timer started');
    } catch (error: any) {
        toastError(error?.response?.data?.message || 'Timer start failed');
    }
}

export async function requestDocumentCorrection(
    app: StaffApplicationDetail,
    docLabel: string,
): Promise<void> {
    if (!app.applicant?.uuid) {
        toastError('Applicant account is not linked to this filing.');
        return;
    }

    const message = await confirmWithReason({
        title: 'Request document correction?',
        text: `Notify the applicant that “${humanizeKey(docLabel)}” must be uploaded or corrected.`,
        inputLabel: 'Message to applicant',
        inputPlaceholder: 'Please upload a clear PDF of the required document…',
        confirmButtonText: 'Send request',
        minLength: 10,
    });
    if (!message) return;

    try {
        await window.axios.post('/api/v1/staff/notifications/send', {
            user_uuid: app.applicant.uuid,
            template_code: 'document.correction_requested',
            message: `Document request for ${app.application_no || 'your application'} — ${humanizeKey(docLabel)}: ${message}`,
            vars: {
                name: app.applicant.name || 'Applicant',
                application_no: app.application_no || '',
                document: humanizeKey(docLabel),
                message: `Please upload/correct “${humanizeKey(docLabel)}” for ${app.application_no || 'your application'}. ${message}`,
            },
            url: '/applications',
        });
        toastSuccess('Correction request sent (in-app + email)');
    } catch (error: any) {
        toastError(error?.response?.data?.message || 'Unable to send request');
    }
}

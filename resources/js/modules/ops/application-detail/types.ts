import type { FormSchema } from '../../applications/dynamic-fields';

export type StaffApplicationDetail = {
    uuid: string;
    application_no?: string;
    status?: string;
    classification?: string | null;
    classified_by_rule?: string | null;
    classified_at?: string | null;
    project_title?: string | null;
    project_location?: string | null;
    latitude?: number | null;
    longitude?: number | null;
    payload?: Record<string, unknown> | null;
    submitted_at?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
    applicant?: {
        uuid?: string;
        name?: string;
        email?: string;
        phone?: string | null;
    };
    form?: {
        code?: string;
        title?: string;
        schema?: FormSchema;
        required_attachments?: string[];
    } | null;
    documents?: Array<{
        uuid: string;
        label: string;
        original_name: string;
        mime_type?: string | null;
        size?: number;
        is_pdf?: boolean;
        is_image?: boolean;
    }>;
    routing_slips?: Array<{
        uuid?: string;
        slip_no?: string;
        status?: string;
        generated_at?: string | null;
        template?: { uuid?: string; code?: string; name?: string; classification?: string } | null;
        steps?: Array<{
            uuid?: string;
            step_order?: number;
            label?: string;
            status?: string;
            started_at?: string | null;
            completed_at?: string | null;
            notes?: string | null;
            department?: { code?: string; name?: string };
        }>;
    }>;
    evaluations?: Array<{ decided_at?: string | null; status?: string | null; result?: string | null }>;
    inspections?: Array<{
        uuid?: string;
        inspection_no?: string;
        type?: string | null;
        scheduled_at?: string | null;
        completed_at?: string | null;
        status?: string | null;
        result?: string | null;
        location?: string | null;
        notes?: string | null;
        team_inspectors?: Array<{ name?: string; role?: string; discipline?: string }>;
        schedule_sheet?: {
            form_code?: string;
            purpose?: string;
            meeting_point?: string;
            disciplines?: string[];
            remarks?: string;
            coordination_notes?: string;
        };
        inspector_notes?: {
            form_code?: string;
            weather?: string;
            site_conditions?: string;
            findings?: string;
            observed_defects?: string;
            recommendations?: string;
        };
        compliance_sheet?: {
            form_code?: string;
            items?: Array<{ code?: string; label?: string; status?: string; remarks?: string }>;
            overall_remarks?: string;
        };
        electrical_form?: Record<string, string | undefined>;
        requires_electrical_form?: boolean;
        print_urls?: Partial<
            Record<'qms-38' | 'qms-39' | 'o-03' | 'qms-65' | 'dpwh-77-006-e', string>
        > | null;
        inspector?: { uuid?: string; name?: string };
    }>;
    orders_of_payment?: Array<{
        issued_at?: string | null;
        paid_at?: string | null;
        status?: string | null;
    }>;
    compliance_notices?: Array<{ issued_at?: string | null; status?: string | null; type?: string | null }>;
};

export type TimelineEvent = {
    key: string;
    label: string;
    at?: string | null;
    state: 'done' | 'current' | 'pending';
};

import axios from 'axios';
import './bootstrap';
import '../css/apics-brand.css';
import '../css/apics-shell.css';
import '../css/apics-status.css';
import '../css/apics-ops.css';
import '../css/apics-modal.css';
import '../css/apics-document-viewer.css';
import '../css/apics-location-picker.css';
import '../css/apics-dashboard.css';
import '../css/apics-notifications-inbox.css';
import '../css/apics-audit.css';
import '../css/apics-users.css';
import '../css/landing-motion.css';
import { toastSuccess, toastError } from './utils/toast';
import { showLoading, hideLoading, withLoading, withButtonLoading } from './utils/loading';
import { initAppShell } from './modules/layout/shell';

declare global {
    interface Window {
        axios: typeof axios;
        toastSuccess: typeof toastSuccess;
        toastError: typeof toastError;
        showLoading: typeof showLoading;
        hideLoading: typeof hideLoading;
        withLoading: typeof withLoading;
        withButtonLoading: typeof withButtonLoading;
    }
}

window.toastSuccess = toastSuccess;
window.toastError = toastError;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.withLoading = withLoading;
window.withButtonLoading = withButtonLoading;

document.addEventListener('DOMContentLoaded', () => {
    initAppShell();

    void (async () => {
        const boot = async (label: string, run: () => Promise<void> | void): Promise<void> => {
            try {
                await run();
            } catch (error) {
                console.error(`[APICS] Failed to boot ${label}`, error);
                toastError(`Unable to load ${label}. Hard-refresh (Cmd+Shift+R) if this persists.`);
            }
        };

        if (document.getElementById('btn-browse-routing-templates')) {
            await boot('routing templates', async () => {
                const { initOpsRoutingTemplatesBrowse } = await import('./modules/ops/routing-templates-browse');
                initOpsRoutingTemplatesBrowse();
            });
        }

        const guide = document.getElementById('apics-pipeline-guide');
        const guideToggle = document.querySelector<HTMLElement>('.apics-pipeline__guide-toggle span');
        if (guide && guideToggle) {
            guide.addEventListener('show.bs.collapse', () => {
                guideToggle.textContent = 'Hide workflow guide';
            });
            guide.addEventListener('hide.bs.collapse', () => {
                guideToggle.textContent = 'Show workflow guide';
            });
        }

        if (document.querySelector('[data-landing-home]')) {
            await boot('landing', async () => {
                const { initLandingHomePage } = await import('./modules/landing/home');
                await initLandingHomePage();
            });
        }

        if (document.getElementById('login-form')) {
            await boot('login', async () => {
                const { initLoginPage } = await import('./modules/auth/login');
                initLoginPage();
            });
        }

        if (document.getElementById('register-form')) {
            await boot('register', async () => {
                const { initRegisterPage } = await import('./modules/auth/register');
                initRegisterPage();
            });
        }

        if (document.getElementById('applications-table')) {
            await boot('applications', async () => {
                const { initApplicationsPage } = await import('./modules/applications/applications');
                initApplicationsPage();
            });
        }

        if (document.getElementById('departments-table')) {
            await boot('departments', async () => {
                const { initDepartmentsPage } = await import('./modules/admin/departments');
                initDepartmentsPage();
            });
        }

        if (document.getElementById('forms-table')) {
            await boot('forms', async () => {
                const { initFormsPage } = await import('./modules/admin/forms');
                initFormsPage();
            });
        }

        if (document.getElementById('apics-audit-console') || document.getElementById('audit-table')) {
            await boot('audit', async () => {
                const { initAuditPage } = await import('./modules/admin/audit');
                initAuditPage();
            });
        }

        if (document.getElementById('registrations-table')) {
            await boot('registrations', async () => {
                const { initRegistrationsPage } = await import('./modules/admin/registrations');
                initRegistrationsPage();
            });
        }

        if (document.getElementById('users-table')) {
            await boot('users', async () => {
                const { initUsersPage } = await import('./modules/admin/users');
                initUsersPage();
            });
        }

        if (document.getElementById('rules-table')) {
            await boot('classification rules', async () => {
                const { initClassificationRulesPage } = await import('./modules/admin/classification-rules');
                initClassificationRulesPage();
            });
        }

        if (document.getElementById('templates-table')) {
            await boot('routing templates admin', async () => {
                const { initRoutingTemplatesPage } = await import('./modules/admin/routing-templates');
                initRoutingTemplatesPage();
            });
        }

        if (document.getElementById('queue-table')) {
            await boot('evaluation queue', async () => {
                const { initEvaluationQueuePage } = await import('./modules/admin/evaluation-queue');
                initEvaluationQueuePage();
            });
        }

        if (document.getElementById('fee-rules-table')) {
            await boot('fee rules', async () => {
                const { initFeeRulesPage } = await import('./modules/admin/fee-rules');
                initFeeRulesPage();
            });
        }

        if (document.getElementById('inspections-table')) {
            await boot('inspections', async () => {
                const { initInspectionsPage } = await import('./modules/admin/inspections');
                initInspectionsPage();
            });
        }

        if (document.getElementById('oop-table')) {
            await boot('orders of payment', async () => {
                const { initOrdersOfPaymentPage } = await import('./modules/admin/orders-of-payment');
                initOrdersOfPaymentPage();
            });
        }

        if (document.getElementById('notices-table')) {
            await boot('compliance notices', async () => {
                const { initComplianceNoticesPage } = await import('./modules/admin/compliance-notices');
                initComplianceNoticesPage();
            });
        }

        if (document.getElementById('logbooks-table')) {
            await boot('logbooks', async () => {
                const { initLogbooksPage } = await import('./modules/admin/logbooks');
                initLogbooksPage();
            });
        }

        if (document.getElementById('archives-table')) {
            await boot('archives', async () => {
                const { initArchivesPage } = await import('./modules/admin/archives');
                initArchivesPage();
            });
        }

        if (document.getElementById('apics-notif-inbox')) {
            await boot('notifications', async () => {
                const { initNotificationsPage } = await import('./modules/admin/notifications');
                initNotificationsPage();
            });
        }

        if (document.getElementById('apics-admin-dashboard') || document.getElementById('ops-stats')) {
            await boot('dashboard stats', async () => {
                const { initDashboardStats } = await import('./modules/admin/dashboard');
                initDashboardStats();
            });
        }

        if (document.getElementById('project-plan-root')) {
            await boot('project plan', async () => {
                const { initProjectPlanPage } = await import('./modules/project-plan/project-plan');
                initProjectPlanPage();
            });
        }

        if (document.getElementById('modal-edit-profile')) {
            await boot('account profile', async () => {
                const { initAccountProfile } = await import('./modules/account-profile/account-profile');
                initAccountProfile();
            });
        }
    })();
});

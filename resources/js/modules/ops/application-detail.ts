/**
 * Compatibility entry for Vite HMR after the module moved into a folder.
 * Prefer importing from `./application-detail/index` in new code.
 */
export {
    openOpsApplicationDetail,
    renderStaffApplicationDetailHtml,
} from './application-detail/index';
export type { StaffApplicationDetail } from './application-detail/index';

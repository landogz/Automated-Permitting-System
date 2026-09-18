{{-- Shared account profile / password modals — Velzon shell + APICS craft --}}
<div class="modal fade" id="modal-edit-profile" tabindex="-1" aria-labelledby="modal-edit-profile-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable apics-modal apics-account-modal">
        <div class="modal-content border-0">
            <form id="form-edit-profile" novalidate>
                <div class="modal-header apics-account-modal__header">
                    <div class="apics-account-modal__intro">
                        <span class="apics-account-modal__mark apics-account-modal__mark--primary" aria-hidden="true">
                            <i class="ri-user-settings-line"></i>
                        </span>
                        <div class="min-w-0">
                            <h5 class="modal-title mb-1" id="modal-edit-profile-label">Edit profile</h5>
                            <p class="apics-account-modal__lede mb-0">Keep your OCBO contact details current for notices and audit records.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body apics-account-modal__body">
                    <div class="apics-account-modal__identity">
                        <div class="apics-account-modal__avatar-wrap">
                            <button type="button" class="apics-account-modal__avatar-btn" id="btn-profile-avatar-pick" aria-label="Change profile photo">
                                <span class="apics-account-modal__avatar" data-profile-avatar aria-hidden="true">AA</span>
                                <span class="apics-account-modal__avatar-overlay" aria-hidden="true">
                                    <i class="ri-camera-line"></i>
                                </span>
                            </button>
                            <input type="file" id="profile-avatar-input" class="d-none" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" capture="user">
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <p class="apics-account-modal__identity-name mb-0 text-truncate" data-profile-preview-name>Your name</p>
                            <p class="apics-account-modal__identity-meta mb-1 text-truncate" data-profile-preview-email>email@example.com</p>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <button type="button" class="btn btn-sm btn-soft-primary" id="btn-profile-avatar-upload">
                                    <i class="ri-upload-2-line align-middle me-1" aria-hidden="true"></i>Upload photo
                                </button>
                                <button type="button" class="btn btn-sm btn-ghost-danger d-none" id="btn-profile-avatar-remove">
                                    <i class="ri-delete-bin-line align-middle me-1" aria-hidden="true"></i>Remove
                                </button>
                            </div>
                            <p class="text-muted fs-11 mb-0 mt-1">JPEG, PNG, or WebP · max 2 MB</p>
                            <div class="invalid-feedback d-block" data-error-for="avatar"></div>
                        </div>
                    </div>

                    <div class="apics-account-modal__fields">
                        <div class="mb-3">
                            <label class="form-label" for="profile-name">Full name <span class="text-danger">*</span></label>
                            <div class="form-icon">
                                <input type="text" class="form-control form-control-icon" id="profile-name" name="name" autocomplete="name" maxlength="255" required placeholder="e.g. Juan Dela Cruz">
                                <i class="ri-user-line" aria-hidden="true"></i>
                            </div>
                            <div class="invalid-feedback" data-error-for="name"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="profile-email">Email <span class="text-danger">*</span></label>
                            <div class="form-icon">
                                <input type="email" class="form-control form-control-icon" id="profile-email" name="email" autocomplete="email" maxlength="255" required placeholder="name@agency.gov.ph">
                                <i class="ri-mail-line" aria-hidden="true"></i>
                            </div>
                            <div class="invalid-feedback" data-error-for="email"></div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="profile-phone">Mobile / phone <span class="text-muted fw-normal">(optional)</span></label>
                            <div class="form-icon">
                                <input type="tel" class="form-control form-control-icon" id="profile-phone" name="phone" autocomplete="tel" maxlength="30" placeholder="09XX XXX XXXX">
                                <i class="ri-smartphone-line" aria-hidden="true"></i>
                            </div>
                            <div class="invalid-feedback" data-error-for="phone"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer apics-account-modal__footer">
                    <button type="button" class="btn apics-btn-cancel apics-account-modal__cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta apics-account-modal__cta" id="btn-save-profile">
                        <i class="ri-check-line" aria-hidden="true"></i>
                        <span class="btn-label">Save changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-change-password" tabindex="-1" aria-labelledby="modal-change-password-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable apics-modal apics-account-modal">
        <div class="modal-content border-0">
            <form id="form-change-password" novalidate>
                <div class="modal-header apics-account-modal__header">
                    <div class="apics-account-modal__intro">
                        <span class="apics-account-modal__mark apics-account-modal__mark--security" aria-hidden="true">
                            <i class="ri-shield-keyhole-line"></i>
                        </span>
                        <div class="min-w-0">
                            <h5 class="modal-title mb-1" id="modal-change-password-label">Change password</h5>
                            <p class="apics-account-modal__lede mb-0">Strengthen your account with a unique password you do not reuse elsewhere.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body apics-account-modal__body">
                    <div class="apics-account-modal__tip" role="note">
                        <i class="ri-information-line" aria-hidden="true"></i>
                        <span>You will stay signed in on this device after updating.</span>
                    </div>

                    <div class="apics-account-modal__fields">
                        <div class="mb-3">
                            <label class="form-label" for="password-current">Current password <span class="text-danger">*</span></label>
                            <div class="position-relative apics-pass-field">
                                <div class="form-icon">
                                    <input type="password" class="form-control form-control-icon pe-5" id="password-current" name="current_password" autocomplete="current-password" required placeholder="Enter current password">
                                    <i class="ri-lock-line" aria-hidden="true"></i>
                                </div>
                                <button class="btn btn-link apics-pass-field__toggle password-addon material-shadow-none" type="button" data-password-toggle="password-current" aria-label="Show password">
                                    <i class="ri-eye-line align-middle" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" data-error-for="current_password"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password-new">New password <span class="text-danger">*</span></label>
                            <div class="position-relative apics-pass-field">
                                <div class="form-icon">
                                    <input type="password" class="form-control form-control-icon pe-5" id="password-new" name="password" autocomplete="new-password" required placeholder="Create a strong password">
                                    <i class="ri-key-2-line" aria-hidden="true"></i>
                                </div>
                                <button class="btn btn-link apics-pass-field__toggle password-addon material-shadow-none" type="button" data-password-toggle="password-new" aria-label="Show password">
                                    <i class="ri-eye-line align-middle" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="apics-pass-meter mt-2" data-pass-meter aria-live="polite">
                                <div class="apics-pass-meter__track" aria-hidden="true">
                                    <span class="apics-pass-meter__bar" data-pass-meter-bar></span>
                                </div>
                                <p class="apics-pass-meter__label mb-0" data-pass-meter-label>Password strength</p>
                            </div>
                            <ul class="apics-pass-rules list-unstyled mb-0 mt-2" data-pass-rules>
                                <li data-rule="length"><i class="ri-checkbox-blank-circle-line" aria-hidden="true"></i> 8+ characters</li>
                                <li data-rule="case"><i class="ri-checkbox-blank-circle-line" aria-hidden="true"></i> Upper &amp; lower case</li>
                                <li data-rule="number"><i class="ri-checkbox-blank-circle-line" aria-hidden="true"></i> A number</li>
                                <li data-rule="symbol"><i class="ri-checkbox-blank-circle-line" aria-hidden="true"></i> A symbol</li>
                            </ul>
                            <div class="invalid-feedback" data-error-for="password"></div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label" for="password-confirm">Confirm new password <span class="text-danger">*</span></label>
                            <div class="position-relative apics-pass-field">
                                <div class="form-icon">
                                    <input type="password" class="form-control form-control-icon pe-5" id="password-confirm" name="password_confirmation" autocomplete="new-password" required placeholder="Re-enter new password">
                                    <i class="ri-shield-check-line" aria-hidden="true"></i>
                                </div>
                                <button class="btn btn-link apics-pass-field__toggle password-addon material-shadow-none" type="button" data-password-toggle="password-confirm" aria-label="Show password">
                                    <i class="ri-eye-line align-middle" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" data-error-for="password_confirmation"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer apics-account-modal__footer">
                    <button type="button" class="btn apics-btn-cancel apics-account-modal__cancel" data-bs-dismiss="modal">
                        <i class="ri-close-circle-line" aria-hidden="true"></i>
                        <span class="btn-label">Cancel</span>
                    </button>
                    <button type="submit" class="btn apics-btn-cta apics-account-modal__cta" id="btn-save-password">
                        <i class="ri-shield-check-line" aria-hidden="true"></i>
                        <span class="btn-label">Update password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


/**
 * validation.js
 * Client-side form validation for the contact form
 */

const Validator = {

  /**
   * Validate the entire contact form
   * @param {object} lang - current language translations
   * @returns {boolean}
   */
  validateContactForm(lang) {
    const t = TRANSLATIONS[lang];
    let isValid = true;

    // Clear previous errors
    this.clearErrors();

    // Name
    const name = document.getElementById('contactName');
    if (!name.value.trim()) {
      this.showError('nameError', name, t.err_name_req);
      isValid = false;
    } else if (name.value.trim().length < 2) {
      this.showError('nameError', name, t.err_name_short);
      isValid = false;
    } else {
      this.showSuccess(name);
    }

    // Email
    const email = document.getElementById('contactEmail');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email.value.trim()) {
      this.showError('emailError', email, t.err_email_req);
      isValid = false;
    } else if (!emailRegex.test(email.value.trim())) {
      this.showError('emailError', email, t.err_email_invalid);
      isValid = false;
    } else {
      this.showSuccess(email);
    }

    // Subject
    const subject = document.getElementById('contactSubject');
    if (!subject.value.trim()) {
      this.showError('subjectError', subject, t.err_subject_req);
      isValid = false;
    } else {
      this.showSuccess(subject);
    }

    // Message
    const message = document.getElementById('contactMessage');
    if (!message.value.trim()) {
      this.showError('messageError', message, t.err_message_req);
      isValid = false;
    } else if (message.value.trim().length < 10) {
      this.showError('messageError', message, t.err_message_short);
      isValid = false;
    } else {
      this.showSuccess(message);
    }

    return isValid;
  },

  showError(errorId, field, message) {
    const errorEl = document.getElementById(errorId);
    if (errorEl) errorEl.textContent = message;
    if (field) {
      field.classList.add('error');
      field.classList.remove('valid');
    }
  },

  showSuccess(field) {
    if (field) {
      field.classList.remove('error');
      field.classList.add('valid');
    }
  },

  clearErrors() {
    ['nameError', 'emailError', 'subjectError', 'messageError'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.textContent = '';
    });
    ['contactName', 'contactEmail', 'contactSubject', 'contactMessage'].forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.classList.remove('error', 'valid');
      }
    });
  },

  // Real-time validation on blur
  attachRealTime(lang) {
    const fields = [
      { id: 'contactName', errorId: 'nameError' },
      { id: 'contactEmail', errorId: 'emailError' },
      { id: 'contactSubject', errorId: 'subjectError' },
      { id: 'contactMessage', errorId: 'messageError' }
    ];

    fields.forEach(({ id }) => {
      const el = document.getElementById(id);
      if (el) {
        el.addEventListener('input', () => {
          if (el.classList.contains('error') || el.classList.contains('valid')) {
            this.validateContactForm(lang);
          }
        });
      }
    });
  }
};

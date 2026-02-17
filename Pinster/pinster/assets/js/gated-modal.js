/**
 * Gated download modal: open on #download or button click, submit via AJAX.
 *
 * @package Pinster
 */
(function () {
	'use strict';

	var modal = document.getElementById('pinster-gated-modal');
	if (!modal) return;

	var form = document.getElementById('pinster-gated-form');
	var messageEl = document.getElementById('pinster-gated-message');
	var closeButtons = modal.querySelectorAll('[data-close-modal]');
	var defaultSubmitText = null;

	function openModal() {
		modal.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
		var name = document.getElementById('pinster-gated-name');
		if (name) name.focus();
	}

	function closeModal() {
		modal.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';
		if (messageEl) {
			messageEl.hidden = true;
			messageEl.className = 'pinster-modal-message';
			messageEl.textContent = '';
		}
		if (form) {
			form.reset();
		}
	}

	function showMessage(text, isError) {
		if (!messageEl) return;
		messageEl.textContent = text;
		messageEl.className = 'pinster-modal-message ' + (isError ? 'pinster-modal-error' : 'pinster-modal-success');
		messageEl.hidden = false;
	}

	// Open when landing with #download
	if (window.location.hash === '#download') {
		openModal();
	}

	// Trigger buttons (e.g. Download on single page)
	document.addEventListener('click', function (e) {
		var trigger = e.target.closest('.pinster-gated-trigger');
		if (!trigger) return;
		e.preventDefault();
		var tid = trigger.getAttribute('data-template-id');
		var nonce = trigger.getAttribute('data-nonce');
		if (tid && nonce) {
			var tidInput = document.getElementById('pinster-gated-template-id');
			var nonceInput = document.getElementById('pinster-gated-nonce');
			if (tidInput) tidInput.value = tid;
			if (nonceInput) nonceInput.value = nonce;
		}
		openModal();
	});

	// Close on backdrop or close button
	closeButtons.forEach(function (btn) {
		btn.addEventListener('click', closeModal);
	});
	modal.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') closeModal();
	});

	// Form submit via AJAX
	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var submitBtn = form.querySelector('button[type="submit"]');
			if (submitBtn) {
				if (!defaultSubmitText) defaultSubmitText = submitBtn.textContent;
				submitBtn.disabled = true;
				submitBtn.textContent = 'Sending…';
			}
			var data = new FormData(form);
			data.append('action', 'pinster_gated_submit');

			fetch(typeof pinsterGated !== 'undefined' ? pinsterGated.ajaxUrl : '', {
				method: 'POST',
				body: data,
				credentials: 'same-origin'
			})
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res.success) {
						showMessage(res.data.message, !res.data.sent);
						if (res.data.sent) {
							setTimeout(closeModal, 2500);
						}
					} else {
						showMessage(res.data && res.data.message ? res.data.message : 'Something went wrong.', true);
					}
				})
				.catch(function () {
					showMessage('Something went wrong. Please try again.', true);
				})
				.finally(function () {
					if (submitBtn) {
						submitBtn.disabled = false;
						submitBtn.textContent = defaultSubmitText || 'Send me the template';
					}
				});
		});
	}
})();

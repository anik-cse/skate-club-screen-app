/**
 * Form Handler
 *
 * Handles form submissions and validation.
 */

jQuery(document).ready(function($) {
	// Auto-close window after 3 seconds of inactivity
	let inactivityTimer;

	function resetInactivityTimer() {
		clearTimeout(inactivityTimer);
		inactivityTimer = setTimeout(function() {
			// Optional: Show message before closing
			// window.close();
		}, 180000); // 3 minutes
	}

	// Reset timer on any activity
	$(document).on('mousemove keypress', resetInactivityTimer);
	resetInactivityTimer();

	// Form validation helpers
	window.validateEmail = function(email) {
		const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return re.test(email);
	};

	window.validatePhone = function(phone) {
		const cleaned = phone.replace(/[^0-9+\-\s()]/g, '');
		const digits = cleaned.replace(/[^0-9]/g, '');
		return digits.length >= 7 && digits.length <= 15;
	};
});

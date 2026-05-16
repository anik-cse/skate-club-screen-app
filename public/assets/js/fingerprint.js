/**
 * Browser Fingerprinting
 *
 * Simple browser fingerprinting for duplicate detection.
 */

function generateFingerprint() {
	const canvas = document.createElement('canvas');
	const ctx = canvas.getContext('2d');

	ctx.textBaseline = 'top';
	ctx.font = '14px Arial';
	ctx.fillText('fingerprint', 2, 2);

	const canvasData = canvas.toDataURL();

	const fingerprint = {
		userAgent: navigator.userAgent,
		language: navigator.language,
		colorDepth: screen.colorDepth,
		screenResolution: screen.width + 'x' + screen.height,
		timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
		platform: navigator.platform,
		canvas: canvasData.substring(0, 100)
	};

	// Simple hash function
	const str = JSON.stringify(fingerprint);
	let hash = 0;

	for (let i = 0; i < str.length; i++) {
		const char = str.charCodeAt(i);
		hash = ((hash << 5) - hash) + char;
		hash = hash & hash;
	}

	return Math.abs(hash).toString(36);
}

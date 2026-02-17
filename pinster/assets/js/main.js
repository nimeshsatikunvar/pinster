/**
 * Pinster theme – front-end scripts.
 *
 * @package Pinster
 */
(function () {
	'use strict';

	// Optional: focus management for filter chips / search (accessibility).
	var searchInput = document.getElementById('pinster-search-input');
	if (searchInput && window.location.search.indexOf('s=') !== -1) {
		searchInput.focus();
	}
})();

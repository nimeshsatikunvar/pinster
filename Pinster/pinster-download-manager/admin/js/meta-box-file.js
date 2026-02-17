/**
 * Meta box: select or remove template file (PDF/DOCX).
 *
 * @package Pinster_Download_Manager
 */
(function () {
	'use strict';

	var frame;
	var fileInput = document.getElementById('pinster-dm-file-id');
	var fileInfo = document.getElementById('pinster-dm-file-info');
	var uploadBtn = document.getElementById('pinster-dm-upload-file');
	var removeBtn = document.getElementById('pinster-dm-remove-file');

	if (!fileInput || !uploadBtn) {
		return;
	}

	function openMediaLibrary() {
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media({
			title: typeof pinsterDmMetaBox !== 'undefined' ? pinsterDmMetaBox.title : 'Select file',
			library: { type: ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'] },
			multiple: false,
			button: { text: typeof pinsterDmMetaBox !== 'undefined' ? pinsterDmMetaBox.button : 'Use this file' }
		});
		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			fileInput.value = attachment.id;
			setFileInfo(attachment.filename || attachment.title, attachment.url);
			if (removeBtn) {
				removeBtn.style.display = '';
			}
		});
		frame.open();
	}

	function setFileInfo(name, url) {
		if (!fileInfo) {
			return;
		}
		var html = name || '';
		if (url) {
			html += ' <a href="' + url + '" target="_blank" rel="noopener">View</a>';
		}
		fileInfo.innerHTML = html;
	}

	uploadBtn.addEventListener('click', function (e) {
		e.preventDefault();
		openMediaLibrary();
	});

	if (removeBtn) {
		removeBtn.addEventListener('click', function (e) {
			e.preventDefault();
			fileInput.value = '0';
			if (fileInfo) {
				fileInfo.textContent = 'No file selected. Allowed: PDF, DOCX.';
			}
			removeBtn.style.display = 'none';
		});
	}
})();

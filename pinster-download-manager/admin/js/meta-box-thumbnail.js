/**
 * Meta box: Set/Remove template thumbnail (featured image).
 *
 * @package Pinster_Download_Manager
 */
(function () {
	'use strict';

	var frame;
	var box = document.querySelector('.pinster-dm-thumbnail-box');
	if (!box) return;

	var postId = box.getAttribute('data-post-id');
	var preview = box.querySelector('.pinster-dm-thumbnail-preview');
	var setBtn = box.querySelector('.pinster-dm-set-thumbnail');
	var removeBtn = box.querySelector('.pinster-dm-remove-thumbnail');
	var hiddenInput = box.querySelector('#pinster-dm-thumbnail-id');

	function openMedia() {
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media({
			title: 'Select template thumbnail',
			library: { type: 'image' },
			multiple: false,
			button: { text: 'Use as thumbnail' }
		});
		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			setThumbnail(attachment.id, attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url);
		});
		frame.open();
	}

	function setThumbnail(attachmentId, thumbUrl) {
		var data = new FormData();
		data.append('action', 'pinster_dm_set_thumbnail');
		data.append('nonce', typeof pinsterDmThumbnail !== 'undefined' ? pinsterDmThumbnail.nonce : '');
		data.append('post_id', postId);
		data.append('attachment_id', attachmentId);

		fetch(pinsterDmThumbnail.ajaxUrl, {
			method: 'POST',
			body: data,
			credentials: 'same-origin'
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res.success && res.data.thumb_url) {
					preview.innerHTML = '<img src="' + res.data.thumb_url + '" alt="" style="max-width:100%;height:auto;display:block;border:1px solid #ddd;" />';
					if (hiddenInput) hiddenInput.value = res.data.attachment_id;
					if (removeBtn) removeBtn.style.display = '';
				}
			});
	}

	function removeThumbnail() {
		var data = new FormData();
		data.append('action', 'pinster_dm_set_thumbnail');
		data.append('nonce', typeof pinsterDmThumbnail !== 'undefined' ? pinsterDmThumbnail.nonce : '');
		data.append('post_id', postId);
		data.append('attachment_id', '0');
		fetch(pinsterDmThumbnail.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res.success) {
					preview.innerHTML = '<div style="background:#f0f0f0;border:1px dashed #ccc;padding:40px 20px;text-align:center;color:#666;">No thumbnail set. This image appears on the template card.</div>';
					if (hiddenInput) hiddenInput.value = '0';
					if (removeBtn) removeBtn.style.display = 'none';
				}
			});
	}

	if (setBtn) setBtn.addEventListener('click', function (e) { e.preventDefault(); openMedia(); });
	if (removeBtn) removeBtn.addEventListener('click', function (e) { e.preventDefault(); removeThumbnail(); });
})();

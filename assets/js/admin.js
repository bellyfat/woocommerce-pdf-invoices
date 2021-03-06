(function () {
	'use strict';

	var setting = {};

	setting.settings = ['bewpi-intro-text'];

	setting.enableDisableNextInvoiceNumbering = function (elem) {
		document.getElementById('bewpi-next-invoice-number').readOnly = !elem.checked;
	};

	var notice = {};

	notice.dismiss = function (event) {
		event.preventDefault();
		var attrValue, optionName, dismissableLength, data;

		attrValue = event.target.parentElement.getAttribute('data-dismissible').split('-');

		// remove the dismissible length from the attribute value and rejoin the array.
		dismissableLength = attrValue.pop();
		optionName = attrValue.join('-');

		var params = 'action=dismiss-notice&option_name=' + optionName + '&dismissible_length=' + dismissableLength + '&nonce=' + BEWPI_AJAX.dismiss_nonce;

		var xhr = new XMLHttpRequest();
		xhr.open('POST', BEWPI_AJAX.ajaxurl, true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.send(params);
	};

	window.addEventListener('load', function () {
		// Add click listener to dismiss notice.
		var notice = document.querySelector('div[data-dismissible] button.notice-dismiss');
		if (notice !== null) {
			notice.onclick = bewpi.notice.dismiss;
		}

		if (pagenow === 'woocommerce_page_woocommerce-pdf-invoices') {
			var template = document.querySelector('select#bewpi-template-name');
			if (template !== null) {

				template.addEventListener('change', bewpi.setting.switchSettings);

				var event = new Event('change');
				template.dispatchEvent(event);
			}
		}
	});

	window.bewpi = {};
	window.bewpi.notice = notice;
	window.bewpi.setting = setting;
})();

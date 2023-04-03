function doSowModal(title, url, popupSize, popupTall) {
		// var popupSize = $(this).data('popup-size');
		if (popupSize === undefined)
			popupSize = 'sm';
		var modalName = '#modal-' + popupSize;
		var modalElement = $(modalName);

		// var popupTall = $(this).data('popup-tall');
		if (popupTall === undefined)
			modalElement.removeClass('tall');
		else
			modalElement.addClass('tall');

		var modalHeaderObject = modalElement.find('#modalHeaderContent');
//console.log(modalHeaderObject);
		var modalContentObject = modalElement.find('#modalContainer');

		var modalInstance = bootstrap.Modal.getInstance(modalElement);

		// if (!modalElement.data('bs.modal').isShown)
		if (modalInstance._isShown == false)
			modalInstance.show();

		//modalHeaderObject.innerHTML = $(this).attr('title');
		modalHeaderObject.html('<h1>' + title + '</h1>');

		modalContentObject.html(modalElement.data('loader'));

// console.log('v2.5');
		$.ajax({
			url: url,
			// type: "POST", //error in csrf checking, remove '{{action}}' => ['post'] at models controller in behaviors.verb actions
			async: true,
			data: {
				ajax_popupSize: popupSize,
				ajax_popupTall: popupTall,
			},
		})
		.done(function(result) {
// console.log(result);
			//https://developer.mozilla.org/en-US/docs/Web/HTML/Element/script
			//Dynamically inserted scripts (using document.createElement) execute asynchronously by default, so to turn on synchronous execution (i.e. scripts execute in the order they were inserted) set async=false.
			var res = result.replace(/<script\ssrc/g, '<script crossorigin="anonymous" async=false src');
			modalContentObject.html(res);
			// console.log(res);
			// modalContentObject.html(result);
		})
		.fail(function(jqXHR, exception) {
// console.log(jqXHR, exception);
			// Our error logic here
			var msg = '';
			// if (jqXHR.status === 0)
				// msg = 'Not connect. Verify Network.';
			// else if (jqXHR.status == 404)
				// msg = 'Requested page not found. [404]';
			// else if (jqXHR.status == 500)
				// msg = 'Internal Server Error [500].';
			// else if (exception === 'parsererror')
				// msg = 'Requested JSON parse failed.';
			// else if (exception === 'timeout')
				// msg = 'Time out error.';
			// else if (exception === 'abort')
				// msg = 'Ajax request aborted.';
			// else
				msg = 'Uncaught Error: ' + jqXHR.responseText;
			modalContentObject.html(msg);
		})
		;
		// modalContentObject.load(
			// $(this).attr('value'),
			// function (response, status, xhr)
			// {
				// if (status == "error")
				// {
					// console.log(xhr);
					// modalContentObject.html(response); //xhr.status + " " + xhr.statusText);
				// }
			// }
		// );
}

(function(jQuery) {
	$(document).on('click', '.showModalButton', function() {
		var title = $(this).attr('title');
		var url = $(this).attr('value');
		var popupSize = $(this).data('popup-size');
		var popupTall = $(this).data('popup-tall');

		doSowModal(title, url, popupSize, popupTall);
	});
})(jQuery || this.jQuery || window.jQuery);

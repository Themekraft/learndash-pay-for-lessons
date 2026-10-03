jQuery(document).ready(function ($) {
	prod_course_lessons();

	$('#_course_id').on('change', function () {
		prod_course_lessons();
	});

	$('#_lesson_id').on('change', function () {
		if ($('#_all_lessons').prop('checked') === true) {
			var s = 0;
			$('#_lesson_id option').each(function () {
				if (s > 0) {
					$(this).attr('selected', 'selected');
				}
				s++;
			});
			alert(lpflajax.i18n.cannotUnselectAll);
		}
	});
});

function prod_course_lessons() {
	var courses = jQuery('#_course_id').val();
	jQuery.ajax({
		type: 'POST',
		url: lpflajax.ajaxurl,
		data: {
			action: 'get_course_lessons',
			courses: courses,
			productID: lpflajax.product_id,
			_wpnonce: lpflajax.nonce
		},
		cache: false,
		success: function (data) {
			jQuery('#_lesson_id').html(data);
			if (jQuery('#_all_lessons').prop('checked') === true) {
				setTimeout(function () {
					var s = 0;
					jQuery('#_lesson_id option').each(function () {
						if (s > 0) {
							jQuery(this).attr('selected', 'selected');
						}
						s++;
					});
				}, 2000);
			}
		}
	});
}

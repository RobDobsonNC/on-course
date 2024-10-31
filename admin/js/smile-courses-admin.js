(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );

document.addEventListener('DOMContentLoaded', (event) => {
	var body = '';
	var header = '';
	var add_new = '';
	var parent_id = data_obj.parent_id;
	var search_url = data_obj.search_url;

	var parent_url = data_obj.parent_url;

	if ( document.getElementById('wpbody-content') ) {
		var body = document.getElementById('wpbody-content');
	}

	if ( body.getElementsByClassName("wp-heading-inline") ) {
		header = body.getElementsByClassName("wp-heading-inline")[0];
	}

	if ( body.getElementsByClassName("page-title-action") ) {
		add_new = body.getElementsByClassName("page-title-action")[0];
	}
	
	if ( add_new ) {
		add_new.href = add_new.href + '&post_parent=' + parent_id;
		add_new.innerHTML = "Add New Course Mode";
	}

	add_new.insertAdjacentHTML('afterend', '<a class="page-title-action" href="' + search_url + '">View All Courses</a>');

	header.outerHTML = '<h1 class="wp-heading-inline"><a href="' + parent_url + '">' + header.innerHTML + '</a></h1>';
});
/**
 * Rules Table Controller
 * 
 * Author: Kevin Carwile
 */

;( function($, _, undefined){
	"use strict";

	ips.controller.register( 'rules.front.ui.table', {
	
		initialize: function()
		{
			this.on( 'paginationClicked paginationJump', this.paginationClicked );
		},
		
		/**
		 * Responds to a pagination click
		 *	
		 * @param 		{event} 	e 		Event object
		 * @param 		{object} 	data 	Event data object
		 * @returns 	{void}
		 */
		paginationClicked: function (e, data) {
			e.stopPropagation();
		}
		
	});
}(jQuery, _));
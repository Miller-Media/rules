/**
 * Rules Chosen Controller
 * 
 * Author: Kevin Carwile
 */

;( function($, _, undefined){
	"use strict";

	ips.controller.register( 'rules.admin.ui.tokens', {
	
		initialize: function()
		{
			var scope = this.scope;
			
			scope.find( '.tokens-toggle' ).click( function() {
				scope.find( '.tokens-list' ).slideToggle();
				$(this).find( 'i' ).toggleClass( 'fa-caret-right fa-caret-down' );
			});
		}
		
	});
}(jQuery, _));
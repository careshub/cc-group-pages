(function( $ ) {
	'use strict';

    var wp = window.wp,
	    ccgpTab = wp.template( "ccgp-tab" ),
    	ccgpPage = wp.template( "ccgp-page" ),
    	processing = false,
    	error = false,
        access_levels = [
                { 'level': 1, 'bp_level': 'anyone', 'label': 'Anyone' },
                { 'level': 2, 'bp_level': 'loggedin', 'label': 'Logged-in Site Members' },
                { 'level': 3, 'bp_level': 'member', 'label': 'Hub Members' },
                { 'level': 4, 'bp_level': 'mod', 'label': 'Hub Moderators' },
                { 'level': 5, 'bp_level': 'admin', 'label': 'Hub Administrators' },
            ],
        defaultTabDetails = { "label":"", "slug":"", "visibility":"" };

    // First, build the tabs and pages
    // ccgpGetPageOrderOnInit();

	$( ".page-details" ).hide();

	initializeSortable();
	populatePageOrder();

	function initializeSortable() {
	    $( ".sortable" ).sortable({
	        connectWith: '.sortable',
	        items: "> li",
	        placeholder: "ui-draggable-drop-zone",
	        // revert: true
	        stop: function(event, ui) {
		        ui.item.first().removeAttr('style'); // undo styling set by jQueryUI while the items are being transported.
		        populatePageOrder();
		        updatePageVisibility();
		    }
	    });
	    $( ".draggable" ).draggable({
	        connectToSortable: ".sortable",
	    });
	}

    function populatePageOrder(){
        var completeOrder = new Array();
        $('.sortable').each( function() {
            completeOrder.push({
                name: $(this).attr('id'),
                value: $(this).sortable('toArray')
                });
        });
        $( '#pages-order' ).val( $.param( completeOrder ) );
        updatePageVisibility();
    }

    function updatePageVisibility() {
    	// Loop through each fieldset
    	$( '.tab-details' ).not('#unused-pages').each(function(){
			var tab_option = jQuery( this ).find(".tab-visibility option:selected");
			var tab_access = tab_option.data("level");
			var tab_value = tab_option.val();
			var i = 1;

			$( this ).find( ".page-visibility" ).each( function() {
				// Inside this each(), "this" is the page-vis select.
				// First pages must have the same access level as the tab.
				if ( i == 1 ) {
		            jQuery( this ).val( tab_value );
		            // Disable all other options.
		            jQuery( this ).find("option").not(":selected").prop("disabled", true);
		            // Hmm. Let's also hide the select.
		            jQuery( this ).closest(".page-visibility-control").hide();
				} else {
    	            jQuery( this ).closest(".page-visibility-control").show();
		            // Other pages must have an access level that is greater than the section's.
		            var page_access = jQuery( this ).find(":selected").data("level");
		            if ( page_access < tab_access ) {
		                jQuery( this ).val( tab_value );
		            }
		            // Disable options that are less restrictive.
		            jQuery( this ).find("option").each( function() {
      					// Inside this each(), "this" is a single option from the select.
		                if ( jQuery(this).data("level") < tab_access ) {
		                    jQuery(this).prop("disabled", true);
		                } else {
		                    jQuery(this).prop("disabled", false);
		                }
		            });
				}
				i++;
			});
		});
		// Hide the visibility select if the page is in the bullpen.
		$("#unused-pages").find(".page-visibility").each( function() {
			$( this ).closest(".page-visibility-control").hide();
		});
	}
	// function ccgpRefreshTabIDs(){
	// 	var i = 1;
	// 	$( '.tab-details' ).not( '#unused-pages' ).each( function() {
	// 		$(this).attr( 'id', 'tabs-1' + i );
	// 		i++;
	// 	});

	// }
	function addPageSuccess( data ) {
		$( '#'+data.target_list ).append( ccgpPage( data ) );
		//We have to reinitialize the sortable. Ugh.
		initializeSortable();
		populatePageOrder();
		updatePageVisibility();
	}
	function addPageError( data ) {}
	function ccgpGetPageDetails( target_list, post_id ) {
		var args =  {
					  nonce: window.ccgpAJAXNonce,
					  group_id: $( '#group-id' ).val(),
					  target_list: target_list
					}
		// If a post_id is specified, send it.
		if ( post_id != 0 ) {
			args['post_id'] = post_id;
		}
		wp.ajax.send(
			"ccgp_get_page_details", {
				success: addPageSuccess,
				error:   addPageError,
				data: {
				  nonce: window.ccgpAJAXNonce,
				  group_id: $( '#group-id' ).val(),
				  target_list: target_list
				}
		});
	}
	function ccgpGetPageOrderOnInit() {
		wp.ajax.send(
			"ccgp_get_page_order", {
				success: ccgpBuildTabsOnInit,
				error:   function(){ return ''; },
				data: {
				  nonce: window.ccgpAJAXNonce,
				  group_id: $( '#group-id' ).val(),
				}
		});
	}
	function ccgpCreatePageonClick( event ) {
		// Stop the form from submitting normally
		event.preventDefault();

		// Do not process if we are currently creating a page
		if ( processing !== false ) {
			return;
		}

		processing = true;
		// User feedback
		var target_list = $( this ).siblings( 'ul' ).attr('id');
		ccgpGetPageDetails( target_list, 0 );
		processing = false;

	}
	function getNextTabID(){
		var increment = [ 0 ];
		jQuery('.tab-details').not('#unused-pages').each( function() {
			increment.push( jQuery(this).attr("id").replace('tabs-','') );
		});
		return Math.max.apply(Math, increment) + 1;
	}
	function ccgpCreateTabonClick( event ) {
		event.preventDefault();
		var id = getNextTabID(),
			details = defaultTabDetails;

		ccgpCreateTab( id, details );
		// Expand the details pane for the new tab.
		$( "#tabs-" + id ).find(".details-pane").show();
	}
	function ccgpCreateTab( id, details) {
		var tabData = { "tab_id": id,
						"details": details,
						"access_levels": access_levels
					};

		$( '#unused-pages' ).before( ccgpTab( tabData ) );
		initializeSortable();
		populatePageOrder();
		maybeHideAddTabButton();
	}
	function ccgpRemoveTab( e, that) {
		var target = e.target;
		$( target ).parents( '.tab-details' ).fadeOut(400, function(){
			// Move the pages from the removed tab to the bullpen.
			var items = $(this).find('.draggable');
			$('#unused-page-list').append( items );
			// Remove the fieldset from the DOM.
			$(this).remove();
			// Refresh the page order.
			initializeSortable();
			populatePageOrder();
			maybeShowAddTabButton();
		});
	}

	// We can only handle 6 tabs total, so don't allow the user to add more than that.
	function maybeHideAddTabButton(){
		// The Bullpen is the fifth fieldset
		if ( $(".tab-details").length >= 7 ) {
			$("#ccgp-add-tab").hide();
		}
	}
	// If less than 6 tabs total, the user may add another.
	function maybeShowAddTabButton(){
		// The Bullpen is the fifth fieldset
		if ( $(".tab-details").length < 7 ) {
			$("#ccgp-add-tab").show();
		}
	}

	// Listen for various clicks click.
	$( '.standard-form' ).on( 'click', '.ccgp-add-page', ccgpCreatePageonClick );
	$( '#ccgp-add-tab' ).on( 'click', ccgpCreateTabonClick );
    $( '.standard-form' ).on( 'click', '.toggle-details-pane', function( e ) {
        e.preventDefault();
        $(this).siblings( ".details-pane" ).toggle();
    });
    $( '.standard-form' ).on( 'change', '.tab-visibility', updatePageVisibility );
    $( '.standard-form' ).on( 'click', '.remove-tab', function(e){
    	e.preventDefault();
    	var that = $(this);
	    if ( window.confirm("Are you sure you would like to remove that tab?") ){
	       ccgpRemoveTab(e, that);
	    }
    } );
    $( '.standard-form' ).on( 'change', '.show-tab-setting', function( e ) {
        $(this).parent().siblings( ".navigation-order-container" ).toggleClass( 'toggled-off' );
    });

    window.partial = function(which, data) {
        var tmpl = $('#' + which + '-partial').html();
        return _.template(tmpl)(data);
    };

	// Utility
	window.onbeforeunload = function(e) {
		if ( $( '#ccgp_published' ).val() == 'trash' ) {
			return 'Are you sure you want to delete this page?';
		}
	};

})( jQuery );

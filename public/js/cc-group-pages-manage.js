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
	        stop: populatePageOrder,
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
        console.log( 'page order in populatePageOrder' );
        console.log( $.param( completeOrder ) );
    }

	function addPageSuccess( data ) {
		console.log( data );
		$( '#'+data.target_list ).append( ccgpPage( data ) );
		//We have to reinitialize the sortable. Ugh.
		initializeSortable();
		populatePageOrder();
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
		jQuery('.tab-details').each( function() {
		  increment.push( jQuery(this).attr("id").replace('tabs-','') );
		});
		return Math.max.apply(Math, increment) + 1;
	}
	function ccgpCreateTabonClick( event ) {
		console.log(' clicked it ');
		// Stop the form from submitting normally
		event.preventDefault();
		var id = getNextTabID(),
			details = defaultTabDetails;

		console.log( 'data for tab creation on click' );
		console.log( id );
		console.log( details );
		ccgpCreateTab( id, details );


	}
	function ccgpCreateTab( id, details) {
		var tabData = { 	"tab_id": id,
						"details": details,
						"access_levels": access_levels
					};
		console.log( 'data for tab creation on init' );
		console.log( tabData );

		$( '#tabs-container' ).append( ccgpTab( tabData ) );
	}
	function ccgpSetTabValues( id, details) {
		for ( var field in details ) {
			// We're doing this because the AJAX-produced input fields don't show their values.
			$( '#ccgp-tab-'+id+'-'+field ).val( details[field] );
		}
	}

	function ccgpBuildTabsOnInit( data ){
		console.log( 'incoming init info' );
		console.log(data);
		if ( data === Object(data) ) {
			for ( var id in data ) {
				ccgpCreateTab( id, data[id] );
				ccgpSetTabValues( id, data[id] );
				ccgpBuildPagesOnInit( id, data[id] )
			}
		}
	}
	function ccgpBuildPagesOnInit( tabID, data ){
		console.log( 'incoming page init info' );
		console.log(data);
		if ( data.pages === Object(data.pages) ) {
			var pages = data.pages
			console.log( 'pages object?' );
			console.log( pages );
			for ( var i in pages ) {
				console.log( 'post_id?' );
				console.log( pages[i].post_id );
				ccgpGetPageDetails( tabID, pages[i].post_id );
			}
		}
	}

	// Listen for various clicks click.
	$( '.standard-form' ).on( 'click', '.ccgp-add-page', ccgpCreatePageonClick );
	$( '#ccgp-add-tab' ).on( 'click', ccgpCreateTabonClick );
    $( '.standard-form' ).on( 'click', '.toggle-details-pane', function( e ) {
        e.preventDefault();
        $(this).siblings( ".details-pane" ).toggle();
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

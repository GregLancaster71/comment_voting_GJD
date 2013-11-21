jQuery( document ).ready( function( $ ) { 
	$( '#vote_buttons .upvote').click( function($upVoteButton) { 			
	
	 var commentid = $( this ).attr('id').replace(/upvote/, '');
	 var direction = 'upvote';
	 var nonce = $( this ).attr('data-nonce');
	 $upVoteButton = $( this );	 
	 $downVoteButton = $( '#downvote'+commentid );	
	 
var data = { 
	'action': 'amc_comment_vote', 
	'commentid': commentid,
	'direction': direction,
	'nonce': nonce
} 


$.ajax({ 
	type: 'post',
	url: anotherAjax.ajaxurl, 
	data: data,
	success: function ( response ) { 
	if ( response.success ) { 
	$upVoteButton.parent("#vote_buttons").find(".getvotes").html(function(i, val) { return +val+1 });

	$upVoteButton.toggleClass('voted')
	$downVoteButton.removeClass('voted')
		 
    } else {
	if (data.loggedIn == true) {
		//alert('you are logged in')
        //jQuery('#divForLoggedInStatus').text('You are logged in');
    } else {
		//alert('you are NOT logged in')
        //jQuery('#divForLoggedInStatus').text('You are NOT logged in');
    }
	}
    
	}
		
	
}); 
  }); 
  
  
  
  $( '#vote_buttons .downvote').click( function($downVoteButton) { 			
  
	 // $( this ).toggleClass( "downVoted" );
	 var commentid = $( this ).attr('id').replace(/downvote/, '');
	 var direction = 'downvote';
	 var nonce = $( this ).attr('data-nonce');
	 $downVoteButton = $( this );	 
	 $upVoteButton = $( '#upvote'+commentid );	
	
var data = { 
	'action': 'amc_comment_vote', 
	'commentid': commentid,
	'direction': direction,
	'nonce': nonce
} 

$.ajax({ 
	type: 'post',
	url: anotherAjax.ajaxurl, 
	data: data,
	success: function ( response ) { 
	if ( response.success ) { 
	$downVoteButton.parent("#vote_buttons").find(".getvotes").html(function(i, val) { return +val-1 });
	$downVoteButton.toggleClass('voted')
	$upVoteButton.removeClass('voted')

    } else {
	if (data.loggedIn == true) {
		alert(alertMsg);
		//alert('you are logged in')
        //jQuery('#divForLoggedInStatus').text('You are logged in');
    } else {
		//alert('you are NOT logged in')
        //jQuery('#divForLoggedInStatus').text('You are NOT logged in');
    }
	}
    
	}
		
	
}); 
  }); 
  }); 
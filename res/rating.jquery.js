function tx_ratings_submit(id, rating, ajaxData, check) {
	jQuery('#tx-ratings-display-' + id).css('visibility', 'hidden');
    jQuery('#tx-ratings-wait-' + id).css('visibility', 'visible');
    jQuery.ajax({
		type: 'POST',
		url: 'index.php?eID=tx_ratings_ajax',
		async: true,
		data: 'ref=' + id + '&rating=' + rating + '&data=' + ajaxData + '&check=' + check,
		success: function(html){
            jQuery('#tx-ratings-' + id).html(html);
		}
	});
}

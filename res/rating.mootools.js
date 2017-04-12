function tx_ratings_submit(id, rating, ajaxData, check) {
	$('tx-ratings-display-' + id).style.visibility = 'hidden';
	$('tx-ratings-wait-' + id).style.visibility = 'visible';
	var url = 'index.php?eID=tx_ratings_ajax';
	var params = 'ref=' + id + '&rating=' + rating + '&data=' + ajaxData + '&check=' + check;
	var myAjax = new Request({
		url:url,
		method: 'post',
		data: params,
		onComplete: function(responseText) {
			$('tx-ratings-' + id).set('html', responseText);
		}
	});
	myAjax.send();
}

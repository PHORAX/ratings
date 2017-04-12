var id;
function tx_ratings_callback(html) {
	document.getElementById('tx-ratings-' + id).innerHTML = html;
}
function tx_ratings_submit(localId, rating, ajaxData, check) {
	id = localId
	document.getElementById('tx-ratings-display-' + id).style.visibility = 'hidden';
	document.getElementById('tx-ratings-wait-' + id).style.visibility = 'visible';
	var request = Lokris.AjaxCall('index.php?eID=tx_ratings_ajax', tx_ratings_callback, {
		async: true,
		method: 'POST',
		postBody: 'ref=' + id + '&rating=' + rating + '&data=' + ajaxData + '&check=' + check
	});
}

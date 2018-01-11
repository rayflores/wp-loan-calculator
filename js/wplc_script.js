jQuery(document).ready(function($) { 
	$('.wplc-form').calx({ 
		data : {
			A4: { format: '$0,0[.]00', formula: 'PMT((A2/12),A3,-(A1))' }
		}
	});
});
/* global wcPagarmeParams, PagarMe */
(function( $ ) {
	'use strict';
	$( function() {

		// $("#document_number").on('change', function(){

		//     var tamanho = $("#document_number").val().length;
			
		//     if(tamanho < 11){
		//         $("#document_number").mask("999.999.999-99");
		//     } else if(tamanho >= 11){
		//         $("#document_number").mask("99.999.999/9999-99");
		//     }                   
		// });


		$('#transfer_interval').on('change', function (e) {
		    var optionSelected = $("option:selected", this);
		    var valueSelected = this.value;
		    $('.transfer_day').hide(100);
		    $('.disable_day').prop('disabled', 'disabled');
		    if( valueSelected == 'weekly' ){
		    	$('.weekly #transfer_day').prop('disabled', false);
		    	$('.transfer_day.weekly').show(100);
		    }else{
		    	$('.monthly #transfer_day').prop('disabled', false);
		    	$('.transfer_day.monthly').show(100);
		    }
		});

	});

}( jQuery ));
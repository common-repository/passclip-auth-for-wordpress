

jQuery(document).ready(function($){

	$('fieldset').remove();

	var pca_user_meta = pca_made_by_pca;
	if ( pca_user_meta['made_by_pca'] > 0 ){
		$('#account_email').attr( "disabled" , "disabled" );

		$('#account_email').css( "background-color" , "gray" );
	}

 });

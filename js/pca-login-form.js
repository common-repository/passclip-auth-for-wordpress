
jQuery(document).ready(function($){

		var pca_password = pca_password_form;

		$('#' + pca_password['pca_password_id'] ).attr("placeholder", pca_password['pca_password_placeholder'] );

		if ( '' != pca_password["pca_lostpassword_url"]){
			$('a[href="' + pca_password["pca_lostpassword_url"] + '"]').remove();
		}

 });

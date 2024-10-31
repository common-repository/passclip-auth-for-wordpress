
jQuery(document).ready(function($){
	$('form').submit(function(){
		var isFilled = true;
		if ( '' == $('#PcaPassClipCode').val().trim() ){
			$('#validate_required2').eq(0).text('Input PassClip Code');
			$('#PcaPassClipCode').eq(0).css( 'outline-style', 'solid');
			$('#PcaPassClipCode').eq(0).css( 'outline-color', 'red');
			$('#PcaPassClipCode').eq(0).css( 'outline-width', '2px');
			$('body').eq(0).scrollTop( $('#PcaPassClipCode').eq(0).offset().top - $('#PcaPassClipCode').eq(0).height() - $('h2').eq(0).height() );
			$('#PcaPassClipCode').eq(0).focus();
			isFilled = false;
		} else {
			$('#validate_required2').text(' ');
			$('#PcaPassClipCode').eq(0).css( 'outline-style', 'solid');
			$('#PcaPassClipCode').eq(0).css( 'outline-color', 'red');
			$('#PcaPassClipCode').eq(0).css( 'outline-width', '0px');
		}
		if ( '' == $('#PcaAppServiceId').val().trim() ){
			$('#validate_required').eq(0).text('Input PCA app service id');
			$('#PcaAppServiceId').eq(0).css( 'outline-style', 'solid');
			$('#PcaAppServiceId').eq(0).css( 'outline-color', 'red');
			$('#PcaAppServiceId').eq(0).css( 'outline-width', '2px');
			$('body').eq(0).scrollTop( $('#PcaAppServiceId').eq(0).offset().top - $('#PcaAppServiceId').eq(0).height() - $('h2').eq(0).height() );
			$('#PcaAppServiceId').eq(0).focus();
			isFilled = false;
		} else {
			$('#validate_required').text(' ');
			$('#PcaAppServiceId').eq(0).css( 'outline-style', 'solid');
			$('#PcaAppServiceId').eq(0).css( 'outline-color', 'red');
			$('#PcaAppServiceId').eq(0).css( 'outline-width', '0px');
		}

		if ( false == isFilled ){
			return false;
		}
	});

	if( true == $('#PcaOptionsPreset4').prop('checked') ){
		$('#pca_advanced input').attr('disabled', false);
	} else{
		$('#pca_advanced input').attr('disabled', true);
	}
	$('.PcaOptionsPreset').click(function(){
		if( true == $('#PcaOptionsPreset4').prop('checked') ){
			$('#pca_advanced input').attr('disabled', false);
			$('#pca_advanced option').attr('disabled', false);
			if( true == $('#PcaOptionsWidget').prop('checked') ){
				$('#PcaOptionsWidgetRedirect').attr('disabled', false);
			} else{
				$('#PcaOptionsWidgetRedirect').attr('disabled', true);
			}
		}else if ( true == $('#PcaOptionsPreset1').prop('checked') ){ //setting for blog
			$('#pca_advanced input').attr('disabled', true);
			$('#pca_advanced option').attr('disabled', true);
			$('#PcaOptionShowPassclipCode').attr('checked',false);
			$('#PcaOptionDefaultRole option[value=""]').attr('selected', true);
			$('#PcaOptionAllowWpLogin input').attr('checked',false);
			$('#PcaOptionSendNewUserNoticeTo input[value="both"]').attr('checked',true);
			$('#PcaOptionHideLostpasswordLink').attr('checked',true);
			$('#PcaOptionDontCreateUser').attr('checked',true);
			$('#PcaOptionsDontShowAdminBar').attr('checked',true);
			$('#PcaOptionsWidget').attr('checked',false);
			$('#PcaOptionsWidgetRedirect').attr('disabled', true);
		}else if( true == $('#PcaOptionsPreset2').prop('checked') ){ //setting for community
			$('#pca_advanced input').attr('disabled', true);
			$('#pca_advanced option').attr('disabled', true);
			$('#PcaOptionShowPassclipCode').attr('checked',true);
			$('#PcaOptionDefaultRole option[value=""]').attr('selected', true);
			$('#PcaOptionAllowWpLogin input').attr('checked',false);
			$('#PcaOptionSendNewUserNoticeTo input[value="both"]').attr('checked',true);
			$('#PcaOptionHideLostpasswordLink').attr('checked',true);
			$('#PcaOptionDontCreateUser').attr('checked',false);
			$('#PcaOptionsDontShowAdminBar').attr('checked',true);
			$('#PcaOptionsWidget').attr('checked',true);
			$('#PcaOptionsWidgetRedirect').attr('disabled', false);
		}else if( true == $('#PcaOptionsPreset3').prop('checked') ){ //setting for ECsite
			$('#pca_advanced input').attr('disabled', true);
			$('#pca_advanced option').attr('disabled', true);
			$('#PcaOptionShowPassclipCode').attr('checked',true);
			$('#PcaOptionDefaultRole option[value="subscriber"]').attr('selected', true);
			$('#PcaOptionAllowWpLogin input').attr('checked',false);
			$('#PcaOptionSendNewUserNoticeTo input[value="both"]').attr('checked',true);
			$('#PcaOptionHideLostpasswordLink').attr('checked',true);
			$('#PcaOptionDontCreateUser').attr('checked',false);
			$('#PcaOptionsDontShowAdminBar').attr('checked',false);
			$('#PcaOptionsWidget').attr('checked',true);
			$('#PcaOptionsWidgetRedirect').attr('disabled', false);
		}
	});

	if( true == $('#PcaOptionsWidget').prop('checked') ){
		$('#PcaOptionsWidgetRedirect').attr('disabled', false);
	} else{
		$('#PcaOptionsWidgetRedirect').attr('disabled', true);
	}
	$('#PcaOptionsWidget').click(function(){
		if( true == $('#PcaOptionsWidget').prop('checked') ){
			$('#PcaOptionsWidgetRedirect').attr('disabled', false);
		} else{
			$('#PcaOptionsWidgetRedirect').attr('disabled', true);
		}
	});
});
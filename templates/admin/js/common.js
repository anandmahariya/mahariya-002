$(function(){
	$("#frm-shorten").validate({
		rules: {
			url: {
				required:true,
				url:true
			}
		},
		messages: {
			url: {
				required: 'Enter valid URL!',
				url: 'Enter valid URL!'
			}
		},
		submitHandler : function(form){
			$.ajax({
				type: "post",
				url: home,
				data: $(form).serialize(),
				dataType: "json",
				contentType: "application/x-www-form-urlencoded",
				success: function(responseData, textStatus, jqXHR) {
					var json = $.parseJSON(responseData);
					if(json.error == 0){
						$('#msgcontainer').html('<div role="alert" class="alert alert-success">'+json.data.ckey+'</div>');
					}else{
						$('#msgcontainer').html('<div role="alert" class="alert alert-danger">'+json.display_msg+'</div>');
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert(errorThrown);
				}
			});
		}
	});
});
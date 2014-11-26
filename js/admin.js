$(function()
{
	$.ajax(
	{
		dataType : "json",
		success : function(data)
		{
			if (!data.ok)
			{
				$("#login").show();
			}
		},
		url : "../ajax.php?get=checklogin"
	});
});

function login()
{
	var username = $("#username").val();
	var password = $("#password").val();

	// TODO: Check if username and password has been entered

	$.ajax(
	{
		data :
		{
			username : username,
			password : password
		},
		dataType : "json",
		success : function(data)
		{
			if (data.ok)
			{
				$("#login").hide();
			}
			else
			{
				$("#login-info").text("Username or password wrong!").show();

				var shakes = 2;
				var distance = 10;
				var duration = 400;

				for (var shake = 1; shake <= shakes; shake++)
				{
					$("#login").animate({left : (distance * -1)}, (((duration / shakes) / 4))).animate({left : distance}, ((duration / shakes) / 2)).animate({left : 0}, (((duration / shakes) / 4)));
				}
			}
		},
		type : "POST",
		url : "../ajax.php?get=checklogin"
	});
}
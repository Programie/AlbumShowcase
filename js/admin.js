$(function()
{
	$("#change-password-apply").on("click", changePassword);

	$("#logout").on("click", function()
	{
		$.ajax(
		{
			dataType : "json",
			success : function()
			{
				document.location.href = "..";
			},
			url : "../ajax.php?get=logout"
		});
	});

	$("#album-list").on("click", ".delete-album", function()
	{
		$("#delete-confirmation-album").text($(this).closest(".album-row").find(".album-title").text());

		$("#delete-confirmation").modal("show");
	});

	$.ajax(
	{
		dataType : "json",
		success : function(data)
		{
			if (data.ok)
			{
				$("#user-dropdown-username").text(data.username);
				$(".show-loggedin").show();
				loadAlbums();
			}
			else
			{
				$("#login").show();
			}
		},
		url : "../ajax.php?get=checklogin"
	});
});

function shake(element)
{
	var shakes = 2;
	var distance = 10;
	var duration = 400;

	for (var shake = 1; shake <= shakes; shake++)
	{
		element.animate({left : (distance * -1)}, (((duration / shakes) / 4))).animate({left : distance}, ((duration / shakes) / 2)).animate({left : 0}, (((duration / shakes) / 4)));
	}
}

function changePassword()
{
	var currentPassword = $("#current-password").val();
	var newPassword = $("#new-password").val();
	var newPasswordConfirm = $("#new-password-confirm").val();

	if (newPassword != newPasswordConfirm)
	{
		$("change-password-info").text("The new passwords do not match!").show();
		return;
	}

	$.ajax(
	{
		data :
		{
			currentPassword : currentPassword,
			newPassword : newPassword
		},
		dataType : "json",
		success : function(data)
		{
			if (data.ok)
			{
				$("#change-password").modal("hide");
				$(".show-loggedin").hide();
				$("#login").show();
			}
			else
			{
				var reason;

				switch (data.reason)
				{
					case "auth_fail":
						reason = "The current password is wrong!";
						break;
					case "demo_user":
						reason = "The password of the demo user can't be changed!";
						break;
					default:
						reason = "Unknown error: " + data.reason;
				}

				$("#change-password-info").text(reason).show();

				shake($("#change-password"));
			}
		},
		type : "POST",
		url : "../ajax.php?get=changepassword"
	});
}

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
				$("#user-dropdown-username").text(username);
				$(".show-loggedin").show();
				loadAlbums();
			}
			else
			{
				$("#login-info").text("Username or password wrong!").show();

				shake($("#login"));
			}
		},
		type : "POST",
		url : "../ajax.php?get=checklogin"
	});
}

function loadAlbums()
{
	$.ajax(
	{
		dataType : "json",
		error : function(jqXhr)
		{
			console.log(jqXhr);
		},
		success : function(data)
		{
			for (var index in data)
			{
				var albumData = data[index];

				albumData.releaseDate = moment(albumData.releaseDate, "YYYY-MM-DD").format("L");
			}

			$("#album-list").html(Mustache.render($("#album-list-template").html(),
			{
				list : data
			}));
		},
		url : "../ajax.php?get=allalbums"
	});
}
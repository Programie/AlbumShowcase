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
				document.location.href = "../";
			},
			url : "../service/user/logout"
		});
	});

	$("#new-album-button").on("click", function()
	{
		showEditAlbum(null);
	});

	$("#edit-album-releasedate").datepicker(
	{
		calendarWeeks : true,
		autoclose : true,
		todayHighlight : true
	});

	$("#edit-album-tracklist").on("click", ".delete-track", function()
	{
		$(this).closest(".tracklist-row").remove();
	});

	$("#edit-album-addtrack").on("click", function()
	{
		var highestTrackNumber = 0;
		var tracklist = $("#edit-album-tracklist");
		tracklist.find(".tracklist-row").find(".edit-album-tracklist-number").each(function()
		{
			highestTrackNumber = Math.max(highestTrackNumber, $(this).val());
		});

		tracklist.append(Mustache.render($("#edit-album-tracklist-template").html(),
		[
			{
				number : highestTrackNumber + 1,
				title : "",
				artist : "",
				length : "00:00"
			}
		]));
	});

	$("#edit-album-readfrommetadata").on("click", function()
	{
		$.ajax(
		{
			dataType : "json",
			success : function(data)
			{
				buildTracklist(data);
			},
			url : "../service/albums/" + $("#edit-album").data("albumid") + "/metadata"
		});
	});

	$("#delete-confirmation-button").on("click", function()
	{
		var deleteConfirmationModal = $("#delete-confirmation");

		$.ajax(
		{
			success : function()
			{
				loadAlbums();
				deleteConfirmationModal.modal("hide");
			},
			type : "DELETE",
			url : "../service/albums/" + deleteConfirmationModal.data("albumid")
		});
	});

	var albumList = $("#album-list");

	albumList.on("click", ".delete-album", function()
	{
		var deleteConfirmationModal = $("#delete-confirmation");
		var albumRow = $(this).closest(".album-row");

		$("#delete-confirmation-album").text(albumRow.find(".album-title").text());

		deleteConfirmationModal.data("albumid", albumRow.data("albumid"));
		deleteConfirmationModal.modal("show");
	});

	albumList.on("click", ".edit-album", function()
	{
		showEditAlbum($(this).closest(".album-row").data("albumid"));
	});

	albumList.on("click", ".show-album-stats", function()
	{
		$.ajax(
		{
			dataType : "json",
			success : showStats,
			url : "../service/albums/" + $(this).closest(".album-row").data("albumid") + "/stats"
		});
	});

	var uploadCoverProgressbar = $("#edit-album-uploadcover-progressbar");
	var uploadFileProgressbar = $("#edit-album-uploadfile-progressbar");

	$("#edit-album-uploadcover").fileupload(
	{
		done : function()
		{
			$("#edit-album-uploadcover-progressbar-container").hide();
			$("#edit-album-cover").attr("src", "../service/albums/" + $("#edit-album").data("albumid") + "/cover.jpg?timestamp=" + new Date().getTime());
		},
		fail : function()
		{
			uploadCoverProgressbar[0].className = "progress-bar progress-bar-danger progress-bar-striped";
			uploadCoverProgressbar.text("Failed");
		},
		progress : function (event, data)
		{
			var percent = parseInt(data.loaded / data.total * 100, 10) + "%";
			uploadCoverProgressbar[0].className = "progress-bar progress-bar-success progress-bar-striped active";
			uploadCoverProgressbar.css("width", percent);
			uploadCoverProgressbar.text(percent)
		},
		start : function()
		{
			uploadCoverProgressbar[0].className = "";
			$("#edit-album-uploadcover-progressbar-container").show();
		}
	});

	$("#edit-album-uploadfile").fileupload(
	{
		done : function()
		{
			uploadFileProgressbar[0].className = "progress-bar progress-bar-success progress-bar-striped";
			uploadFileProgressbar.text("Finished");
		},
		fail : function()
		{
			uploadFileProgressbar[0].className = "progress-bar progress-bar-danger progress-bar-striped";
			uploadFileProgressbar.text("Failed");
		},
		progress : function (event, data)
		{
			var percent = parseInt(data.loaded / data.total * 100, 10) + "%";
			uploadFileProgressbar[0].className = "progress-bar progress-bar-success progress-bar-striped active";
			uploadFileProgressbar.css("width", percent);
			uploadFileProgressbar.text(percent)
		},
		start : function()
		{
			uploadFileProgressbar[0].className = "";
			$("#edit-album-uploadfile-progressbar-container").show();
		}
	});

	$("#stats").on("shown.bs.modal", function()
	{
		Flotr.draw($("#stats-container")[0], [$("#stats").data("data")],
		{
			xaxis :
			{
				minorTickFreq : 4,
				mode : "time"
			},
			grid :
			{
				minorVerticalLines : true
			},
			mouse :
			{
				track : true,
				relative : true,
				trackFormatter : function(object)
				{
					return "<b>" + moment(parseInt(object.x)).format("L") + "</b><br/>" + parseInt(object.y) + " downloads";
				}
			}
		});
	});

	$.ajax(
	{
		dataType : "json",
		error : function(jqXhr)
		{
			if (jqXhr.status == 403)
			{
				$("#login").show();
			}
		},
		success : function(data)
		{
			$("#user-dropdown-username").text(data.username);
			$(".show-loggedin").show();
			loadAlbums();
		},
		url : "../service/user/login"
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

function buildTracklist(data)
{
	for (var index in data)
	{
		var duration = moment.duration(data[index].length * 1000);

		var minutes = Math.floor(duration.asMinutes()).toString();
		var seconds = duration.seconds().toString();

		data[index].length = minutes.paddingLeft("00") + ":" + seconds.paddingLeft("00");
	}

	$("#edit-album-tracklist").html(Mustache.render($("#edit-album-tracklist-template").html(), data));
}

function showEditAlbum(albumId)
{
	var editAlbumElement = $("#edit-album");

	$("#edit-album-modal-title").text("New album");
	$("#edit-album-title").val("");
	$("#edit-album-artist").val("");
	$("#edit-album-releasedate").data("datepicker").setDate(new Date());
	$("#edit-album-tracklist").html("");
	$("#edit-album-cover").attr("src", "");
	$("#edit-album-uploadcover-progressbar-container").hide();
	$("#edit-album-uploadfile-progressbar-container").hide();
	editAlbumElement.data("albumid", albumId);

	if (albumId)
	{
		$.ajax(
		{
			dataType : "json",
			success : function(data)
			{
				$("#edit-album-modal-title").text("Edit album - " + data.title);
				$("#edit-album-title").val(data.title);
				$("#edit-album-artist").val(data.artist);
				$("#edit-album-releasedate").data("datepicker").setDate(moment(data.releaseDate).toDate());
				$("#edit-album-cover").attr("src", "../service/albums/" + albumId + "/cover.jpg");

				buildTracklist(data.tracks);
			},
			url : "../service/albums/" + albumId
		});
	}

	var uploadCover = $("#edit-album-uploadcover");
	var uploadFile = $("#edit-album-uploadfile");

	uploadCover.attr("disabled", !albumId);
	uploadFile.attr("disabled", !albumId);

	uploadCover.fileupload("option",
	{
		url : "../service/albums/" + albumId + "/cover.jpg"
	});
	uploadFile.fileupload("option",
	{
		url : "../service/albums/" + albumId + "/file.zip"
	});

	editAlbumElement.modal("show");
}

function saveAlbum()
{
	var tracks = [];

	$("#edit-album-tracklist").find(".tracklist-row").each(function()
	{
		var minutes = 0;
		var seconds = 0;

		var length = $(this).find(".edit-album-tracklist-length").val().split(":");
		if (length.length > 1)
		{
			minutes = parseInt(length[0]);
			seconds = parseInt(length[1]);
		}
		else
		{
			seconds = parseInt(length[0]);
		}

		tracks.push(
		{
			number : parseInt($(this).find(".edit-album-tracklist-number").val()),
			title : $(this).find(".edit-album-tracklist-title").val(),
			artist : $(this).find(".edit-album-tracklist-artist").val(),
			length : minutes * 60 + seconds
		});
	});

	var url = ["../service/albums"];
	var method = "POST";

	var albumId = $("#edit-album").data("albumid");
	if (albumId)
	{
		url.push(albumId);
		method = "PUT";
	}

	$.ajax(
	{
		data : JSON.stringify(
		{
			title : $("#edit-album-title").val(),
			artist : $("#edit-album-artist").val(),
			releaseDate : moment($("#edit-album-releasedate").data("datepicker").getDate()).format("YYYY-MM-DD"),
			tracks : tracks
		}),
		success : function()
		{
			loadAlbums();
			$("#edit-album").modal("hide");
		},
		type : method,
		url : url.join("/")
	});
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
			password : currentPassword,
			newPassword : newPassword
		},
		dataType : "json",
		error : function(jqXhr)
		{
			var changePasswordElement = $("#change-password");
			var infoElement = $("#change-password-info");

			if (jqXhr.status == 403)
			{
				switch (jqXhr.responseText)
				{
					case "demo_user":
						infoElement.text("The password of the demo user can't be changed!").show();
						shake(changePasswordElement);
						break;
					case "forbidden":
						infoElement.text("The current password is wrong!").show();
						shake(changePasswordElement);
						break;
				}
			}
		},
		success : function()
		{
			$("#change-password").modal("hide");
			$(".show-loggedin").hide();
			$("#login").show();
		},
		type : "POST",
		url : "../service/user/password"
	});
}

function login()
{
	var username = $("#username").val();
	var password = $("#password").val();

	if (!username || !password)
	{
		$("#login-info").text("Please enter a username and password!").show();
		return;
	}

	$.ajax(
	{
		data :
		{
			username : username,
			password : password
		},
		dataType : "json",
		error : function(jqXhr)
		{
			if (jqXhr.status == 403)
			{
				$("#login-info").text("Username or password wrong!").show();

				shake($("#login"));
			}
		},
		success : function()
		{
			$("#login").hide();
			$("#user-dropdown-username").text(username);
			$(".show-loggedin").show();
			loadAlbums();
		},
		type : "POST",
		url : "../service/user/login"
	});
}

function loadAlbums()
{
	$.ajax(
	{
		dataType : "json",
		success : function(data)
		{
			for (var index in data)
			{
				var albumData = data[index];

				albumData.releaseDate = moment(albumData.releaseDate).format("L");
			}

			var noAlbumsInfo = $("#no-albums-info");
			var albumsTable = $("#albums-table");

			if (data.length)
			{
				$("#album-list").html(Mustache.render($("#album-list-template").html(),
				{
					list : data
				}));

				noAlbumsInfo.hide();
				albumsTable.show();
			}
			else
			{
				noAlbumsInfo.show();
				albumsTable.hide();
			}
		},
		url : "../service/albums/all"
	});
}

function showStats(data)
{
	$("#stats-modal-title").text("Stats for " + moment(data.startDate).format("L") + " - " + moment(data.endDate).format("L"));

	var modal = $("#stats");
	modal.data("data", data.data);
	modal.modal("show");
}
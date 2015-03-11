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
			url : "../ajax.php?get=metadata&id=" + $("#edit-album").data("albumid")
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
			url : "../ajax.php?get=deletealbum&id=" + deleteConfirmationModal.data("albumid")
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
			url : "../ajax.php?get=getstats&id=" + $(this).closest(".album-row").data("albumid")
		});
	});

	var uploadCoverProgressbar = $("#edit-album-uploadcover-progressbar");
	var uploadFileProgressbar = $("#edit-album-uploadfile-progressbar");

	$("#edit-album-uploadcover").fileupload(
	{
		done : function()
		{
			$("#edit-album-uploadcover-progressbar-container").hide();
			$("#edit-album-cover").attr("src", "../albums/" + $("#edit-album").data("albumid") + ".jpg?timestamp=" + new Date().getTime());
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
				$("#edit-album-cover").attr("src", "../albums/" + albumId + ".jpg");

				buildTracklist(data.tracks);
			},
			url : "../ajax.php?get=albumdata&id=" + albumId
		});
	}

	var uploadCover = $("#edit-album-uploadcover");
	var uploadFile = $("#edit-album-uploadfile");

	uploadCover.attr("disabled", !albumId);
	uploadFile.attr("disabled", !albumId);

	uploadCover.fileupload("option", "url", "../ajax.php?get=uploadfile&id=" + albumId + "&type=cover");
	uploadFile.fileupload("option", "url", "../ajax.php?get=uploadfile&id=" + albumId + "&type=upload");

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

	var url = "../ajax.php?get=savealbum";

	var albumId = $("#edit-album").data("albumid");
	if (albumId)
	{
		url += "&id=" + albumId;
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
		type : "POST",
		url : url
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
		url : "../ajax.php?get=allalbums"
	});
}

function showStats(data)
{
	$("#stats-modal-title").text("Stats for " + moment(data.startDate).format("L") + " - " + moment(data.endDate).format("L"));

	var modal = $("#stats");
	modal.data("data", data.data);
	modal.modal("show");
}
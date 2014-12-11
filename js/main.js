$(function()
{
	$("#albums").on("click", ".tracklist-button", function()
	{
		$("#tracklist").modal("show");

		var albumRootElement = $(this).closest(".album");

		var tracklistContent = $("#tracklist-content");
		tracklistContent.empty();

		$("#tracklist-label").html(albumRootElement.find(".album-title").html());

		$.ajax(
		{
			dataType : "json",
			success : function(data)
			{
				var duration;
				var minutes;
				var seconds;
				var totalLength = 0;

				for (var index in data)
				{
					totalLength += data[index].length;

					duration = moment.duration(data[index].length * 1000);

					minutes = Math.floor(duration.asMinutes()).toString();
					seconds = duration.seconds().toString();

					data[index].length = minutes.paddingLeft("00") + ":" + seconds.paddingLeft("00");
				}

				duration = moment.duration(totalLength * 1000);

				minutes = Math.floor(duration.asMinutes()).toString();
				seconds = duration.seconds().toString();

				$("#tracklist-content").html(Mustache.render($("#tracklist-template").html(),
				{
					list : data,
					totalLength : minutes.paddingLeft("00") + ":" + seconds.paddingLeft("00")
				}));
			},
			url : "ajax.php?get=tracklist&id=" + albumRootElement.data("albumid")
		});
	});

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

			$("#albums").html(Mustache.render($("#albums-template").html(),
			{
				list : data
			}));
		},
		url : "ajax.php?get=albums"
	});
});
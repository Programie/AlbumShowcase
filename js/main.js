String.prototype.paddingLeft = function(paddingValue)
{
	return String(paddingValue + this).slice(-paddingValue.length);
};

function formatTime(seconds)
{
	var minutesDivisor = seconds % (60 * 60);
	var secondsDivisor = minutesDivisor % 60;

	var result =
	{
		minutes : Math.floor(seconds / 60),
		seconds : Math.ceil(secondsDivisor)
	};

	return result;
}

$(function()
{
	$("#albums").on("click", ".show-tracklist-button", function()
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
				var totalLength = 0;

				for (var index in data)
				{
					totalLength += data[index].length;

					var length = formatTime(data[index].length);

					var minutes = length.minutes.toString();
					var seconds = length.seconds.toString();

					data[index].length = minutes.paddingLeft("00") + ":" + seconds.paddingLeft("00");
				}

				totalLength = formatTime(totalLength);

				var totalMinutes = totalLength.minutes.toString();
				var totalSeconds = totalLength.seconds.toString();

				$("#tracklist-content").html(Mustache.render($("#tracklist-template").html(),
				{
					list : data,
					totalLength : totalMinutes.paddingLeft("00") + ":" + totalSeconds.paddingLeft("00")
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
			$("#albums").html(Mustache.render($("#albums-template").html(),
			{
				list : data
			}));
		},
		url : "ajax.php?get=albums"
	});
});
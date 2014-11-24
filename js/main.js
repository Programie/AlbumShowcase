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
	$(".show-tracklist-button").click(function()
	{
		$("#tracklist").modal("show");

		var albumRootElement = $(this).closest(".album");

		var tracklistContent = $("#tracklist-content");
		tracklistContent.empty();

		$("#tracklist-label").text(albumRootElement.find(".album-title").text() + " (" + albumRootElement.find(".album-releasedate").text() + ")");

		$.ajax(
		{
			dataType : "json",
			success : function(data)
			{
				var row;
				var cell;

				var table = $("<table>");
				table.addClass("table table-striped");
				tracklistContent.empty();// Make sure the content is still empty after data has been loaded
				tracklistContent.append(table);

				var header = $("<thead>");
				table.append(header);

				row = $("<tr>");
				header.append(row);

				cell = $("<th>");
				cell.text("#");
				row.append(cell);

				cell = $("<th>");
				cell.text("Title");
				row.append(cell);

				cell = $("<th>");
				cell.text("Artist");
				row.append(cell);

				cell = $("<th>");
				cell.text("Length");
				row.append(cell);

				var body = $("<tbody>");
				table.append(body);

				var totalLength = 0;

				for (var index in data)
				{
					var trackData = data[index];

					totalLength += trackData.length;

					trackData.length = formatTime(trackData.length);

					var minutes = trackData.length.minutes.toString();
					var seconds = trackData.length.seconds.toString();

					row = $("<tr>");
					body.append(row);

					cell = $("<td>");
					cell.text(trackData.number);
					row.append(cell);

					cell = $("<td>");
					cell.text(trackData.title);
					row.append(cell);

					cell = $("<td>");
					cell.text(trackData.artist);
					row.append(cell);

					cell = $("<td>");
					cell.text(minutes.paddingLeft("00") + ":" + seconds.paddingLeft("00"));
					row.append(cell);
				}

				totalLength = formatTime(totalLength);

				var totalMinutes = totalLength.minutes.toString();
				var totalSeconds = totalLength.seconds.toString();

				var footer = $("<tfoot>");
				table.append(footer);

				row = $("<tr>");
				footer.append(row);

				cell = $("<th>");
				row.append(cell);

				cell = $("<th>");
				row.append(cell);

				cell = $("<th>");
				row.append(cell);

				cell = $("<th>");
				cell.text(totalMinutes.paddingLeft("00") + ":" + totalSeconds.paddingLeft("00"));
				row.append(cell);
			},
			url : "ajax.php?get=tracklist&id=" + albumRootElement.data("albumid")
		});
	});
});
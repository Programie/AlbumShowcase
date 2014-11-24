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

				row = $("<tr>");
				table.append(row);

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

				for (var index in data)
				{
					var trackData = data[index];

					trackData.length = formatTime(trackData.length);

					var minutes = trackData.length.minutes.toString();
					var seconds = trackData.length.seconds.toString();

					row = $("<tr>");
					table.append(row);

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
			},
			url : "ajax.php?get=tracklist&id=" + albumRootElement.data("albumid")
		});
	});
});
(function($) {
	$(document).ready(
		function() {
			$("#DataObjectSorterList").sortable(
				{
					handle : ".sortHandle",
					update : function () {
						$("#DataObjectSorterListInfo").text("updating records, please wait ...");
						var order = $('#DataObjectSorterList').sortable('serialize');
						$("#DataObjectSorterListInfo").load( DataObjectSorterURL + "?" + order );
					}
				}
			);
		}
	);
})(jQuery);

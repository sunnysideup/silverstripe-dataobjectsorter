(function($) {
	$(document).ready(
		function() {
			$("#test-list").sortable(
				{
					handle : ".sortHandle",
					update : function () {
						var order = $('#DataObjectSorterList').sortable('serialize');
						$("#DataObjectSorterListInfo").load( DataObjectSorterURL + "?" + order );
						}
				}
			);
		}
	);
})(jQuery);

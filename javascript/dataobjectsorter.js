(function($) {
	$(document).ready(
		function() {
			$("#DataObjectSorterList").sortable(
				{
					handle : ".sortHandle",
					update : function () {
						var order = $('#DataObjectSorterList').sortable('serialize');
						$("#DataObjectSorterListInfo").load( DataObjectSorterURL + "dodataobjectsort/?" + order );
						}
				}
			);
		}
	);
})(jQuery);

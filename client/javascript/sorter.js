(function($) {
    $(document).ready(
        function() {
            $("#DataObjectSorterList").sortable(
                {
                    handle : ".sortHandle",
                    update : function () {
                        $("#DataObjectSorterListInfo").text("updating records, please wait ...");
                        var order = $('#DataObjectSorterList').sortable('serialize');
                        // state change: POST the order together with the security token.
                        var data = order + "&" + encodeURIComponent(DataObjectSecurityIDName) + "=" + encodeURIComponent(DataObjectSecurityID);
                        $.post(DataObjectSorterURL, data, function (response) {
                            $("#DataObjectSorterListInfo").html(response);
                        });
                    }
                }
            );
        }
    );
})(jQuery);

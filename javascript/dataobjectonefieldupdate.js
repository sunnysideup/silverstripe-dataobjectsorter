/**
*@author nicolaas[at]sunnysideup . co . nz
*
**/

(function($){
	$(document).ready(
		function() {
			DataObjectOneFieldUpdate.init();
		}
	);


})(jQuery);


var DataObjectOneFieldUpdate = {

	ulSelector: "#DataObjectOneFieldUpdateUL",

	inputSelector: "#DataObjectOneFieldUpdateUL input.updateField",

	feedbackSelector: ".DataObjectOneFieldUpdateFeedback",

	fieldNameSelector: "#DataObjectOneFieldUpdateFieldName",

	loadingText: "updating data ...",

	fieldName: "",

	init: function () {
		DataObjectOneFieldUpdate.fieldName = jQuery(DataObjectOneFieldUpdate.fieldNameSelector).val();
		jQuery(DataObjectOneFieldUpdate.inputSelector).change(
			function () {
				var nameValue = jQuery(this).attr("name");
				var nameArray = nameValue.split("/");
				var table = nameArray[0];
				var id = nameArray[1];
				var value = parseInt(jQuery(this).val());
				if(table) {
					if(parseInt(id)) {
						if(DataObjectOneFieldUpdate.fieldName) {
							jQuery(this).parent("span").parent("li").addClass("loading");
							jQuery(DataObjectOneFieldUpdate.feedbackSelector).html(DataObjectOneFieldUpdate.loadingText);
							jQuery.get(
								DataObjectOneFieldUpdateURL + table + "/" + DataObjectOneFieldUpdate.fieldName + "/?value=" + escape(value) + "&id=" + id,
								{},
								function(data) {
									jQuery(DataObjectOneFieldUpdate.feedbackSelector).html(data)
									jQuery(".loading").removeClass("loading");
								}
							);
						}
						else {
							jQuery(DataObjectOneFieldUpdate.feedbackSelector).html("ERROR: could not find field to update");
						}
					}
					else {
						jQuery(DataObjectOneFieldUpdate.feedbackSelector).html("ERROR: could not find record to update");
					}
				}
				else {
					jQuery(DataObjectOneFieldUpdate.feedbackSelector).html("ERROR: could not find table to update");
				}
			}
		);
	}


}



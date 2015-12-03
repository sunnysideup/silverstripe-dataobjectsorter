/**
*@author nicolaas[at]sunnysideup . co . nz
*
**/

(function($){
	$(document).ready(
		function() {
			DataObjectOneFieldUpdate.dirtyfixes();
			DataObjectOneFieldUpdate.init();
		}
	);


})(jQuery);


var DataObjectOneFieldUpdate = {

	ulSelector: "#DataObjectOneFieldUpdateUL",

	inputSelector: "#DataObjectOneFieldUpdateUL input.updateField, #DataObjectOneFieldUpdateUL textarea.updateField, , #DataObjectOneFieldUpdateUL select.updateField",

	feedbackSelector: ".DataObjectOneFieldUpdateFeedback",

	fieldNameSelector: "#DataObjectOneFieldUpdateFieldName",

	loadingText: "updating data ...",

	fieldName: "",

	dirtyfixes: function() {
		jQuery(this.inputSelector).each(
			function(i) {
				var typeClass = jQuery(this).attr("type");
				console.debug(typeClass);
				if("checkbox" == typeClass) {
					jQuery(this).change(
						function() {
							if(jQuery(this).attr("checked")) {
								jQuery(this).attr("value", 1);
							}
							else {
								jQuery(this).attr("value", 0);
							}
						}
					)
				}
				jQuery(this).addClass(typeClass);
			}
		);
		var elementType = jQuery("#DataObjectOneFieldUpdateUL li span .updateField").first().prop('nodeName');
		if(elementType == "SELECT"){
			var selectID = jQuery("#DataObjectOneFieldUpdateUL li span .updateField").first().attr('id');
			jQuery('select#' + selectID).clone().attr({name: "ApplyToAll", id: 'ApplyToAll'}).insertAfter("#ApplyToAllButton");
		}
		else {
			var inputType = jQuery("#DataObjectOneFieldUpdateUL li input").first().attr("type");
			var inputValue = jQuery("#DataObjectOneFieldUpdateUL li input").first().val();
			jQuery("<input type='" + inputType + "' />").attr({ value: inputValue, name: "ApplyToAll", id: "ApplyToAll"}).insertAfter("#ApplyToAllButton");
		}
		
	},

	init: function () {
		DataObjectOneFieldUpdate.fieldName = jQuery(DataObjectOneFieldUpdate.fieldNameSelector).val();
		jQuery(DataObjectOneFieldUpdate.inputSelector).change(
			function () {
				var el = this;
				jQuery(this).css("border", "2px solid orange");;
				var nameValue = jQuery(this).attr("name");
				var nameArray = nameValue.split("/");
				var table = nameArray[0];
				var id = nameArray[1];
				var value = jQuery(this).val();
				if(jQuery(this).attr("type") == "checkbox") {
					if(jQuery(this).is(":checked")) {
						value = 1;
					}
					else {
						value = 0;
					}
				}
				if(table) {
					if(parseInt(id)) {
						if(DataObjectOneFieldUpdate.fieldName) {
							jQuery(this).parent("span").parent("li").addClass("loading");
							jQuery(DataObjectOneFieldUpdate.feedbackSelector).html(DataObjectOneFieldUpdate.loadingText);
							jQuery.get(
								DataObjectOneFieldUpdateURL + table + "/" + DataObjectOneFieldUpdate.fieldName + "/?value=" + escape(value) + "&id=" + id,
								{},
								function(data) {
									jQuery(el).css("border", "2px solid green");;
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
		).css("border", "2px solid blue");

		jQuery('#TextMatchFilter').on(
			'input',
			function(event){
				event.preventDefault();
				var filterValue = jQuery("#TextMatchFilter").val().toLowerCase();
				jQuery("#DataObjectOneFieldUpdateUL li label").each(
					function( index, value ) {
						var match = true;
						var currentLabel = jQuery(this);
						var labelText = currentLabel.text().toLowerCase();
						var filterValueArray = filterValue.split(" ");
						for(var i = 0, len = filterValueArray.length; i < len; i++) {
							if(labelText.indexOf(filterValueArray[i]) == -1){
								match = false;
							}
						}
						if (match){
							currentLabel.closest("li").show();
						}
						else{
							currentLabel.closest("li").hide();
						}
					}
				);
			}
		);

		jQuery('#ApplyToAllButton').on(
			'click',
			function(event){
				event.preventDefault();
				var applyToAllValue = jQuery("#ApplyToAll").val();
				var elementType = jQuery("#ApplyToAll").prop('nodeName');
				if(elementType == "SELECT"){
					jQuery("#DataObjectOneFieldUpdateUL li:visible select").each(
						function( index, el ) {
							var currentInput = jQuery(el);
							currentInput.val(applyToAllValue);
							currentInput.change();
						}
					);
				}
				else {
					jQuery("#DataObjectOneFieldUpdateUL li:visible input").each(
						function( index, el ) {
							var currentInput = jQuery(el);
							currentInput.val(applyToAllValue);
							currentInput.change();
						}
					);
				}
			}
		);
	}

}

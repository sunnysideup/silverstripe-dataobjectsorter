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

	inputSelector: "#DataObjectOneFieldUpdateUL input.updateField, #DataObjectOneFieldUpdateUL textarea.updateField, , #DataObjectOneFieldUpdateUL select.updateField",

	feedbackSelector: ".DataObjectOneFieldUpdateFeedback",

	fieldNameSelector: "input#DataObjectOneFieldUpdateFieldName",

	tableNameSelector: "input#DataObjectOneFieldUpdateTableName",

	loadingText: "updating data ...",

	tableName: "",

	fieldName: "",

	dirtyfixes: function() {
		jQuery(this.inputSelector).each(
			function(i) {
				var typeClass = jQuery(this).attr("type");
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
		var elementType = jQuery("#DataObjectOneFieldUpdateUL li span .updateField").first().prop('nodeName').toLowerCase();
		if(elementType == "select"){
			var selectID = jQuery("#DataObjectOneFieldUpdateUL li span .updateField").first().attr('id');
			jQuery('select#' + selectID).clone().attr({name: "ApplyToAll", id: 'ApplyToAll'}).insertAfter("#ApplyToAllButton");
		}
		else {
			var inputType = jQuery("#DataObjectOneFieldUpdateUL li input").first().attr("type").toLowerCase();
			var inputValue = jQuery("#DataObjectOneFieldUpdateUL li input").first().val();
			jQuery("<input type='" + inputType + "' />").attr({ value: inputValue, name: "ApplyToAll", id: "ApplyToAll"}).insertAfter("#ApplyToAllButton");
		}
	},

	init: function () {
		this.dirtyfixes();
		DataObjectOneFieldUpdate.fieldName = jQuery(DataObjectOneFieldUpdate.fieldNameSelector).val();
		DataObjectOneFieldUpdate.tableName = jQuery(DataObjectOneFieldUpdate.tableNameSelector).val();
		this.setupChangeListener();
		this.setupFilter();
		this.setupApplyAll();
	},

	setupChangeListener: function(){
		jQuery(DataObjectOneFieldUpdate.inputSelector).change(
			function () {
				var el = this;
				if(jQuery(el).attr("data-ignore-input-change") == "true") {
					return;
				}
				var idAndValue = DataObjectOneFieldUpdate.retrieveDetailsFromInput(el);
				var id = idAndValue.id
				var value = idAndValue.value
				var ids = new Array();
				ids.push(id);
				jQuery(el).addClass("runningUpdate");
				if(parseInt(id)) {
					if(DataObjectOneFieldUpdate.fieldName) {
						jQuery(el).parent("span").parent("li").addClass("loading");
						jQuery(DataObjectOneFieldUpdate.feedbackSelector).html(DataObjectOneFieldUpdate.loadingText);
						DataObjectOneFieldUpdate.updateServer(ids, value, el);
					}
					else {
						jQuery(DataObjectOneFieldUpdate.feedbackSelector).html("ERROR: could not find field to update");
					}
				}
				else {
					jQuery(DataObjectOneFieldUpdate.feedbackSelector).html("ERROR: could not find record to update");
				}
			}
		).addClass("readyForAction");
	},

	setupFilter: function(){
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
	},

	setupApplyAll: function() {
		jQuery('#ApplyToAllButton').on(
			'click',
			function(event){
				event.preventDefault();
				var canDo = confirm("Are you sure you would like to apply the selected value to all visible elements?");
				if (canDo == true) {
					var applyToAllValue = jQuery("#ApplyToAll").val();
					var elementType = jQuery("#ApplyToAll").prop('nodeName').toLowerCase();
					var ids = new Array();
					var inputSelector = "";
					if(elementType == "select"){
						inputSelector = "#DataObjectOneFieldUpdateUL li:visible select";
						jQuery(inputSelector).each(
							function( index, el ) {
								var currentInput = jQuery(el);
								currentInput.val(applyToAllValue);
								var idAndValue = DataObjectOneFieldUpdate.retrieveDetailsFromInput(currentInput);
								ids.push(idAndValue.id);
								//bypass on change
								currentInput.attr("data-ignore-input-change", "true");
								currentInput.change();
								currentInput.removeAttr("data-ignore-input-change");
							}
						);
					}
					else {
						elementType = jQuery("#ApplyToAll").attr('type').toLowerCase();
						if(elementType == "checkbox"){
							if(jQuery('#ApplyToAll').attr("checked")) {
								applyToAllValue = 1;
							}
							else {
								applyToAllValue = 0;
							}
						}
						inputSelector = "#DataObjectOneFieldUpdateUL li:visible input";
						jQuery(inputSelector).each(
							function( index, el ) {
								var currentInput = jQuery(el);
								currentInput.val(applyToAllValue);
								if(jQuery('#ApplyToAll').attr("checked")) {
									currentInput.prop('checked', true);
								}
								else{
									currentInput.prop('checked', false);
								}
								var idAndValue = DataObjectOneFieldUpdate.retrieveDetailsFromInput(currentInput);
								ids.push(idAndValue.id);
								//bypass on change
								currentInput.attr("data-ignore-input-change", "true");
								currentInput.change();
								currentInput.removeAttr("data-ignore-input-change");
							}
						);
					}
					DataObjectOneFieldUpdate.updateServer(ids, applyToAllValue, inputSelector);
				}
			}
		);
	},

	retrieveDetailsFromInput: function(el) {
		var nameValue = jQuery(el).attr("name");
		var nameArray = nameValue.split("/");
		var table = nameArray[0];
		if(table  !== this.tableName) {
			alert("ERROR 51");
			return;
		}
		var id = nameArray[1];
		var value = jQuery(el).val();
		if(jQuery(el).attr("type") == "checkbox") {
			if(jQuery(this).is(":checked")) {
				value = 1;
			}
			else {
				value = 0;
			}
		}
		return {
			"id": id,
			"value": value
		};
	},

	updateServer: function(ids, value, elementSelector) {
		var url = DataObjectOneFieldUpdateURL + this.tableName + "/" + this.fieldName + "/?value=" + escape(value) + "&id=" + ids.join()
		jQuery.get(
			url,
			{},
			function(data) {
				jQuery(elementSelector).addClass("updated");
				jQuery(DataObjectOneFieldUpdate.feedbackSelector).html(data)
				jQuery(".loading").removeClass("loading");
			}
		);
	}

}

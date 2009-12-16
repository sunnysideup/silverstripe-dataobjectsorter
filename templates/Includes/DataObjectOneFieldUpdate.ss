<div class="DataObjectOneFieldUpdateFeedback">please update fields below - NOTE: there is no undo!</h1>
<% if DataObjectsToBeUpdated %>
<input type="hidden" name="field" id="DataObjectOneFieldUpdateFieldName" value="$FieldToBeUpdated" />
<ul id="DataObjectOneFieldUpdateUL">
<% control DataObjectsToBeUpdated %>
	<li>
		<label for="input{$ID}">$Title</label>
		<span><input id="input{$ID}" name="$ClassName/$ID" class="updateField" /></span>
	</li>
<% end_control %>
</ul>
<% end_if %>
<div class="DataObjectOneFieldUpdateFeedback">please update fields above - NOTE: there is no undo!</h1>
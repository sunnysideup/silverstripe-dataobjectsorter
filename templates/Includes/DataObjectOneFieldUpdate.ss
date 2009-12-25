<h1 class="DataObjectOneFieldUpdateFeedback">please update fields below (<em>$SecureFieldToBeUpdated</em> in <em>$HumanReadableTableName</em>) - NB: there is no undo!</h1>
<% if DataObjectsToBeUpdated %>
<form id="tokenEffortForm" action="#" method="get">
<input type="hidden" name="field" id="DataObjectOneFieldUpdateFieldName" value="$SecureFieldToBeUpdated" />
<ul id="DataObjectOneFieldUpdateUL">
<% control DataObjectsToBeUpdated %>
	<li>
		<span>
			$FormField
		</span>
		<label for="input{$ID}">$Title</label>
	</li>
<% end_control %>
</ul>
<% end_if %>
</form>
<h1 class="DataObjectOneFieldUpdateFeedback">please update fields above ($SecureFieldToBeUpdated in $SecureTableToBeUpdated) - NB: there is no undo!</h1>
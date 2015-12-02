<% if DataObjectsToBeUpdated %>

<% include DataObjectsToBeUpdatedPagination %>

<h1 class="DataObjectOneFieldUpdateFeedback">please update fields below (<em>$SecureFieldToBeUpdated</em> in <em>$HumanReadableTableName</em>) - NB: there is no undo!</h1>

<form id="tokenEffortForm" action="#" method="get">
	<div id="FilterAndApplyToAllInputs">
		<div> 
			<label for="TextMatchFilter">Filter fields for the following text:</label>
			<input type="text" id="TextMatchFilter" name="TextMatchFilter"/>
		</div>
		<div>
			<label for="ApplyToAll">Apply the following value to all displayed fields:</label>
			<a href="#" id="ApplyToAllButton">APPLY TO ALL</a>
		</div>
	</div>
	<input type="hidden" name="field" id="DataObjectOneFieldUpdateFieldName" value="$SecureFieldToBeUpdated" />
	<ul id="DataObjectOneFieldUpdateUL">
	<% loop DataObjectsToBeUpdated %>
		<li>
			<span>
				$FormField
			</span>
			<label for="input{$ID}">$MyTitle</label>
		</li>
	<% end_loop %>
	</ul>
</form>
<h1 class="DataObjectOneFieldUpdateFeedback">please update fields above ($SecureFieldToBeUpdated in $SecureTableToBeUpdated) - NB: there is no undo!</h1>
<% include DataObjectsToBeUpdatedPagination %>
<% else %>
<p>No records can be found.</p>
<% end_if %>





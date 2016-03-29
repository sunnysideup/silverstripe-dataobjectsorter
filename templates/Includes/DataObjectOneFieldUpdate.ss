<% if DataObjectsToBeUpdated %>

<h1 class="DataObjectOneFieldUpdateFeedback">please update fields below (<em>$SecureFieldToBeUpdated</em> in <em>$HumanReadableTableName</em>) - NB: there is no undo!</h1>
<div id="FilterAndApplyToAllInputs">
	<div>
		<label for="TextMatchFilter">Filter:</label>
		<input type="text" id="TextMatchFilter" name="TextMatchFilter"/>
	</div>
	<div>
		<label for="ApplyToAll">Apply the following value to all visible fields:</label>
		<a href="#" id="ApplyToAllButton">APPLY TO ALL</a>
	</div>
</div>


<form id="tokenEffortForm" action="#" method="get">
	<input type="hidden" name="field" id="DataObjectOneFieldUpdateFieldName" value="$SecureFieldToBeUpdated" />
	<input type="hidden" name="field" id="DataObjectOneFieldUpdateTableName" value="$SecureTableToBeUpdated" />
	<ul id="DataObjectOneFieldUpdateUL">
	<% loop DataObjectsToBeUpdated %>
		<li class="fieldHolder">
			<span>
				$FormField
			</span>
			<label for="input{$ID}">$MyTitle</label>
		</li>
	<% end_loop %>
	</ul>
</form>
<% include DataObjectsToBeUpdatedPagination %>
<% else %>
<p>No records can be found.</p>
<% end_if %>





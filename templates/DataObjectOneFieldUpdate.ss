<% if $DataObjectsToBeUpdated %>

<h1 class="DataObjectOneFieldUpdateFeedback">Edit <em><u>$SecureFieldToBeUpdatedNice</u></em> in <em><u>$HumanReadableTableName</u></em></h1>
<div id="FilterAndApplyToAllInputs">
    <div>
        <label for="TextMatchFilter">Filter:</label>
        <input type="text" id="TextMatchFilter" name="TextMatchFilter"/>
    </div>
    <div>
        <a href="#" id="ApplyToAllButton">APPLY TO ALL</a>
    </div>
</div>


<form id="tokenEffortForm" action="#" method="get">
    <input type="hidden" name="field" id="DataObjectOneFieldUpdateFieldName" value="$SecureFieldToBeUpdated" />
    <input type="hidden" name="table" id="DataObjectOneFieldUpdateTableName" value="$SecureClassNameToBeUpdatedAsString" />
    <ul id="DataObjectOneFieldUpdateUL">
    <% loop $DataObjectsToBeUpdated %>
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
<p id="NoRecordsCanBeFound">No records can be found.</p>
<% end_if %>

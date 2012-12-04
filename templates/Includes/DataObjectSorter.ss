<% if Children %>
<div id="DataObjectSorterListInfo">
	<h1>Sort objects by <a href="#" title="drag-and-drop: click on item with mouse, hold click-button while moving item with your mouse, release click button once item is in the desired spot">drag-and-dropping</a> them into a new order and close this window when done.</h1>
</div>
<ul id="DataObjectSorterList">
<% control Children  %>
	<li id="dos_{$ID}"><div class="sortHandle"><img src="dataobjectsorter/images/arrow.png" alt="move" width="16" height="16" class="moveMe" />$SortTitle</div></li>
<% end_control  %>
</ul>
<% else  %>
<p>There are no objects to sort. Please close this window.</p>
<% end_if  %>

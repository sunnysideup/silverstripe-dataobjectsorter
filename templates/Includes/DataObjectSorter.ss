<div id="DataObjectSorterListInfo">
	<h1>Sort objects by drag-and-dropping them into a new order</h1>
	<p>(drag-and-drop: click on item with mouse, hold click-button while moving item with your mouse, release click button once item is in the desired spot)</p>
</div>
<ul id="DataObjectSorterList">
<% control Children  %>
	<li id="dos_{$ID}"><div class="sortHandle"><img src="dataobjectsorter/images/arrow.png" alt="move" width="16" height="16" class="moveMe" />$Title</div></li>
<% end_control  %>
</ul>
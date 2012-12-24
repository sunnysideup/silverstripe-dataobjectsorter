<% if DataObjectsToBeUpdated.MoreThanOnePage %>
<p class="pagination">
	<% if DataObjectsToBeUpdated.PrevLink %><a href="$DataObjectsToBeUpdated.PrevLink">&lt;&lt; Prev</a> | <% end_if %>
	<% loop DataObjectsToBeUpdated.Pages %>
		<% if CurrentBool %><strong>$PageNum</strong><% else %><a href="$Link">$PageNum</a><% end_if %>
	<% end_loop %>
	<% if DataObjectsToBeUpdated.NextLink %> | <a href="$DataObjectsToBeUpdated.NextLink">Next &gt;&gt;</a><% end_if %>
</p>
<% end_if %>

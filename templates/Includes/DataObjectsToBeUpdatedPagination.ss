<% if DataObjectsToBeUpdated.MoreThanOnePage %>
<p class="pagination">
	<% if DataObjectsToBeUpdated.PrevLink %><a href="$DataObjectsToBeUpdated.PrevLink">&lt;&lt; Prev</a> | <% end_if %>
	<% control DataObjectsToBeUpdated.Pages %>
		<% if CurrentBool %><strong>$PageNum</strong><% else %><a href="$Link">$PageNum</a><% end_if %>
	<% end_control %>
	<% if DataObjectsToBeUpdated.NextLink %> | <a href="$DataObjectsToBeUpdated.NextLink">Next &gt;&gt;</a><% end_if %>
</p>
<% end_if %>

<% if PaginatedListItems.MoreThanOnePage %>
<p class="pagination">
	<% if PaginatedListItems.PrevLink %><a href="$PaginatedListItems.PrevLink">&lt;&lt; Prev</a> | <% end_if %>
	<% loop PaginatedListItems.Pages %>
		<% if CurrentBool %><strong>$PageNum</strong><% else %><a href="$Link">$PageNum</a><% end_if %>
	<% end_loop %>
	<% if PaginatedListItems.NextLink %> | <a href="$PaginatedListItems.NextLink">Next &gt;&gt;</a><% end_if %>
</p>
<% else %>
<% end_if %>

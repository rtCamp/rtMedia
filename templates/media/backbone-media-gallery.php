<!-- template for single media in gallery -->
<ul>
<% _.each(data,function(media) { %>
	<li class="rt-media-list-item">
        <div class="rt-media-item-thumbnail">
            <a href ="<%= media.rt_permalink %>">
                <img src="<%= media.guid %>">
            </a>
        </div>

        <div class="rt-media-item-title">
            <h4 title="<%= media.media_title %>">
                <a href="<%= media.rt_permalink %>">
                    <%= media.media_title %>
                </a>
            </h4>
        </div>
    </li>
<% }); %>
<ul>
<!-- rt_media_actions remained in script tag -->
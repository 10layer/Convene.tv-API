<script src="<?= base_url() ?>resources/js/underscore-min.js"></script>
<script src="<?= base_url() ?>resources/js/backbone-min.js"></script>
<script src="<?= base_url() ?>resources/js/jquery-1.7.2.min.js"></script>
<script src="<?= base_url() ?>resources/js/jquery.pagination.js"></script>
<link rel="stylesheet" href="<?= base_url() ?>resources/less/admin.css" type="text/css" media="screen, projection" charset="utf-8" />
<script>
	$(function() {
		
		$("#convene-pagination").pagination(<?= $count ?>, {
			items_per_page: 25,
			callback: function(pg) {
				var offset=(pg)*25;
				$("#convene-comments").html("Loading…");
				$.getJSON("<?= base_url() ?>admin/get_comments/"+offset+"/25/<?= $private_key ?>?jsoncallback=?", function(data) {
					$("#convene-comments").html("");
					_.each(data.comments, function(comment) {
						console.log(comment);
						$("#convene-comments").append(_.template($("#comment-template").html(), comment));
					});
				});
			}
		});
		
		$.getJSON("<?= base_url() ?>admin/get_comments/0/25/<?= $private_key ?>?jsoncallback=?", function(data) {
			$("#convene-comments").html("");
			_.each(data.comments, function(comment) {
				$("#convene-comments").append(_.template($("#comment-template").html(), comment));
			});
		});
		
		$(document).on("click", ".convene-comment-live-toggle", function() {
			var comment_id=$(this).attr("commentid");
			var el=$(this);
			$.getJSON("<?= base_url() ?>admin/toggle_live/<?= $private_key ?>?jsoncallback=?", {comment_id: comment_id }, function(data) {
				if (data.live) {
					el.html("Delete");
				} else {
					el.html("Restore");
				}
			});
		});
		
		$(document).on("click", ".convene-comment-edit", function() {
			var comment_id=$(this).attr("commentid");
			var comment=$("#convene-comment-"+comment_id).html().trim();
			$("#convene-comment-"+comment_id).html(_.template($("#comment-edit-template").html(), { id: comment_id, comment: comment }));
		});
		
		$(document).on("click", ".convene-edit-save", function() {
			var comment_id=$(this).attr("commentid");
			var comment=$("#convene-textarea-"+comment_id).val().trim();
			$.post("<?= base_url() ?>admin/edit_comment/<?= $private_key ?>?jsoncallback=?", {comment_id: comment_id, comment:comment}, 
				function(data) {
					if (data.success) {
						$("#convene-comment-"+comment_id).html(comment);
					} else {
						alert("Problem saving comment");
					}
				},"jsonp"
			);
		});
		
		$(document).on("click", "#convene-search-search", function() {
			var searchstring=$("#convene-search-txt").val();
			if (searchstring.trim()=="") return false;
			$("#convene-comments").html("Loading...");
			$.getJSON("<?= base_url() ?>admin/search/0/25/<?= $private_key ?>?jsoncallback=?", {searchstring: searchstring}, function(data) {
				$("#convene-comments").html("");
				_.each(data.comments, function(comment) {
					$("#convene-comments").append(_.template($("#comment-template").html(), comment));
				});
				$("#convene-pagination").pagination(data.count, {
					items_per_page: 25,
					callback: function(pg) {
						var offset=(pg)*25;
						$("#convene-comments").html("Loading…");
						$.getJSON("<?= base_url() ?>admin/search/"+offset+"/25/<?= $private_key ?>?jsoncallback=?", {searchstring: searchstring}, function(data) {
							$("#convene-comments").html("");
							_.each(data.comments, function(comment) {
								$("#convene-comments").append(_.template($("#comment-template").html(), comment));
							});
						});
					}
				});
			});
		});
		
		$(document).on("click", "#convene-user-search", function() {
			var searchstring=$("#convene-user-filter").val();
			_user_search(searchstring);
		});
		
		function _user_search(searchstring) {
			if (searchstring.trim()=="") return false;
			$("#convene-comments").html("Loading...");
			$.getJSON("<?= base_url() ?>admin/user_search/0/25/<?= $private_key ?>?jsoncallback=?", {searchstring: searchstring}, function(data) {
				$("#convene-comments").html("");
				_.each(data.comments, function(comment) {
					$("#convene-comments").append(_.template($("#comment-template").html(), comment));
				});
				$("#convene-pagination").pagination(data.count, {
					items_per_page: 25,
					callback: function(pg) {
						var offset=(pg)*25;
						$("#convene-comments").html("Loading…");
						$.getJSON("<?= base_url() ?>admin/user_search/"+offset+"/25/<?= $private_key ?>?jsoncallback=?", {searchstring: searchstring}, function(data) {
							$("#convene-comments").html("");
							_.each(data.comments, function(comment) {
								$("#convene-comments").append(_.template($("#comment-template").html(), comment));
							});
						});
					}
				});
			});
		}
		
		$(document).on("click", "#convene-urlid-search", function() {
			var searchstring=$("#convene-urlid-filter").val();
			_urlid_search(searchstring);
		});
		
		function _urlid_search(searchstring) {
			if (searchstring.trim()=="") return false;
			$("#convene-comments").html("Loading...");
			$.getJSON("<?= base_url() ?>admin/urlid_search/0/25/<?= $private_key ?>?jsoncallback=?", {searchstring: searchstring}, function(data) {
				$("#convene-comments").html("");
				_.each(data.comments, function(comment) {
					$("#convene-comments").append(_.template($("#comment-template").html(), comment));
				});
				$("#convene-pagination").pagination(data.count, {
					items_per_page: 25,
					callback: function(pg) {
						var offset=(pg)*25;
						$("#convene-comments").html("Loading…");
						$.getJSON("<?= base_url() ?>admin/urlid_search/"+offset+"/25/<?= $private_key ?>?jsoncallback=?", {searchstring: searchstring}, function(data) {
							$("#convene-comments").html("");
							_.each(data.comments, function(comment) {
								$("#convene-comments").append(_.template($("#comment-template").html(), comment));
							});
						});
					}
				});
			});
		}
		
		$(document).on("click", "#convene-search-clear", function() {
			$("#convene-search-txt").val("");
			$("#convene-user-filter").val("");
			$("#convene-urlid-filter").val("");
			$("#convene-pagination").pagination(<?= $count ?>, {
				items_per_page: 25,
				callback: function(pg) {
					var offset=(pg)*25;
					$("#convene-comments").html("Loading…");
					$.getJSON("<?= base_url() ?>admin/get_comments/"+offset+"/25/<?= $private_key ?>?jsoncallback=?", function(data) {
						$("#convene-comments").html("");
						_.each(data.comments, function(comment) {
							$("#convene-comments").append(_.template($("#comment-template").html(), comment));
						});
					});
				}
			});
			
			$.getJSON("<?= base_url() ?>admin/get_comments/0/25/<?= $private_key ?>?jsoncallback=?", function(data) {
				$("#convene-comments").html("");
				_.each(data.comments, function(comment) {
					$("#convene-comments").append(_.template($("#comment-template").html(), comment));
				});
			});
			
		});
		
		$(document).on("click", ".convene-user", function() {
			searchstring=$(this).html();
			$("#convene-user-filter").val(searchstring);
			_user_search(searchstring);
		});
		
		$(document).on("click", ".convene-urlid", function() {
			searchstring=$(this).html();
			$("#convene-urlid-filter").val(searchstring);
			_urlid_search(searchstring);
		});
	});
</script>

<script type="text/template" id="comment-template">
	<a name="comment-<%= id %>"></a>
	<div class="convene-commentcontainer">
		<div class="convene-comment" id="convene-comment-<%= id %>"><%= comment %></div>
		<div class="convene-comment_footer">
			<div class='convene-urlid convene-action'><%= urlid %></div>
			<span class='convene-user convene-action' userid='<%= user_id %>'><%= fname + " " + sname %></span> on <%= date_created %>
| <span class='convene-comment-live-toggle convene-action' commentid='<%= id %>'><%= (live==1) ? 'Delete' : 'Restore' %></span> | <span class='convene-comment-edit convene-action' commentid='<%= id %>'>Edit</span>
		</div>
	</div>
</script>

<script type="text/template" id="comment-edit-template">
	<textarea class="convene-edit-textarea" id="convene-textarea-<%= id %>"><%= comment %></textarea>
	<input class="convene-edit-save" commentid='<%= id %>' type="button" name="save" value="Save" />
</script>

<div id="convene">
<div class="convene-search-container">
	<input type="text" id="convene-user-filter" value="" />
	<input type="button" id="convene-user-search" name="submit" value="Search users" /><br />
	<input type="text" id="convene-urlid-filter" value="" />
	<input type="button" id="convene-urlid-search" name="submit" value="Search urlid" /><br />
	<input type="text" id="convene-search-txt" name="search" value="" />
	<input type="button" id="convene-search-search" name="submit" value="Search comments" /><br />
	<input type="button" id="convene-search-clear" name="clear" value="Clear" />
</div>
<div id="convene-pagination"></div>
<div id="convene-comments">Loading...</div>
</div>
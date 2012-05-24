<script src="<?= base_url() ?>resources/js/underscore-min.js"></script>
<script src="<?= base_url() ?>resources/js/jquery-1.7.2.min.js"></script>
<script src="<?= base_url() ?>resources/js/jquery.pagination.js"></script>
<link rel="stylesheet" href="<?= base_url() ?>resources/less/admin.css" type="text/css" media="screen, projection" charset="utf-8" />
<script>
	$(function() {
		
		
		
		function _convene_populate_data(offset, orderby, orderdir) {
			$("#convene-users").html("Loading...");
			searchstring=$("#convene-user-filter").val();
			if (!offset) {
				offset=0;
			}
			$.getJSON("<?= base_url() ?>admin/get_users/"+offset+"/<?= $perpage ?>/"+orderby+"/"+orderdir+"/<?= $private_key ?>?jsoncallback=?", {searchstring: searchstring }, function(data) {
				$("#convene-users").html("");
				$("#convene-users").append(_.template($("#convene-usertable-template").html(), {data: data} ));
				$("#convene-pagination").pagination(data.count, {
					items_per_page: <?= $perpage ?>,
					current_page: (offset / <?= $perpage ?> ),
					callback: function(pg) {
						var offset=(pg)*<?= $perpage ?>;
						_convene_populate_data(offset, orderby, orderdir);
					}
				});
			});
		}
		
		$(document).on("click", ".convene-active_checkbox", function() {
			var user_id=$(this).attr("userid");
			var el=$(this);
			$.getJSON("<?= base_url() ?>admin/user_toggle_active/<?= $private_key ?>?jsoncallback=?", {user_id: user_id }, function(data) {
				el.prop('checked',data.active);
			});
		});
		
		$(document).on("click", ".convene-moderated_checkbox", function() {
			var user_id=$(this).attr("userid");
			var el=$(this);
			$.getJSON("<?= base_url() ?>admin/user_toggle_moderated/<?= $private_key ?>?jsoncallback=?", {user_id: user_id }, function(data) {
				el.prop('checked',data.active);
			});
		});
		
		$(document).on("click", "#convene-user-search", function() {
			_convene_populate_data(0);
		});
				
		$(document).on("click", "#convene-search-clear", function() {
			$("#convene-user-filter").val("");
			_convene_populate_data(0);
		});
		
		$(document).on("click", ".convene-user", function() {
			searchstring=$(this).html();
			$("#convene-user-filter").val(searchstring);
			_user_search(searchstring);
		});
		
		$(document).on('click', '.cell_title', function() {
			var dir="ASC";
			if ($(this).hasClass('sorted')) {
				if ($(this).hasClass('sort-desc')) {
					dir="ASC";
				} else {
					dir="DESC";
				}
			}
			_convene_populate_data(0, $(this).attr("value"), dir);
		});
		
		//Finally, load some data
		_convene_populate_data();
	});
</script>

<script type="text/template" id="convene-usertable-template">
	<table id="convene-user-table" class="table-bordered table-condensed table-striped">
		<thead id='cell_titles'>
		<tr>
			<th value='active' class='cell_title <%= (data.order_by=='active' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Active</th>
			<th value='moderated' class='cell_title <%= (data.order_by=='moderated' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Moderated</th>
			<th value='sname' class='cell_title <%= (data.order_by=='sname' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Name</th>
			<th value='email' class='cell_title <%= (data.order_by=='email' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Email</th>
			<th value='cel' class='cell_title <%= (data.order_by=='cel' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Cel</th>
			<th value='tel' class='cell_title <%= (data.order_by=='tel' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Tel</th>
			<th value='date_created' class='cell_title <%= (data.order_by=='date_created' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Date Created</th>
			<th value='date_edited' class='cell_title <%= (data.order_by=='date_edited' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Date Edited</th>
			<th value='date_login' class='cell_title <%= (data.order_by=='date_login' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Last Login</th>
			<th value='designation' class='cell_title <%= (data.order_by=='designation' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Designation</th>
			<th value='company' class='cell_title <%= (data.order_by=='company' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Company</th>
			<th value='city' class='cell_title <%= (data.order_by=='city' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>City</th>
			<th value='country' class='cell_title <%= (data.order_by=='country' ? 'sorted '+(data.order_dir=='DESC' ? 'sort-desc' : '') : '') %>'>Country</th>
		</tr>
		</thead>
		<% var x=0; _.each(data.users, function(user){ %>
			<tr class="convene-usercontainer <%= (x % 2) ? '' : 'alt' %>" userid="<%= user.id %>">
				<td class="convene-active"><input type='checkbox' class='convene-active_checkbox' <%= (user.active==1) ? 'checked="checked"' : '' %> userid='<%= user.id %>' /></td>
				<td class="convene-moderated"><input type='checkbox' class='convene-moderated_checkbox' <%= (user.moderated==1) ? 'checked="checked"' : '' %> userid='<%= user.id %>' /></td>
				<td class="convene-name" id="convene-user-<%= user.id %>"><%= user.sname %>, <%= user.fname %></td>
				<td class="convene-email"><%= user.email %></td>
				<td class="convene-cel"><%= user.cel %></td>
				<td class="convene-tel"><%= user.tel %></td>
				<td class="convene-date_created"><%= user.date_created %></td>
				<td class="convene-date_edited"><%= user.date_edited %></td>
				<td class="convene-date_login"><%= user.date_login %></td>
				<td class="convene-designation"><%= user.designation %></td>
				<td class="convene-company"><%= user.company %></td>
				<td class="convene-city"><%= user.city %></td>
				<td class="convene-country"><%= user.country %></td>
				
			</tr>
		<% x++ }); %>
	</table>
</script>

<div id="convene" class="container">
<div class="convene-search-container">
	<input type="text" id="convene-user-filter" value="" />
	<input type="button" id="convene-user-search" name="submit" value="Search users" /><br />
	<input type="button" id="convene-search-clear" name="clear" value="Clear" />
</div>
<div id="convene-pagination" class="pagination"></div>
<div id="convene-users">
	
</div>
</div>
<!DOCTYPE html>
<meta charset="UTF-8">
<script src="<?= base_url() ?>resources/js/underscore-min.js"></script>
<script src="<?= base_url() ?>resources/js/jquery-1.7.2.min.js"></script>
<script>
	$(function() {
		$.getJSON("<?= base_url() ?>user/check_login/<?= $this->uri->segment(3) ?>", function(data) {
			user=data;
			$("#convene_interface").append(_.template($((user.logged_in) ? "#comments-loggedin-template" : "#comments-login-template").html()));
		});
	});
</script>

<script type="text/template" id="comments-loggedin-template">
	<div id="comment_subscribe"><%= (! subscribed) ? "Subscribe to" : "Unsubscribe from" %> this articles comments | <a id='logout' href="#">Log out</a></div>
	<div id="newcomment">
		<div class="message" id="comment_message"></div>
		<br />
		<form method="POST" id="commentform">
			<div class="uppercase">Add your comment</div>
			<input type="hidden" name="parent_id" id="parent_id" value="0" />
			<input type="hidden" name="nextlevel" id="nextlevel" value="0" />
			<textarea name="comment" id="newcommentbox"></textarea><br />
			<input type="button" name="submit" value="Comment" id="submit_comment" />
			<img id="comment_spinner" class="spinner" src="/resources/images/ajax-loader.gif" />
		</form>
	</div>
</script>

<script type="text/template" id="comments-login-template">
	<div id="login_dialog">
		<div id='comment-login' class=''>You must be logged in to leave a comment.</div>
		<div class="message" id="login_message"></div>
		<div class="form">
			<label class="uppercase">Email</label>
			<input type="text" name="email" id="txt_email" value="" />
			<label class="uppercase">Password</label>
			<input type="password" name="password" id="txt_password" value="" />
			<input type="button" id="btn_login" value="Log in" />
			<img class="spinner" id="spinner" src="/resources/images/ajax-loader.gif" />
		</div>
		<div class="signup ">
			<p>Don''t have an account? <span class="uppercase strong"><?= anchor("user/register","Sign up") ?></span> Did you forget your password? <span class="uppercase strong"><?= anchor("user/retrieve_password", "Retrieve password") ?></span></p>
		</div>
	</div>
</script>

<link rel="stylesheet" href="/resources/less/dailymaverick.css" type="text/css" media="screen, projection" charset="utf-8" />
<div id="convene_interface"></div>
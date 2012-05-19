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

<script>
	$(function() {
		
		
		$.getJSON("<?= base_url() ?>comment/get/<?= $this->uri->segment(3) ?>", function(data) {
			_.each(data.comments, function(comment) {
				$("#comments").append(_.template($("#comment-template").html(), comment));
			});
		});
		
		
		$("#comments").delegate("#comment_subscribe", "click", function() {
			$.getJSON("<?= base_url() ?>comment/ajax_subscribe/<?= $article->id ?>", function(data) {
				if (data.success) {
					if (data.message=="subscribed") {
						$("#comment_subscribe").html("Unsubscribe from this article's comments");
					} else {
						$("#comment_subscribe").html("Subscribe to this article's comments");
					}
				} else {
					$("#comment_message").html("Oh dear, something went wrong subscribing you to this article's comments.").slideDown();
				}
			});
		});
		
		$(document).on("click", "#btn_login", function() {
			var email=$("#txt_email").val();
			var password=$("#txt_password").val();
			if (email=="") {
				$("#login_message").html("Please enter your email").slideDown();
				return;
			}
			if (password=="") {
				$("#login_message").html("Please enter your password").slideDown();
				return;
			}
			$("#spinner").show();
			$.post("<?= base_url() ?>user/login/ajax_login", {email: email, password: password}, 
				function(data) {
					console.log(data);
					$("#spinner").hide();
						if (data.success) {
							$("#login_dialog").slideUp();
							$("#newcomment-anchor").append(_.template($("#comments-loggedin-template").html(), data.user));
							$(".commentreply-span").each(function() {
								$(this).removeClass("hidden");
							});
						} else {
							$("#login_message").html("<strong>Unable to log in</strong><br /> "+data.message).slideDown();
							return;
						}
					},
					"json");
				});
		
		$(document).on("click", ".commentreply", function() {
			$("#newcomment").clone().insertAfter($(this).parents(".commentcontainer"));
			$("#newcomment").hide();
			$("#newcomment").remove();
			$("#parent_id").val($(this).attr("commentid"));
			$("#nextlevel").val($(this).attr("nextlevel"));
			$("#commentform").slideDown();
		});
		
		$("#comments").delegate("#submit_comment", "click", function() {
			var comment=$("#newcommentbox").val();
			var article_id=<?= $article->id ?>;
			var parent_id=$("#parent_id").val();
			var level=$("#nextlevel").val();
			if (comment=="") {
				$("#comment_message").html("Please enter a comment").slideDown();
				return false;
			}
			$("#comment_spinner").show();
			$.post("/comment/ajax_submit", { comment: comment, article_id: article_id, parent_id: parent_id }, function(data) {
				if (data.success) {
					$("#comment_message").html("<strong>Comment submitted successfully</strong>").slideDown();
				} else {
					$("#comment_message").html("<strong>Error submitting comment</strong><br /> "+data.message).slideDown();
					$("#comment_spinner").hide();
					return true;
				}
				$("#comment_spinner").hide();
				$("#commentform").slideUp();
				$("#commentform").after('<div class="commentcontainer commentlevel-'+level+'"><div class="comment">'+comment+'</div><div class="comment_footer">Me, a few seconds ago</div></div>')
			}, "json");
		});
	});
</script>
<script type="text/template" id="comment-template">
	<a name="comment-<%= id %>"></a>
	<div class="commentcontainer commentlevel-<%= level %>">
		<div class="comment">
			<%= comment %>
		</div>
		<div class="comment_footer">
			<%= fname + " " + sname %> on <%= commentdate %> at <%= commenttime %>
			<span class='hidden commentreply-span'> | <a class='commentreply' commentid='<%= id %>' id='commentreply-<%= id %>' href='#comment-<%= id %>' nextlevel='<%= (level+1) %>' >Reply</a></span>
		</div>
	</div>
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
<a name="comments"></a>
<div id="comments">
	<div class="title">Comments</div>
	<div id="convene-interface"></div>
	<div id="newcomment-anchor"></div>
</div>
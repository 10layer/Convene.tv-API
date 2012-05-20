<script src="<?= base_url() ?>resources/js/underscore-min.js"></script>
<script>
	$(function() {
		$.getJSON("<?= base_url() ?>user/check_login/<?= $public_key ?>?jsoncallback=?", function(data) {
			var user=data;
			$.getJSON("<?= base_url() ?>comment/get/<?= $urlid ?>/<?= $public_key ?>?jsoncallback=?", function(data) {
				if (user.logged_in) {
					user.subscribed=data.commentalert;
					$("#comments-actions").html(_.template($("#comments-loggedin-template").html(), user));
				} else {
					$("#comments-actions").html(_.template($("#comments-login-template").html()));
				}
				_.each(data.comments, function(comment) {
					$("#comments-comments").append(_.template($("#comment-template").html(), comment));
				});
				if (user.logged_in) {
					$(".commentreply-span").each(function() {
						$(this).removeClass("hidden");
					});
				}
			});
		});
		
		$("#comments").delegate("#comment_subscribe", "click", function() {
			$.getJSON("<?= base_url() ?>comment/ajax_subscribe/<?= $urlid ?>/<?= $public_key ?>?jsoncallback=?", function(data) {
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
				$("#comments-message").html("Please enter your email").slideDown();
				return;
			}
			if (password=="") {
				$("#comments-message").html("Please enter your password").slideDown();
				return;
			}
			$("#spinner").show();
			$.post("<?= base_url() ?>user/login/ajax_login/<?= $public_key ?>?jsoncallback=?", {email: email, password: password}, 
				function(data) {
					$("#spinner").hide();
					if (data.success) {
						data.user.subscribed=false;
						$("#comments-actions").html(_.template($("#comments-loggedin-template").html(), data.user));
						$(".commentreply-span").each(function() {
							$(this).removeClass("hidden");
						});
						$.getJSON("<?= base_url() ?>comment/ajax_check_subscribe/<?= $urlid ?>/<?= $public_key ?>?jsoncallback=?", function(data) {
							if (data.success) {
								if (data.message=="subscribed") {
									$("#comment_subscribe").html("Unsubscribe from this article's comments");
								} else {
									$("#comment_subscribe").html("Subscribe to this article's comments");
								}
							}
						});
					} else {
						$("#comments-message").html("<strong>Unable to log in</strong><br /> "+data.message).slideDown();
						return;
					}
				},
			"jsonp");
		});
		
		$(document).on("click", ".commentreply", function() {
			$("#newcomment").clone().insertAfter($(this).parents(".commentcontainer"));
			$("#newcomment").hide();
			$("#newcomment").remove();
			$("#parent_id").val($(this).attr("commentid"));
			$("#nextlevel").val($(this).attr("nextlevel"));
			$("#commentform").slideDown();
		});
		
		$(document).on("click", "#logout", function() {
			$("#comment_spinner").show();
			$.getJSON("<?= base_url() ?>user/logout/<?= $public_key ?>?jsoncallback=?", function(data) {
				$("#comments-message").html(data.message).slideDown("slow").delay(3000).slideUp("slow");
				$("#comment_spinner").hide();
				$("#comments-actions").html(_.template($("#comments-login-template").html()));
				$(".commentreply-span").each(function() {
					$(this).addClass("hidden");
				});
			});
		});
		
		var retrievePassword=false;
		$(document).on("click", "#retrieve-password", function() {
			if (! retrievePassword) $("#comments-actions").append(_.template($("#comments-retrieve-password-template").html()));
			retrievePassword=true;
		});
		
		$(document).on("click", "#btn_retrieve_password_submit", function() {
			var email=$("#txt_retrieve_password_email").val();
			if (email=="") {
				$("#comments-message").html("Please enter an email address").slideDown().delay(3000).slideUp();
				return false;
			}
			$("#retrieve-password-spinner").show();
			$.post("<?= base_url() ?>user/retrieve_password/<?= $public_key ?>?jsoncallback=?", { email: email }, function(data) {
				retrievePassword=false;
				if (data.success) {
					$("#comments-message").html("Your password has been sent to your email address.").slideDown();
				} else {
					$("#comments-message").html("We're experiencing a problem at the moment. Please try again later.").slideDown().delay(5000).slideUp();
					$("#retrieve-password-spinner").hide();
					return;
				}
				$("#retrieve-password-spinner").hide();
				$("#comments-actions").html(_.template($("#comments-login-template").html()));
			}, "jsonp");
		});
		
		$("#comments").delegate("#submit_comment", "click", function() {
			var comment=$("#newcommentbox").val();
			var article_id="<?= $urlid ?>";
			var parent_id=$("#parent_id").val();
			var level=$("#nextlevel").val();
			if (comment=="") {
				$("#comment_message").html("Please enter a comment").slideDown();
				return false;
			}
			$("#comment_spinner").show();
			$.post("<?= base_url() ?>comment/ajax_submit/<?= $public_key ?>?jsoncallback=?", { comment: comment, article_id: article_id, parent_id: parent_id }, function(data) {
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
			}, "jsonp");
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
	<div id="comments_actions"><span id="comment_subscribe"><%= (! subscribed) ? "Subscribe to" : "Unsubscribe from" %> this article&#8217;s comments</span> | <span id='logout'>Log out</span></div>
	<div id="newcomment">
		<div class="message" id="comment_message"></div>
		<br />
		<form method="POST" id="commentform">
			<div class="uppercase">Add your comment</div>
			<input type="hidden" name="parent_id" id="parent_id" value="0" />
			<input type="hidden" name="nextlevel" id="nextlevel" value="0" />
			<textarea name="comment" id="newcommentbox"></textarea><br />
			<input type="button" name="submit" value="Comment" id="submit_comment" />
			<img id="comment_spinner" class="spinner" src="<?= base_url() ?>resources/images/ajax-loader.gif" />
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
			<img class="spinner" id="spinner" src="<?= base_url() ?>resources/images/ajax-loader.gif" />
		</div>
		<div class="signup ">
			<p>Don&#8217;t have an account? <span class="uppercase strong"><a href="/user/register">Sign up</a></span> Did you forget your password? <span class="uppercase strong" id="retrieve-password">Retrieve password</span></p>
		</div>
	</div>
</script>

<script type="text/template" id="comments-retrieve-password-template">
	<div id="retrieve-password_dialog">
		<p>Please enter your email address, and we will mail your Daily Maverick password to you.</p>
		<label class="uppercase">Email</label>
		<input type="text" name="email" id="txt_retrieve_password_email" value="" />
		<input type="button" id="btn_retrieve_password_submit" value="Submit" />
		<img class="spinner" id="retrieve-password-spinner" src="<?= base_url() ?>resources/images/ajax-loader.gif" />
	</div>
</script>
<?php
	$userid=$this->session->userdata("user_id");
?>
<a name="comments"></a>
<div id="comments">
	<div class="title">Comments</div>
	<div class="message" id="comments-message"></div>
	<div id="comments-actions"></div>
	<div id="comments-newcomment"></div>
	<div id="comments-comments"></div>
</div>
<script src="<?= base_url() ?>resources/js/underscore-min.js"></script>
<script src="<?= base_url() ?>resources/js/jquery-1.7.2.min.js"></script>
<script>
	$(function() {
		$(document).on("click", "#convene-submit", function() {
			var email=$("#convene-email").val();
			var fname=$("#convene-fname").val();
			var sname=$("#convene-sname").val();
			var password=$("#convene-password").val();
			var cel=$("#convene-cel").val();
			var tel=$("#convene-tel").val();
			var designation=$("#convene-designation").val();
			var company=$("#convene-company").val();
			var city=$("#convene-city").val();
			var country=$("#convene-country").val();
			if (email=="" || fname=="" || sname=="" || password=="") {
				$("#convene-message").html("Please complete all the required fields").slideDown();
				return;
			}
			$("#convene-user-subscribe-spinner").show();
			$.post("<?= base_url() ?>user/register?jsoncallback=?", { email: email, fname: fname, sname: sname, password: password, cel: cel, tel: tel, designation: designation, company: company, city: city, country: country }, function(data) {
				retrievePassword=false;
				if (data.success) {
					$("#convene-message").html("You have been subscribed.").slideDown();
				} else {
					$("#convene-message").html("<b>There's been a problem subscribing</b><br />"+data.message+"<br />If you need assistance, mail us on <a href='mailto:registrations@dailymaverick.co.za'>registrations@dailymaverick.co.za</a>").slideDown();
					$("#convene-user-subscribe-spinner").hide();
					return;
				}
				$("#convene-user-subscribe-spinner").hide();
				location.href="http://dailymaverick.co.za/user/register/pending";
			}, "jsonp");
		});
	});
</script>

<script type="text/template" id="convene-user-subscribe-template">
	<h1>Thanks for registering with Daily Maverick</h1>
			An email has been dispatched with details on how to confirm your registration. If you don&#8217;t get it in an hour or so, please check your spam folder. If it&#8217;s still not found, <a href="mailto:registrations@dailymaverick.co.za">email us</a> and we&#8217;ll sort it out.
</script>
<div id="convene-message" class="message"></div>
<form id="convene-user-subscribe-form">
	<h2>Have to haves</h2>
	<label>Email</label>
	<input type="text" id="convene-email" name="email" value="" /><br />
	<label>First Name</label>
	<input type="text" id="convene-fname" name="fname" value="" /><br />
	<label>Surname</label>
	<input type="text" id="convene-sname" name="sname" value="" /><br />
	<label>Password</label>
	<input type="password" id="convene-password" name="password" value="" /><br />

	<h2>Nice to haves</h2>
	<label>Cel number</label>
	<input type="text" id="convene-cel" name="cel" value="" /><br />
	<label>Tel number</label>
	<input type="text" id="convene-tel" name="tel" value="" /><br />
	<label>Designation</label>
	<input type="text" id="convene-designation" name="designation" value="" /><br />
	<label>Company</label>
	<input type="text" id="convene-company" name="company" value="" /><br />
	<label>City</label>
	<input type="text" id="convene-city" name="city" value="" /><br />
	<label>Country</label>
	<input type="text" id="convene-country" name="country" value="" /><br />
	<input type="button" id="convene-submit" name="submit" value="Submit" class="button submit" />
	<img class="spinner" id="convene-user-subscribe-spinner" src="<?= base_url() ?>resources/images/ajax-loader.gif" />
</form>
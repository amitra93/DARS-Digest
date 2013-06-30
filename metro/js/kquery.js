$(document).ready(function() {
	var advcom_counter = 0;
	var reqcla_counter = 0;
	var eceele_counter = 0;
	var tecele_counter = 0;

	$("#advcom_choose_from").click(function() {
		advcom_counter++;
		$("#advcom_choose_from_opts").toggle(350);
		if(advcom_counter%2 === 1)
			$("#advcom_choose_from").text("Hide...");
		else
			$("#advcom_choose_from").text("Show...");
	});

	$("#eceele_choose_from").click(function() {
		eceele_counter++;
		$("#eceele_choose_from_opts").toggle(350);
		if(eceele_counter%2 === 1)
			$("#eceele_choose_from").text("Hide...");
		else
			$("#eceele_choose_from").text("Show...");
	});

	$("#reqcla_choose_from").click(function() {
		reqcla_counter++;
		$(".reqcla").toggle(350);
		if(reqcla_counter%2 === 1)
			$("#reqcla_choose_from").text("Hide...");
		else
			$("#reqcla_choose_from").text("Show...");
	});

	$("#tecele_choose_from").click(function() {
		tecele_counter++;
		$(".tecele").toggle(350);
		if(tecele_counter%2 === 1)
			$("#tecele_choose_from").text("Hide...");
		else
			$("#tecele_choose_from").text("Show...");
	});

	$("#rec_reqcla_link").click(function() {
		$("#rec_reqcla").toggle(350);
	});

	$("#rec_tectra_link").click(function() {
		$("#rec_tectra").toggle(350);
	});

	$("#rec_tecele_link").click(function() {
		$("#rec_tecele").toggle(350);
	});

	$("#rec_ececse_link").click(function() {
		$("#rec_ececse").toggle(350);
	});
});

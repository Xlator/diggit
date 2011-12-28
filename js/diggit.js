$(document).ready (function () {
	
	$("div.arrowup, div.arrowdown, div.arrowupmod, div.arrowdownmod").each(function() { // Voting
		var type = $(this).parent().parent()[0].id;
		var pointsid = $(this)[0].id;
		$(this).click(function() {
			if($(this).hasClass("upvote")) { // Remove upvote
				window.vote=0;
				$(this).removeClass("upvote");
			}
			else if($(this).hasClass("downvote")) { // Remove downvote
				window.vote=0;
				$(this).removeClass("downvote");
			}
			else if($(this).is(".arrowup")) { // Vote up, removing any downvote
				window.vote=1;
				$(this).toggleClass("upvote");
				
				if($(this).next().hasClass("downvote")) {
					$(this).next().removeClass("downvote");
				}
			}
			else if($(this).is(".arrowdown")) { // Vote down, removing any upvote
				window.vote=-1;
				$(this).toggleClass("downvote");
				
				if($(this).prev().hasClass("upvote")) {
					$(this).prev().removeClass("upvote");
				}
			}
			 
			// Send vote and update vote count live
			$.post("ajax/vote.php", { 'vote[]' : [$(this)[0].id, window.vote, type] }, 
					function(data) { $("body").find("span.points#"+type+pointsid).html( data ); },"html");
	
		});
	
	});

	$("a.reply").click(function() { // Slide down reply box
		var formid=$(this)[0].id;	
		$(this).toggleClass("open");
		$("textarea:not(:first)").val("comment..."); // Reset all textareas except the first one
		$("input[type=submit]:not(:first)").attr("disabled","disabled"); // Disable submit buttons (to avoid accidental submission)
		$("textarea:not(:first)").removeClass("active");
		$("input[type=submit]").val("Comment");
		if($(this).hasClass("open")) {
			$("div.commentform:not(:first)").hide(); // Hide all comment boxes except the first one
			$("a.reply, a.edit").not(this).removeClass("open"); // Close any other open boxes before opening this one
			$("a.reply").not(this).text("reply"); // Reset other reply links
			$("a.edit").text("edit"); // Reset all edit links
			$(this).next("a.edit").text(""); // Remove the edit link for the current comment
			$(this).text("cancel"); // Change "reply" to "cancel"
			$(this).parent().nextAll("div").slideDown("slow");
		}
		else { // If the box is already open, hide it
			$(this).text("reply"); // Reset the reply link
			$(this).next("a.edit").text("edit"); // Reset the edit link
			$(this).parent().nextAll("div").hide();
		}

		
				return false;
	});

	$("a.edit").click(function() {
		$(this).toggleClass("open");
		var commentid=$(this)[0].id;
		$("textarea:not(:first)").val("comment...");
		$("textarea:not(:first)").removeClass("active");
		$("input[type=submit]:not(:first)").attr("disabled","disabled");
		$("input[type=submit]").val("Comment");
		if($(this).hasClass("open")) {
			$("div.commentform:not(:first)").hide();
			$("a.reply, a.edit").not(this).removeClass("open");
			$("a.edit").not(this).text("edit");
			$("a.reply").text("reply");
			$(this).prev("a.reply").text("");
			$(this).text("cancel");
			//$.get("rawcomment.php", { id: commentid }, function(data) { alert(data)});
			$.get("ajax/rawcomment.php", { id: commentid }, function(data) { $("body").find("textarea#"+commentid).val(data); });
			$("body").find("textarea#"+commentid).toggleClass("active");
			$(this).parent().nextAll("div").slideDown("slow");
			$("body").find("form#"+commentid+" > input[name=edit]").val("1");
			$("body").find("form#"+commentid+" > input[type=submit]").val("Edit");
		}
		else {
			$(this).text("edit");
			$(this).prev("a.reply").text("reply");
			$(this).parent().nextAll("div").hide();
		}
		return false;
	});

	$("textarea").click(function() { // Enable comment form when clicked	
		if(!($(this).hasClass("active"))) {
			$(this).toggleClass("active");
			$(this).val("");
		}
		
		$(this).nextAll("input[type=submit]").removeAttr("disabled"); // Enable the submit button
	});

	$("a.delete").click(function() {
		if($(this).hasClass("yes")) { 
			var commentid=$(this)[0].id;
			$.get("ajax/delete.php", { id: commentid, type: "comment" });
			$(this).parent().parent().parent().siblings("div").toggleClass("hide"); 
			$(this).parent().parent().parent().parent().fadeTo(0,"0.33");
		       	$(this).parent().hide();
			$(this).parent().parent().html("<em>deleted</em><br /><br />");
			$(this).parent().siblings().hide(); 
		}
		else if($(this).hasClass("no")) { 
			$(this).parent().prev("a.delete").text("delete");
			$(this).parent().hide(); 
		}
		else {
			$(this).text("delete?");
			$(this).next("span").show();
		}
	return false;
	});

	$("a.linkedit").click(function() {
		$("div.linkedit").hide();
		$("a.linkedit").text("edit");
		$(this).toggleClass("open");
		if($(this).hasClass("open")) {
			$(this).parent().parent().siblings("div.linkedit").slideDown("slow");
			$(this).parent().parent().parent().parent().fadeTo(0,"1");
			$(this).parent().parent().parent().parent().siblings("li#link").fadeTo(0,"0.33");
			$(this).text("cancel");
		}
		else {
			$(this).parent().parent().parent().parent().siblings("li#link").fadeTo(0,"1");
		}
	});

	$("a.linkdel").click(function() {
		if($(this).hasClass("yes")) {
			var linkid=$(this)[0].id;
			$.get("ajax/delete.php", { id: linkid, type: "link" });
			$(this).parent().parent().parent().parent().parent().hide();
		}
		else if($(this).hasClass("no")) {
			$(this).parent().siblings("a.linkdel").text("delete");
			$(this).parent().hide();
		}
		else {
			$(this).text("< delete?");
			$(this).next("span").show();
		}
	return false;
	});
	
	$("a.nsfw").click(function() {
		var linkid=$(this)[0].id;
		$.get("ajax/nsfw.php", { id: linkid });
		$(this).toggleClass("on");
		if(!($(this).hasClass("on"))) {
			$(this).text("nsfw?");
			$(this).parent().siblings("span").children("strong.nsfw").hide();
		}

		if($(this).hasClass("on")) {
			$(this).text("sfw?");
			$(this).parent().siblings("span").children("strong.nsfw").show();
		}
	return false;
	});
});

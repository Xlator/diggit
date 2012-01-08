$(document).ready (function () {
	window.prefix='/diggit/';
	$("div.arrowup, div.arrowdown").each(function() { // Voting
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
			$.post(window.prefix+"ajax/vote.php", { 'vote[]' : [$(this)[0].id, window.vote, type] }, 
					function(data) { $("body").find("span.points#"+type+pointsid).html( data ); },"html");
	
		});
	
	});

	$("a.reply").click(function() { // Slide down reply box
		var formid=$(this)[0].id;	
		$(this).toggleClass("open");
		$("textarea:not(:first)").val("comment...").removeClass("active"); // Reset all textareas except the first one
		$("div.formatting:not(:first)").hide();				   //
		$("a.formathelp:not(:first)").text("formatting help");             //
		$("input[type=submit]:not(:first)").attr("disabled","disabled"); // Disable submit buttons (to avoid accidental submission)
		$("input[type=submit]").val("Comment");
		
		if($(this).hasClass("open")) {
			$("div.commentform:not(:first)").hide(); 		// Hide all comment boxes except the first one
			$("a.reply, a.edit").not(this).removeClass("open"); 	//
			$("a.reply").not(this).text("reply"); // Reset other reply links
			$("a.edit").text("edit"); // Reset all edit links
			$(this).next("a.edit").hide(); // Hide the edit link for the current comment
			$(this).text("cancel"); // Change "reply" to "cancel"
			$(this).parent().nextAll("div").slideDown("slow");
		}
		
		else { // If the box is already open, hide it
			$(this).text("reply"); // Reset the reply link
			$(this).next("a.edit").show(); // Reset the edit link
			$(this).parent().nextAll("div").hide();
			$("div.formatting:not(:first)").hide();	
		}
		
		return false;
	});

	$("a.edit").click(function() { // Edit a comment
		$(this).toggleClass("open");
		var commentid=$(this)[0].id;
		$("textarea:not(:first)").val("comment...").removeClass("active"); // Reset all textareas except the first one
		$("div.formatting:not(:first)").hide();				   //
		$("a.formathelp:not(:first)").text("formatting help");             //
		$("input[type=submit]:not(:first)").attr("disabled","disabled");   //
		$("input[type=submit]").val("Comment");                            //
		
		if($(this).hasClass("open")) {
			$("div.commentform:not(:first)").hide();		// Hide all comment boxes except the first one
			$("a.reply, a.edit").not(this).removeClass("open");	// 
			$("a.edit").not(this).text("edit"); // Reset other edit links
			$("a.reply").text("reply"); // Reset all reply links
			$(this).prev("a.reply").hide(); // Hide the reply link for the current comment
			$(this).text("cancel"); // Change "edit" to "cancel"
			
			/* Get the unformatted comment text from the database via AJAX, and insert it into the textarea for editing */
			$.get(window.prefix+"ajax/rawcomment.php", { id: commentid }, function(data) { $("body").find("textarea#"+commentid).val(data); });
			$("body").find("textarea#"+commentid).toggleClass("active"); // Activate our textarea
			$(this).parent().nextAll("div").slideDown("slow"); // Show the form div
			$("body").find("form#"+commentid+" > input[name=edit]").val("1"); // Set the edit field
			$("body").find("form#"+commentid+" > input[type=submit]").val("Edit"); // Set the submit button's text to "Edit"
		}
		
		else { // If the box is already open, hide it
			$(this).text("edit"); // Reset the edit link
			$(this).prev("a.reply").show(); // Reset the reply link
			$(this).parent().nextAll("div").hide();
			$("div.formatting:not(:first)").hide();
			$("a.formathelp:not(:first)").text("formatting help");
		}
		
		return false;
	});

	$("textarea").click(function() { // Enable comment form when clicked	
		if(!($(this).hasClass("active"))) {
			$(this).toggleClass("active");
			$(this).val(""); // Clear the textarea
		}
		
		$(this).nextAll("input[type=submit]").removeAttr("disabled"); // Enable the submit button
	});

	$("a.delete").click(function() { // Delete a comment
		if($(this).hasClass("yes")) { // If deletion is confirmed by the user...
			var commentid=$(this)[0].id;
			$.get(window.prefix+"ajax/delete.php", { id: commentid, type: "comment" }); // Send the AJAX request to "delete" the comment from the database (changes field "deleted" to 1)
			$(this).parent().parent().parent().siblings("div").toggleClass("hide"); // Hide the voting arrows
			$(this).parent().parent().parent().parent().fadeTo(0,"0.33"); // Fade the comment
		       	$(this).parent().hide(); // Hide the reply/edit/delete links
			$(this).parent().parent().html("<em>deleted</em><br /><br />"); // Replace the text with "deleted"
			$("div.commentform:not(:first)").hide(); // Hide the comment form
		}
		else if($(this).hasClass("no")) { // If the user cancels the deletion... 
			$(this).parent().prev("a.delete").text("delete"); // Reset the link text
			$(this).parent().hide(); // Hide the confirmation links
		}
		else { // On clicking the delete link ...
			$(this).text("delete?"); // Change the link text
			$(this).next("span").show(); // Show confirmation links (yes/no)
		}
	return false;
	});

	$("a.linkedit").click(function() { // Edit a link
		$("div.linkedit").hide(); // Hide any open link edit forms
		$("a.linkedit").text("edit"); // Reset link text of all edit links
		$(this).toggleClass("open");  // Open/close the edit form
		if($(this).hasClass("open")) {
			$(this).parent().parent().siblings("div.linkedit").slideDown("slow"); // Show the form
			$(this).parent().parent().parent().parent().fadeTo(0,"1"); // Make the link we are editing full opacity
			$(this).parent().parent().parent().parent().siblings("li#link").fadeTo(0,"0.33"); // Make other links one third opacity
			$(this).text("cancel"); // Change link text to cancel
		}
		else { // Cancel the edit
			$(this).parent().parent().parent().parent().siblings("li#link").fadeTo(0,"1"); // Make all the links full opacity
		}
		return false;
	});

	$("a.linkdel").click(function() { // Delete a link
		if($(this).hasClass("yes")) { // If the user confirms the deletion
			var linkid=$(this)[0].id;
			$.get(window.prefix+"ajax/delete.php", { id: linkid, type: "link" }); // Send the AJAX request to delete the link from the database
			if(window.location.href.indexOf("comments.php") != -1) { window.location = "./"; } // Return to index if we're on the comments page
			else { $(this).parent().parent().parent().parent().parent().hide() } // Hide the deleted link
		}
		else if($(this).hasClass("no")) { // If the user cancels the deletion
			$(this).parent().siblings("a.linkdel").text("delete"); // Reset link text
			$(this).parent().hide(); // Hide confirmation links
		}
		else { // On clicking delete ...
			$(this).text("< delete?"); // Change link text
			$(this).next("span").show(); // Show confirmation links (yes/no)
		}
	return false;
	});
	
	$("a.nsfw").click(function() { // Toggle nsfw status of a link
		var linkid=$(this)[0].id;
		$.get(window.prefix+"ajax/nsfw.php", { id: linkid }); // Send the request
		$(this).toggleClass("on"); // Toggle the nsfw class
		if(!($(this).hasClass("on"))) { // If the link is currently sfw
			$(this).text("nsfw?"); // Change link text to "nsfw?"
			$(this).parent().siblings("span").children("strong.nsfw").hide(); // Hide the "NSFW" marker
		}

		if($(this).hasClass("on")) { // If the link is currently nsfw
			$(this).text("sfw?"); // Change the link to "sfw?"
			$(this).parent().siblings("span").children("strong.nsfw").show(); // Show the "NSFW" marker
		}
	return false;
	});

	$("h3 > a").click(function() { // Insert the id of a clicked link into the "lastvisited" field in the user table
		var linkid=$(this)[0].id;
		var linkurl=$(this).attr('href');
		$.get(window.prefix+"ajax/lastvisited.php", { id: linkid }, window.location = linkurl); 
		return false;
	});
	
	$("a.catselect").click(function() { // When submitting/editing a link, insert the name of a clicked category into the category input
		var cat=$(this).text();
		$(this).parent().prevAll("input#catbox").val(cat);
		return false;
	});

	$("a.formathelp").click(function() { // Show comment formatting help
		//$("body").find("div.formatting").toggle();
		$(this).parent().nextAll("div.formatting").toggle();
		if($(this).parent().nextAll("div.formatting").is(":visible")) { $(this).text("hide help"); }
		else { $(this).text("formatting help"); }	
		return false; 
	});
	
	$("label[for=nsfw]").click(function() {
		if($(this).prev("input").is(":checked")) { $(this).prev("input").removeAttr("checked"); }
		else { $(this).prev("input").attr("checked","checked"); }
	});

});

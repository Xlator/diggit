$(document).ready (function () {
	
	$("div.arrowup, div.arrowdown, div.arrowupmod, div.arrowdownmod").each(function() {
		var type = $(this).parent().parent()[0].id;
		var pointsid = $(this)[0].id;
		$(this).click(function() {
			if($(this).hasClass("upvote")) {
				window.vote=0;
				$(this).removeClass("upvote");
			}
			else if($(this).hasClass("downvote")) {
				window.vote=0;
				$(this).removeClass("downvote");
			}
			else if($(this).is(".arrowup")) {
				window.vote=1;
				$(this).toggleClass("upvote");
				
				if($(this).next().hasClass("downvote")) {
					$(this).next().removeClass("downvote");
				}
			}
			else if($(this).is(".arrowdown")) {
				window.vote=-1;
				$(this).toggleClass("downvote");
				
				if($(this).prev().hasClass("upvote")) {
					$(this).prev().removeClass("upvote");
				}
			}
			 			
			$.post("vote.php", { 'vote[]' : [$(this)[0].id, window.vote, type] }, 
					function(data) { $("body").find("span.points#"+type+pointsid).html( data ); },"html");
	
		});
	
	});

	$("a.reply").click(function() {
		var formid=$(this)[0].id;	
		$("div.commentform:not(:first)").hide(); // Hide all comment boxes except the first one
		$(this).parent().nextAll("div").slideToggle("slow");
		return false;
	});
});

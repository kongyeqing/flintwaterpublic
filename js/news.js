google.load("feeds", "1");
google.setOnLoadCallback(onStartup);

function onStartup() {
	mLive();
    michiganRadio();
}

function mLive() {
	var feed = new google.feeds.Feed("http://blog.mlive.com/newsnow_impact/atom.xml");
	feed.setNumEntries(50);
	feed.load(function(result) {
	   if (!result.error) {
		   var html = '';
			result.feed.entries.sort(function (a,b){
			return new Date(b.publishedDate) - new Date(a.publishedDate);
			});
	   for (var i = 0; i < result.feed.entries.length; i++) {
		   var entry = result.feed.entries[i];
			if(entry.categories.indexOf("tag:flint-water") != -1) {
				placeholder = entry;
				html += '<div class="card">';
				html += '<div class="card-main">';
				html += '<div class="card-inner"><p><img class="pull-left" src="../images/Mlive.jpg" alt="MLive"></p>' + '<span>MLive</span>' + '<h5>' + date(entry.publishedDate) + '</h5></div>';
				html += '<p><a href="' + entry.link +'" target="_blank">' + entry.title + " " + '</a></p><h6>' + entry.contentSnippet + '</h6></div>';
				html += '</div>';
			}
	   }
	   $("#news").html(html);

	   }	
	});
}


function michiganRadio()  {
	var feed = new google.feeds.Feed("http://michiganradio.org/rss.xml");
	feed.setNumEntries(50);
	feed.load(function(result) {
	   if (!result.error) {
		   var html = '';
			result.feed.entries.sort(function (a,b){
			return new Date(b.publishedDate) - new Date(a.publishedDate);
			});
	   for (var i = 0; i < result.feed.entries.length; i++) {
		   var entry = result.feed.entries[i];
			if(entry.title.search(/(Flint)+(')*[\w\s\d]+(water)*/) > 0){
				placeholder = entry;
				html += '<div class="card">';
				html += '<div class="card-main">';
				html += '<div class="card-inner"><p><img class="pull-left" src="../images/michigan_radio.png" alt="Michigan radio"></p>' + '<span>Michigan Radio</span>' + '<h5>' + date(entry.publishedDate) + '</h5></div>';
				html += '<p><a href="' + entry.link +'" target="_blank">' + entry.title + " " + '</a></p><h6>' + entry.contentSnippet + '</h6></div>';
				html += '</div>';
			}
	   }

	   $("#news").append(html);
		
		}	
	})
}

function date(input) {
	return input.substring(0,16);
}
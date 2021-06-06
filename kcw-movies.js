jQuery(document).ready(function(){
    /*
        The various variables keeping track of the application
        TODO: Put these in the data object passed from the server
    */
    var perpage = 28;
    var currentvideo = null;
    var currentsrc = null;
    var currentpage = 1;
    var currentsearch = "";

    var searchSet = null;
    var lastSearch = 0;

    /*
        Functions dealing with showing the play button on thumbnails
    */
    //Show the play button on hover
    jQuery("ul.kcw-movies-list").on("mouseenter", "a.kcw-movies-thumb-wrapper",function() {
        var ph = parseInt(jQuery("div.kcw-movies-playbutton").css("height"));
        var pw = parseInt(jQuery("div.kcw-movies-playbutton").css("width"));

        var vh = parseInt(jQuery("img", this).height());
        var vw = parseInt(jQuery("img", this).width());

        var ptop = jQuery(this).position().top + (vh/2); 
        ptop -= (ph/2) + 5;
        var pleft = jQuery(this).position().left + (vw/2);
        pleft -= (pw/2);

        var src = jQuery(this).data("src");

        ShowPlayButton(ptop, pleft, src);
    });
    //Hide the play button on hover
    jQuery("ul.kcw-movies-list").on("mouseleave", "a.kcw-movies-thumb-wrapper",function() {
        HidePlayButton();
    });
    function ShowPlayButton(x, y, src){
        DoPlayButton(x, y, src, true);
    }
    function HidePlayButton() {
        var ptop = -9999;
        var pleft = -9999;
        DoPlayButton(ptop, pleft, null, false);
    }
    function DoPlayButton(x, y, src, show) {
        var color = GetPlayButtonColor(src);
        var op = (show ? 1 : 0);

        jQuery("div.kcw-movies-playbutton").css({top: x, left: y, "background-color": color});
        jQuery("div.kcw-movies-playbutton").css({opacity: op});
    }
    function GetPlayButtonColor(src) {
        if (src == "vimeo" || src == "uploads") return "rgba(0, 173, 239, .75)";
        else if (src == "youtube") return "rgba(204, 0, 0, .75)";
        else return "rgba(0, 0, 0, .75)";
    }

    /*
        Functions dealing with the embed code
    */
    //Copy the embed link on click
    jQuery("a.kcw-movies-copy-embed").on("click touchstart", function() {
        toggleEmbedCode();
    });
    /* Keep the clicking the input from closing the embed dialog */
    jQuery("a.kcw-movies-copy-embed input").on("click touchstart", function(e) {
        e.stopPropagation();
        DoEmbedCopy();
    });
    function showEmbedCode(then) {
        //Save the current width of the element
        var embedWidth = jQuery("input.kcw-movies-link").val().length;
        embedWidth *= parseInt(jQuery("input.kcw-movies-link").css("font-size")) / 1.8;

        var viewWidth = jQuery("html").outerWidth() * .7;
        if (embedWidth > viewWidth) {
            embedWidth = viewWidth;
        }
        //Set the width of the element to zero so it can be animated out
        jQuery("input.kcw-movies-link").css({width: 0});
        //Animate the element popping into view
        jQuery("input.kcw-movies-link").animate({
            opacity: 1,
            width: embedWidth,
            "margin-left": -embedWidth,
            padding: "5px 5px"
        }, 400, then());
    }
    function hideEmbedCode() {
        //Save the current width of the element
        //Animate the element popping into view
        jQuery("input.kcw-movies-link").animate({
            width: 0,
            "margin-left": 0,
            opacity: 0,
            padding: 0
        }, 400);
    }
    var wait = false;
    function toggleEmbedCode() {
        if (wait == true) return;
        if (jQuery("input.kcw-movies-link").css("opacity") == 0) {
            showEmbedCode(DoEmbedCopy);
        } else {
            hideEmbedCode();
        }
        wait = true;
        setTimeout(function(){
            wait = false;
        }, 500);
    }
    function DoEmbedCopy() {
        jQuery("input.kcw-movies-link").select();
        document.execCommand("copy");
        jQuery("input.kcw-movies-link").blur();
        DoEmbedMessage();
    }
    function DoEmbedMessage() {
        var topPx = 0, leftPx = 0;
        var buttonpos = jQuery("a.kcw-movies-copy-embed").position();
        topPx = buttonpos.top - (jQuery("div.kcw-movies-copy-embed-message").height() + 10);
        leftPx = buttonpos.left;
        jQuery("div.kcw-movies-copy-embed-message").css({top: topPx, left: leftPx})

        jQuery("div.kcw-movies-copy-embed-message").animate({opacity: 1.0}, 150, function() {
            setTimeout(function() {
                jQuery("div.kcw-movies-copy-embed-message").animate({opacity: 0}, 250, function(){
                    jQuery("div.kcw-movies-copy-embed-message").css({left: -99999})
                });
            }, 1000);
        });
    }

    jQuery("div.kcw-movies-search input").on("keydown", function (e, key){
        if (e.which == 13) DoImmediateSearch();
    });
    jQuery("div.kcw-movies-search input").on("input", function (){
        if (!isLoading) DoDelayedSearch();
    });

    var searchTimeout = null;
    var ms_between_keypress = 550;
    var ms_keypress_wait = 650;
    var ms_short_wait = 300;
    var lastSearch = 0;
    //Handle performing and immediate search
    function DoImmediateSearch() {
         //Clear the delayed search
         clearTimeout(searchTimeout);
         //Perform the search immediately
         var search = jQuery("div.kcw-movies-search input").val();
         ShowSearch(search);
         jQuery("div.kcw-movies-search input").blur();
    }
    //Handle delaying & performing search until user is 'done' typing
    function DoDelayedSearch() {
         var search = jQuery("div.kcw-movies-search input").val();
         var timeDiff = Date.now() - lastSearch;
         lastSearch = Date.now();
         var wait = 0;
 
         clearTimeout(searchTimeout);
         if (timeDiff <= ms_between_keypress) wait = ms_keypress_wait;
         else                                 wait = ms_short_wait;
         searchTimeout = setTimeout(ShowSearch, wait, search); 
    }
    //Filter search string
    function FilterSearch(search) {
        search = search.replace(' ', '+');
        search = search.replace('/', '');
        search = search.replace('\\', '');
        search = search.replace('/[^A-Za-z0-9]+/g', '');
        return search;
    }
    //Perform the search.
    function ShowSearch(search) {
        if (search.length == 0) {
            kcw_movies.search = "";
            kcw_movies.pages = [];
            ShowListPage(0);
        } else {
            //jQuery("div.kcw-gallery-list-container").css({display: "block"});
            //ShowLoadingGif(null);

            if (kcw_movies.pages == undefined || kcw_movies.pages[0] == undefined || kcw_movies.search != search) {
                kcw_movies.search = search;
                ApiCall("search", "/"+FilterSearch(search), function(data) {
                    kcw_movies.pages = [];
                    ShowListPage_callback(data, 1)
                });
            } else {
                DisplayListPage(1);
            }
        }
    }

    //Show the paging links for the current set of videos
    function DisplayPagingLinks(total, perpage, current = 0) {
        jQuery("ul.kcw-movies-pagination").empty();
        
        var totalpages = total/perpage;

        for (var i = 0;i < totalpages;i++) {
            var $link = jQuery("<a></a>");

            if (i == current) {
                $link.attr({
                    "class": "current_page"
                });
            }

            var page = i + 1;
            $link.text(page.toString());
            var $li = jQuery("<li></li>");
            $li.attr({
                "data-page": page
            });
            $li.append($link);
            jQuery("ul.kcw-movies-pagination").append($li);
        }
    }

    //Remove all non alphanumeric characters from a string to simplify it
    function removeSpecial(str) {
        return str.replace(/[^\w\s]/gi, '').trim().toLowerCase();
    }

    //Handle page changes from the user
    jQuery("ul.kcw-movies-pagination").on("click touchstart", "li a",function() {
        var page = jQuery(this).parent().data("page");
        var current = jQuery("ul.kcw-movies-pagination li a.current_page").parent().data("page");
            
        if (page != current) ShowListPage(page);

        var offset = jQuery("ul.kcw-movies-list").offset().top - 150;
        jQuery("html, body").animate({scrollTop: offset}, 400);
    });

    function ShowListPage_callback(data, page) {
        kcw_movies.pages[page-1] = data.items;
        kcw_movies.total = data.total;
        kcw_movies.per_page = data.per_page;
        DisplayListPage(page);
        currentpage = page;
        SetQueryParameters();
    }

    function ShowListPage(page) {
        //Check if the page is cached
        if (page > kcw_movies.pages.length || kcw_movies.pages[page-1] == undefined || kcw_movies.pages[page-1].length == 0) {
            if (page < 1) page = 1;
            if (kcw_movies.search.length > 0) {
                ApiCall("search", "/"+kcw_movies.search+"/"+page, function(data) { 
                    ShowListPage_callback(data, page)
                });
            } else {
                ApiCall("list", "/"+page, function(data) { 
                    ShowListPage_callback(data, page)
                });
            }
        } else {
            DisplayListPage(page);
        }

        
    }

    function DisplayListPage(page) {
        var videos = kcw_movies.pages[page-1];

        jQuery("ul.kcw-movies-list").empty();
        for (var i = 0;i < videos.length;i++) {
            var $li = jQuery("<li>" + BuildVideoThumbnail(videos[i]) + "</li>");
            jQuery("ul.kcw-movies-list").append($li);
        }
        DisplayPagingLinks(kcw_movies.total, kcw_movies.per_page, page-1);
    }

    function BuildVideoThumbnail(video) {
        var thumb = `<a class='kcw-movies-thumb-wrapper' data-src='${video.src}' data-id='${video.id}' title='${video.name}'>`;
        thumb += `<img class='kcw-movies-thumb' src='${video.thumb}' alt='${video.name}' width='320', height='180'>`;
        thumb += `<p class='kcw-movies-title'>${video.name}</p>`;
        thumb += `<div class='kcw-movies-length'><pre class='kcw-movies-length'>${video.length}</pre></div>`;
        return thumb;
    }

    /*
        Functions dealing with displaying videos
    */
    //Detect touch drag VS tap
    var isDragging = false;
    jQuery("ul.kcw-movies-list").on("touchmove", "a.kcw-movies-thumb-wrapper",function() {
        isDragging = true;
    });
    //Open the video represented by this element
    jQuery("ul.kcw-movies-list").on("click touchend", "a.kcw-movies-thumb-wrapper",function() {
        if (isDragging) { isDragging = false; return; }

        var videoid = jQuery(this).data("id");
        var offset = jQuery("div.kcw-movies-video").offset().top - 50;
        jQuery("html, body").animate({scrollTop: offset}, 400);
        if (videoid == currentvideo) return;
        var videosrc = jQuery(this).data("src");
        DoPlayButton(-9999, -9999, null, false); 
        DoVideoDisplay(videoid, videosrc);
        hideEmbedCode();
    }); 
    //Show a selected video
    function DoVideoDisplay(videoid, videosrc) {
        currentvideo = videoid;
        currentsrc = videosrc;
        //For each page
        for (var i = 0;i < kcw_movies.pages.length;i++) {
            if (kcw_movies.pages[i] == undefined) continue;
            //for each video
            for (var j = 0;j < kcw_movies.pages[i].length;j++) {
                //If it matches the id and source, display it
                if (kcw_movies.pages[i][j].id == videoid && kcw_movies.pages[i][j].src == videosrc) {
                    var video = kcw_movies.pages[i][j];
                    var embedlink = kcw_movies.links[videosrc].embed + video.id;

                    DisplayVideo(embedlink, video.name);
                    DisplayVideoDetails(video.name, video.views, video.created, embedlink);
                    SetQueryParameters();
                    HidePlayButton();
                    break;
                }
            }
        }
    }
    //Display a single videos title and embed link
    function DisplayVideoDetails(title, views, date, link) {
        jQuery("h3.kcw-movies-video-title").text(title);
        var viewText = views + " view" + (views == 1 ? '' : 's');
        jQuery("p.kcw-movies-video-views").text(viewText);
        
        if (date != undefined) {
            jQuery("p.kcw-movies-video-created, p.kcw-movies-video-separator").css({display: "inline"});
            jQuery("p.kcw-movies-video-created").text(date);
        } else {
            jQuery("p.kcw-movies-video-created, p.kcw-movies-video-separator").css({display: "none"});
        }
        
        jQuery("input.kcw-movies-link").attr("value",  link);
    }
    //Display a single videos iframe
    function DisplayVideo(embedlink, name) { 
        jQuery("div.kcw-movies-video iframe").attr('src', embedlink);
        jQuery("div.kcw-movies-video iframe").attr('title', name);
        
        if (jQuery("div.kcw-movies-video").css("display") == "none") {
            jQuery("div.kcw-movies-video").css({display: "block"});
            jQuery("div.kcw-movies-video").animate({opacity: 1}, 600);
        }
    }


    //Perform an API call to the gallery
    var api_url = kcw_movies.api_url;
    var current_request = null;
    function ApiCall(endpoint, paremeter_string, then) {
        var url = api_url + endpoint + paremeter_string;
        console.log("REQUEST: " + url);
        if (current_request != null) current_request.abort();
        current_request = jQuery.get(url, then).done(function() {
        }).fail(function() {
            FailedRequest(endpoint);
        }).always(function() {

        });
    }

    function FailedRequest(endpoint) {
        //Search API error
        if (endpoint.indexOf("search") > -1) {
            NoSearchResults();
        } 
        //List API error
        else if (endpoint.indexOf("list") > -1) {
        } 

        console.log("Request Failed");
    }

    function NoSearchResults() {
        jQuery("h3.kcw-movies-list-message").text("No results for " + jQuery("div.kcw-movies-search input").val());
        jQuery("h3.kcw-movies-list-message").css({display: "block"});
        jQuery("ul.kcw-movies-pagination").css({display: "none"});
        jQuery("ul.kcw-movies-list").css({display: "none"});
    }

    var isLoading = false;
    var loadingTimeout = null;
    //Display the loading gif on the given element
    function ShowLoadingGif(callback) {
        isLoading = true;

        var pos = {};
        
        var lw = jQuery("div.kcw-gallery-loading-wrapper").outerWidth();
        var lh = jQuery("div.kcw-gallery-loading-wrapper").outerHeight();

        pos.top = jQuery(window).height() / 2;
        pos.top -= lh/2;

        pos.left = jQuery(window).width() / 2;
        pos.left -= lw/2;

        jQuery("div.kcw-gallery-loading-wrapper").css({top: pos.top, left: pos.left});
        jQuery("div.kcw-gallery-loading-wrapper").animate({opacity: 1});

        if (callback != null) {
            loadingTimeout = setTimeout(function(){
                callback();
            }, 3000);
        }
    }
    //Hide the loading gif
    function HideLoadingGif(){
        //Stop any loading timeouts from firing
        clearTimeout(loadingTimeout);

        isLoading = false;
        jQuery("div.kcw-gallery-loading-wrapper").animate({opacity: 0}, function() {
            jQuery(this).css({top: "-999px", left: "-999px"});
            jQuery("p.kcw-gallery-loading-status").text("");
            jQuery("p.kcw-gallery-loading-status").css({display: "none", opacity: 0});
        });
    }

    //Setup the KCW movies application
    (function initKCWMovies() {
        GetQueryStringParameters();
        
        DisplayPagingLinks(kcw_movies.total, kcw_movies.per_page, currentpage - 1);
    })();

    /*
        Functions dealing with editing the URL's query string
    */
    //Load variables from the query string
    function GetQueryStringParameters() {
        currentvideo = getQueryStringParam("v");
        currentsrc = getQueryStringParam("vsrc");
        currentpage = getQueryStringParam("vpage");
        kcw_movies.search = getQueryStringParam("vsearch");

        if (currentpage == null) currentpage = 1;
        if (kcw_movies.search == null) kcw_movies.search = "";
    }
    //Set variables into the query string
    function SetQueryParameters() {
        if (currentvideo != null) updateQueryStringParam("v", currentvideo);
        else removeQueryStringParam("v");
        
        if (currentsrc != null) updateQueryStringParam("vsrc", currentsrc);
        else removeQueryStringParam("v");

        if (kcw_movies.search != null && kcw_movies.search.length > 0) updateQueryStringParam("vsearch", kcw_movies.search);
        else removeQueryStringParam("vsearch");
        
        updateQueryStringParam("vpage", currentpage);
    }
    //Stolen from: https://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript
    function getQueryStringParam(name, url = window.location.href) {
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }
    //Stolen from: https://gist.github.com/excalq/2961415
    function updateQueryStringParam(key, value) {
        var baseUrl = [location.protocol, '//', location.host, location.pathname].join(''),
            urlQueryString = document.location.search,
            newParam = key + '=' + value,
            params = '?' + newParam;
    
        // If the "search" string exists, then build params from it
        if (urlQueryString) {
            keyRegex = new RegExp('([\?&])' + key + '[^&]*');
    
            // If param exists already, update it
            if (urlQueryString.match(keyRegex) !== null) {
                params = urlQueryString.replace(keyRegex, "$1" + newParam);
            } else { // Otherwise, add it to end of query string
                params = urlQueryString + '&' + newParam;
            }
        }
        window.history.replaceState({}, "", baseUrl + params);
    };
    //Stolen from: https://stackoverflow.com/questions/1634748/how-can-i-delete-a-query-string-parameter-in-javascript
    function removeQueryStringParam(parameter) {
        var url = document.location.href;
        var urlparts = url.split('?');
    
        if (urlparts.length >= 2) {
            var urlBase = urlparts.shift();
            var queryString = urlparts.join("?");
    
            var prefix = encodeURIComponent(parameter) + '=';
            var pars = queryString.split(/[&;]/g);
            for (var i = pars.length; i-- > 0;) {
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                    pars.splice(i, 1);
                }
            }
    
            if (pars.length == 0) {
                url = urlBase;
            } else {
                url = urlBase + '?' + pars.join('&');
            }
    
            window.history.pushState('', document.title, url); // push the new url in address bar
        }
        return url;
    }
});
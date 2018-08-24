var baseurl = "https://"+window.location.hostname+"/";
$(document).ready(function(){
    $("span.imgName").on("click", "label", function() {
        if($(this).find("input").prop("checked") == true) {
            $(this).find("i").removeClass("fa fa-square-o");
            $(this).find("i").addClass("fa fa-check-square-o");
        } else {
            $(this).find("i").removeClass("fa fa-check-square-o");
            $(this).find("i").addClass("fa fa-square-o");
        }
    });

    $(".mobileMenu").on("click", function() {
        $(".menu > ul").toggle(400);
        $(".mobileMenu > i").toggleClass("fa-bars");
    });

    $(".menu").on("click", "a.downloadSelectedAlbum", function() {
        download('multiple',event);
    });

    $(".menu").on("click", "a.downloadAll", function() {
        download('all',event);
    });

    
});
/*
var myVar = "";
function downloadAlbum(albumId,e)
{
    e.preventDefault();
  
   // myVar = setInterval(gcount, 1000);
    
    $.ajax({
        dataType:'json',
        type:'post',
        data:'albumId='+albumId,
        url:baseurl+'example/downloadSingle',
        catch:false,
        success:function(response)
        {
        	alert("download call");
        	//clearInterval(myVar);
        }
    });
}
*/
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

var totalAlbums = 0;
var doneAlbum = 0;
function downloadAlbum(albumId,type,e="")
{

    document.getElementById("progress-bar-in").style.width= "0%";
    document.getElementById("progress-bar-in-album").style.width= "0%";
    document.getElementById("download-file").style.display = "block";
    document.getElementById("closeDiv").style.color = "#fff";
    $("#download-button").html("");
    $("#progress-bar-in-album-counter").html("");
    $("#progress-bar-in-album").html("");
	//e.preventDefault();
	var inresult = "";
   $.ajax({
        dataType:'json',
        type:'get',
        url:baseurl+'example/getAllImageCount/'+albumId,
        catch:false,
        success:async function(response)
        {
        	console.log(response);
        	var total = response.album.data.length;

            var totalPhotos = response.album.data.length;
            var percentage = 100/totalPhotos;

        	for(i=0;i<response.album.data.length;i++)
        	{
        	//alert(i);
        	var picno = i+1;
        	var jdata = { albumId: albumId, albumName: response.album.name,source: response.album.data[i].source, photono: picno };
			    $.ajax({
			        dataType:'json',
			        type:'post',
				data:jdata,				        
			        url:baseurl+'example/downloadSingle',
			        catch:false,
			        success:async function(result)
			        {
			        	inresult = result;
			        	if(result.status == 1)
			        	{
				        	// $("#downloader").html(( response.album.data.length-total+1 )+" of "+response.album.data.length);
                            // alert(response.album.data.length-total+1);
                            var progress = (response.album.data.length-total+1) * (100/response.album.data.length);
                            document.getElementById("progress-bar-in").style.width= progress + "%";

                             $("#progress-bar-in-counter").html(( response.album.data.length-total+1 )+" of "+response.album.data.length);                

                            
				        	//$("#downloader").html("downloading");
				        	if(total == 1)
				        	{
                                // alert(total);
                                doneAlbum++;
                                // alert(doneAlbum);
                                var progressRate = doneAlbum/totalAlbums * 100;
                                document.getElementById("progress-bar-in-album").style.width= progressRate + "%";

                                $("#progress-bar-in-album-counter").html(doneAlbum + " of "+ totalAlbums);     
				        		
                                
                                 //alert(doneAlbum+" total : "+totalAlbums);
                                // progress-bar-in-album-counter
                                if(totalAlbums <= doneAlbum)
                                {
                                    if(totalAlbums == 1 && type == "single")
                                    {       
                                        var album = {albumName:response.album.name, albumId: albumId};
                                    }
                                    else
                                    {   
                                        var album = {albumName:"", albumId:""};
                                    }
                                    $.ajax({
                                        async:false,
                                        dataType:'json',
                                        type:'post',
                                        data:album,			        
                                        url:baseurl+'example/zipping/'+type,
                                        catch:false,
                                        success:function(zipresult)
                                        {
                                            document.getElementById("closeDiv").style.color = "black";
                                            $("#download-button").append("<button onclick='download_zip(\""+zipresult.url+"\");'>Download</button>");
                                            totalAlbums = 0;
                                            doneAlbum = 0;
                                            console.log(zipresult);
                                            
                                        }
                                    });
                                    
                                }


				        	}
				        	total--;
				        	
			        	}
			        	else
			        	{
			        		alert(result.error);
			        	}
			        	await sleep(1000);
			        	
			        }
			    });    
        	}
        	await sleep(1000);
        	
        }
        
        
    });
}
function download_zip(url){    
    window.location.href =url;    
    closeDownload();
}

function download(type,e,albumId="")
{
//e.preventDefault();
   if(type == "single")
   {
       totalAlbums = 1;
       downloadAlbum(albumId,type,e);
   }
   else if(type == "multiple")
   {
   	var selected_albums =  [];
    	$(".selectedAlbums").each(function () 
    	{
        	if ($(this).is(":checked")) 
        	{
            		selected_albums.push($(this).val());
        	}
    	});
    	totalAlbums = selected_albums.length;
    	for (i = 0; i < selected_albums.length; i++) 
    	{ 
		downloadAlbum(selected_albums[i],type,e);
	}

   }
   else
   {
   	var selected_albums =  [];
   	$(".selectedAlbums").each(function () 
    	{        	
		selected_albums.push($(this).val());
    	});
    	totalAlbums = selected_albums.length;
    	for (i = 0; i < selected_albums.length; i++) 
    	{ 
	       downloadAlbum(selected_albums[i],type,e);
        }
   }
}

/*
function downloadAll()
{
    $.ajax({
        dataType:'json',
        type:'post',
        data:'albums=all',
        url:baseurl+'example/downloadall',
        catch:false,
        success:function(response)
        {
            alert(response.status);
        }
    });
}
*/

function downloadSelected()
{
    var selected_albums =  [];
    $(".selectedAlbums").each(function () {
        if ($(this).is(":checked")) {
            selected_albums.push($(this).val());
        }
    });

    $.ajax({
        dataType:'json',
        type:'post',
        data:'selected='+selected_albums+'&albums=selected',
        url:baseurl+'example/downloadall',
        catch:false,
        success:function(response)
        {
            console.log(response);
            alert(response.status);
        }
    });
}

function moveAlbum(albumId,albumName,e)
{
    e.preventDefault();
    $("#driveFiles").show();
    setTimeout(function(){
        $("#driveFiles").hide();
    }, 2000);
    $.ajax({
        dataType:'json',
        type:'post',
        data:'albumId='+albumId+'&albumName='+albumName,
        url:baseurl+'example/moveSingle',
        catch:false,
        success:function(response)
        {
            console.log(response);            
        }
    });
}


function moveAll()
{
    $("#driveFiles").show();
    setTimeout(function(){
        $("#driveFiles").hide();
    }, 2000);
    $.ajax({
        dataType:'json',
        type:'post',
        data:'albums=all',
        url:baseurl+'example/moveAll',
        catch:false,
        success:function(response)
        {
            //alert(response.message);
        }
    });
}


function moveSelected()
{
    var selected_albums =  [];
    $(".selectedAlbums").each(function () {
        if ($(this).is(":checked")) {
            selected_albums.push($(this).val());
        }
    });

    $.ajax({
        dataType:'json',
        type:'post',
        data:'selected='+selected_albums+'&albums=selected',
        url:baseurl+'example/moveSelected',
        catch:false,
        success:function(response)
        {
            // console.log(response);
            // alert(response.status);
        }
    });
}




var total = (document.getElementById("sider").childElementCount*100);
//document.getElementById("sider").style.width = (total+5)+"vw";
var marginTop = 0;
var playSide;
var playFlag = true;

function playSider(event) {

    if(playFlag) {
        event.target.classList.remove("fa-play");   
        event.target.classList.add("fa-pause");   
        playSide = setInterval(function(){ 
            marginTop += 100;        
            if (marginTop==total) {
                marginTop = 0;
            }
            side();        
        }, 2000);   
        playFlag = false; 
    } else {
        clearInterval(playSide);  
        event.target.classList.remove("fa-pause");   
        event.target.classList.add("fa-play");   
        playFlag = true;
    }
}

function nextSide() {  	
    clearInterval(playSide);  
    marginTop += 100;    
    if (marginTop==total) {
        marginTop = 0;
    }
    side();    
}

function prevSide() {
    clearInterval(playSide);
    marginTop -= 100;   
    if (marginTop < 0) {
        marginTop = total-100;
    }
    side();
}

function side(){	
    document.getElementById("sider").style.marginTop = "-"+marginTop+"vh";    
}
function closeDownload()
{
    document.getElementById("download-file").style.display = "none";   
}

var elem = document.getElementById("fullscreen");
function openFullscreen() {
  if (elem.requestFullscreen) {
    elem.requestFullscreen();
  } else if (elem.mozRequestFullScreen) { /* Firefox */
    elem.mozRequestFullScreen();
  } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
    elem.webkitRequestFullscreen();
  } else if (elem.msRequestFullscreen) { /* IE/Edge */
    elem.msRequestFullscreen();
  }
}
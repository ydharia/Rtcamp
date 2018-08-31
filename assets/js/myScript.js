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

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

var totalAlbums = 0;
var doneAlbum = 0;
var xhr = [];
function downloadAlbum(albumId,type,e="")
{
    $("#download-button").html('<button><i class="fa fa-refresh fa-spin" /></button>');
    document.getElementById("progress-bar-in").style.width= "0%";
    document.getElementById("progress-bar-in-album").style.width= "0%";
    document.getElementById("download-file").style.display = "block";
    document.getElementById("closeDiv").style.color = "#fff";
   
    $("#progress-bar-in-album-counter").html("");
    $("#progress-bar-in-counter").html("");
    $("#progress-bar-in-album").html("");
	var inresult = "";
   	$.ajax({
        dataType:'json',
        type:'get',
        url:baseurl+'myfacebook/getAllImageCount/'+albumId,
        catch:false,
        success:async function(response)
        {
        	console.log(response);
        	var total = response.album.data.length;

            var totalPhotos = response.album.data.length;
            var percentage = 100/totalPhotos;
    $("#download-button").html('<button><i class="fa fa-refresh fa-spin" /></button> <button style="background-color:#d9534f;" onclick="canceldownload()">cancel</button>');
        	for(i=0;i<response.album.data.length;i++)
        	{
        	var picno = i+1;
        	var jdata = { albumId: albumId, albumName: response.album.name,source: response.album.data[i].source, photono: picno };
        	
			  $.ajax({
			        dataType:'json',
			        type:'post',
				data:jdata,				        
			        url:baseurl+'myfacebook/downloadSingle',
			        catch:false,
				beforeSend: function (jqXHR, settings) {
				        xhr.push(jqXHR);
				},
			        success:async function(result)
			        {
			        	$("#downloadAlbumName").html('Downloading '+response.album.name+' photos');
			        	inresult = result;
			        	
			        	if(result.status == 1)
			        	{
                            var progress = (response.album.data.length-total+1) * (100/response.album.data.length);
                            document.getElementById("progress-bar-in").style.width= progress + "%";

                             $("#progress-bar-in-counter").html(( response.album.data.length-total+1 )+" of "+response.album.data.length);                

				        	if(total == 1)
				        	{
                                doneAlbum++;
                                var progressRate = doneAlbum/totalAlbums * 100;
                                document.getElementById("progress-bar-in-album").style.width= progressRate + "%";

                                $("#progress-bar-in-album-counter").html(doneAlbum + " of "+ totalAlbums);     
				        		
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
                                        url:baseurl+'myfacebook/zipping/'+type,
                                        catch:false,
				    	beforeSend: function (jqXHR, settings) {
						xhr.push(jqXHR);
					},
                                        success:function(zipresult)
                                        {
                                            document.getElementById("closeDiv").style.color = "black";
                                            $("#download-button").html("<button onclick='download_zip(\""+zipresult.url+"\");'>Download</button>");
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

function canceldownload()
{
	$("#download-button").html('<button><i class="fa fa-refresh fa-spin" /></button> <button style="background-color:#d9534f;" onclick="canceldownload()"><i class="fa fa-refresh fa-spin" /></button>');
	$.each(xhr, function(idx, jqXHR) {
	        jqXHR.abort();
	});
	closeDownload();
	$.ajax({
		dataType:'json',
		type:'get',
		url:baseurl+'myfacebook/cancledownload',
		catch:false,
		success:function(result){
			location. reload(true);
		}
	});
}

function download(type,e,albumId="")
{
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
        url:baseurl+'myfacebook/downloadall',
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
    $.ajax({
        dataType:'json',
        type:'post',
        data:'albumId='+albumId+'&albumName='+albumName,
        url:baseurl+'myfacebook/moveSingle',
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
    $.ajax({
        dataType:'json',
        type:'post',
        data:'albums=all',
        url:baseurl+'myfacebook/moveAll',
        catch:false,
        success:function(response){
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
    if(selected_albums.length != 0)
    {
        $("#driveFiles").show();
    }

    $.ajax({
        dataType:'json',
        type:'post',
        data:'selected='+selected_albums+'&albums=selected',
        url:baseurl+'myfacebook/moveSelected',
        catch:false,
        success:function(response){
        }
    });
}

function cancelupload()
{
	$.ajax({
	        dataType:'json',
	        type:'get',
	        url:baseurl+'myfacebook/cancleupload',
	        catch:false,
	        success:function(response){
	        	$('#driveFiles').hide();
	        }
    });
}




var total = (document.getElementById("sider").childElementCount*100);
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

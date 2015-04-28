(function ($) {
    'use strict';

    $(function () {
        /*notif message hiding*/
        function notif_message_hide() {
            setTimeout(function () {
                $('div.updated, div.error').slideUp('3000', function () {
                    $(this).remove();
                });
            }, 3000);
        }
        notif_message_hide();
    
        /*notif message show*/
        function notif_message_show(msg) {
            
            var html = '<div class="updated"><p>' + msg + '</p></div>';

            $('.wrap h2').after(html);
            notif_message_hide();
        }
        
        /*notif message show*/
        function notif_error_message_show(msg, error_form) {
            
            var html = '<div class="error"><p>' + msg + '</p>';
            
            if(error_form != 'undefined'){
                html += '<ul>';
                $.each(error_form, function(k, v){
                    html += '<li>'+v+'</li>';
                });
                html += '</ul>';
            }
            
            $('.wrap h2').after(html);
            notif_message_hide();
        }
        
        function unzipAttachment(id, callback){
            var ajaxData = {
                action : 'upunzipper_unzip_file',
                ajaxNonce : upunzipperParams.nonce,
                attachmentId : id
            };
            doRequest( ajaxData, function(data){
                if(data.status){
                    callback(data);
                }else{
                    notif_error_message_show(data.msg, data.form_errors);
                }
            });
        }
        
        function initFileProcessing(attachmentId, objArray){
            
            var totalSize = objArray.length;
            
            displayStatusProgressText();
            
            $.each(objArray, function(key, obj){
                
                addFile(obj.filename, function(data){
                    obj.status = true;
                    displayThumbnail(key, data.supp_data.url, data.msg);
                    
                    var nbr = displayStatusProgressText();
                    
                    var percent = displayStatusProgressBar(nbr.totalNbrOfImage,nbr.loadedNbrOfImage);
                    
                    if(percent == 100){
                        
                        var ajaxData = {
                            action : 'upunzipper_clean_temp',
                            ajaxNonce : upunzipperParams.nonce,
                            attachmentId : attachmentId
                        };
                        
                        doRequest( ajaxData, function(data){
                            if(data.status){
                                notif_message_show(data.msg);
                            }else{
                                notif_error_message_show(data.msg);
                            }
                        });
                    }
                    
                }, function(){
                    obj.status = false;
                });
                
            });
            
        }
        
        function displayStatusProgressText(){
            
            if($('.upunzipper-status > p').length > 0){
                $('.upunzipper-status > p').remove();
            }
            
            $('.upunzipper-status-progress').show();
            
            var totalNbrOfImage = $('.upunzipper-processing li').length;
            var loadedNbrOfImage = $('.upunzipper-processing li.upunzipper-loaded').length;
            
            $('.upunzipper-status-progress .progress-text-loaded').empty().html(loadedNbrOfImage);
            $('.upunzipper-status-progress .progress-text-total').empty().html(totalNbrOfImage);
            
            return {
                totalNbrOfImage : totalNbrOfImage,
                loadedNbrOfImage : loadedNbrOfImage
            };
        }
        
        function displayLoading(hide){
            
            if($('.upunzipper-status > p').length > 0){
                $('.upunzipper-status > p').remove();
            }
            
            if(hide != 'undefined' && hide == true){
                $('.upunzipper-status-loading').slideUp('500');
            }else{
                $('.upunzipper-status-loading').show();
            }
            
        }
        
        function addFile(filePath, ok, ko){
            var ajaxData = {
                action : 'upunzipper_add_file',
                ajaxNonce : upunzipperParams.nonce,
                filePath : filePath
            };
            
            doRequest( ajaxData, function(data){
                if(data.status){
                    ok(data);
                }
            }, function(){
                ko();
            });
        }
        
        function createProcessingObjArray(data){
            var finalArray = [];
            $.each(data.supp_data, function(key, val){
                
                var obj = {
                    filename : val,
                    status   : false
                };
                
                finalArray.push(obj);
                
            });

            return finalArray;
        }
        
        function displayStatusProgressBar(totalNbrOfImage,loadedNbrOfImage){
            var progressBar = $(".upunzipper-status-progress .progress-bar");
            var percent   = Math.round( (loadedNbrOfImage / totalNbrOfImage) * 100 );
            var progressBarWidth =  percent * progressBar.width() / 100 ;
            progressBar.find('div').stop().animate({ width: progressBarWidth }, 500).html(percent + "%&nbsp;");
            
            return percent;
        }
        
        function displayThumbnail(elemId, imgSrc, label){
            $('.upunzipper-processing #upunzipper-elem-'+elemId+' img').attr("src",imgSrc);
            $('.upunzipper-processing #upunzipper-elem-'+elemId+' .filename').empty().html(label);
            $('.upunzipper-processing #upunzipper-elem-'+elemId).addClass('upunzipper-loaded');
            
            displayStatusProgressBar();
        }
        
        function displayProcessing(data){
            $('.upunzipper-processing').show();
           
            var html = '';
            
            $.each(data, function(key, val){
                html += '<li id="upunzipper-elem-'+key+'" class="attachment"><div class="attachment-preview"><div class="thumbnail">'
                            +'<div class="centered">'
				+'<img draggable="false" class="icon" src="images/spinner.gif">'
                            +'</div>'
                            +'<div class="filename">'
                                +'<div>Loading...</div>'
                            +'</div>'
			+'</div></div></li>';
            });
            
            $('.upunzipper-processing ul').empty().append(html);

        }
        
        function doRequest( requestData, okCallback, koCallback){
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: requestData,
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    if(okCallback != 'undefined'){
                            okCallback(data, jqXHR);
                    }

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if(koCallback != 'undefined'){
                            koCallback(jqXHR);
                    }
                }
            });

        }

        $('#uploadButton').click(function (e) {
            e.preventDefault();
            
            var customUploader = wp.media({
                title: upunzipperParams.uploaderTitle,
                button: {
                    text: upunzipperParams.uploaderButton
                },
                library:   {type: 'application/zip'},
                multiple: false  // Set this to true to allow multiple files to be selected
            }).on('select', function () {
                var attachment = customUploader.state().get('selection').first().toJSON();
                if(attachment.mime == 'application/zip'){
                    displayLoading();
                    var files = null;
                    unzipAttachment(attachment.id, function(data){
                        displayLoading(true);
                        if(data.status){
                            notif_message_show(data.msg);
                            files = createProcessingObjArray(data);
                            displayProcessing(files);
                            initFileProcessing(attachment.id, files);
                            
                        }else{
                            notif_error_message_show(data.msg);
                        }
                    });
                    
                }
                
            }).open();
        });
        
        
        

    });

}(jQuery));
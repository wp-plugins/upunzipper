<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://ravidhu.com
 * @since      1.0.0
 *
 * @package    Upunzipper
 * @subpackage Upunzipper/admin/partials
 */
?>
<div class="wrap">
    <h2>UpUnzipper</h2>
    <div class="upunzipper-status">
        <p>
            <?php echo __('Upload or select an zip file.', $plugin_name) ?>
             <button id="uploadButton" class="button-primary">+</button>
        </p>
        <div class="upunzipper-status-loading">
            <p>Unzipping ...<img draggable="false" class="icon" src="images/spinner.gif"></p>
        </div>
        <div class="upunzipper-status-progress">
            
            <div class="progress-text">
                <p>
                    <span class="progress-text-loaded"></span> / <span class="progress-text-total"></span> 
                    <?php echo __('files processed.', $plugin_name) ?>
                </p>
            </div>
            <div class="progress-bar">
                <div>
                </div>
            </div>
            <hr>
        </div>
    </div>
    <div id="wp-media-grid">
        <div class="media-frame wp-core-ui mode-grid mode-edit hide-menu">
            <div class="upunzipper-processing media-frame-content" data-columns="10">
                <ul class="attachments"> 

                </ul>
            </div>
        </div>
    </div>

    
</div>

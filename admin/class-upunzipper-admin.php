<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://ravidhu.com
 * @since      1.0.0
 *
 * @package    Upunzipper
 * @subpackage Upunzipper/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Upunzipper
 * @subpackage Upunzipper/admin
 * @author     Ravidhu Dissanayake <ravidhu.dissa@gmail.com>
 */
class Upunzipper_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

            $this->plugin_name = $plugin_name;
            $this->version = $version;

            // Add the options page and menu item.
            add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );    
            
            // Ajax call registration
            add_action('wp_ajax_upunzipper_unzip_file', array( $this, 'ajax_unzip_file' ));
            add_action('wp_ajax_upunzipper_add_file', array( $this, 'ajax_add_file' ));
            add_action('wp_ajax_upunzipper_clean_temp', array( $this, 'ajax_clean_temp' ));
            
	}
        
        /**
        * Register the administration menu for this plugin into the WordPress Dashboard menu.
        *
        * @since    1.0.0
        */
        public function add_plugin_admin_menu() {

           $this->plugin_screen_hook_suffix[] = add_menu_page(
                   __('UpUnzipper', $this->plugin_name), 
                   __('UpUnzipper', $this->plugin_name), 
                   'manage_options', 
                   $this->plugin_name, 
                   array($this, 'display_plugin_admin_dashboard')
           );
           
        }
        
        /**
        * Render the settings page for this plugin.
        *
        * @since    1.0.0
        */
        public function display_plugin_admin_dashboard() {
            $plugin_name = $this->plugin_name;
            include_once( 'partials/upunzipper-admin-display.php' );
        }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/upunzipper-admin.css', array(), $this->version, 'all' );
        }

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
            
            wp_enqueue_media();
            wp_enqueue_script( $this->plugin_name. '-admin-script', plugin_dir_url( __FILE__ ) . 'js/upunzipper-admin.js', array( 'jquery' ), $this->version, true );
            wp_localize_script(
                $this->plugin_name . '-admin-script', 
                $this->plugin_name . 'Params', 
                array( 
                    'nonce' => $this->_get_nonce(),
                    'uploaderButton' => __('Select your zip file.', $this->plugin_name),
                    'uploaderTitle'  => __('Add your zip containing images', $this->plugin_name)
                )
            );
	}
        
        public function ajax_unzip_file(){
            
            $this->_check_is_ajax();
          
            $attachment_id = is_numeric($_POST['attachmentId']) ? (int)$_POST['attachmentId'] : FALSE;
        
            if($attachment_id){
            
                $full_size_path = get_attached_file( $attachment_id );
                $upload_dir = wp_upload_dir(); 
                
                $tmp_dir_path = $upload_dir['basedir'].'/upunzipper-tmp'.$upload_dir['subdir'].'/'.$attachment_id;
                
                if(file_exists( $tmp_dir_path )){
                    $this->_recursiveDelete($tmp_dir_path);
                }
                
                if( ! file_exists( $tmp_dir_path ) && wp_mkdir_p( $tmp_dir_path ) ){
                    
                    WP_Filesystem();
                    $unzip_file = unzip_file( $full_size_path, $tmp_dir_path);
                    if($unzip_file === TRUE){
                      
                        $file_tree = $this->_createTempFilesList($tmp_dir_path);
                        Upunzipper_Ajax_Request::success(__('The zip is ...unzipped ;).', $this->plugin_name), $file_tree);
                    }else{
                        Upunzipper_Ajax_Request::error(__('The unzipping failed.', $this->plugin_name), $unzip_file->get_error_messages());
                    }
                }else{
                    Upunzipper_Ajax_Request::error(__('Temporary folder failed to be created.', $this->plugin_name));
                }
                   
            }else{
                Upunzipper_Ajax_Request::error(__('Id is missing.', $this->plugin_name));
            }
            
        }
        
        public function ajax_add_file(){
            
            $this->_check_is_ajax();
            
            $file_path = is_string($_POST['filePath']) ? $_POST['filePath'] : FALSE;
            
            if($file_path){
                
                // Check the type of file. We'll use this as the 'post_mime_type'.
                $filetype = wp_check_filetype( basename( $file_path ), null );

                // Get the path to the upload directory.
                $wp_upload_dir = wp_upload_dir();
                $filename = array_pop(explode("/", $file_path));
                $new_file_path = $wp_upload_dir['path'].'/'.$filename;
                
                if (!copy($file_path, $new_file_path)) {
                    Upunzipper_Ajax_Request::error(__('Failed to move the file.', $this->plugin_name));
                }
                
                if (!unlink($file_path)) {
                    Upunzipper_Ajax_Request::error(__('Failed to erase temporary file.', $this->plugin_name));
                }
                
                // Prepare an array of post data for the attachment.
                $attachment = array(
                        'guid'           => $wp_upload_dir['url'] . '/' . basename( $new_file_path ), 
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $new_file_path ) ),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                );

                // Insert the attachment.
                $attach_id = wp_insert_attachment( $attachment, $new_file_path );

                // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                require_once( ABSPATH . 'wp-admin/includes/image.php' );

                // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata( $attach_id, $new_file_path );
                $check = wp_update_attachment_metadata( $attach_id, $attach_data );
                
                if($check){
                    $thumbnail_src = wp_get_attachment_image_src( $attach_id);
                    Upunzipper_Ajax_Request::success(array_pop(explode("/", $new_file_path)) , array(
                        'url' => $thumbnail_src[0]
                    ) );
                }else{
                    Upunzipper_Ajax_Request::error(__('Attachement creation failed.', $this->plugin_name));
                }

            }else{
                Upunzipper_Ajax_Request::error(__('No files.', $this->plugin_name));
            }
        }
        
        public function ajax_clean_temp(){
            
            $this->_check_is_ajax();
         
            $attachment_id = is_numeric($_POST['attachmentId']) ? (int)$_POST['attachmentId'] : FALSE;
        
            if($attachment_id){
            
                $upload_dir = wp_upload_dir(); 
                
                $tmp_dir_path= $upload_dir['basedir'].'/upunzipper_tmp/'.$upload_dir['subdir'].'/'.$attachment_id;
                
                if( ! file_exists( $tmp_dir_path ) ){
                    
                    $delete_check = $this->_recursiveDelete( $tmp_dir_path );
                    
                    if($delete_check){              
                        Upunzipper_Ajax_Request::success(__('Temporary folder have been deleted.', $this->plugin_name));
                    }else{
                        Upunzipper_Ajax_Request::error(__('No temporary files to clean.', $this->plugin_name));
                    }
                }else{
                    Upunzipper_Ajax_Request::error(__('The temporary folder is not found.', $this->plugin_name));
                }
                   
            }else{
                Upunzipper_Ajax_Request::error(__('Id is missing.', $this->plugin_name));
            }
            
        }
        
        private function _check_nonce($nonce){
            return Upunzipper_Ajax_Request::check_nonce($nonce, $this->plugin_name.'_ajax_nonce');

        }

        private function _get_nonce(){
            return Upunzipper_Ajax_Request::get_nonce($this->plugin_name.'_ajax_nonce');
        }
        
        private function _check_is_ajax(){
            if( Upunzipper_Ajax_Request::is_launched() ){
                if(!$this->_check_nonce($_POST['ajaxNonce'])){
                    Upunzipper_Ajax_Request::error(__('Wrong request.', $this->plugin_name));
                }
            }
        }
        
        private function _recursiveDelete($dir) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? $this->_recursiveDelete("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }
        
        private function _createTempFilesList($temp_dir){
            
            $iterator = new RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($temp_dir, \FilesystemIterator::SKIP_DOTS)
            );

            $files = array();
            foreach ($iterator as $filename => $file_info) {
                if(strstr($filename,'.DS_Store') === FALSE 
                        && strstr($filename,'Thumbs.db') === FALSE ){
                    $files[] = $filename;
                }
            }
            
            return $files;
          
        }

}

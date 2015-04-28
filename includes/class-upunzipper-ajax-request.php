<?php
/**
 * Ajax Request manager.
 *
 * @package   BannerArea
 * @author    Ravidhu Dissanayake <contact@ravidhu.com>
 * @license   GPL-2.0+
 * @link      http://ravidhu.com
 * @copyright 2014 Ravidhu Dissanayake
 */

class Upunzipper_Ajax_Request{
    
    public static function is_launched($method = 'POST'){
        
        if ($_SERVER['REQUEST_METHOD'] === $method &&
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']))
        {
            return true;
        }else{
            return false;
        }
    }
	
    public static function success($msg, $supp_data = NULL){
        
        $data = array(
            'status' => true,
            'msg'    => $msg,
        );
        
        if($supp_data !== NULL && is_array($supp_data)){
            $data['supp_data'] = $supp_data;
        }
        
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
        
    }
    
    public static function error($msg, $form_errors = NULL){
        
        $data = array(
            'status' => false,
            'msg'    => $msg,
        );
        
        if($form_errors !== NULL && is_array($form_errors)){
            $data['form_errors'] = $form_errors;
        }
    
        header('Content-Type: application/json');
        echo json_encode($data);
        die();

    }
    
    public static function check_nonce($nonce, $name){
        return wp_verify_nonce($nonce, $name);
    }
    
    public static function get_nonce($name){
        return wp_create_nonce($name);
    }
}
?>
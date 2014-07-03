<?php
/*
  Plugin Name: GF Upload to Email Attachment
  Plugin URI: http://www.gregwhitehead.us/
  Description: Gravity Forms was built to be able to store all uploaded files to the server and email you a link.  There are times that you need to have that file get attached to the notification email.  By creating a notification in the form with GFUEA added to the end of it tells Gravity Forms to also attach any files to the outbound email as well as save it with the entry in the back-end.
  Author: Greg Whitehead
  Author URI: http://www.billiardgreg.com
  Version: 1.0
 */
 
 
add_filter('gform_notification', 'GFUEA_custom_notification_attachments', 10, 3);
function GFUEA_custom_notification_attachments( $notification, $form, $entry ) {
	$log = 'rw_notification_attachments() - ';
	GFCommon::log_debug( $log . 'starting.' );
    
    if(substr($notification["name"],-5) == "GFUEA"){

       $fileupload_fields = GFCommon::get_fields_by_type( $form, array( 'fileupload' ) );
 
        if ( ! is_array( $fileupload_fields ) ) {
            return $notification;
        }
 
        $attachments = array();
        $upload_root = RGFormsModel::get_upload_root();
        
        foreach( $fileupload_fields as $field ) {
            
            $url = $entry[ $field['id'] ];
            
            if ( empty( $url ) ) {
                continue;
            } elseif ( $field['multipleFiles'] ) {
                $uploaded_files = json_decode( stripslashes( $url ), true );
                foreach ( $uploaded_files as $uploaded_file ) {
                    $attachment = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $uploaded_file );
                    GFCommon::log_debug( $log . 'attaching the file: ' . print_r( $attachment, true  ) );
                    $attachments[] = $attachment;
                }
            } else {
                $attachment = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $url );
                GFCommon::log_debug( $log . 'attaching the file: ' . print_r( $attachment, true  ) );
                $attachments[] = $attachment;
            }
            
        }
 
        $notification['attachments'] = $attachments;
 
    }
    
    GFCommon::log_debug( $log . 'stopping.' );
 
    return $notification;
}


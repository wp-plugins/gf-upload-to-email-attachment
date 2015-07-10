<?php
/*
  Plugin Name: GF Upload to Email Attachment
  Plugin URI: http://wpcms.ninja/
  Description: Gravity Forms was built to be able to store all uploaded files to the server and email you a link.  There are times that you need to have that file get attached to the notification email.  By creating a notification in the form with GFUEA added to the end of it tells Gravity Forms to also attach any files to the outbound email as well as save it with the entry in the back-end.  If multiple files are attached it attempts to create a zip file to send.
  Author: Greg Whitehead
  Author URI: http://wpcms.ninja
  Version: 1.1
 */
 
 
add_filter('gform_notification', 'GFUEA_custom_notification_attachments', 10, 3);
function GFUEA_custom_notification_attachments( $notification, $form, $entry ) {
	$log = 'rw_notification_attachments() - ';
	GFCommon::log_debug( $log . 'starting.' );
	
#    mail("greg@wpcms.ninja","Notification Fire" . date("Y-m-d h:i:s"), print_r($notification, true) . print_r($form,true) . print_r($entry,true));
	
	
    if(substr($notification["name"],-5) == "GFUEA" ){
    	//mail("greg@wpcms.ninja","Notification Fire" . date("Y-m-d h:i:s"), print_r($notification, true) . print_r($form,true) . print_r($entry,true));

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
			  
			$zip = new ZipArchive();
			  //$filetext = date("Y-m-d his");
			$filename = $upload_root . "/uploaded_files".$entry['id'].".zip";
			  if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
	                foreach ( $uploaded_files as $uploaded_file ) {
     	               $attachment = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $uploaded_file );
                    	GFCommon::log_debug( $log . 'attaching the file: ' . print_r( $attachment, true  ) );
                    	$attachments[] = $attachment;
                	}
			  } else {
	                foreach ( $uploaded_files as $uploaded_file ) {
     	               $attachment = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $uploaded_file );
                    	GFCommon::log_debug( $log . 'attaching the file: ' . print_r( $attachment, true  ) );
					$new_filename = substr($attachment,strrpos($attachment,'/') + 1);
					$zip->addFile($attachment,$new_filename);
					 
                    	//$attachments[] = $attachment;
                	}
				  $zip->close();
				  $attachments[] = $filename;
				  add_filter( 'gform_confirmation', 'gfuea_clean_zips', 10, 4 );				  
			  }
			  
            } else {
                $attachment = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $url );
                GFCommon::log_debug( $log . 'attaching the file: ' . print_r( $attachment, true  ) );
                $attachments[] = $attachment;
            }
            
        }
 
        $notification['attachments'] = $attachments;
 
    }

	//mail("greg@wpcms.ninja","Notification Fire" . date("Y-m-d h:i:s"), "Attach IDs:\n" . print_r($attachIds, true) . "\nNotification:\n" . print_r($notification, true) . "\nForm:\n" .print_r($form,true) . "\nEntry:\n" .print_r($entry,true));
	
    GFCommon::log_debug( $log . 'stopping.' );
 
    return $notification;
}

function gfuea_clean_zips($confirmation, $form, $entry, $ajax) {
	$upload_root = RGFormsModel::get_upload_root();
	
	$filename = $upload_root . "/uploaded_files".$entry['id'].".zip";
	if (is_file($filename)){
		unlink($filename);
	}
	return $confirmation;
}


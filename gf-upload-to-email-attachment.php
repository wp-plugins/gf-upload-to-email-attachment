<?php
/*
  Plugin Name: GF Upload to Email Attachment
  Plugin URI: http://wpcms.ninja/
  Description: Gravity Forms was built to be able to store all uploaded files to the server and email you a link.  There are times that you need to have that file get attached to the notification email.  You can now tick a checkbox in the notifications area to specify whether or not you want the file attached. If multiple files you are then able to have it attempt to zip before sending too. Also ability to delete files on email getting sent.
  Author: Greg Whitehead
  Author URI: http://wpcms.ninja
  Version: 2.2
 */


add_filter('gform_notification', 'GFUEA_custom_notification_attachments', 10, 3);
function GFUEA_custom_notification_attachments( $notification, $form, $entry ) {
    global $gf_delete_files;
	$log = 'rw_notification_attachments() - ';
	GFCommon::log_debug( $log . 'starting.' );
    GFCommon::log_debug( $log. 'PRE-Notification: '. print_r( $notification, true) );
	$last5 = substr($notification["name"],-5);
	$last7 = substr($notification["name"],-7);
	$attach_upload_to_email = rgar( $notification, 'gfu_attach_upload_to_email' );
    $zip_attachment = rgar( $notification, 'gfu_zip_attachment' );
    $gf_delete_files = rgar( $notification, 'gfu_delete_files' );
    $gf_unlink_files = rgar( $notification, 'gfu_unlink_files' );
    if( ($last5 == "GFUEA" || $last7 == "GFUEANZ") || $attach_upload_to_email == 'yes' ) {
		if ($last7 == "GFUEANZ" || $zip_attachment != 'yes'){
			$attemptzip = false;
		} else {
			$attemptzip = true;
		}
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
    			$filename = $upload_root . "/uploaded_files".$entry['id'].".zip";
	            if ($zip->open($filename, ZipArchive::CREATE)!==TRUE || $attemptzip == false) {
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
                	}
				  $zip->close();
				  $attachments[] = $filename;
			  }
            } else {
                $attachment = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $url );
                GFCommon::log_debug( $log . 'attaching the file: ' . print_r( $attachment, true  ) );
                $attachments[] = $attachment;
            }
        }
        add_filter( 'gform_confirmation', 'gfuea_clean_zips', 10, 4 );
        $notification['attachments'] = $attachments;
    }
    GFCommon::log_debug( $log. 'POST-Notification: '. print_r( $notification, true) );
    GFCommon::log_debug( $log . 'stopping.' );
    return $notification;
}

function gfuea_clean_zips($confirmation, $form, $entry, $ajax) {
    global $gf_delete_files;
    $upload_root = RGFormsModel::get_upload_root();
    $filename = $upload_root . "/uploaded_files".$entry['id'].".zip";

    if (is_file($filename)){
        unlink($filename);
    }

    if ($gf_delete_files == 'yes') {
        //delete all files that were uploaded

        $attachments_to_delete = array();

        $fileupload_fields = GFCommon::get_fields_by_type( $form, array( 'fileupload' ) );
        if ( is_array( $fileupload_fields ) ) {
            $upload_root = RGFormsModel::get_upload_root();
            foreach( $fileupload_fields as $field ) {
                $url = $entry[ $field['id'] ];
                if ( empty( $url ) ) {
                    continue;
                } elseif ( $field['multipleFiles'] ) {
                    $uploaded_files = json_decode( stripslashes( $url ), true );
                    if (is_array($uploaded_files))
                        foreach ( $uploaded_files as $uploaded_file ) {
                            $attachments_to_delete[] = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $uploaded_file );
                        }

                } else {
                    $attachments_to_delete[] =  preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $url );
                }
          }
        }

        if (is_array($attachments_to_delete) && count($attachments_to_delete) > 0 ) {
            foreach ($attachments_to_delete as $attachment) {
                unlink($attachment);
            }
        }
    }

    return $confirmation;
}

add_filter( 'gform_notification_ui_settings', 'gf_upload_notification_setting', 10, 3 );
function gf_upload_notification_setting( $ui_settings, $notification, $form ) {
	$gf_upload = rgar( $notification, 'gfu_attach_upload_to_email' );
    $gf_zip = rgar( $notification, 'gfu_zip_attachment' );
    $gf_delete = rgar( $notification, 'gfu_delete_files' );
    $ui_settings['gf_upload_section'] = '
        <tr>
            <th><label for="attach_upload_to_email">GF Upload Options</label></th>
            <td>
            <input type="checkbox" value="yes" '. ( $gf_upload == 'yes' ? ' checked ' : '' ) .' name="gfu_attach_upload_to_email"><label for="gfu_attach_upload_to_email">Attach File to Outbound Email</label><br>
            <input type="checkbox" value="yes" '. ( $gf_zip == 'yes' ? ' checked ' : '' ) .' name="gfu_zip_attachment"><label for="gfu_zip_attachment">Attempt to zip file before sending</label><br>
            <input type="checkbox" value="yes" '. ( $gf_delete == 'yes' ? ' checked ' : '' ) .' name="gfu_delete_files"><label for="gfu_delete_files">Delete files after sending</label>
            <input type="checkbox" value="yes" '. ( $gf_unlink == 'yes' ? ' checked ' : '' ) .' name="gfu_unlink_files"><label for="gfu_unlink_files">Unlink Files in Notifications</label>
            </td>
        </tr>
        ';
    return $ui_settings;
}

add_filter( 'gform_pre_notification_save', 'gf_upload_notification_save', 10, 2 );
function gf_upload_notification_save( $notification, $form ) {
	$gf_upload = rgpost( 'gfu_attach_upload_to_email' );
    $gf_zip = rgpost( 'gfu_zip_attachment' );
    $gf_delete = rgpost( 'gfu_delete_files' );
    $gf_unlink = rgpost( 'gfu_unlink_files');
    $notification['gfu_attach_upload_to_email'] = ( $gf_upload == 'yes' ? $gf_upload : 'no' );
    $notification['gfu_zip_attachment'] = ( $gf_zip == 'yes' ? $gf_zip : 'no' );
    $notification['gfu_delete_files'] = ( $gf_delete == 'yes' ? $gf_delete : 'no' );
    $notification['gfu_unlink_files'] = ( $gf_unlink == 'yes' ? $gf_unlink : 'no' );
    return $notification;
}
=== GF Upload to Email Attachment ===
Contributors: billiardgreg
Donate link: http://www.billiardgreg.com/
Tags: 
Requires at least: 3.8.3
Tested up to: 3.9.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This allows you to create a notification in gravity forms of an email that would send with the files being uploaded by that form as an attachment. 

== Description ==

Gravity Forms was built to be able to store all uploaded files to the server and email you a link.  There are times that you need to have that file get attached to the notification email.  By creating a notification in the form with GFUEA added to the end of it tells Gravity Forms to also attach any files to the outbound email as well as save it with the entry in the back-end.  

Works with both single and multiple upload boxes as well as multiiple notifiations.  As this notification name isn't really used in any other place I thought it would be the easiest way to add this functionality.

Utilizes code example from Gravity Forms gforms_notification page modified to attach the files getting uploaded to the notification email based upon last 5 characters of the notification name.    

== Frequently Asked Questions ==

= Where can I get answers to questions? =

You can email greg@billiardgreg.com to receive answers or go to http://www.gregwhitehead.us

== Installation ==

Install plugin and activate.

Add GFUEA to the Gravity Forms email notification name and all files uploaded will be attached to outbound email.

== Screenshots ==

1. No Screenshot

== Changelog ==

= 1.0 =
* Updated description and changed to stable version 1.0

= .1 =
* Initial Release of Plugin

== Upgrade Notice ==

= .1 =
* Initial Release of Plugin
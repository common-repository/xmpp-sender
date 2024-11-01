=== XMPP sender ===
Tags: jabber, xmpp, messages, comments, send, im
Contributors: Alexey Laptev
Requires at least: 2.7
Tested up to: 2.9.2
Stable tag: trunk

XMPP sender allows to send (and receive) notifications about new comments in blog. Also it can override email function to xmpp for your blog.

== Description ==

	XMPP sender allows to send  notifications about new comments in blog using XMPP protokol (you know it. This is a jabber. For example, it used in Google Talk).You can customize it to send notifications to blog admin or(and) post author about all comments. 
	Message can include comment author, his email, comment content, post title etc. The plugin includes a little template manager that give you possibility to make your own messages. You can change interface language (russian an english included).
!!!--->	It can override email function to xmpp. You can use very good plugins like "Wordpress Thread Comment" or "Subscribe to Comments" to receive notifications about different events on XMPP!

It should work with previouse versions of wordpress, but i didn't test it.


== Installation ==

1. Put XMPP-sender folder into [wordpress_dir]/wp-content/plugins/
2. Go into the WordPress admin interface and activate the plugin
3. Go into the WordPress admin interface to the XMPP options page and customize server options and messages template. You can test server options using button "Test" after saving options.


== Screenshots ==

1. Options screenshot.


==ToDo==



==Versions history==

---------------------------------------------------------------
0.9 
* XMPPHP version updated (to XMPPHP 0.1 RC2 Rev 77)
---------------------------------------------------------------
0.3 
*Translation is made using standard method (text domain etc) only English and Russian now. Who want to translate into his own language - write me.
*Added option for overriding email function to xmpp. You can use very good plugins like "Wordpress Thread Comment" or "Subscribe to Comments" to receive notifications about different events on XMPP!

---------------------------------------------------------------
0.2 
*Translation is made using arrives in inglish and Russian (i will correct it in future)
*Added options for server port, message template

---------------------------------------------------------------
0.1 
*First version/ It works!!! Really!!! :)
*It has some options (server, emails etc)

---------------------------------------------------------------

*/



<?php
/*
Plugin Name: XMPP sender
Plugin URI: http://blog.laptev.info/projects
Description: Notifications from blog to Jabber accounts of post author and(or) admin in case of new comment. Also you can override standard mailing to xmpp. Send your suggestions and critics to <a href="mailto:webmaster@blog.laptev.info">webmaster@blog.laptev.info</a> or in the plugin page as a comments. 
Author: Alexey Laptev
Author URI: http://blog.laptev.info/
Version: 0.9

Copyright 2009 by Alexey Laptev

	
    XMPPHP project library (http://code.google.com/p/xmpphp/) is used in project.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    For a copy of the GNU General Public License, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/ 

/*
New in this version:
---------------------------------------------------------------
0.9 
* XMPPHP version updated (to XMPPHP 0.1 RC2 Rev 77)
---------------------------------------------------------------
0.3 
*Translation is made using standard method (text domain etc) only English and Russian now. Who want to translate into his own language - write me.
*Added option for overriding email function to xmpp. You can use very good plugins like "Wordpress Thread Comment" or "Subscribe to Comments" to receive notifications about different events on XMPP (instead of email)!

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



include( "XMPPHP/XMPP.php" ); 
/*include ( ABSPATH . "/wp-content/plugins/xmpp-sender/XMPPHP/XMPP.php" );*/

load_plugin_textdomain('xmpp-sender', false, dirname( plugin_basename(__FILE__) ) . '/lang');

//override standard function
if ( !function_exists('wp_mail') & ( get_option('sxmpp') != false ) ) :
function wp_mail($to, $subject, $message, $headers) {
	$gs=htmlspecialchars(stripslashes(get_option('server')));
	if ( htmlspecialchars(stripslashes(get_option('server')))=='talk.google.com' ) { $gs='gmail.com'; }
		
	$conn = new XMPPHP_XMPP( htmlspecialchars(stripslashes(get_option('server'))), htmlspecialchars(stripslashes(get_option('port'))), htmlspecialchars(stripslashes(get_option('account'))), htmlspecialchars(stripslashes(get_option('password'))), 'xmpphp', $gs, $printlog=False, $loglevel=LOGGING_INFO);
		try
		{
			$conn->connect();
			$conn->processUntil('session_start');
			$conn->presence();
			$conn->message( $to ,$subject . '  ' . $message );
			$conn->disconnect();
		}
		catch(XMPPHP_Exception $e)
		{
			die($e->getMessage());
		}
	}
endif;
//finish override

function xmppsend($post_ID) {

        global $wpdb, $wp_version;

        $comment_content = $wpdb->get_var("SELECT comment_content FROM {$wpdb->comments} WHERE comment_ID = {$post_ID}");// comment text
	$comment_author_email = $wpdb->get_var("SELECT comment_author_email FROM {$wpdb->comments} WHERE comment_ID = {$post_ID}"); // email of comment author
	$comment_author = $wpdb->get_var("SELECT comment_author FROM {$wpdb->comments} WHERE comment_ID = {$post_ID}"); // comment author
	$p_id = $wpdb->get_var("SELECT comment_post_id FROM {$wpdb->comments} WHERE comment_ID = {$post_ID}");//post id
	$post_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->posts} WHERE ID = {$p_id}");//Post title
	$post_guid = $wpdb->get_var("SELECT guid FROM {$wpdb->posts} WHERE ID = {$p_id}");//post href
	$u_id = $wpdb->get_var("SELECT post_author FROM {$wpdb->posts} WHERE ID = {$p_id}");// post author id
	$u_email = $wpdb->get_var("SELECT user_email FROM {$wpdb->users} WHERE ID = {$u_id}");// post author email
	$post_jabber = $wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = {$u_id} and meta_key = 'jabber' ");
	$gs=htmlspecialchars(stripslashes(get_option('server')));
	if ( htmlspecialchars(stripslashes(get_option('server')))=='talk.google.com' ) { $gs='gmail.com'; }
	$conn = new XMPPHP_XMPP( htmlspecialchars(stripslashes(get_option('server'))), htmlspecialchars(stripslashes(get_option('port'))), htmlspecialchars(stripslashes(get_option('account'))), htmlspecialchars(stripslashes(get_option('password'))), 'xmpphp', $gs, $printlog=False, $loglevel=LOGGING_INFO);
	try

	{
		$conn->connect();
		$conn->processUntil('session_start');
		$conn->presence();

		if ( get_option('sauthor')!='false' ) {
			$conn->message( $post_jabber , get_templ( $comment_author, $post_title, $comment_author_email, $comment_content, $post_guid ) );
		}

		if ( get_option('scopy')!='false' ) {
			$conn->message( htmlspecialchars(stripslashes(get_option('cjabber'))) , get_templ( $comment_author, $post_title, $comment_author_email, $comment_content, $post_guid ) );

		}

		$conn->disconnect();
	}
	catch(XMPPHP_Exception $e)
	{
		die($e->getMessage());
	}

return $post_ID;
}


/// send message function ;)
function xmpp_s( $server, $port, $account, $password, $gs, $JIT, $text ) {

$conn = new XMPPHP_XMPP( $server, $port, $account, $password, 'xmpphp', $gs, $printlog=False, $loglevel=LOGGING_INFO);
try

	{
		$conn->connect();
		$conn->processUntil('session_start');
		$conn->presence();
		$conn->message( $JIT, $text );
		$conn->disconnect();
	}
	catch(XMPPHP_Exception $e)
	{
		die($e->getMessage());
	}
}

/////
function get_templ($cauthor, $ptitle, $cauthor_email, $ccontent, $chref) {
//var $t11, $t12, $t13, $t21, $t22, $t23, $t31, $t32, $t33, $t41, $t42, $t43, $t51, $t52, $t53;
	$t11 = get_option('t11');
	$t12 = tempcase( get_option('t12'), $cauthor, $ptitle, $cauthor_email, $ccontent, $chref ); 
	$t13 = get_option('t13');
	$t21 = get_option('t21');
	$t22 = tempcase( get_option('t22'), $cauthor, $ptitle, $cauthor_email, $ccontent, $chref ); 
	$t23 = get_option('t23');
	$t31 = get_option('t31');
	$t32 = tempcase( get_option('t32'), $cauthor, $ptitle, $cauthor_email, $ccontent, $chref ); 
	$t33 = get_option('t33');
	$t41 = get_option('t41');
	$t42 = tempcase( get_option('t42'), $cauthor, $ptitle, $cauthor_email, $ccontent, $chref ); 
	$t43 = get_option('t43');
	$t51 = get_option('t51');
	$t52 = tempcase( get_option('t52'), $cauthor, $ptitle, $cauthor_email, $ccontent, $chref ); 
	$t53 = get_option('t53');
	return $t11 . $t12 . $t13 . $t21 . $t22 . $t23 . $t31 . $t32 . $t33 . $t41 . $t42 . $t43 . $t51 . $t52 . $t53;
}

function tempcase($str, $cauthor, $ptitle, $cauthor_email, $ccontent, $chref) {
	if ( $str == 'comment_author' ) { return $cauthor; }
	if ( $str == 'post_title' ) { return $ptitle; }
	if ( $str == 'cauthor_email' ) { return $cauthor_email; }
	if ( $str == 'ccontent' ) { return $ccontent; }
	if ( $str == 'chref' ) { return $chref; }
}


// options page
function xmpp_options_page() {

//send test to admin account
	if (isset($_POST['test_rp'])) {
		$gs=htmlspecialchars(stripslashes(get_option('server')));
		if ( htmlspecialchars(stripslashes(get_option('server')))=='talk.google.com' ) { $gs='gmail.com'; }
		$r = xmpp_s( htmlspecialchars(stripslashes(get_option('server'))), htmlspecialchars(stripslashes(get_option('port'))), htmlspecialchars(stripslashes(get_option('account'))), htmlspecialchars(stripslashes(get_option('password'))), $gs, htmlspecialchars(stripslashes(get_option('cjabber'))), 'test' );
}
//send test finished

//options save  
	if (isset($_POST['update_rp'])) {
	       	$option_server = $_POST['server'];
		$option_account = $_POST['account'];
	       	$option_password = $_POST['password'];
	       	$option_scopy = $_POST['scopy'];
	       	$option_cjabber = $_POST['cjabber'];
	       	$option_sauthor = $_POST['sauthor'];
	       	$option_port = $_POST['port'];
	       	$option_t11 = $_POST['t11'];
	       	$option_t12 = $_POST['t12'];
	       	$option_t13 = $_POST['t13'];
	       	$option_t21 = $_POST['t21'];
	       	$option_t22 = $_POST['t22'];
	       	$option_t23 = $_POST['t23'];
	       	$option_t31 = $_POST['t31'];
	       	$option_t32 = $_POST['t32'];
	       	$option_t33 = $_POST['t33'];
	       	$option_t41 = $_POST['t41'];
	       	$option_t42 = $_POST['t42'];
	       	$option_t43 = $_POST['t43'];
	       	$option_t51 = $_POST['t51'];
	       	$option_t52 = $_POST['t52'];
	       	$option_t53 = $_POST['t53'];
		$option_sxmpp = $_POST['sxmpp'];

		update_option('server', $option_server);
		update_option('account', $option_account);
		if ( $option_password!='**********' ) {update_option('password', $option_password); }
		update_option('scopy', $option_scopy);
		update_option('cjabber', $option_cjabber);
		update_option('sauthor', $option_sauthor);
		update_option('port', $option_port);
		update_option('t11', $option_t11);		
		update_option('t12', $option_t12);
		update_option('t13', $option_t13);
		update_option('t21', $option_t21);		
		update_option('t22', $option_t22);
		update_option('t23', $option_t23);
		update_option('t31', $option_t31);		
		update_option('t32', $option_t32);
		update_option('t33', $option_t33);
		update_option('t41', $option_t41);		
		update_option('t42', $option_t42);
		update_option('t43', $option_t43);
		update_option('t51', $option_t51);		
		update_option('t52', $option_t52);
		update_option('t53', $option_t53);
		update_option('sxmpp', $option_sxmpp);
		   
	       ?> <div class="updated"><p><?PHP _e('Options saved!', 'xmpp-sender'); ?></p></div> <?php
	}
//finished options saved
// Options page
?>
<div class="wrap">
	<h2><?PHP _e('XMPP sender options', 'xmpp-sender'); ?></h2>
		<form method="post">
		<fieldset class="options">
		<table>
			<tr>
				<td><label for="server"><?PHP _e('Enter a jabber server which you will use to send messages from site. You should have a jabber account on it (talk.google.com for example)', 'xmpp-sender'); ?></label>:</td>
				<td><input name="server" type="text" id="server" value="<?php echo htmlspecialchars(stripslashes(get_option('server'))); ?>" size="30" /></td>
			</tr>

			<tr>
				<td><label for="port"><?PHP _e('Enter a jabber server port (5222 for example)', 'xmpp-sender'); ?></label>:</td>
				<td><input name="port" type="text" id="port" value="<?php echo htmlspecialchars(stripslashes(get_option('port'))); ?>" size="5" /></td>
			</tr>

		 	<tr>
           		<td><label for="account"><?PHP _e('Enter login to your account on server entered previously', 'xmpp-sender'); ?></label>:</td>
				<td><input name="account" type="text" id="account" value="<?php echo htmlspecialchars(stripslashes(get_option('account'))); ?>" size="30" /> 
				</td>
			</tr>
		 	<tr>
           		<td><label for="password"><?PHP _e('Enter password to your account on server entered previously', 'xmpp-sender'); ?></label>:</td>
				<td><input name="password" type="password" id="password" value="**********" size="30" /> 
				</td>
			</tr>
			<tr>
				<td><?PHP _e('Send copy to special admin jabber account?', 'xmpp-sender'); ?></td>
          		<td>
        			<select name="scopy" id="scopy">
        	  		<option <?php if(get_option('scopy') == 'false') { echo 'selected'; } ?> value="false"><?PHP _e('No', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('scopy') == 'true') { echo 'selected'; } ?> value="true"><?PHP _e('Yes', 'xmpp-sender'); ?></option>
					</select>
				</td> 
			</tr>
			<tr>
				<td><label for="cjabber"><?PHP _e('Admin account (for copies):', 'xmpp-sender'); ?></label></td>
				<td><input name="cjabber" type="text" id="cjabber" value="<?php echo htmlspecialchars(stripslashes(get_option('cjabber'))); ?>" size="30" /> 
			</tr>
			<tr>
				<td><?PHP _e('Send message to the author of the post (new comment)?', 'xmpp-sender'); ?></td>
          		<td>
        			<select name="sauthor" id="sauthor">
        	  		<option <?php if(get_option('sauthor') == 'false') { echo 'selected'; } ?> value="false"><?PHP _e('No', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('sauthor') == 'true') { echo 'selected'; } ?> value="true"><?PHP _e('Yes', 'xmpp-sender'); ?></option>
					</select>
				</td> 
			</tr>

			<tr>
				<td><?PHP _e('Override emailing with xmpp for all notifications(included standard and in another plugins?', 'xmpp-sender'); ?></td>
          		<td>
        			<select name="sxmpp" id="sxmpp">
        	  		<option <?php if(get_option('sxmpp') == 'false') { echo 'selected'; } ?> value="false"><?PHP _e('No', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('sxmpp') == 'true') { echo 'selected'; } ?> value="true"><?PHP _e('Yes', 'xmpp-sender'); ?></option>
					</select>
				</td> 
			</tr>

	
		<tr>
			<td>
			<table border="1" width = 99%>
				<tr bgcolor="#ffffcc">
				<td colspan=3><h3><?PHP _e('Messages template', 'xmpp-sender'); ?></h3></td>
				</tr>
				<tr>
				<td width = 33%><h3><?PHP _e('Text befor variable', 'xmpp-sender'); ?></h3></td>
				<td width = 33%><h3><?PHP _e('Variable', 'xmpp-sender'); ?></h3></td>
				<td width = 33%><h3><?PHP _e('Text after variable', 'xmpp-sender'); ?></h3></td>
				</tr>
				
				<tr>
				<td width = 33%>
					<input name="t11" type="text" id="t11" value="<?php echo htmlspecialchars(stripslashes(get_option('t11'))); ?>" size="30" />
				</td>
				<td width = 33%>
					<select name="t12" id="t12">
        	  			<option <?php if(get_option('t12') == 'comment_author') { echo 'selected'; } ?> value="comment_author"><?PHP _e('Comment author', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t12') == 'post_title') { echo 'selected'; } ?> value="post_title"><?PHP _e('Post title', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t12') == 'cauthor_email') { echo 'selected'; } ?> value="cauthor_email"><?PHP _e('Comment author E-mail', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t12') == 'ccontent') { echo 'selected'; } ?> value="ccontent"><?PHP _e('Comment content', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t12') == 'chref') { echo 'selected'; } ?> value="chref"><?PHP _e('Post URL', 'xmpp-sender'); ?></option>
					</select>				
				</td>
				<td width = 33%>
					<input name="t13" type="text" id="t13" value="<?php echo htmlspecialchars(stripslashes(get_option('t13'))); ?>" size="30" />
				</td>
				</tr>
<tr>
				<td width = 33%>
					<input name="t21" type="text" id="t21" value="<?php echo htmlspecialchars(stripslashes(get_option('t21'))); ?>" size="30" />
				</td>
				<td width = 33%>
					<select name="t22" id="t22">
        	  			<option <?php if(get_option('t22') == 'comment_author') { echo 'selected'; } ?> value="comment_author"><?PHP _e('Comment author', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t22') == 'post_title') { echo 'selected'; } ?> value="post_title"><?PHP _e('Post title', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t22') == 'cauthor_email') { echo 'selected'; } ?> value="cauthor_email"><?PHP _e('Comment author E-mail', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t22') == 'ccontent') { echo 'selected'; } ?> value="ccontent"><?PHP _e('Comment content', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t22') == 'chref') { echo 'selected'; } ?> value="chref"><?PHP _e('Post URL', 'xmpp-sender'); ?></option>
					</select>				
				</td>
				<td width = 33%>
					<input name="t23" type="text" id="t23" value="<?php echo htmlspecialchars(stripslashes(get_option('t23'))); ?>" size="30" />
				</td>
				</tr>
<tr>
				<td width = 33%>
					<input name="t31" type="text" id="t31" value="<?php echo htmlspecialchars(stripslashes(get_option('t31'))); ?>" size="30" />
				</td>
				<td width = 33%>
					<select name="t32" id="t32">
        	  			<option <?php if(get_option('t32') == 'comment_author') { echo 'selected'; } ?> value="comment_author"><?PHP _e('Comment author', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t32') == 'post_title') { echo 'selected'; } ?> value="post_title"><?PHP _e('Post title', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t32') == 'cauthor_email') { echo 'selected'; } ?> value="cauthor_email"><?PHP _e('Comment author E-mail', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t32') == 'ccontent') { echo 'selected'; } ?> value="ccontent"><?PHP _e('Comment content', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t32') == 'chref') { echo 'selected'; } ?> value="chref"><?PHP _e('Post URL', 'xmpp-sender'); ?></option>
					</select>				
				</td>
				<td width = 33%>
					<input name="t33" type="text" id="t33" value="<?php echo htmlspecialchars(stripslashes(get_option('t33'))); ?>" size="30" />
				</td>
				</tr>
<tr>
				<td width = 33%>
					<input name="t41" type="text" id="t41" value="<?php echo htmlspecialchars(stripslashes(get_option('t41'))); ?>" size="30" />
				</td>
				<td width = 33%>
					<select name="t42" id="t42">
        	  			<option <?php if(get_option('t42') == 'comment_author') { echo 'selected'; } ?> value="comment_author"><?PHP _e('Comment author', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t42') == 'post_title') { echo 'selected'; } ?> value="post_title"><?PHP _e('Post title', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t42') == 'cauthor_email') { echo 'selected'; } ?> value="cauthor_email"><?PHP _e('Comment author E-mail', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t42') == 'ccontent') { echo 'selected'; } ?> value="ccontent"><?PHP _e('Comment content', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t42') == 'chref') { echo 'selected'; } ?> value="chref"><?PHP _e('Post URL', 'xmpp-sender'); ?></option>
					</select>				
				</td>
				<td width = 33%>
					<input name="t43" type="text" id="t43" value="<?php echo htmlspecialchars(stripslashes(get_option('t43'))); ?>" size="30" />
				</td>
				</tr>
<tr>
				<td width = 33%>
					<input name="t51" type="text" id="t51" value="<?php echo htmlspecialchars(stripslashes(get_option('t51'))); ?>" size="30" />
				</td>
				<td width = 33%>
					<select name="t52" id="t52">
        	  			<option <?php if(get_option('t52') == 'comment_author') { echo 'selected'; } ?> value="comment_author"><?PHP _e('Comment author', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t52') == 'post_title') { echo 'selected'; } ?> value="post_title"><?PHP _e('Post title', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t52') == 'cauthor_email') { echo 'selected'; } ?> value="cauthor_email"><?PHP _e('Comment author E-mail', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t52') == 'ccontent') { echo 'selected'; } ?> value="ccontent"><?PHP _e('Comment content', 'xmpp-sender'); ?></option>
					<option <?php if(get_option('t52') == 'chref') { echo 'selected'; } ?> value="chref"><?PHP _e('Post URL', 'xmpp-sender'); ?></option>
					</select>				
				</td>
				<td width = 33%>
					<input name="t53" type="text" id="t53" value="<?php echo htmlspecialchars(stripslashes(get_option('t53'))); ?>" size="30" />
				</td>
				</tr>
			</table>
			

			</td>
		</tr>

		</table>
		</fieldset>

		<p><div class="submit"><input type="submit" name="update_rp" value="<?php _e('Save', 'xmpp-sender') ?>"  style="font-weight:bold;" />
		<input type="submit" name="test_rp" value="<?php _e('Send Test to Admin account (after save)!', 'xmpp-sender') ?>"  style="font-weight:bold;" /></div></p>
        
		</form>       
</div>
<?php
}// finish of options page



function xmpp_add_menu() {
		add_options_page('XMPP', 'XMPP', 8, __FILE__, 'xmpp_options_page');
}



add_action('admin_menu', 'xmpp_add_menu');
add_action('comment_post', 'xmppsend', 50);
?>

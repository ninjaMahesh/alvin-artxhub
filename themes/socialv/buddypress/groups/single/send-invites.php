<?php

/**
 * BuddyPress - Groups Send Invites
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 * @version 3.0.0
 */

/**
 * Fires before the send invites content.
 *
 * @since 1.1.0
 */
do_action('bp_before_group_send_invites_content');
if (!bp_is_active('friends')) : ?>
	<div id="message" class="info">
		<p class="notice"><?php esc_html_e('Group invitations can only be extended to friends.', 'socialv'); ?></p>
	</div>
<?php
/* Does the user have friends that could be invited to the group? */
elseif (bp_get_new_group_invite_friend_list()) : ?>

	<h2 class="bp-screen-reader-text"><?php esc_html_e('Send invites', 'socialv'); ?></h2>
	<?php /* 'send-invite-form' is important for AJAX support */ ?>
	<form action="<?php bp_group_send_invite_form_action(); ?>" method="post" id="send-invite-form" class="standard-form1">
		<div class="invite" aria-live="polite" aria-atomic="false" aria-relevant="all">
			<?php bp_get_template_part('groups/single/invites-loop'); ?>
		</div>
		<div class="form-edit-btn">
			<div class="submit">
				<input type="submit" name="submit" id="submit" class="btn socialv-btn-success" value="<?php esc_attr_e('Send Invites', 'socialv'); ?>" />
			</div>
		</div>
		<?php wp_nonce_field('groups_send_invites', '_wpnonce_send_invites'); ?>
		<?php /* This is important, don't forget it */ ?>
		<input type="hidden" name="group_id" id="group_id" value="<?php bp_group_id(); ?>" />
	</form><!-- #send-invite-form -->

<?php
/* No eligible friends? Maybe the user doesn't have any friends yet. */
elseif (0 == bp_get_total_friend_count(bp_loggedin_user_id())) : ?>

	<div id="message" class="info">
		<p class="notice"><?php esc_html_e('Group invitations can only be extended to friends.', 'socialv'); ?></p>
		<p class="message-body"><?php esc_html_e("Once you've made some friendships, you'll be able to invite those members to this group.", 'socialv'); ?></p>
	</div>

<?php
/* The user does have friends, but none are eligible to be invited to this group. */
else : ?>

	<div id="message" class="info">
		<p class="notice"><?php esc_html_e('All of your friends already belong to this group.', 'socialv'); ?></p>
	</div>

<?php endif;
/**
 * Fires after the send invites content.
 *
 * @since 1.2.0
 */
do_action('bp_after_group_send_invites_content');

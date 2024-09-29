<?php

/**
 * SocialV\Utility\Custom_Helper\Helpers\Common class
 *
 * @package socialv
 */

namespace SocialV\Utility\Custom_Helper\Helpers;

use LP_Settings;
use WP_Query;
use SocialV\Utility\Custom_Helper\Component;
use function add_action;
use function SocialV\Utility\socialv;

class Common extends Component
{
    public $socialv_option;
    public function __construct()
    {
        $this->socialv_option = get_option('socialv-options');
        // login page 
        if (class_exists('Redux')) {
            add_action('template_redirect', [$this, 'socialv_redirect_to_specific_page'], 999);
            add_filter('login_redirect', [$this, 'socialv_login_redirect'], 20, 3);
            add_action('admin_init', [$this, 'socialv_disable_dashboard']);
            add_filter('login_url', [$this, 'socialv_custom_login_url'], 10);
        }
        if (class_exists('Wordfence')) {
            add_action('init', [$this, 'socialv_worfence_login_url_redirect']);
        }

        if (is_user_logged_in() && function_exists('advanced_google_recaptcha_load_frontend_scripts')) {
            remove_action('wp_enqueue_scripts', 'advanced_google_recaptcha_load_frontend_scripts');
        }

        if (class_exists('bbPress')) {
            add_filter('bbp_breadcrumb_separator', [$this, 'socialv_bbp_breadcrumb_separator']);
            add_filter('bbp_after_get_user_favorites_link_parse_args', [$this, 'socialv_bbp_after_get_user_favorites_link_parse_args']);
            add_filter('bbp_get_reply_author_avatar', [$this, 'socialv_bbp_get_reply_author_avatar']);
            add_filter('bbp_after_get_user_subscribe_link_parse_args', [$this,  'socialv_bbp_after_get_user_subscribe_link_parse_args']);
            add_filter('bbp_get_cancel_reply_to_link', [$this, 'socialv_bbp_get_cancel_reply_to_link'], 10, 3);
        }


        if (isset($_GET['redirect_to']))
            add_filter("socialv_global_script_vars", [$this, "socialv_append_global_script_vars"]);

        if (class_exists('Wpstory_Premium')) :
            add_action('socialv_user_stories', [$this, 'socialv_user_stories']);
        endif;

        //social 
        if (class_exists('miniorange_openid_sso_settings') && shortcode_exists('miniorange_social_login') && !is_user_logged_in()) :
            add_action('get_socialv_social_after', [$this, 'get_socialv_social_login']);
        endif;

        //buddypress poll
        add_action('activated_plugin', [$this, 'socialv_custom_modify_bpolls_settings']);


        //buddypress new version post type
        add_filter('register_buddypress_post_type_args', [$this, 'socialv_generate_buddypres_post_type_ui']);
    }
    // Forms
    function get_shortcode_content($options = '')
    {
        $output = '<div class="socialv-info text-center">';
        socialv()->socialv_logo();
        if (isset($this->socialv_option['header_display_login']) && $this->socialv_option['header_display_login'] == 'yes') {
            switch ($options) {
                case 'login':
                    $desc = $this->socialv_option['site_login_desc'];
                    if (!empty($desc)) {
                        $output .= '<p>' . esc_html(sprintf(_x('%s', 'site_login_desc', 'socialv'), $desc)) . '</p>';
                    }
                    break;
                case 'register':
                    $desc = $this->socialv_option['site_register_desc'];
                    if (!empty($desc)) {
                        $output .= '<p>' . esc_html(sprintf(_x('%s', 'site_register_desc', 'socialv'), $desc)) . '</p>';
                    }
                    break;
                case 'forgetpwd':
                    $desc = $this->socialv_option['site_forgetpwd_desc'];
                    if (!empty($desc)) {
                        $output .= '<p>' . esc_html(sprintf(_x('%s', 'site_forgetpwd_desc', 'socialv'), $desc)) . '</p>';
                    }
                    break;
                case 'resend_verify_email':
                    $desc = $this->socialv_option['resend_email_verify_desc'];
                    if (!empty($desc)) {
                        $output .= '<p>' . esc_html(sprintf(_x('%s', 'site_forgetpwd_desc', 'socialv'), $desc)) . '</p>';
                    }
                    break;
            }
        }

        $output .= '</div>';
        echo apply_filters('socialv_form_top', $output);
    }

    function get_shortcode_links($options = '')
    {
        $output = '';
        if (isset($this->socialv_option['header_display_login']) && $this->socialv_option['header_display_login'] == 'yes') {
            switch ($options) {
                case 'login':
                    $signin_link = isset($this->socialv_option['site_login_link']) ? get_page_link($this->socialv_option['site_login_link']) : '#';
                    $output .= '<div class="col-md-12 text-center"><p class="register-link">' . esc_html__("Already have an account?", "socialv") . '<a id="user_forget_form" href="' . esc_url($signin_link) . '" class="socialv-button socialv-button-link">' . esc_html__('Login', 'socialv') . '</a></p></div>';
                    break;
                case 'register':
                    $is_verifyemail = isset($this->socialv_option['registration_process']) && ($this->socialv_option['registration_process'] == 'verification_key') ? true : false;
                    if ($is_verifyemail) {
                        $is_resend_email = isset($this->socialv_option['resend_email_verify']) && ($this->socialv_option['resend_email_verify'] == '1') ? true : false;
                        if ($is_resend_email) {
                            $signin_link = isset($this->socialv_option['site_login_link']) ? get_page_link($this->socialv_option['resend_email_verify_link']) : '#';
                            $output .= '<div class="text-center register-link verification-email">' . esc_html__("Request For Activation Key?", "socialv") . '<a id="user_forget_form" href="' . esc_url($signin_link) . '">' . esc_html__('Resend', 'socialv') . '</a></div>';
                        }
                    }
                    $signup_link = isset($this->socialv_option['site_register_link']) ? get_page_link($this->socialv_option['site_register_link']) : '#';
                    $output .= '<div class="text-center register-link">' . esc_html__("Don't have an account?", 'socialv') . '<a id="user_register_form" href="' . esc_url($signup_link) . '">' . esc_html__('Sign Up', 'socialv') . '</a></div>';
                    break;
                case 'forgetpwd':
                    $signin_link = isset($this->socialv_option['site_login_link']) ? get_page_link($this->socialv_option['site_login_link']) : '#';
                    $current_page_link = get_the_ID();
                    $forget_psw_id = isset($this->socialv_option['site_forgetpwd_link']) ? $this->socialv_option['site_forgetpwd_link'] : '';
                    if ($this->socialv_option['site_login'] == 1 && $current_page_link != $forget_psw_id) {
                        $signin_link = '#loginform';
                    }
                    $output .= '<div class="col-md-12 text-center"><p class="register-link">' . esc_html__("Already have an account?", "socialv") . '<a id="user_login_form" href="' . esc_url($signin_link) . '" class="socialv-button socialv-button-link">' . esc_html__('Login', 'socialv') . '</a></p></div>';
                    break;
                case 'resend_verify_email':
                    $resend_email_link = isset($this->socialv_option['site_login_link']) ? get_page_link($this->socialv_option['site_login_link']) : '#';
                    $output .= '<div class="col-md-12 text-center"><p class="register-link">' . esc_html__("Go to Login Page?", "socialv") . '<a id="user_login_form" href="' . esc_url((isset($this->socialv_option['display_resticated_page']) && $this->socialv_option['display_resticated_page'] == 'no' && $this->socialv_option['site_login'] == 1) ? '#loginform' : $resend_email_link) . '" class="socialv-button socialv-button-link">' . esc_html__('Login', 'socialv') . '</a></p></div>';
                    break;
            }
        }
        echo apply_filters('socialv_form_bottom', $output);
    }

    function socialv_custom_login_url($login_url)
    {
        if (!is_admin()) {
            // Replace your custom links here
            $login_url = isset($this->socialv_option['site_login_link']) ? get_page_link($this->socialv_option['site_login_link']) : $login_url;
        }
        return $login_url;
    }

    // Members Page Searc
    function get_ajax_search_content($search = '')
    {
        global $wpdb;
        switch ($search) {
            case 'search_terms':
                $search_terms = isset($_POST['search_terms']) ? sanitize_text_field($_POST['search_terms']) : '';
                break;
            case 'keyword':
                $search_terms = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
                break;
        }
        $search_query = esc_sql($wpdb->esc_like($search_terms) . '%');
        // Retrieve the user IDs of the users whose display name matches the search query
        $include = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->users}
        WHERE display_name LIKE %s OR user_login LIKE %s",
                $search_query,
                $search_query
            )
        );
        return $include;
    }


    function get_socialv_social_login()
    {
        if (!empty($this->socialv_option['social_login_shortcode_text'])) {
            echo '<div class="text-center social-login-label">' . esc_html__("or", 'socialv') . '</div>';
            echo do_shortcode('' . $this->socialv_option['social_login_shortcode_text'] . '');
        }
    }
    function get_default_login_user($user = '')
    {
        if (isset($this->socialv_option['display_default_login_access']) && $this->socialv_option['display_default_login_access'] == 'yes') :
            echo esc_attr($user);
        endif;
    }


    function socialv_user_stories()
    {
        
        if (function_exists('WPSTORY') &&  !WPSTORY()->options('buddypress_users_activities')) {
            return;
        }
        if (isset($this->socialv_option['display_activity_showing_story']) && $this->socialv_option['display_activity_showing_story'] == 'on') {
            $display_form = WPSTORY()->options('buddypress_activities_form', false);
            $form_attr    = $display_form ? 'yes' : null; ?>
            <div class="wpstory-buddypress-activities">
                <?php echo do_shortcode('[wpstory-activities form="' . $form_attr . '"]'); ?>
            </div>
        <?php
        } else {
            if (bp_is_activity_directory() && bp_current_component() == "activity" && shortcode_exists('wpstory-user-single-stories')) {
                $id = get_current_user_id();
                echo do_shortcode('[wpstory-user-single-stories id="' . $id . '"]');
            }
        }
    }

    function socialv_append_global_script_vars($vars)
    {
        $vars['login_referer'] = $_GET['redirect_to'];
        return $vars;
    }
    function socialv_redirect_to_specific_page()
    {
        if (!current_user_can('manage_options') && isset($this->socialv_option['display_resticated_page']) && $this->socialv_option['display_resticated_page'] == 'yes') {
            if (function_exists('icl_object_id')) {
                $redirect_page_id = (!empty($this->socialv_option['default_page_link'])) ? icl_object_id($this->socialv_option['default_page_link'], 'page', true) : home_url();
                $page_id = icl_object_id(get_queried_object_id(), 'page', true);
            } else {
                $redirect_page_id = (!empty($this->socialv_option['default_page_link'])) ? $this->socialv_option['default_page_link'] : home_url();
                $page_id = get_queried_object_id();
            }
            global $wp;
            $page = home_url($wp->request);
            $current_page = explode("\n", $page);
            if (is_post_type_archive('lp_course')) {
                $page_id = learn_press_get_page_id('courses');
            }
            array_push($current_page, $page_id);
            $post_id = url_to_postid($page);
            $nonrestricted = isset($this->socialv_option['nonrestricted_page']) ? $this->socialv_option['nonrestricted_page'] : null;

            if (bp_current_component()) {
                array_push($current_page, bp_core_get_directory_page_id());
            }
            if (function_exists('is_shop') && is_shop()) {
                $shop_page_id = get_option('woocommerce_shop_page_id');
                if (in_array($shop_page_id, $nonrestricted, true)) {
                    array_push($current_page, $shop_page_id);
                    array_push($nonrestricted, $shop_page_id);
                }
            }

            $nonrestricted[] = $redirect_page_id;
            $nonrestricted_url = isset($this->socialv_option['nonrestricted_url']) ? $this->socialv_option['nonrestricted_url'] : null;
            $nonrestricted_url = str_replace("\n", ' ', $nonrestricted_url);
            $nonrestricted_url_array = explode(' ', $nonrestricted_url);
            $modifiedUrls = array_map(function ($url) {
                $url = trim($url);
                $url = rtrim($url, '/');
                return $url;
            }, $nonrestricted_url_array);

            $nonrestricted_post_types = isset($this->socialv_option['nonrestricted_post_types']) ? $this->socialv_option['nonrestricted_post_types'] : array();

            if (is_archive() || is_home()) {
                $archive_name = get_post_type();
                if (!empty($archive_name)) {
                    array_push($current_page, $archive_name);
                }
            }
            $is_single = is_single();
            if ($is_single) {
                $single_page_post = get_post_type();
                if (in_array($single_page_post, $nonrestricted_post_types, true)) {
                    $single_page_name = get_the_title($post_id);
                    array_push($current_page, $single_page_name);
                    array_push($nonrestricted, $single_page_name);
                }
            }


            $nonrestricted = array_merge($nonrestricted, $modifiedUrls, $nonrestricted_post_types);
            $redirect = (function_exists('pmpro_getOption') ? (is_page(array(pmpro_getOption('levels_page_id'), pmpro_getOption('checkout_page_id')))) : '');
            if (!is_user_logged_in() && !array_intersect($current_page, $nonrestricted) && !apply_filters('socialv_exclude_page', $redirect) && !bp_is_activation_page()) {
                global $wp;
                $redirect_to = home_url($wp->request);
                $show_url = isset($this->socialv_option['display_after_login_redirect']) ? $this->socialv_option['display_after_login_redirect'] : '';
                if ($show_url == 'false' && ($redirect_to == home_url())) {
                    if (function_exists('icl_object_id')) {
                        $redirect_to = !empty($this->socialv_option['display_after_login_page']) ? get_permalink(icl_object_id($this->socialv_option['display_after_login_page'], 'page', true)) : home_url();
                    } else {
                        $redirect_to = !empty($this->socialv_option['display_after_login_page']) ? get_permalink($this->socialv_option['display_after_login_page']) : home_url();
                    }
                }

                if ($_GET) {
                    $redirect_to = add_query_arg($_GET, $redirect_to);
                }

                $url = apply_filters('socialv_redirect_url', add_query_arg('redirect_to', urlencode($redirect_to), get_permalink($redirect_page_id)), $redirect_to, $redirect_page_id);
                wp_redirect($url);
                exit();
            } else {
                if (is_user_logged_in() && is_page($redirect_page_id)) {
                    wp_redirect(home_url());
                } elseif (is_post_type_archive('lp_course')) {
                    add_filter('learn-press/page-template', function ($template) {
                        $template = 'archive-course.php';
                        return $template;
                    });
                }
            }
        } else {
            if (is_post_type_archive('lp_course')) {
                add_filter('learn-press/page-template', function ($template) {
                    $template = 'archive-course.php';
                    return $template;
                });
            }
        }
    }

    function socialv_worfence_login_url_redirect()
    {
        if (class_exists('Redux') && isset($_GET['wfls-email-verification'])) {
            $redirect_page_id = $this->socialv_option['default_page_link'];
            $page_content = get_post_field('post_content', $redirect_page_id);
            if (has_shortcode($page_content, 'iqonic-login')) {
                $token = $_GET['wfls-email-verification'];
                $url = add_query_arg('wfls-email-verification', urlencode($token), get_permalink($redirect_page_id));
                wp_redirect($url);
            }
        }
    }

    function socialv_login_redirect($url, $request, $user)
    {
        if ($user && is_object($user) && is_a($user, 'WP_User')) {
            $url = ($user->has_cap('administrator')) ? admin_url() : home_url();
        }
        return $url;
    }



    function socialv_disable_dashboard()
    {
        if (!is_user_logged_in()) {
            return null;
        }
        if (current_user_can('demo-user') && is_admin() && !defined('DOING_AJAX') && !is_multisite()) {
            wp_redirect(home_url());
            exit;
        }
    }

    //Banner 
    public function socialv_bp_banner($title = '', $subtitle = '')
    {

        $subtitle = (!empty($this->socialv_option['bp_banner_subtitle_text'])) ? esc_html($this->socialv_option['bp_banner_subtitle_text']) : '';
        if (bp_is_members_directory()) :
            $title  = esc_html__('Member Directory', 'socialv');
        elseif (bp_is_groups_directory()) :
            $title  = esc_html__('Group Directory', 'socialv');
        elseif (function_exists('bbp_is_forum_archive') && is_singular(bbp_get_topic_post_type())) :
            $title  = get_the_title();
        elseif (function_exists('GamiPress') && is_post_type_archive(gamipress_get_achievement_types_slugs())) :
            $title  = esc_html__('Our Badges', 'socialv');
        elseif (function_exists('GamiPress') && is_post_type_archive(gamipress_get_rank_types_slugs())) :
            $title  = esc_html__('Our levels', 'socialv');
        elseif (is_bbpress()) :
            if (function_exists('bbp_is_forum_archive') && bbp_is_forum_archive()) :
                $title = bbp_get_forum_archive_title();
            endif;
            if (empty($title)) :
                $title  = get_the_title();
            endif;
        else :
            $title  = '';
        endif;
        $title_tag = 'h1';
        if (isset($this->socialv_option['bp_banner_title_tag'])) {
            $title_tag = $this->socialv_option['bp_banner_title_tag'];
        }

        ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="socialv-bp-banner text-start">
                        <div class="socialv-bp-banner-box">
                            <div class="heading-title white socialv-bp-banner-title">
                                <?php if (isset($this->socialv_option['bp_display_banner_title']) && $this->socialv_option['bp_display_banner_title'] == 'yes') { ?>
                                    <<?php echo esc_attr($title_tag); ?> class="title m-0">
                                        <?php echo apply_filters('socialv_bp_banner_heading', $title); ?>
                                    </<?php echo esc_attr($title_tag); ?>>
                                <?php } ?>
                                <?php if (!empty($subtitle)) : ?>
                                    <p class="socialv-subtitle mb-0">
                                        <?php echo esc_html($subtitle); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    // Breadcrumb
                    if (class_exists('bbPress') && is_bbpress()) :
                        bbp_breadcrumb();
                    endif;
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    // Model 
    public function socialv_user_profile_modal()
    {

        if (isset($this->socialv_option['site_login']) && $this->socialv_option['site_login'] == 1) { ?>
            <div class="socialv-authentication-modal">
                <div class="modal fade btn_login" id="register_modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php
                                echo (!empty($this->socialv_option['site_login_shortcode']) ? do_shortcode($this->socialv_option['site_login_shortcode']) : '');
                                echo (!empty($this->socialv_option['site_forgetpwd_shortcode']) ? do_shortcode($this->socialv_option['site_forgetpwd_shortcode']) : '');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php }
    }


    function socialv_bbp_breadcrumb_separator()
    {
        return '<i class="iconly-Arrow-Right-2 icli"></i>';
    }


    function socialv_bbp_after_get_user_favorites_link_parse_args($args)
    {
        $args['favorite'] = '<i class="iconly-Star icli" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="' . esc_attr__('Favorite', 'socialv') . '"></i>';
        $args['favorited'] = '<i class="iconly-Star icbo" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="' . esc_attr__('Unfavorite', 'socialv') . '"></i>';
        return $args;
    }

    function socialv_bbp_after_get_user_subscribe_link_parse_args($args)
    {
        $args['before'] = '';

        return $args;
    }

    function socialv_bbp_get_reply_author_avatar($reply_id = 0, $size = 40)
    {
        $reply_id = bbp_get_reply_id($reply_id);
        if (!empty($reply_id)) {
            // Check for anonymous user
            if (!bbp_is_reply_anonymous($reply_id)) {
                $author_avatar = get_avatar(bbp_get_reply_author_id($reply_id), $size, '', '', array('class' => 'rounded-circle'));
            } else {
                $author_avatar = get_avatar(get_post_meta($reply_id, '_bbp_anonymous_email', true), $size, '', '', array('class' => 'rounded-circle'));
            }
        } else {
            $author_avatar = '';
        }

        return $author_avatar;
    }

    function socialv_bbp_get_cancel_reply_to_link($retval, $link, $text)
    {
        $reply_to = isset($_GET['bbp_reply_to'])
            ? (int) $_GET['bbp_reply_to']
            : 0;

        $style  = !empty($reply_to) ? '' : ' style="display:none;"';
        $retval = sprintf('<a href="%1$s" class="btn socialv-btn-danger" id="bbp-cancel-reply-to-link"%2$s>%3$s</a>', esc_url($link), $style, esc_html($text));

        return $retval;
    }
    function socialv_custom_modify_bpolls_settings($plugin)
    {
        if ('buddypress-polls/buddypress-polls.php' === $plugin && get_option('bpolls_settings_modified') !== 'yes') {
            $bpolls_settings = get_option('bpolls_settings');
            $bpolls_settings['polls_background_color'] = 'var(--color-theme-primary)';
            update_option('bpolls_settings', $bpolls_settings);
            update_option('bpolls_settings_modified', 'yes');
        }
    }
    function socialv_pagination($total_page)
    {
        $pagination_args = array(
            'base' => @add_query_arg('paged', '%#%'),
            'format' => '&paged=%#%',
            'mid-size' => 1,
            'end_size' => 1,
            'current' => (get_query_var('paged')) ? get_query_var('paged') : 1,
            'total' => $total_page,
            'show_all' => true,
            'prev_next' => true,
            'prev_text' => __('←', 'socialv'),
            'next_text' => __('→', 'socialv'),
        );
        return paginate_links($pagination_args);
    }
    function socialv_generate_buddypres_post_type_ui($args = array())
    {
        $buddypress_data = get_plugin_data(WP_PLUGIN_DIR . '/buddypress/bp-loader.php');
        $buddypress_version = $buddypress_data['Version'];
        if (version_compare($buddypress_version, '12.0.0', '>=')) {
            $args['show_ui'] = current_user_can('manage_options');
            return $args;
        }
    }
}

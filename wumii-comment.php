<?php
/*
Plugin Name: 无觅评论插件
Plugin URI: http://wordpress.org/extend/plugins/wumii-comment-widget/
Author: Wumii Team
Version: 1.0.0.0
Author URI: http://www.wumii.com
Description: 无觅为您打造更活跃、更具互动性的评论平台，智能连接新浪微博、QQ 等社交网络，帮你消灭 0 评论，迅速提升优质评论。

Copyright 2013 wumii.com (email : team[at]wumii.com)
*/

if (!class_exists('WumiiComment')) {
    class WumiiComment {
        private $excludePostIds = array();
        
        function __construct() {
            $this->WumiiComments();
        }
        
        function WumiiComments() {}
        
        function commentTemplate($file) {
            global $post;
            $this->openDefaultCommentStatus();
            if ($this->canDisplayWumiiContent()) {
                return dirname(__FILE__) . '/comment.php';
            } 
        }
        
        private function canDisplayWumiiContent() {
            // We can identify the live-blogging post by checking if Live Blogging plugin is activated and the shortcode in the content.
            // The related items will show in such live blogging post only one time generally,
            // but if the theme call the filter on 'the_content' hook or run another 'The Loop' to fetch the post content using in some other cases before to display,
            // the related items will not show.
            // In brief, the related items show one time at most in each post.
            global $post;
            if (array_key_exists($post->ID, $this->excludePostIds)) {
                return false;
            }
            
            global $shortcode_tags; // Container for storing shortcode tags and their hook to call for the shortcode
            if (!empty($shortcode_tags) && is_array($shortcode_tags)
                    && array_key_exists('liveblog', $shortcode_tags) && strpos(get_the_content(), '[liveblog]') !== false) {
                $this->excludePostIds[$post->ID] = 1;
            }
            
            return get_post_status($post->ID) == 'publish' &&
                   get_post_type() == 'post' && // In some pages e.g. "attachment page" should not display related items
                   empty($post->post_password) &&
                   !is_preview() && // When create a new post and press the preview button before publish it,
                                    // the post's permalink is not the correct form as the setting in "Permalink".
                                    // We have to prevent the related items displaying in these pages.
                   !is_feed() &&
                   !is_page(); // In some pages e.g. "about me" also should not display.
        }
        
        function openDefaultCommentStatus() {
            if (strcmp(get_option('default_comment_status'), 'open') != 0) {
                update_option('default_comment_status', 'open');
            }
        }
    }
    
    $wumii_comment = new WumiiComment();
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    add_filter('comments_template', array($wumii_comment, 'commentTemplate'),
                   is_plugin_active('duoshuo/duoshuo.php') ? 99999 : 10);

}
?>
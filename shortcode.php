<?php

/*
  Plugin Name: Custom ShortCode
  Plugin URI: https://board.te.ua
  Description: Custom shortcode creator
  Version: 1.0
  Author: org100h
  Author URI: https://board.te.ua
  License: GPLv2
 */

class org_custom_shortcode {

    /**
     * The unique instance of the plugin.
     *
     * @var org_custom_shortcode
     */
    private static $instance = null;
    public static $post_type = "org";

    public static function get_instance() {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        //actions
        add_action('init', [$this, 'org_create_shortcode']);
        add_action("manage_posts_custom_column", [$this, "org_custom_columns"]);
        add_action('post_submitbox_misc_actions', [$this, 'org_add_shcode_post_submitbox']);

        //filter 
        add_filter("manage_edit-org_columns", [$this, "org_edit_columns"]);

        //shortcode
        add_shortcode('shortcode', [$this, 'org_content_func']);
    }

    public function org_create_shortcode() {
        register_post_type(self::$post_type, ['labels' => [
                'name' => 'Shortcodes',
                'singular_name' => 'Short Code',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Short Code',
                'edit' => 'Edit',
                'edit_item' => 'Edit Short Code',
                'new_item' => 'New Short Code',
                'view' => 'View',
                'view_item' => 'View Short Code',
                'search_items' => 'Search Short Codes',
                'not_found' => 'No Short Codes found',
                'not_found_in_trash' => 'No Short Codes found in Trash',
                'parent' => 'Parent Short Code'
            ],
            'public' => true,
            'menu_position' => 15,
            'taxonomies' => [''],
            'has_archive' => false,
			'exclude_from_search' => true,
            'publicly_queryable' => false
                ]
        );
    }

    public function org_edit_columns($columns) {
        $columns = ["cb" => "<input type=\"checkbox\" />",
            "title" => "ShortCodes Title",
            "id" => "Shortcode",
            "author" => "Author",
            "date" => "Date",
        ];
        return $columns;
    }

    public function org_custom_columns($column) {
        switch ($column) {
            case "id":
                echo '<input value=\'[shortcode id="';
                echo get_the_ID();
                echo '"]\' onClick="select()" />';
                break;
        }
    }

    public function org_content_func($atts) {
        $point_start = [];
        $point_end = [];

        extract(shortcode_atts(['id' => null,
                        ], $atts, 'org_content'));
        $post = get_post($id);
        
                //$content = ($post)?$post->post_content:'';
		if($post){
			$content = $post->post_content;
		} else {
			$content= '';
		}
        if (!empty($atts)) {
            $keys = [];
            $values = [];
            $i = 0;

            foreach ($atts as $key => $val) {
                if ($key !== 0) {
                    $keys[$i] = "%%" . $key . "%%";
                    $values[$i] = $val;
                }

                $i++;
            }
            preg_match('/%%if [^%\s]+%%/', $content, $point_start, PREG_OFFSET_CAPTURE);
            preg_match('/%%endif%%/', $content, $point_end, PREG_OFFSET_CAPTURE);
            if ($point_start && $point_end) {
                $content = $this->macro_if($point_start, $point_end, $content, $keys);
            }

            $content = preg_replace('/%%[^%\s]+%%/', '', str_replace($keys, $values, $content));
        }
        return $content;
    }

    private function macro_if($start, $end, $cnt, $keys) {

        $part = preg_replace('/%%if [^%\s]+%%/', '', substr($cnt, $start[0][1], $end[0][1] - $start[0][1]));

        /* @var $emiter type */
        $emiter = str_replace('if ', '', $start[0][0]);

        /* @var $cnt type */
        $cnt = preg_replace('/%%if [^%\s]+%%/', '', $cnt);
        if (!in_array($emiter, $keys)) {
            $cnt = str_replace($part, '', $cnt);
        }
        return $cnt;
    }

    public function org_add_shcode_post_submitbox() {
        if (get_post_type(get_the_ID()) == self::$post_type) {
            echo '<input style="margin-left:.7rem;" value=\'[shortcode id="';
            echo get_the_ID();
            echo '"]\' onClick="select()" />';
        }
    }

}

$custom_sh_plugin = org_custom_shortcode::get_instance();

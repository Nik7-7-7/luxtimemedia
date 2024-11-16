/*
* Ultimate Learning Pro - Gutenberg Integration
*/
"use strict";
var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};

registerBlockType( 'indeed-learning-pro/list-courses', {
    title                 : 'ULP - List Courses',
    icon                  : 'universal-access-alt',
    category              : 'ulp-shortcodes',

    edit: function() {
        return el( 'p', '', '[ulp-list-courses]' );
    },
    save: function() {
        return el( 'p', '', '[ulp-list-courses]' );
    },
});

registerBlockType( 'indeed-learning-pro/student-profile', {
    title                 : 'ULP - Student Profile',
    icon                  : 'universal-access-alt',
    category              : 'ulp-shortcodes',

    edit: function() {
        return el( 'p', '', '[ulp-student-profile]' );
    },
    save: function() {
        return el( 'p', '', '[ulp-student-profile]' );
    },
});

registerBlockType( 'indeed-learning-pro/become-instructor', {
    title                 : 'ULP - Become Instructor',
    icon                  : 'universal-access-alt',
    category              : 'ulp-shortcodes',

    edit: function() {
        return el( 'p', '', '[ulp-become-instructor]' );
    },
    save: function() {
        return el( 'p', '', '[ulp-become-instructor]' );
    },
});

registerBlockType( 'indeed-learning-pro/list-watch-list', {
    title                 : 'ULP - List Watch List',
    icon                  : 'universal-access-alt',
    category              : 'ulp-shortcodes',

    edit: function() {
        return el( 'p', '', '[ulp_list_watch_list]' );
    },
    save: function() {
        return el( 'p', '', '[ulp_list_watch_list]' );
    },
});

registerBlockType( 'indeed-learning-pro/checkout', {
    title                 : 'ULP - Checkout',
    icon                  : 'universal-access-alt',
    category              : 'ulp-shortcodes',

    edit: function() {
        return el( 'p', '', '[ulp_checkout]' );
    },
    save: function() {
        return el( 'p', '', '[ulp_checkout]' );
    },
});
